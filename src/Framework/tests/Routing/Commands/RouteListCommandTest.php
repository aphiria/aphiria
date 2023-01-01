<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Routing\Commands;

use Aphiria\Framework\Routing\Commands\RouteListCommand;
use PHPUnit\Framework\TestCase;

class RouteListCommandTest extends TestCase
{
    public function testCorrectValuesAreSetInConstructor(): void
    {
        $command = new RouteListCommand();
        $this->assertSame('route:list', $command->name);
        $this->assertSame('Lists the routes in your app', $command->description);
        $this->assertCount(2, $command->options);
        $this->assertSame('fqn', $command->options[0]->name);
        $this->assertSame('middleware', $command->options[1]->name);
    }
}
