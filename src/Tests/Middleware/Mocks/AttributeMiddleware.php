<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Middleware\Mocks;

use Closure;
use Opulence\Api\Middleware\AttributeMiddleware as BaseAttributeMiddleware;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Mocks attribute middleware for use in tests
 */
class AttributeMiddleware extends BaseAttributeMiddleware
{
    /**
     * Gets an attribute value for test verification
     *
     * @param string $name The name of the attribute to get
     * @param mixed $default The default value if there was no attribute
     * @return mixed The attribute value
     */
    public function getAttribute(string $name, $default = null)
    {
        return parent::getAttribute($name, $default);
    }

    /**
     * @inheritdoc
     */
    public function handle(IHttpRequestMessage $request, Closure $next): IHttpResponseMessage
    {
        return $next($request);
    }
}
