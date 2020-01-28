<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Logging\Bootstrappers;

use Aphiria\Configuration\Configuration;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Defines the logging bootstrapper
 */
final class LoggingBootstrapper extends Bootstrapper
{
    /**
     * @inheritdoc
     */
    public function registerBindings(IContainer $container): void
    {
        /**
         * ----------------------------------------------------------
         * Create a PSR-3 logger
         * ----------------------------------------------------------
         *
         * Note: You may use any PSR-3 logger you'd like
         * For convenience, the Monolog library is included here
         */
        $logger = new Logger(Configuration::getString('logging.name'));

        foreach (Configuration::getArray('logging.handlers') as $handlerConfiguration) {
            switch ($handlerConfiguration['type']) {
                case StreamHandler::class:
                    $logger->pushHandler(new StreamHandler($handlerConfiguration['path']));
                    break;
                case SyslogHandler::class:
                    $logger->pushHandler(new SyslogHandler($handlerConfiguration['ident'] ?? 'app'));
                    break;
            }
        }

        $container->bindInstance(LoggerInterface::class, $logger);
    }
}
