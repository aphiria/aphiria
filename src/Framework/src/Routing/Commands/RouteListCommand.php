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
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionType;

/**
 * Defines the command to list your application's routes
 */
final class RouteListCommand extends Command
{
    public function __construct()
    {
        $options = [
            new Option('fqn', OptionType::NoValue, description: 'Shows the fully-qualified class names of controllers and middleware'),
            new Option('middleware', OptionType::NoValue, description: 'Shows the middleware for each route')
        ];
        parent::__construct('route:list', [], $options, 'Lists the routes in your app');
    }
}
