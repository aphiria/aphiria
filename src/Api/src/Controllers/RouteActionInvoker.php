<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

use Aphiria\Api\Validation\IRequestBodyValidator;
use Aphiria\ContentNegotiation\ContentNegotiator;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Response;
use Closure;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Defines the route action invoker
 */
class RouteActionInvoker implements IRouteActionInvoker
{
    /** @const The name of the property to store the parsed body in */
    private const PARSED_BODY_PROPERTY_NAME = '__APHIRIA_PARSED_BODY';
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
        private ?IRequestBodyValidator $requestBodyValidator = null,
        IResponseFactory $responseFactory = null,
        IControllerParameterResolver $controllerParameterResolver = null
    ) {
        $contentNegotiator ??= new ContentNegotiator();
        $this->responseFactory = $responseFactory ?? new NegotiatedResponseFactory($contentNegotiator);
        $this->controllerParameterResolver = $controllerParameterResolver ?? new ControllerParameterResolver($contentNegotiator);
    }

    /**
     * @inheritdoc
     */
    public function invokeRouteAction(
        callable $routeActionDelegate,
        IRequest $request,
        array $routeVariables
    ): IResponse {
        try {
            $reflectionFunction = $this->reflectRouteActionDelegate($routeActionDelegate);
        } catch (ReflectionException $ex) {
            throw new HttpException(
                HttpStatusCodes::INTERNAL_SERVER_ERROR,
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
                /** @psalm-suppress MixedAssignment The resolved parameter could legitimately be mixed */
                $resolvedParameter = $this->controllerParameterResolver->resolveParameter(
                    $reflectionParameter,
                    $request,
                    $routeVariables
                );

                if ($this->requestBodyValidator !== null) {
                    $this->requestBodyValidator->validate($request, $resolvedParameter);
                }

                /** @psalm-suppress MixedAssignment The resolved parameter could legitimately be mixed */
                $resolvedParameters[] = $resolvedParameter;

                if (\is_object($resolvedParameter)) {
                    $request->getProperties()->add(self::PARSED_BODY_PROPERTY_NAME, $resolvedParameter);
                }
            }
        } catch (MissingControllerParameterValueException | FailedScalarParameterConversionException $ex) {
            throw new HttpException(
                HttpStatusCodes::BAD_REQUEST,
                'Failed to invoke ' . self::getRouteActionDisplayName($routeActionDelegate),
                0,
                $ex
            );
        } catch (FailedRequestContentNegotiationException $ex) {
            throw new HttpException(
                HttpStatusCodes::UNSUPPORTED_MEDIA_TYPE,
                'Failed to invoke ' . self::getRouteActionDisplayName($routeActionDelegate),
                0,
                $ex
            );
        } catch (RequestBodyDeserializationException $ex) {
            throw new HttpException(
                HttpStatusCodes::UNPROCESSABLE_ENTITY,
                'Failed to invoke ' . self::getRouteActionDisplayName($routeActionDelegate),
                0,
                $ex
            );
        }

        /** @var array|float|int|object|string|null $actionResult */
        $actionResult = $routeActionDelegate(...$resolvedParameters);

        if ($actionResult instanceof IResponse) {
            return $actionResult;
        }

        // Handle void return types
        if ($actionResult === null) {
            return new Response(HttpStatusCodes::NO_CONTENT);
        }

        // Attempt to create an OK response from the return value
        return $this->responseFactory->createResponse(
            $request,
            HttpStatusCodes::OK,
            null,
            $actionResult
        );
    }

    /**
     * Reflects a route action delegate
     * Note: This is split out primarily for testability
     *
     * @param callable $routeActionDelegate The route action delegate to reflect
     * @return ReflectionFunctionAbstract The reflected method/function
     * @throws ReflectionException Thrown if there was an error reflecting the delegate
     */
    protected function reflectRouteActionDelegate(callable $routeActionDelegate): ReflectionFunctionAbstract
    {
        if (\is_array($routeActionDelegate)) {
            return new ReflectionMethod($routeActionDelegate[0], $routeActionDelegate[1]);
        }

        /** @psalm-suppress ArgumentTypeCoercion Psalm is being a little strict here with what's allowed */
        return new ReflectionFunction($routeActionDelegate);
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
                return $routeActionDelegate[0] . '::' . $routeActionDelegate[1];
            }

            return $routeActionDelegate[0]::class . '::' . $routeActionDelegate[1];
        }

        return Closure::class;
    }
}
