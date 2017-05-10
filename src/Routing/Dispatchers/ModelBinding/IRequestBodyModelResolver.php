<?php
namespace Opulence\Routing\Dispatchers\ModelBinding;

use InvalidArgumentException;

/**
 * Defines the interface for HTTP request body model resolvers to implement
 */
interface IRequestBodyModelResolver
{
    /**
     * Resolves the HTTP request body as a model
     *
     * @param string $rawBody The raw body of the HTTP request
     * @param string $contentType The content type of the body
     * @returns mixed An instance of the model
     * @throws InvalidArgumentException Thrown if the model could not be resolved
     */
    public function resolveModel(string $rawBody, string $contentType);
}
