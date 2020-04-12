<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

use Aphiria\Api\Validation\IRequestBodyValidator;
use Aphiria\Net\Http\ContentNegotiation\ContentNegotiator;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Response;
use Closure;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Defines the route action invoker
 */
final class RouteActionInvoker implements IRouteActionInvoker
{
    /** @const The name of the property to store the parsed body in */
    private const PARSED_BODY_PROPERTY_NAME = '__APHIRIA_PARSED_BODY';
    /** @var IRequestBodyValidator The validator for request bodies, or null if we aren't validating them */
    private ?IRequestBodyValidator $requestBodyValidator;
    /** @var IResponseFactory The response factory */
    private IResponseFactory $responseFactory;
    /** @var IControllerParameterResolver The controller parameter resolver to use */
    private IControllerParameterResolver $controllerParameterResolver;

    /**
     * @param IContentNegotiator|null $contentNegotiator The content negotiator, or null if using the default negotiator
     * @param IRequestBodyValidator|null $requestBodyValidator The validator for request bodies, or null if we aren't validating them
     * @param IResponseFactory|null $responseFactory The response factory
     * @param IControllerParameterResolver|null $controllerParameterResolver The controller parameter resolver to use
     */
    public function __construct(
        IContentNegotiator $contentNegotiator = null,
        IRequestBodyValidator $requestBodyValidator = null,
        IResponseFactory $responseFactory = null,
        IControllerParameterResolver $controllerParameterResolver = null
    ) {
        $this->requestBodyValidator = $requestBodyValidator;
        $contentNegotiator ??= new ContentNegotiator();
        $this->responseFactory = $responseFactory ?? new NegotiatedResponseFactory($contentNegotiator);
        $this->controllerParameterResolver = $controllerParameterResolver ?? new ControllerParameterResolver($contentNegotiator);
    }

    /**
     * @inheritdoc
     */
    public function invokeRouteAction(
        callable $routeActionDelegate,
        IHttpRequestMessage $request,
        array $routeVariables
    ): IHttpResponseMessage {
        try {
            if (\is_array($routeActionDelegate)) {
                $reflectionFunction = new ReflectionMethod($routeActionDelegate[0], $routeActionDelegate[1]);

                if (!$reflectionFunction->isPublic()) {
                    throw new HttpException(
                        HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                        sprintf(
                            'Controller method %s must be public',
                            self::getRouteActionDisplayName($routeActionDelegate)
                        )
                    );
                }
            } else {
                $reflectionFunction = new ReflectionFunction($routeActionDelegate);
            }
        } catch (ReflectionException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                sprintf(
                    'Reflection failed for %s',
                    self::getRouteActionDisplayName($routeActionDelegate)
                ),
                0,
                $ex
            );
        }

        $resolvedParameters = [];

        try {
            foreach ($reflectionFunction->getParameters() as $reflectionParameter) {
                $resolvedParameter = $this->controllerParameterResolver->resolveParameter(
                    $reflectionParameter,
                    $request,
                    $routeVariables
                );

                if ($this->requestBodyValidator !== null) {
                    $this->requestBodyValidator->validate($request, $resolvedParameter);
                }

                $resolvedParameters[] = $resolvedParameter;

                if (\is_object($resolvedParameter)) {
                    $request->getProperties()->add(self::PARSED_BODY_PROPERTY_NAME, $resolvedParameter);
                }
            }
        } catch (MissingControllerParameterValueException | FailedScalarParameterConversionException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_BAD_REQUEST,
                'Failed to invoke ' . self::getRouteActionDisplayName($routeActionDelegate),
                0,
                $ex
            );
        } catch (FailedRequestContentNegotiationException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_UNSUPPORTED_MEDIA_TYPE,
                'Failed to invoke ' . self::getRouteActionDisplayName($routeActionDelegate),
                0,
                $ex
            );
        } catch (RequestBodyDeserializationException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_UNPROCESSABLE_ENTITY,
                'Failed to invoke ' . self::getRouteActionDisplayName($routeActionDelegate),
                0,
                $ex
            );
        }

        $actionResult = $routeActionDelegate(...$resolvedParameters);

        if ($actionResult instanceof IHttpResponseMessage) {
            return $actionResult;
        }

        // Handle void return types
        if ($actionResult === null) {
            return new Response(HttpStatusCodes::HTTP_NO_CONTENT);
        }

        // Attempt to create an OK response from the return value
        return $this->responseFactory->createResponse(
            $request,
            HttpStatusCodes::HTTP_OK,
            null,
            $actionResult
        );
    }

    /**
     * Gets the display name for a route action for use in exception messages
     *
     * @param callable $routeActionDelegate The route action delegate whose display name we want
     * @return string The route action display name
     */
    private static function getRouteActionDisplayName(callable $routeActionDelegate): string
    {
        if (\is_array($routeActionDelegate)) {
            if (\is_string($routeActionDelegate[0])) {
                return $routeActionDelegate[0];
            }

            return \get_class($routeActionDelegate[0]) . '::' . $routeActionDelegate[1];
        }

        return Closure::class;
    }
}
