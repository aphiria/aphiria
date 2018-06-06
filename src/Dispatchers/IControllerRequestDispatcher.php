<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Dispatchers;

use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Defines the interface for controller request dispatchers to implement
 */
interface IControllerRequestDispatcher
{
    /**
     * Dispatches a request to a controller
     *
     * @param IHttpRequestMessage $request The incoming request
     * @return IHttpResponseMessage The response
     */
    public function dispatchRequest(IHttpRequestMessage $request): IHttpResponseMessage;
}
