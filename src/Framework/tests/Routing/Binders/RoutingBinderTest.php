<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Routing\Binders;

use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\Configuration\HashTableConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Routing\Binders\RoutingBinder;
use Aphiria\Routing\Annotations\AnnotationRouteRegistrant;
use Aphiria\Routing\Matchers\IRouteMatcher;
use Aphiria\Routing\Matchers\TrieRouteMatcher;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteRegistrantCollection;
use Aphiria\Routing\UriTemplates\AstRouteUriFactory;
use Aphiria\Routing\UriTemplates\IRouteUriFactory;
use Closure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the routing binder
 */
class RoutingBinderTest extends TestCase
{
    private const ROUTE_CACHE_PATH = __DIR__ . '/tmp/routes.txt';
    private const TRIE_CACHE_PATH = __DIR__ . '/tmp/trie.txt';
    /** @var IContainer|MockObject */
    private IContainer $container;
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

        // Some universal assertions
        $this->container->expects($this->at(0))
            ->method('bindInstance')
            ->with(RouteCollection::class, $this->isInstanceOf(RouteCollection::class));
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(RouteRegistrantCollection::class, $this->isInstanceOf(RouteRegistrantCollection::class));
    }

    protected function tearDown(): void
    {
        // Restore the environment name
        if ($this->currEnvironment !== null) {
            putenv("APP_ENV={$this->currEnvironment}");
        }

        // The trie and route cache are in the same directory
        if (\file_exists(\dirname(self::ROUTE_CACHE_PATH))) {
            @\unlink(self::ROUTE_CACHE_PATH);
            @\unlink(self::TRIE_CACHE_PATH);
            \rmdir(\dirname(self::ROUTE_CACHE_PATH));
        }
    }

    public function testAnnotationRegistrantIsBound(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->container->expects($this->at(4))
            ->method('bindInstance')
            ->with(AnnotationRouteRegistrant::class, $this->isInstanceOf(AnnotationRouteRegistrant::class));
        $this->binder->bind($this->container);
    }

    public function testProductionUsesTrieAndRouteCaches(): void
    {
        // Basically just ensuring we cover the production case in this test
        \putenv('APP_ENV=production');
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->binder->bind($this->container);
    }

    public function testRouteUriFactoryIsBound(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->container->expects($this->at(3))
            ->method('bindInstance')
            ->with(IRouteUriFactory::class, $this->isInstanceOf(AstRouteUriFactory::class));
        $this->binder->bind($this->container);
    }

    public function testTrieRouteMatcherIsBound(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->container->expects($this->at(2))
            ->method('bindFactory')
            ->with([IRouteMatcher::class, TrieRouteMatcher::class], $this->callback(function (Closure $factory) {
                return $factory() instanceof TrieRouteMatcher;
            }));
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
                'routing' => [
                    'annotationPaths' => ['/src'],
                    'routeCachePath' => self::ROUTE_CACHE_PATH,
                    'trieCachePath' => self::TRIE_CACHE_PATH
                ]
            ]
        ];
    }
}
