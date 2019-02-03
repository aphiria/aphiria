<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

namespace Aphiria\Api\Controllers;

use Closure;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Aphiria\Net\Http\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\Response;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Defines the route action invoker
 */
class RouteActionInvoker implements IRouteActionInvoker
{
    /** @var INegotiatedResponseFactory The negotiated response factory */
    private $negotiatedResponseFactory;
    /** @var IControllerParameterResolver The controller parameter resolver to use */
    private $controllerParameterResolver;

    /**
     * @param IContentNegotiator $contentNegotiator The content negotiator
     * @param INegotiatedResponseFactory|null $negotiatedResponseFactory The negotiated response factory
     * @param IControllerParameterResolver|null $controllerParameterResolver The controller parameter resolver to use
     */
    public function __construct(
        IContentNegotiator $contentNegotiator,
        INegotiatedResponseFactory $negotiatedResponseFactory = null,
        IControllerParameterResolver $controllerParameterResolver = null
    ) {
        $this->negotiatedResponseFactory = $negotiatedResponseFactory ?? new NegotiatedResponseFactory($contentNegotiator);
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
                            $this->getRouteActionDisplayName($routeActionDelegate)
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
                    $this->getRouteActionDisplayName($routeActionDelegate)
                ),
                0,
                $ex
            );
        }

        $resolvedParameters = [];

        try {
            foreach ($reflectionFunction->getParameters() as $reflectionParameter) {
                $resolvedParameters[] = $this->controllerParameterResolver->resolveParameter(
                    $reflectionParameter,
                    $request,
                    $routeVariables
                );
            }
        } catch (MissingControllerParameterValueException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_BAD_REQUEST,
                "Failed to invoke {$this->getRouteActionDisplayName($routeActionDelegate)}",
                0,
                $ex
            );
        } catch (FailedRequestContentNegotiationException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_UNSUPPORTED_MEDIA_TYPE,
                "Failed to invoke {$this->getRouteActionDisplayName($routeActionDelegate)}",
                0,
                $ex
            );
        } catch (RequestBodyDeserializationException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_UNPROCESSABLE_ENTITY,
                "Failed to invoke {$this->getRouteActionDisplayName($routeActionDelegate)}",
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
        return $this->negotiatedResponseFactory->createResponse(
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
    private function getRouteActionDisplayName(callable $routeActionDelegate): string
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
