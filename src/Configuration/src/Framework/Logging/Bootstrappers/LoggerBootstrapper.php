<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Framework\Logging\Bootstrappers;

use Aphiria\Configuration\Configuration;
use Aphiria\Configuration\ConfigurationException;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Defines the logger bootstrapper
 */
final class LoggerBootstrapper extends Bootstrapper
{
    /**
     * @inheritdoc
     * @throws ConfigurationException Thrown if the config is missing values
     */
    public function registerBindings(IContainer $container): void
    {
        $logger = new Logger(Configuration::getString('aphiria.logging.name'));

        foreach (Configuration::getArray('aphiria.logging.handlers') as $handlerConfiguration) {
            switch ($handlerConfiguration['type']) {
                case StreamHandler::class:
                    $logger->pushHandler(new StreamHandler($handlerConfiguration['path']));
                    break;
                case SyslogHandler::class:
                    $logger->pushHandler(new SyslogHandler($handlerConfiguration['ident'] ?? 'app'));
                    break;
                default:
                    throw new ConfigurationException("Unsupported logging handler type {$handlerConfiguration['type']}");
            }
        }

        $container->bindInstance(LoggerInterface::class, $logger);
    }
}
