<?php
namespace Opulence\Router;

use Opulence\Router\UriTemplates\UriTemplate;

/**
 * Tests the route collection
 */
class RouteCollectionTest extends \PHPUnit\Framework\TestCase
{
    /** @var RouteCollection The collection with routes already added to use in tests */
    private $seededCollection = null;
    /** @var Route The route with a single GET method */
    private $getRoute = null;
    /** @var Route The route with a GET and POST method */
    private $getAndPostRoute = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->seededCollection = new RouteCollection();
        $this->getRoute = new Route(
            ['GET'],
            new UriTemplate('regex', false),
            $this->createMock(RouteAction::class),
            [],
            'getRoute'
        );
        $this->getAndPostRoute = new Route(
            ['GET', 'POST'],
            new UriTemplate('regex', false),
            $this->createMock(RouteAction::class),
            [],
            'getAndPostRoute'
        );
        $this->seededCollection->add($this->getRoute);
        $this->seededCollection->add($this->getAndPostRoute);
    }

    /**
     * Tests adding many routes adds them to the correct methods
     */
    public function testAddManyAddsRoutesToCorrectMethods() : void
    {
        $collection = new RouteCollection();
        $collection->addMany([$this->getRoute, $this->getAndPostRoute]);
        $getRoutes = $collection->getByMethod('GET');
        $postRoutes = $collection->getByMethod('POST');
        $this->assertCount(2, $getRoutes);
        $this->assertSame($this->getRoute, $getRoutes[0]);
        $this->assertSame($this->getAndPostRoute, $getRoutes[1]);
        $this->assertCount(1, $postRoutes);
        $this->assertSame($this->getAndPostRoute, $postRoutes[0]);
    }

    /**
     * Tests getting all routes returns all the routes
     */
    public function testGetAllReturnsAllRoutes() : void
    {
        $routes = $this->seededCollection->getAll();
        $this->assertCount(7, $routes);
        $this->assertSame($this->getRoute, $routes['GET'][0]);
        $this->assertSame($this->getAndPostRoute, $routes['GET'][1]);
        $this->assertSame($this->getAndPostRoute, $routes['POST'][0]);
    }

    /**
     * Tests getting routes by method only returns routes with that method
     */
    public function testGetByMethodReturnsRoutesWithThatMethod() : void
    {
        $getRoutes = $this->seededCollection->getByMethod('GET');
        $postRoutes = $this->seededCollection->getByMethod('POST');
        $this->assertCount(2, $getRoutes);
        $this->assertSame($this->getRoute, $getRoutes[0]);
        $this->assertSame($this->getAndPostRoute, $getRoutes[1]);
        $this->assertCount(1, $postRoutes);
        $this->assertSame($this->getAndPostRoute, $postRoutes[0]);
    }

    /**
     * Tests that getting by name returns the correct routes
     */
    public function testGetByNameReturnsCorrectRoutes() : void
    {
        $this->assertSame($this->getRoute, $this->seededCollection->getNamedRoute('getRoute'));
        $this->assertSame($this->getAndPostRoute, $this->seededCollection->getNamedRoute('getAndPostRoute'));
    }
}
