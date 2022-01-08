<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Routing\Commands;

use Aphiria\Console\Commands\Command;

/**
 * Defines the command to list your application's routes
 */
final class RouteListCommand extends Command
{
    public function __construct()
    {
        parent::__construct('route:list', [], [], 'Lists the routes in your app');
    }
}
