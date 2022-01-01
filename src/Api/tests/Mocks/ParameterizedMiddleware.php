<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Mocks;

use Aphiria\Middleware\ParameterizedMiddleware as BaseAttributeMiddleware;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;

/**
 * Mocks attribute middleware for use in tests
 */
class ParameterizedMiddleware extends BaseAttributeMiddleware
{
    /**
     * Gets an attribute value for test verification
     *
     * @param string $name The name of the attribute to get
     * @param mixed $default The default value if there was no attribute
     * @return mixed The attribute value
     */
    public function getParameter(string $name, mixed $default = null): mixed
    {
        return parent::getParameter($name, $default);
    }

    /**
     * @inheritdoc
     */
    public function handle(IRequest $request, IRequestHandler $next): IResponse
    {
        return $next->handle($request);
    }
}
