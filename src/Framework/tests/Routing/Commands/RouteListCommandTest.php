<?php

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
    }
}
