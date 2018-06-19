<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\ResponseFactories;

use InvalidArgumentException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Uri;

/**
 * Defines a 302 response factory
 */
class FoundResponseFactory extends ResponseFactory
{
    /**
     * @inheritdoc
     * @param string|uri $uri The URI to redirect to
     */
    public function __construct($uri, HttpHeaders $headers = null, $body = null)
    {
        if (\is_string($uri)) {
            $uriString = $uri;
        } elseif ($uri instanceof Uri) {
            $uriString = (string)$uri;
        } else {
            throw new InvalidArgumentException('URI must be a string or instance of ' . Uri::class);
        }

        parent::__construct(HttpStatusCodes::HTTP_FOUND, $headers, $body);

        $this->headers->add('Location', $uriString);
    }
}
