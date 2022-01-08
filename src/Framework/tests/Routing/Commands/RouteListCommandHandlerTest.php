<?php

namespace Aphiria\Framework\Tests\Routing\Commands;

use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\Formatters\PaddingFormatter;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use Aphiria\Framework\Routing\Commands\RouteListCommandHandler;
use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RouteListCommandHandlerTest extends TestCase
{
    private RouteListCommandHandler $commandHandler;
    private IOutput&MockObject $output;
    private Input $input;
    private RouteCollection $routes;
    private PaddingFormatter $paddingFormatter;

    protected function setUp(): void
    {
        $this->routes = new RouteCollection();
        $this->paddingFormatter = new PaddingFormatter();
        $this->commandHandler = new RouteListCommandHandler($this->routes, $this->paddingFormatter);
        $this->input = new Input('route:list', [], []);
        $this->output = $this->createMock(IOutput::class);
    }

    public function testHandlingRouteWithoutHttpMethodConstraintThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No ' . HttpMethodRouteConstraint::class . ' constraint registered for route with path template "/"');
        $this->routes->add(new Route(new UriTemplate(''), new RouteAction(self::class, 'foo'), []));
        $this->commandHandler->handle($this->input, $this->output);
    }

    public function testHttpMethodsAreAlphabetized(): void
    {
        $route = new Route(
            new UriTemplate(''),
            new RouteAction(self::class, 'foo'),
            // Purposely registering out of order
            [new HttpMethodRouteConstraint(['POST', 'DELETE'])]
        );
        $this->routes->add($route);
        $this->setUpOutputExpectations([['DELETE|POST', '/', self::class . '::foo']]);
        $this->assertSame(StatusCode::Ok, $this->commandHandler->handle($this->input, $this->output));
    }

    public function testRoutesAreAlphabetizedByPath(): void
    {
        // Purposely register routes with out of order paths
        $route1 = new Route(
            new UriTemplate('/foo'),
            new RouteAction(self::class, 'foo'),
            [new HttpMethodRouteConstraint(['POST'])]
        );
        $route2 = new Route(
            new UriTemplate('/bar'),
            new RouteAction(self::class, 'bar'),
            [new HttpMethodRouteConstraint(['POST'])]
        );
        $this->routes->addMany([$route1, $route2]);
        $this->setUpOutputExpectations([
            ['POST', '/bar', self::class . '::bar'],
            ['POST', '/foo', self::class . '::foo']
        ]);
        $this->assertSame(StatusCode::Ok, $this->commandHandler->handle($this->input, $this->output));
    }

    public function testRoutesWithSamePathAreAlphabetizedByHttpMethod(): void
    {
        // Purposely register routes with out of order HTTP methods
        $route1 = new Route(
            new UriTemplate('/foo'),
            new RouteAction(self::class, 'foo'),
            [new HttpMethodRouteConstraint(['POST'])]
        );
        $route2 = new Route(
            new UriTemplate('/foo'),
            new RouteAction(self::class, 'bar'),
            [new HttpMethodRouteConstraint(['DELETE'])]
        );
        $this->routes->addMany([$route1, $route2]);
        $this->setUpOutputExpectations([
            ['DELETE', '/foo', self::class . '::bar'],
            ['POST', '/foo', self::class . '::foo']
        ]);
        $this->assertSame(StatusCode::Ok, $this->commandHandler->handle($this->input, $this->output));
    }

    /**
     * Asserts that the output is correct
     *
     * @param list<array> $expectedRows The list of rows with method, path, and action items we expect to see (pre-padding)
     */
    private function setUpOutputExpectations(array $expectedRows): void
    {
        // Prepend the header row
        $expectedRows = [['Method', 'Path', 'Action'], ...$expectedRows];

        $this->output->expects($this->once())
            ->method('write')
            ->with($this->paddingFormatter->format($expectedRows, fn (array $row): string => \implode(' ', $row)));
    }
}
