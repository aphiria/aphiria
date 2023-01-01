<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\RouteAction;
use PHPUnit\Framework\TestCase;

class RouteActionTest extends TestCase
{
    public function testClassAndMethodNamesAreSetInConstructor(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $action = new RouteAction($controller::class, 'bar');
        $this->assertSame($controller::class, $action->className);
        $this->assertSame('bar', $action->methodName);
    }
}
