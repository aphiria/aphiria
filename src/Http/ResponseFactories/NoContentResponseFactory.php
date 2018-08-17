<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\ResponseFactories;

use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;

/**
 * Defines a 204 response factory
 */
class NoContentResponseFactory extends ResponseFactory
{
    /**
     * @inheritdoc
     */
    public function __construct(HttpHeaders $headers = null)
    {
        parent::__construct(HttpStatusCodes::HTTP_NO_CONTENT, $headers, null);
    }
}
