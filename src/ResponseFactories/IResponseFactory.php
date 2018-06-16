<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\ResponseFactories;

use Opulence\Api\RequestContext;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Defines the interface for response factories to implement
 */
interface IResponseFactory
{
    /**
     * Creates a response from a context
     *
     * @param RequestContext $requestContext The current request context
     * @return IHttpResponseMessage The created response
     */
    public function createResponse(RequestContext $requestContext): IHttpResponseMessage;
}
