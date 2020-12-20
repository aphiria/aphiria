<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\MissingConfigurationValueException;
use Aphiria\Console\Commands\Attributes\AttributeCommandRegistrant;
use Aphiria\Console\Commands\Caching\FileCommandRegistryCache;
use Aphiria\Console\Commands\Caching\ICommandRegistryCache;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines the console command binder
 */
final class CommandBinder extends Binder
{
    /**
     * @inheritdoc
     * @throws MissingConfigurationValueException Thrown if the the config is missing values
     */
    public function bind(IContainer $container): void
    {
        $commands = new CommandRegistry();
        $container->bindInstance(CommandRegistry::class, $commands);
        $commandCache = new FileCommandRegistryCache(GlobalConfiguration::getString('aphiria.console.commandCachePath'));
        $container->bindInstance(ICommandRegistryCache::class, $commandCache);

        if (getenv('APP_ENV') === 'production') {
            $commandRegistrants = new CommandRegistrantCollection($commandCache);
        } else {
            $commandRegistrants = new CommandRegistrantCollection();
        }

        $container->bindInstance(CommandRegistrantCollection::class, $commandRegistrants);

        // Register some command attribute dependencies
        /** @var string[] $attributePaths */
        $attributePaths = GlobalConfiguration::getArray('aphiria.console.attributePaths');
        $commandAttributeRegistrant = new AttributeCommandRegistrant($attributePaths);
        $container->bindInstance(AttributeCommandRegistrant::class, $commandAttributeRegistrant);
    }
}
