<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\Console\Commands\Annotations\AnnotationCommandRegistrant;
use Aphiria\Console\Commands\Caching\FileCommandRegistryCache;
use Aphiria\Console\Commands\Caching\ICommandRegistryCache;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Console\Binders\CommandBinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommandBinderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    private CommandBinder $binder;
    private ?string $currEnvironment;

    protected function setUp(): void
    {
        $this->binder = new CommandBinder();
        $this->container = $this->createMock(IContainer::class);
        GlobalConfiguration::resetConfigurationSources();
        $this->currEnvironment = getenv('APP_ENV') ?: null;

        // Some universal assertions
        $this->container->expects($this->at(0))
            ->method('bindInstance')
            ->with(CommandRegistry::class, $this->isInstanceOf(CommandRegistry::class));
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(ICommandRegistryCache::class, $this->isInstanceOf(FileCommandRegistryCache::class));
        $this->container->expects($this->at(2))
            ->method('bindInstance')
            ->with(CommandRegistrantCollection::class, $this->isInstanceOf(CommandRegistrantCollection::class));
    }

    protected function tearDown(): void
    {
        // Restore the environment name
        if ($this->currEnvironment !== null) {
            putenv("APP_ENV={$this->currEnvironment}");
        }
    }

    public function testAnnotationRegistrantIsRegistered(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->container->expects($this->at(3))
            ->method('bindInstance')
            ->with(AnnotationCommandRegistrant::class, $this->isInstanceOf(AnnotationCommandRegistrant::class));
        $this->binder->bind($this->container);
    }

    public function testCommandCacheIsUsedInProd(): void
    {
        // Basically just ensuring we cover the production case in this test
        putenv('APP_ENV=production');
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->binder->bind($this->container);
    }

    /**
     * Gets the base config
     *
     * @return array The base config
     */
    private static function getBaseConfig(): array
    {
        return [
            'aphiria' => [
                'console' => [
                    'annotationPaths' => ['/src'],
                    'commandCachePath' => '/commandCache.txt'
                ]
            ]
        ];
    }
}
