<?php

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Routing\Attributes\QueryString;
use PHPUnit\Framework\TestCase;

class QueryStringTest extends TestCase
{
    public function testNameCanBeCustomized(): void
    {
        $this->assertSame('foo', new QueryString('foo')->name);
    }

    public function testNameDefaultsToNull(): void
    {
        $this->assertNull(new QueryString()->name);
    }
}
