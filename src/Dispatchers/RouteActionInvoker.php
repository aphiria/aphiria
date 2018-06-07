<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Dispatchers;

use Opulence\Api\ControllerContext;
use Opulence\Api\FailedContentNegotiationException;
use Opulence\Net\Formatting\UriParser;
use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Response;
use Opulence\Routing\Matchers\RouteAction;
use Opulence\Serialization\SerializationException;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;

/**
 * Defines the route invoker
 */
class RouteActionInvoker implements IRouteActionInvoker
{
    /** @var UriParser The URI parser to use */
    private $uriParser;

    /**
     * @param UriParser $uriParser The URI parser to use
     */
    public function __construct(UriParser $uriParser = null)
    {
        $this->uriParser = $uriParser ?? new UriParser();
    }

    /**
     * @inheritdoc
     */
    public function invokeRouteAction(ControllerContext $controllerContext): IHttpResponseMessage
    {
        $request = $controllerContext->getRequest();
        $requestContentNegotiationResult = $controllerContext->getRequestContentNegotiationResult();
        $routeVars = $controllerContext->getMatchedRoute()->getRouteVars();
        $queryStringVars = $this->uriParser->parseQueryString($request->getUri());
        $routeAction = $controllerContext->getMatchedRoute()->getAction();

        try {
            $reflectionMethod = new ReflectionMethod($routeAction->getClassName(), $routeAction->getMethodName());
        } catch (ReflectionException $ex) {
            throw new RuntimeException(
                sprintf(
                    'Reflection failed for %s',
                    $this->getRouteActionDisplayName($routeAction)
                ),
                0,
                $ex
            );
        }

        if (!$reflectionMethod->isPublic()) {
            throw new RuntimeException(
                sprintf(
                    'Controller method %s must be public',
                    $this->getRouteActionDisplayName($routeAction)
                )
            );
        }

        $resolvedParameters = [];

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            if ($reflectionParameter->getClass() !== null) {
                $resolvedParameters[] = $this->resolveObjectParameter(
                    $reflectionParameter,
                    $request,
                    $requestContentNegotiationResult,
                    $routeAction
                );
            } elseif (isset($routeVars[$reflectionParameter->getName()])) {
                $resolvedParameters[] = $routeVars[$reflectionParameter->getName()];
            } elseif (isset($queryStringVars[$reflectionParameter->getName()])) {
                $resolvedParameters[] = $queryStringVars[$reflectionParameter->getName()];
            } elseif ($reflectionParameter->isDefaultValueAvailable()) {
                $resolvedParameters[] = $reflectionParameter->getDefaultValue();
            } elseif ($reflectionParameter->allowsNull()) {
                $resolvedParameters[] = null;
            } else {
                throw new RuntimeException(
                    sprintf(
                        'Failed to resolve parameter %s when invoking %s',
                        $reflectionParameter->getName(),
                        $this->getRouteActionDisplayName($routeAction)
                    )
                );
            }
        }

        $response = $controllerContext->getController()->{$routeAction->getMethodName()}(...$resolvedParameters);

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

    /**
     * Resolves an object parameter
     *
     * @param ReflectionParameter $reflectionParameter The parameter to resolve
     * @param IHttpRequestMessage $request The current request
     * @param ContentNegotiationResult|null $requestContentNegotiationResult The request content negotiation result
     * @param RouteAction $routeAction The matched route action
     * @return \object|null The resolved parameter
     * @throws RuntimeException Thrown if the parameter could not be resolved
     * @throws FailedContentNegotiationException Thrown if the request content could not be negotiated
     */
    private function resolveObjectParameter(
        ReflectionParameter $reflectionParameter,
        IHttpRequestMessage $request,
        ?ContentNegotiationResult $requestContentNegotiationResult,
        RouteAction $routeAction
    ): ?object {
        if ($request->getBody() === null) {
            if (!$reflectionParameter->allowsNull()) {
                throw new RuntimeException(
                    sprintf(
                        'Missing request body when invoking %s',
                        $this->getRouteActionDisplayName($routeAction)
                    )
                );
            }

            return null;
        }

        if ($requestContentNegotiationResult === null) {
            if (!$reflectionParameter->allowsNull()) {
                throw new FailedContentNegotiationException(
                    sprintf(
                        'Failed to negotiation content when invoking %s',
                        $this->getRouteActionDisplayName($routeAction)
                    )
                );
            }

            return null;
        }

        try {
            return $requestContentNegotiationResult->getFormatter()
                ->readFromStream($request->getBody()->readAsStream(), $reflectionParameter->getType());
        } catch (SerializationException $ex) {
            if (!$reflectionParameter->allowsNull()) {
                throw new RuntimeException(
                    sprintf(
                        'Failed to resolve parameter %s when invoking %s',
                        $reflectionParameter->getName(),
                        $this->getRouteActionDisplayName($routeAction)
                    ),
                    0,
                    $ex
                );
            }

            return null;
        }
    }
}
