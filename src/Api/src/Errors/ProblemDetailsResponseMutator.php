<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Errors;

use Aphiria\Net\Http\IHttpResponseMessage;

/**
 * Defines the mutator that can convert a response to a problem details response
 */
final class ProblemDetailsResponseMutator
{
    /**
     * Mutates a response so that it properly represents a problem details response
     *
     * @param IHttpResponseMessage $response The response to mutate
     * @return IHttpResponseMessage The mutated response
     * @link https://tools.ietf.org/html/rfc7807
     */
    public function mutateResponse(IHttpResponseMessage $response): IHttpResponseMessage
    {
        $mutatedResponse = clone $response;
        $contentType = null;

        if (!$mutatedResponse->getHeaders()->tryGetFirst('Content-Type', $contentType)) {
            return $mutatedResponse;
        }

        switch ($contentType) {
            case 'application/json':
            case 'text/json':
                $mutatedResponse->getHeaders()->add('Content-Type', 'application/problem+json');
                break;
            case 'application/xml':
            case 'text/xml':
                $mutatedResponse->getHeaders()->add('Content-Type', 'application/problem+xml');
                break;
        }

        return $mutatedResponse;
    }
}
