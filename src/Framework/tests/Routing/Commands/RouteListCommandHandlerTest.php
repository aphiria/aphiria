<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Routing\Commands;

use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\Formatters\PaddingFormatter;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use Aphiria\Framework\Routing\Commands\RouteListCommandHandler;
use Aphiria\Framework\Tests\Routing\Commands\Mocks\MiddlewareA;
use Aphiria\Framework\Tests\Routing\Commands\Mocks\MiddlewareB;
use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
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

    /**
     * Gets URI templates for testing
     *
     * @return list<array{0: string, 1: string}> The list of URI templates and expected outputs
     */
    public function getUriTemplates(): array
    {
        return [
            ['/foo', '/foo'],
            ['/foo/:bar', '/foo/<info>:bar</info>'],
            ['/foo/:bar/baz', '/foo/<info>:bar</info>/baz'],
            ['/foo/:bar(int)', '/foo/<info>:bar(int)</info>'],
            ['/foo[/:bar[/:baz]]', '/foo[/<info>:bar</info>[/<info>:baz</info>]]']
        ];
    }

    public function testFullyQualifiedClassNamesAreUsedForControllersAndMiddlewareWhenOptionIsSpecified(): void
    {
        $route = new Route(
            new UriTemplate(''),
            new RouteAction(self::class, 'foo'),
            [new HttpMethodRouteConstraint(['POST'])],
            [new MiddlewareBinding(MiddlewareA::class)]
        );
        $this->routes->add($route);
        $this->setUpOutputExpectations([['POST', '/', '<comment>' . self::class . '::foo</comment>'], ['', '', '↳ ' . MiddlewareA::class]]);
        $input = new Input('route:list', [], ['fqn' => null, 'middleware' => null]);
        $this->assertSame(StatusCode::Ok, $this->commandHandler->handle($input, $this->output));
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
            // Purposely registering out of alphabetic order
            [new HttpMethodRouteConstraint(['POST', 'DELETE'])]
        );
        $this->routes->add($route);
        $this->setUpOutputExpectations([['DELETE|POST', '/', '<comment>RouteListCommandHandlerTest::foo</comment>']]);
        $this->assertSame(StatusCode::Ok, $this->commandHandler->handle($this->input, $this->output));
    }

    public function testMiddlewareOptionConcatenatesMiddlewareClassNamesBelowRouteData(): void
    {
        $route = new Route(
            new UriTemplate(''),
            new RouteAction(self::class, 'foo'),
            [new HttpMethodRouteConstraint(['POST'])],
            [new MiddlewareBinding(MiddlewareA::class), new MiddlewareBinding(MiddlewareB::class)]
        );
        $this->routes->add($route);
        $this->setUpOutputExpectations([['POST', '/', '<comment>RouteListCommandHandlerTest::foo</comment>'], ['', '', '↳ MiddlewareA → MiddlewareB']]);
        $input = new Input('route:list', [], ['middleware' => null]);
        $this->assertSame(StatusCode::Ok, $this->commandHandler->handle($input, $this->output));
    }

    public function testMiddlewareOptionDoesNotAddRowForRoutesWithNoMiddleware(): void
    {
        $route1 = new Route(
            new UriTemplate('/bar'),
            new RouteAction(self::class, 'bar'),
            [new HttpMethodRouteConstraint(['POST'])]
        );
        $route2 = new Route(
            new UriTemplate('/foo'),
            new RouteAction(self::class, 'foo'),
            [new HttpMethodRouteConstraint(['POST'])],
            [new MiddlewareBinding(MiddlewareA::class), new MiddlewareBinding(MiddlewareB::class)]
        );
        $this->routes->addMany([$route1, $route2]);
        $this->setUpOutputExpectations([
            ['POST', '/bar', '<comment>RouteListCommandHandlerTest::bar</comment>'],
            ['POST', '/foo', '<comment>RouteListCommandHandlerTest::foo</comment>'],
            ['', '', '↳ MiddlewareA → MiddlewareB']
        ]);
        $input = new Input('route:list', [], ['middleware' => null]);
        $this->assertSame(StatusCode::Ok, $this->commandHandler->handle($input, $this->output));
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
            ['POST', '/bar', '<comment>RouteListCommandHandlerTest::bar</comment>'],
            ['POST', '/foo', '<comment>RouteListCommandHandlerTest::foo</comment>']
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
            ['DELETE', '/foo', '<comment>RouteListCommandHandlerTest::bar</comment>'],
            ['POST', '/foo', '<comment>RouteListCommandHandlerTest::foo</comment>']
        ]);
        $this->assertSame(StatusCode::Ok, $this->commandHandler->handle($this->input, $this->output));
    }

    /**
     * @dataProvider getUriTemplates
     *
     * @param string $uriTemplate The raw URI template
     * @param string $expectedFormattedUriTemplate The expected formatted URI template
     */
    public function testRouteVariablesInUriTemplatesAreHighlighted(string $uriTemplate, string $expectedFormattedUriTemplate): void
    {
        $route = new Route(
            new UriTemplate($uriTemplate),
            new RouteAction(self::class, 'foo'),
            [new HttpMethodRouteConstraint(['POST'])]
        );
        $this->routes->add($route);
        $this->setUpOutputExpectations([['POST', $expectedFormattedUriTemplate, '<comment>RouteListCommandHandlerTest::foo</comment>']]);
        $this->assertSame(StatusCode::Ok, $this->commandHandler->handle($this->input, $this->output));
    }

    public function testShortQualifiedClassNamesAreUsedForControllersAndMiddlewareWhenOptionIsSpecified(): void
    {
        $route = new Route(
            new UriTemplate(''),
            new RouteAction(self::class, 'foo'),
            [new HttpMethodRouteConstraint(['POST'])],
            [new MiddlewareBinding(MiddlewareA::class)]
        );
        $this->routes->add($route);
        $input = new Input('route:list', [], ['middleware' => null]);
        $this->setUpOutputExpectations([['POST', '/', '<comment>RouteListCommandHandlerTest::foo</comment>'], ['', '', '↳ MiddlewareA']]);
        $this->assertSame(StatusCode::Ok, $this->commandHandler->handle($input, $this->output));
    }

    /**
     * Asserts that the output is correct
     *
     * @param list<array> $expectedRows The list of rows with method, path, and action items we expect to see (pre-padding)
     */
    private function setUpOutputExpectations(array $expectedRows): void
    {
        // Prepend the header row
        $expectedRows = [['<b>Method</b>', '<b>Path</b>', '<b>Action</b>'], ...$expectedRows];

        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->paddingFormatter->format($expectedRows, fn (array $row): string => \implode(' ', $row)));
    }
}
