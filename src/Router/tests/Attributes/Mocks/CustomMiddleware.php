<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Attributes\Mocks;

use Aphiria\Routing\Attributes\Middleware;
use Attribute;

/**
 * Defines a middleware attribute that extends the middleware attribute
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class CustomMiddleware extends Middleware
{
    public function __construct()
    {
        parent::__construct(DummyMiddleware::class);
    }
}
