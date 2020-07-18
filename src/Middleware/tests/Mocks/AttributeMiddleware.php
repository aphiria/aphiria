<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Tests\Mocks;

use Aphiria\Middleware\AttributeMiddleware as BaseAttributeMiddleware;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;

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
    public function handle(IRequest $request, IRequestHandler $next): IResponse
    {
        return $next->handle($request);
    }
}
