<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Binders;

use Aphiria\Configuration\ConfigurationException;
use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\Console\Commands\Annotations\AnnotationCommandRegistrant;
use Aphiria\Console\Commands\Caching\FileCommandRegistryCache;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Doctrine\Annotations\AnnotationException;

/**
 * Defines the console command binder
 */
final class CommandBinder extends Binder
{
    /**
     * @inheritdoc
     * @throws ConfigurationException Thrown if the the config is missing values
     * @throws AnnotationException Thrown if PHP is not configured to handle scanning for annotations
     */
    public function bind(IContainer $container): void
    {
        $commands = new CommandRegistry();
        $container->bindInstance(CommandRegistry::class, $commands);

        if (getenv('APP_ENV') === 'production') {
            $commandCache = new FileCommandRegistryCache(GlobalConfiguration::getString('aphiria.console.commandCachePath'));
        } else {
            $commandCache = null;
        }

        $commandRegistrants = new CommandRegistrantCollection($commandCache);
        $container->bindInstance(CommandRegistrantCollection::class, $commandRegistrants);

        // Register some command annotation dependencies
        $commandAnnotationRegistrant = new AnnotationCommandRegistrant(
            GlobalConfiguration::getArray('aphiria.console.annotationPaths'),
            $container
        );
        $container->bindInstance(AnnotationCommandRegistrant::class, $commandAnnotationRegistrant);
    }
}
