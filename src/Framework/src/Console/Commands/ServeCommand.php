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

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\MissingConfigurationValueException;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;

/**
 * Defines the command to serve up the app locally
 */
class ServeCommand extends Command
{
    /**
     * @param string|null $routerPath The path to the router file, or null if using the default
     * @throws MissingConfigurationValueException Thrown if the localhost router path is not set in the config
     */
    public function __construct(string $routerPath = null)
    {
        $routerPath ??= GlobalConfiguration::getString('aphiria.api.localhostRouterPath');

        parent::__construct(
            'app:serve',
            [],
            [
                new Option('domain', OptionTypes::REQUIRED_VALUE, null, 'The domain to run your app at', 'localhost'),
                new Option('port', OptionTypes::REQUIRED_VALUE, null, 'The port to run your app at', 8080),
                new Option('docroot', OptionTypes::REQUIRED_VALUE, null, 'The document root of your app', 'public'),
                new Option('router', OptionTypes::REQUIRED_VALUE, null, 'The router file for your app', $routerPath)
            ],
            'Runs your app locally'
        );
    }
}
