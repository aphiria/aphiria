<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Dispatchers;

use Closure;
use Opulence\Net\Http\IHttpRequestMessage;

use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Defines the interface for closure invokers to implement
 */
interface IClosureInvoker
{
    /**
     * Invokes a closure
     *
     * @param Closure $closure The closure to invoke
     * @param IHttpRequestMessage $request The current request
     * @param MatchedRoute $matchedRoute The matched route
     * @return IHttpResponseMessage The response
     */
    public function invokeClosure(
        Closure $closure,
        IHttpRequestMessage $request,
        MatchedRoute $matchedRoute
    ): IHttpResponseMessage;
}
