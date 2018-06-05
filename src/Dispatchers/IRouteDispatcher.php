<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Dispatchers;

use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Routing\Matchers\MatchedRoute;

/**
 * Defines the interface for route dispatchers to implement
 */
interface IRouteDispatcher
{
    /**
     * Dispatches a matched route
     *
     * @param MatchedRoute $matchedRoute The matched route
     * @param IHttpRequestMessage $request The incoming request
     * @return IHttpResponseMessage The response
     */
    public function dispatchRoute(MatchedRoute $matchedRoute, IHttpRequestMessage $request): IHttpResponseMessage;
}
