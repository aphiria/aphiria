<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Discoverers\Delete;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Routing\Attributes\Get;
use Aphiria\Routing\Attributes\Middleware;

#[Middleware('foo')]
class FooController extends Controller
{
    #[Get('route')]
    #[Middleware('bar')]
    public function foo(): void {}
}
