<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

namespace Aphiria\Api\Controllers;

use Aphiria\Net\Formatting\UriParser;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Serialization\SerializationException;
use ReflectionParameter;

/**
 * Defines the default controller parameter resolver
 */
final class ControllerParameterResolver implements IControllerParameterResolver
{
    /** @var IContentNegotiator The content negotiator */
    private $contentNegotiator;
    /** @var UriParser The URI parser to use */
    private $uriParser;

    /**
     * @param IContentNegotiator $contentNegotiator The content negotiator
     * @param UriParser $uriParser The URI parser to use
     */
    public function __construct(IContentNegotiator $contentNegotiator, UriParser $uriParser = null)
    {
        $this->contentNegotiator = $contentNegotiator;
        $this->uriParser = $uriParser ?? new UriParser();
    }

    /**
     * @inheritdoc
     */
    public function resolveParameter(
        ReflectionParameter $reflectionParameter,
        IHttpRequestMessage $request,
        array $routeVariables
    ) {
        $queryStringVars = $this->uriParser->parseQueryString($request->getUri());

        if ($reflectionParameter->getClass() !== null) {
            return $this->resolveObjectParameter(
                $reflectionParameter,
                $request
            );
        }

        if (isset($routeVariables[$reflectionParameter->getName()])) {
            return $routeVariables[$reflectionParameter->getName()];
        }

        if (isset($queryStringVars[$reflectionParameter->getName()])) {
            return $queryStringVars[$reflectionParameter->getName()];
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
     * @param IHttpRequestMessage $request The current request
     * @return \object|null The resolved parameter
     * @throws FailedRequestContentNegotiationException Thrown if the request content negotiation failed
     * @throws MissingControllerParameterValueException Thrown if there was no valid value for the parameter
     * @throws RequestBodyDeserializationException Thrown if the request body could not be deserialized
     */
    private function resolveObjectParameter(
        ReflectionParameter $reflectionParameter,
        IHttpRequestMessage $request
    ): ?object {
        if ($request->getBody() === null) {
            if (!$reflectionParameter->allowsNull()) {
                throw new MissingControllerParameterValueException(
                    "Body is null when resolving parameter {$reflectionParameter->getName()}"
                );
            }

            return null;
        }

        $type = $reflectionParameter->getType();
        $requestContentNegotiationResult = $this->contentNegotiator->negotiateRequestContent(
            $type,
            $request
        );
        $mediaTypeFormatter = $requestContentNegotiationResult->getFormatter();

        if ($mediaTypeFormatter === null) {
            if (!$reflectionParameter->allowsNull()) {
                throw new FailedRequestContentNegotiationException(
                    "Failed to negotiate request content with type $type"
                );
            }

            return null;
        }

        try {
            return $mediaTypeFormatter
                ->readFromStream($request->getBody()->readAsStream(), $type);
        } catch (SerializationException $ex) {
            if (!$reflectionParameter->allowsNull()) {
                throw new RequestBodyDeserializationException(
                    "Failed to deserialize request body when resolving parameter {$reflectionParameter->getName()}",
                    0,
                    $ex
                );
            }

            return null;
        }
    }
}
