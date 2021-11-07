<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\Console\Commands\Attributes\AttributeCommandRegistrant;
use Aphiria\Console\Commands\Caching\FileCommandRegistryCache;
use Aphiria\Console\Commands\Caching\ICommandRegistryCache;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Console\Binders\CommandBinder;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class CommandBinderTest extends TestCase
{
    private IContainer&MockInterface $container;
    private CommandBinder $binder;
    private ?string $currEnvironment;

    protected function setUp(): void
    {
        $this->binder = new CommandBinder();
        $this->container = Mockery::mock(IContainer::class);
        GlobalConfiguration::resetConfigurationSources();
        $this->currEnvironment = \getenv('APP_ENV') ?: null;
    }

    protected function tearDown(): void
    {
        Mockery::close();

        // Restore the environment name
        if ($this->currEnvironment === null) {
            \putenv('APP_ENV=');
        } else {
            \putenv("APP_ENV={$this->currEnvironment}");
        }
    }

    public function testAttributeRegistrantIsRegistered(): void
    {
        $this->setUpContainerMockBindInstance();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testCommandCacheIsUsedInProd(): void
    {
        $this->setUpContainerMockBindInstance();
        // Basically just ensuring we cover the production case in this test
        \putenv('APP_ENV=production');
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    /**
     * Gets the base config
     *
     * @return array<string, mixed> The base config
     */
    private static function getBaseConfig(): array
    {
        return [
            'aphiria' => [
                'console' => [
                    'attributePaths' => ['/src'],
                    'commandCachePath' => '/commandCache.txt'
                ]
            ]
        ];
    }

    private function setUpContainerMockBindInstance(): void
    {
        $parameters = [
            [CommandRegistry::class, CommandRegistry::class],
            [ICommandRegistryCache::class, FileCommandRegistryCache::class],
            [CommandRegistrantCollection::class, CommandRegistrantCollection::class],
            [AttributeCommandRegistrant::class, AttributeCommandRegistrant::class]
        ];

        foreach ($parameters as $parameter) {
            $this->container->shouldReceive('bindInstance')
                ->with($parameter[0], Mockery::type($parameter[1]));
        }
    }
}
