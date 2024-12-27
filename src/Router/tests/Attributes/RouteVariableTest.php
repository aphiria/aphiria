<?php

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Routing\Attributes\RouteVariable;
use PHPUnit\Framework\TestCase;

class RouteVariableTest extends TestCase
{
    public function testNameCanBeCustomized(): void
    {
        $this->assertSame('foo', new RouteVariable('foo')->name);
    }

    public function testNameDefaultsToNull(): void
    {
        $this->assertNull(new RouteVariable()->name);
    }
}
