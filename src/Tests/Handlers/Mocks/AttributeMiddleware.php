<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Handlers\Mocks;

use Closure;
use Opulence\Api\Middleware\AttributeMiddleware as BaseAttributeMiddleware;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Mocks attribute middlware for use in tests
 */
class AttributeMiddleware extends BaseAttributeMiddleware
{
    /**
     * Gets an attribute value for test verification
     *
     * @return mixed The attribute value
     */
    public function getAttribute(string $name, $default = null)
    {
        return parent::getAttribute($name, $default);
    }

    public function handle(IHttpRequestMessage $request, Closure $next): IHttpResponseMessage
    {
        return $next($request);
    }
}
