<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
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
        private readonly IBodyDeserializer $bodyDeserializer = new NegotiatedBodyDeserializer(),
        private readonly UriParser $uriParser = new UriParser()
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

        if ($reflectionParameterType instanceof ReflectionNamedType && !$reflectionParameterType->isBuiltin()) {
            return $this->resolveObjectParameter(
                $reflectionParameter,
                $reflectionParameterType,
                $request
            );
        }

        if (isset($routeVariables[$reflectionParameter->getName()])) {
            return $this->resolveScalarParameter($reflectionParameter, $routeVariables[$reflectionParameter->getName()]);
        }

        if (isset($queryStringVars[$reflectionParameter->getName()])) {
            return $this->resolveScalarParameter($reflectionParameter, $queryStringVars[$reflectionParameter->getName()]);
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

    /**
     * Resolves an object parameter using content negotiator
     *
     * @param ReflectionParameter $reflectionParameter The parameter to resolve
     * @param ReflectionNamedType $type The type to resolve to
     * @param IRequest $request The current request
     * @return object|null The resolved parameter
     * @throws FailedRequestContentNegotiationException Thrown if the request content negotiation failed
     * @throws RequestBodyDeserializationException Thrown if there was an error deserializing the request body
     * @throws MissingControllerParameterValueException Thrown if there was no valid value for the parameter
     * @psalm-suppress InvalidReturnType The media type formatter will resolve to the parameter type, which will be an object
     * @psalm-suppress InvalidReturnStatement Ditto
     */
    private function resolveObjectParameter(
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
     * Resolves a scalar parameter to the correct scalar value
     *
     * @param ReflectionParameter $reflectionParameter The parameter to resolve
     * @param mixed $rawValue The raw value to convert
     * @return mixed The raw value converted to the appropriate scalar type
     * @throws FailedScalarParameterConversionException Thrown if the scalar parameter could not be converted
     */
    private function resolveScalarParameter(ReflectionParameter $reflectionParameter, mixed $rawValue): mixed
    {
        $type = $reflectionParameter->getType();
        $typeName = $type instanceof ReflectionNamedType ? $type->getName() : null;

        switch ($typeName) {
            case 'int':
                return (int)$rawValue;
            case 'float':
                return (float)$rawValue;
            case 'string':
                return (string)$rawValue;
            case 'bool':
                return (bool)$rawValue;
            case null:
                // Do not attempt to convert it
                return $rawValue;
            case 'array':
                throw new FailedScalarParameterConversionException('Cannot automatically resolve array types - you must either read the body or the query string inside the controller method');
            default:
                throw new FailedScalarParameterConversionException("Failed to convert value to $typeName");
        }
    }
}
