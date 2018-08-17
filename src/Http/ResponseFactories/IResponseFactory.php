<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\ResponseFactories;

use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\RequestContext;

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
     * @throws HttpException Thrown if there was an error creating the response
     */
    public function createResponse(RequestContext $requestContext): IHttpResponseMessage;
}
