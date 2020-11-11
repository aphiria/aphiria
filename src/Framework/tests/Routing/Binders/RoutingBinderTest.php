<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RoutingBinderTest extends TestCase
{
    private const ROUTE_CACHE_PATH = __DIR__ . '/tmp/routes.txt';
    private const TRIE_CACHE_PATH = __DIR__ . '/tmp/trie.txt';
    private IContainer|MockObject $container;
    private RoutingBinder $binder;
    private ?string $currEnvironment;

    protected function setUp(): void
    {
        $this->binder = new RoutingBinder();
        $this->container = $this->createMock(IContainer::class);
        GlobalConfiguration::resetConfigurationSources();
        $this->currEnvironment = getenv('APP_ENV') ?: null;

        if (!\file_exists(\dirname(self::ROUTE_CACHE_PATH))) {
            mkdir(\dirname(self::ROUTE_CACHE_PATH));
        }

        if (!\file_exists(\dirname(self::TRIE_CACHE_PATH))) {
            mkdir(\dirname(self::TRIE_CACHE_PATH));
        }
    }

    protected function tearDown(): void
    {
        // Restore the environment name
        if ($this->currEnvironment === null) {
            putenv('APP_ENV=');
        } else {
            putenv("APP_ENV={$this->currEnvironment}");
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
        $this->setUpContainerMock([
            [AttributeRouteRegistrant::class, $this->isInstanceOf(AttributeRouteRegistrant::class)]
        ]);
        $this->container->method('bindFactory')
            ->with([IRouteMatcher::class, TrieRouteMatcher::class], $this->isInstanceOf(Closure::class));
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
        $this->container->method('bindFactory')
            ->with(
                [IRouteMatcher::class, TrieRouteMatcher::class],
                $this->callback(function (Closure $factory) {
                    return $factory() instanceof TrieRouteMatcher;
                })
            );
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testRouteUriFactoryIsBound(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock();
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testTrieRouteMatcherIsBound(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock();
        $this->container->method('bindFactory')
            ->with(
                [IRouteMatcher::class, TrieRouteMatcher::class],
                $this->callback(function (Closure $factory) {
                    return $factory() instanceof TrieRouteMatcher;
                })
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

    /**
     * Sets up the container mock
     *
     * @param array[] $additionalParameters The additional parameters to configure
     */
    private function setUpContainerMock(array $additionalParameters = []): void
    {
        $parameters = [
            [RouteCollection::class, $this->isInstanceOf(RouteCollection::class)],
            [IRouteCache::class, $this->isInstanceOf(FileRouteCache::class)],
            [ITrieCache::class, $this->isInstanceOf(FileTrieCache::class)],
            [RouteRegistrantCollection::class, $this->isInstanceOf(RouteRegistrantCollection::class)],
            [IRouteUriFactory::class, $this->isInstanceOf(AstRouteUriFactory::class)]
        ];

        foreach ($additionalParameters as $additionalParameter) {
            $parameters[] = $additionalParameter;
        }

        $this->container->method('bindInstance')
            ->withConsecutive(...$parameters);
    }
}
