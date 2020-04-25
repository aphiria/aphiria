<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Commands;

use Aphiria\Framework\Console\Commands\FlushFrameworkCachesCommand;
use PHPUnit\Framework\TestCase;

class FlushFrameworkCachesCommandTest extends TestCase
{
    public function testCorrectValuesAreSetInConstructor(): void
    {
        $command = new FlushFrameworkCachesCommand();
        $this->assertEquals('framework:flushcaches', $command->name);
        $this->assertEquals('Flushes all of Aphiria\'s caches', $command->description);
    }
}
