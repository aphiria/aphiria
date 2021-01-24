<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Commands;

use Aphiria\Console\Commands\Command;

/**
 * Defines the command for flushing the framework's caches
 */
final class FlushFrameworkCachesCommand extends Command
{
    public function __construct()
    {
        parent::__construct('framework:flushcaches', [], [], 'Flushes all of Aphiria\'s caches');
    }
}
