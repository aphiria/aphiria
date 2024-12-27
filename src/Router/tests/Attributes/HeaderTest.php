<?php

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Routing\Attributes\Header;
use PHPUnit\Framework\TestCase;

class HeaderTest extends TestCase
{
    public function testNameCanBeCustomized(): void
    {
        $this->assertSame('foo', new Header('foo')->name);
    }

    public function testNameDefaultsToNull(): void
    {
        $this->assertNull(new Header()->name);
    }
}
