<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Mocks;

use Aphiria\Console\Drivers\IDriver;
use Aphiria\Console\Output\IOutput;

/**
 * Defines an output whose driver is writable (useful for mocking)
 */
abstract class MockableOutput implements IOutput
{
    public IDriver $driver;
}
