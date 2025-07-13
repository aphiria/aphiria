<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

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
