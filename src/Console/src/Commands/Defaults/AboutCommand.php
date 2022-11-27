<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Defaults;

use Aphiria\Console\Commands\Command;

/**
 * Defines the about command
 */
final class AboutCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            '',
            [],
            [],
            'Describes the Aphiria console application'
        );
    }
}
