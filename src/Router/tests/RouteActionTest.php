<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\RouteAction;
use PHPUnit\Framework\TestCase;

class RouteActionTest extends TestCase
{
    public function testClassAndMethodNamesAreSetInConstructor(): void
    {
        $action = new RouteAction('Foo', 'bar');
        $this->assertEquals('Foo', $action->className);
        $this->assertEquals('bar', $action->methodName);
    }
}
