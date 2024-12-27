<?php

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Routing\Attributes\RouteParameter;
use PHPUnit\Framework\TestCase;

class RouteParameterTest extends TestCase
{
    public function testNameCanBeCustomized(): void
    {
        $this->assertSame('foo', new RouteParameter('foo')->name);
    }

    public function testNameDefaultsToNull(): void
    {
        $this->assertNull(new RouteParameter()->name);
    }
}
