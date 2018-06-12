<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Handlers;

use Opulence\Api\ControllerContext;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Response;
use Opulence\Routing\RouteAction;
use ReflectionException;
use ReflectionMethod;

/**
 * Defines the reflection route action invoker
 */
class ReflectionRouteActionInvoker implements IRouteActionInvoker
{
    /** @var IControllerParameterResolver The controller parameter resolver to use */
    private $controllerParameterResolver;

    /**
     * @param IControllerParameterResolver|null $controllerParameterResolver The controller parameter resolver to use
     */
    public function __construct(IControllerParameterResolver $controllerParameterResolver = null)
    {
        $this->controllerParameterResolver = $controllerParameterResolver ?? new ControllerParameterResolver();
    }

    /**
     * @inheritdoc
     */
    public function invokeRouteAction(ControllerContext $controllerContext): IHttpResponseMessage
    {
        $routeAction = $controllerContext->getMatchedRoute()->getAction();

        try {
            $reflectionMethod = new ReflectionMethod($routeAction->getClassName(), $routeAction->getMethodName());
        } catch (ReflectionException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                sprintf(
                    'Reflection failed for %s',
                    $this->getRouteActionDisplayName($routeAction)
                ),
                null,
                0,
                $ex
            );
        }

        if (!$reflectionMethod->isPublic()) {
            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                sprintf(
                    'Controller method %s must be public',
                    $this->getRouteActionDisplayName($routeAction)
                )
            );
        }

        $resolvedParameters = [];

        try {
            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $resolvedParameters[] = $this->controllerParameterResolver->resolveParameter(
                    $reflectionParameter,
                    $controllerContext
                );
            }
        } catch (MissingControllerParameterValueException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_BAD_REQUEST,
                "Failed to invoke {$this->getRouteActionDisplayName($routeAction)}",
                null,
                0,
                $ex
            );
        } catch (FailedRequestContentNegotiationException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_UNSUPPORTED_MEDIA_TYPE,
                "Failed to invoke {$this->getRouteActionDisplayName($routeAction)}",
                null,
                0,
                $ex
            );
        } catch (RequestBodyDeserializationException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_UNPROCESSABLE_ENTITY,
                "Failed to invoke {$this->getRouteActionDisplayName($routeAction)}",
                null,
                0,
                $ex
            );
        }

        $response = $controllerContext->getController()->{$routeAction->getMethodName()}(...$resolvedParameters);

        // Handle void return types
        return $response ?? new Response(HttpStatusCodes::HTTP_NO_CONTENT);
    }

    /**
     * Gets the display name for a route action for use in exception messages
     *
     * @param RouteAction $routeAction The route action whose display name we want
     * @return string The route action display name
     */
    private function getRouteActionDisplayName(RouteAction $routeAction): string
    {
        return "{$routeAction->getClassName()}::{$routeAction->getMethodName()}";
    }
}
