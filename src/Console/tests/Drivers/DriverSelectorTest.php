<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Drivers;

use Aphiria\Console\Drivers\DriverSelector;
use Aphiria\Console\Drivers\UnixLikeDriver;
use Aphiria\Console\Drivers\WindowsDriver;
use PHPUnit\Framework\TestCase;

class DriverSelectorTest extends TestCase
{
    public function testDriverIsSelectedBasedOnOS(): void
    {
        $selector = new DriverSelector();

        if (\DIRECTORY_SEPARATOR === '\\') {
            $this->assertInstanceOf(WindowsDriver::class, $selector->select());
        } else {
            $this->assertInstanceOf(UnixLikeDriver::class, $selector->select());
        }
    }
}
