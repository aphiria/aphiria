<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
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
use Aphiria\Console\Input\Compilers\IInputCompiler;
use Aphiria\Console\Input\Compilers\TokenizerInputCompiler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\ConsoleOutput;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;

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
        $inputCompiler = new TokenizerInputCompiler($commands);
        $container->bindInstance(IInputCompiler::class, $inputCompiler);
        $container->bindFactory(Input::class, fn (): Input => $this->getInput($container));
        $container->bindFactory(IOutput::class, fn (): IOutput => $this->getOutput($container));

        if (\getenv('APP_ENV') === 'production') {
            $commandRegistrants = new CommandRegistrantCollection($commandCache);
        } else {
            $commandRegistrants = new CommandRegistrantCollection();
        }

        $container->bindInstance(CommandRegistrantCollection::class, $commandRegistrants);

        // Register some command attribute dependencies
        /** @var list<string> $attributePaths */
        $attributePaths = GlobalConfiguration::getArray('aphiria.console.attributePaths');
        $commandAttributeRegistrant = new AttributeCommandRegistrant($attributePaths);
        $container->bindInstance(AttributeCommandRegistrant::class, $commandAttributeRegistrant);
    }

    /**
     * Gets the compiled input to the console application
     *
     * @param IContainer $container The DI container
     * @return Input The compiled input
     * @throws ResolutionException Thrown if the input compiler could not be resolved
     */
    protected function getInput(IContainer $container): Input
    {
        /** @var array{"argv": array} $_SERVER */
        return $container->resolve(IInputCompiler::class)->compile($_SERVER['argv'] ?? []);
    }

    /**
     * Gets the console application's output
     *
     * @param IContainer $container The DI container
     * @return IOutput The console application output
     */
    protected function getOutput(IContainer $container): IOutput
    {
        return new ConsoleOutput();
    }
}
