<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Drivers;

use Aphiria\Console\Drivers\CliDriverSelector;
use Aphiria\Console\Drivers\UnixLikeDriver;
use Aphiria\Console\Drivers\WindowsDriver;
use PHPUnit\Framework\TestCase;

class CliDriverSelectorTest extends TestCase
{
    public function testCliDriverIsSelectedBasedOnOS(): void
    {
        $selector = new CliDriverSelector();

        if (\DIRECTORY_SEPARATOR === '\\') {
            $this->assertInstanceOf(WindowsDriver::class, $selector->select());
        } else {
            $this->assertInstanceOf(UnixLikeDriver::class, $selector->select());
        }
    }
}
