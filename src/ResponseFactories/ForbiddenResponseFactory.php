<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\ResponseFactories;

use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;

/**
 * Defines a 403 response factory
 */
class ForbiddenResponseFactory extends ResponseFactory
{
    /**
     * @inheritdoc
     */
    public function __construct(HttpHeaders $headers = null, $body = null)
    {
        parent::__construct(HttpStatusCodes::HTTP_FORBIDDEN, $headers, $body);
    }
}
