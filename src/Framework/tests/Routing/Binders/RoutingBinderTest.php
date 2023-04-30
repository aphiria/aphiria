<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Routing\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Routing\Binders\RoutingBinder;
use Aphiria\Routing\Attributes\AttributeRouteRegistrant;
use Aphiria\Routing\Caching\FileRouteCache;
use Aphiria\Routing\Caching\IRouteCache;
use Aphiria\Routing\Matchers\IRouteMatcher;
use Aphiria\Routing\Matchers\TrieRouteMatcher;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteRegistrantCollection;
use Aphiria\Routing\UriTemplates\AstRouteUriFactory;
use Aphiria\Routing\UriTemplates\Compilers\Tries\Caching\FileTrieCache;
use Aphiria\Routing\UriTemplates\Compilers\Tries\Caching\ITrieCache;
use Aphiria\Routing\UriTemplates\IRouteUriFactory;
use Closure;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class RoutingBinderTest extends TestCase
{
    private const ROUTE_CACHE_PATH = __DIR__ . '/tmp/routes.txt';
    private const TRIE_CACHE_PATH = __DIR__ . '/tmp/trie.txt';
    private RoutingBinder $binder;
    private IContainer&MockInterface $container;
    private ?string $currEnvironment;

    protected function setUp(): void
    {
        $this->binder = new RoutingBinder();
        $this->container = Mockery::mock(IContainer::class);
        GlobalConfiguration::resetConfigurationSources();
        $this->currEnvironment = \getenv('APP_ENV') ?: null;

        if (!\file_exists(\dirname(self::ROUTE_CACHE_PATH))) {
            \mkdir(\dirname(self::ROUTE_CACHE_PATH));
        }

        if (!\file_exists(\dirname(self::TRIE_CACHE_PATH))) {
            \mkdir(\dirname(self::TRIE_CACHE_PATH));
        }
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

        // The trie and route cache are in the same directory
        if (\file_exists(\dirname(self::ROUTE_CACHE_PATH))) {
            @\unlink(self::ROUTE_CACHE_PATH);
            @\unlink(self::TRIE_CACHE_PATH);
            \rmdir(\dirname(self::ROUTE_CACHE_PATH));
        }
    }

    public function testAttributeRegistrantIsBound(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock();
        $this->container->shouldReceive('bindFactory')
            ->with([IRouteMatcher::class, TrieRouteMatcher::class], Mockery::type(Closure::class), true);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testProductionUsesTrieAndRouteCaches(): void
    {
        // Basically just ensuring we cover the production case in this test
        \putenv('APP_ENV=production');
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock();
        $this->container->shouldReceive('bindFactory')
            ->with(
                [IRouteMatcher::class, TrieRouteMatcher::class],
                Mockery::on(function (Closure $factory) {
                    return $factory() instanceof TrieRouteMatcher;
                }),
                true
            );
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testRouteUriFactoryIsBound(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock();
        $this->container->shouldReceive('bindFactory')
            ->with(
                [IRouteMatcher::class, TrieRouteMatcher::class],
                Mockery::on(function (Closure $factory) {
                    return $factory() instanceof TrieRouteMatcher;
                }),
                true
            );
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testTrieRouteMatcherIsBound(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock();
        $this->container->shouldReceive('bindFactory')
            ->with(
                [IRouteMatcher::class, TrieRouteMatcher::class],
                Mockery::on(function (Closure $factory) {
                    return $factory() instanceof TrieRouteMatcher;
                }),
                true
            );
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
                'routing' => [
                    'attributePaths' => ['/src'],
                    'routeCachePath' => self::ROUTE_CACHE_PATH,
                    'trieCachePath' => self::TRIE_CACHE_PATH
                ]
            ]
        ];
    }

    private function setUpContainerMock(): void
    {
        $parameters = [
            [RouteCollection::class, RouteCollection::class],
            [IRouteCache::class, FileRouteCache::class],
            [ITrieCache::class, FileTrieCache::class],
            [RouteRegistrantCollection::class, RouteRegistrantCollection::class],
            [IRouteUriFactory::class, AstRouteUriFactory::class],
            [AttributeRouteRegistrant::class, AttributeRouteRegistrant::class]
        ];

        foreach ($parameters as $parameter) {
            $this->container->shouldReceive('bindInstance')
                ->with($parameter[0], Mockery::type($parameter[1]));
        }
    }
}
