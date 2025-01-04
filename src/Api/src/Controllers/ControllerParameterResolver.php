<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

use Aphiria\ContentNegotiation\FailedContentNegotiationException;
use Aphiria\ContentNegotiation\IBodyDeserializer;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\ContentNegotiation\NegotiatedBodyDeserializer;
use Aphiria\Net\Formatting\UriParser;
use Aphiria\Net\Http\IRequest;
use Aphiria\Routing\Attributes\Header;
use Aphiria\Routing\Attributes\QueryString;
use Aphiria\Routing\Attributes\RouteVariable;
use Closure;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Defines the default controller parameter resolver
 */
final class ControllerParameterResolver implements IControllerParameterResolver
{
    /**
     * @param IBodyDeserializer $bodyDeserializer The body negotiator
     * @param UriParser $uriParser The URI parser to use
     */
    public function __construct(
        private readonly IBodyDeserializer             $bodyDeserializer = new NegotiatedBodyDeserializer(),
        private readonly IRequestParameterDeserializer $routeActionParameterDeserializer = new RequestParameterDeserializer(),
        private readonly UriParser                     $uriParser = new UriParser()
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolveParameter(
        ReflectionParameter $reflectionParameter,
        IRequest $request,
        array $routeVariables
    ): mixed {
        $queryStringVars = $this->uriParser->parseQueryString($request->uri);
        $reflectionParameterType = $reflectionParameter->getType();

        // Try to resolve an object parameter
        if ($reflectionParameterType instanceof ReflectionNamedType && !$reflectionParameterType->isBuiltin()) {
            return $this->resolveRequestBody(
                $reflectionParameter,
                $reflectionParameterType,
                $request
            );
        }

        // If this uses the #[RouteVariable] attribute, resolve it from the route
        if (\count($routeVariableAttributes = $reflectionParameter->getAttributes(RouteVariable::class)) === 1) {
            $parameterName = $routeVariableAttributes[0]->newInstance()->name ?? $reflectionParameter->getName();

            return $this->resolveRequestParameters(
                fn (): bool => isset($routeVariables[$parameterName]),
                fn (): mixed => $routeVariables[$parameterName],
                $reflectionParameter
            );
        }

        // If this uses the #[QueryString] attribute, resolve it from the query string
        if (\count($queryStringAttributes = $reflectionParameter->getAttributes(QueryString::class)) === 1) {
            $parameterName = $queryStringAttributes[0]->newInstance()->name ?? $reflectionParameter->getName();

            return $this->resolveRequestParameters(
                fn (): bool => isset($queryStringVars[$parameterName]),
                fn (): mixed => $queryStringVars[$parameterName],
                $reflectionParameter
            );
        }

        // If this uses the #[Header] attribute, resolve it from the headers
        if (\count($headerAttributes = $reflectionParameter->getAttributes(Header::class)) === 1) {
            $parameterName = $headerAttributes[0]->newInstance()->name ?? $reflectionParameter->getName();

            return $this->resolveRequestParameters(
                fn (): bool => isset($request->headers[$parameterName]),
                fn (): mixed => $request->headers->getFirst($parameterName),
                $reflectionParameter
            );
        }

        // No attributes for where to resolve the value from, so check the route
        if (isset($routeVariables[$reflectionParameter->getName()])) {
            return $this->resolveRequestParameters(
                fn (): bool => isset($routeVariables[$reflectionParameter->getName()]),
                fn (): mixed => $routeVariables[$reflectionParameter->getName()],
                $reflectionParameter
            );
        }

        // No attributes for where to resolve the value from, so now check the query string
        if (isset($queryStringVars[$reflectionParameter->getName()])) {
            return $this->resolveRequestParameters(
                fn (): bool => isset($queryStringVars[$reflectionParameter->getName()]),
                fn (): mixed => $queryStringVars[$reflectionParameter->getName()],
                $reflectionParameter
            );
        }

        // We could not resolve this parameter, so try doing it with default values
        return $this->resolveRequestParameters(
            fn (): bool => false,
            fn (): null => null,
            $reflectionParameter
        );
    }

    /**
     * Resolves the request body using content negotiator
     *
     * @param ReflectionParameter $reflectionParameter The parameter to resolve
     * @param ReflectionNamedType $type The type to resolve to
     * @param IRequest $request The current request
     * @return object|null The resolved request body
     * @throws FailedRequestContentNegotiationException Thrown if the request content negotiation failed
     * @throws RequestBodyDeserializationException Thrown if there was an error deserializing the request body
     * @throws MissingControllerParameterValueException Thrown if there was no valid value for the parameter
     * @psalm-suppress InvalidReturnType The media type formatter will resolve to the parameter type, which will be an object
     * @psalm-suppress InvalidReturnStatement Ditto
     */
    private function resolveRequestBody(
        ReflectionParameter $reflectionParameter,
        ReflectionNamedType $type,
        IRequest $request
    ): ?object {
        try {
            $deserializedBody = $this->bodyDeserializer->readRequestBodyAs($type->getName(), $request);

            if ($deserializedBody === null && !$reflectionParameter->allowsNull()) {
                throw new MissingControllerParameterValueException(
                    "Body is null when resolving parameter {$reflectionParameter->getName()}"
                );
            }

            return $deserializedBody;
        } catch (FailedContentNegotiationException $ex) {
            if ($reflectionParameter->allowsNull()) {
                return null;
            }

            throw new FailedRequestContentNegotiationException(
                "Failed to negotiate request content with type $type"
            );
        } catch (SerializationException $ex) {
            if ($reflectionParameter->allowsNull()) {
                return null;
            }

            throw new RequestBodyDeserializationException(
                "Failed to deserialize request body when resolving parameter {$reflectionParameter->getName()}",
                0,
                $ex
            );
        }
    }

    /**
     * Resolves request parameters
     *
     * @param Closure(): bool $issetClosure The closure that returns whether or not the value can be resolved from a source
     * @param Closure(): mixed $getClosure The closure that returns the value from a source
     * @param ReflectionParameter $reflectionParameter The parameter to resolve
     * @return mixed The resolved request parameter value
     * @throws FailedRequestParameterConversionException Thrown if the request parameter could not be converted
     * @throws MissingControllerParameterValueException Thrown if there was no valid value for the parameter
     */
    private function resolveRequestParameters(
        Closure $issetClosure,
        Closure $getClosure,
        ReflectionParameter $reflectionParameter
    ): mixed {
        if ($issetClosure()) {
            $rawValue = $getClosure();
            $type = $reflectionParameter->getType() instanceof ReflectionNamedType
                ? $reflectionParameter->getType()->getName()
                : null;

            if ($type === 'array') {
                throw new FailedRequestParameterConversionException('Cannot automatically resolve array types - you must either read the body or the query string inside the controller method');
            }

            if ($type === null) {
                return $rawValue;
            }

            return $this->routeActionParameterDeserializer->deserializeRouteActionParameter($type, $rawValue);
        }

        if ($reflectionParameter->isDefaultValueAvailable()) {
            return $reflectionParameter->getDefaultValue();
        }

        if ($reflectionParameter->allowsNull()) {
            return null;
        }

        throw new MissingControllerParameterValueException(
            "No valid value for parameter {$reflectionParameter->getName()}"
        );
    }
}
