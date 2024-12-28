<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates;

use Aphiria\Routing\Attributes\Header;
use Aphiria\Routing\Attributes\QueryString;
use Aphiria\Routing\Attributes\RouteVariable;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\AstRouteUriFactory;
use Aphiria\Routing\UriTemplates\Lexers\IUriTemplateLexer;
use Aphiria\Routing\UriTemplates\Lexers\LexingException;
use Aphiria\Routing\UriTemplates\Lexers\UnexpectedTokenException;
use Aphiria\Routing\UriTemplates\Parsers\IUriTemplateParser;
use Aphiria\Routing\UriTemplates\RouteUriCreationException;
use Aphiria\Routing\UriTemplates\UriTemplate;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AstRouteUriFactoryTest extends TestCase
{
    private RouteCollection $routes;
    private AstRouteUriFactory $uriFactory;

    protected function setUp(): void
    {
        $this->routes = new RouteCollection();
        $this->uriFactory = new AstRouteUriFactory($this->routes);
    }

    public static function namedAttributeParameterProvider(): array
    {
        $controller = new class () {
            public function queryString(#[QueryString('foo')] string $unused): void
            {
            }
            public function routeVariable(#[RouteVariable('foo')] string $unused1, #[RouteVariable('bar')] string $unused2): void
            {
            }
        };

        return [
            [$controller, 'queryString', null, '/', '/?foo=bar', ['foo' => 'bar']],
            [$controller, 'routeVariable', ':foo.example.com', '/:bar', 'https://qux.example.com/quux', ['foo' => 'qux', 'bar' => 'quux']]
        ];
    }

    public static function invalidRouteVariableProvider(): array
    {
        $controller = new class () {
            public function multipleParameters(string $foo, string $bar): void
            {
            }

            public function noParameters(): void
            {
            }

            public function queryString(#[QueryString] string $foo): void
            {
            }

            public function routeVariable(#[RouteVariable] string $foo): void
            {
            }

            public function unspecified(string $foo): void
            {
            }
        };

        return [
            [
                'Following route variables have no matching route action parameter in ' . $controller::class . '::noParameters: "foo"',
                $controller,
                'noParameters',
                null,
                '',
                ['foo' => 'bar']
            ],
            [
                'Following route action parameters have no matching value in ' . $controller::class . '::queryString: "foo".  Following route variables have no matching route action parameter in ' . $controller::class . '::queryString: "bar"',
                $controller,
                'queryString',
                null,
                '',
                ['bar' => 'baz']
            ],
            [
                'Following route action parameters have no matching value in ' . $controller::class . '::routeVariable: "foo".  Following route variables have no matching route action parameter in ' . $controller::class . '::routeVariable: "bar"',
                $controller,
                'routeVariable',
                null,
                '/:foo',
                ['bar' => 'baz']
            ],
            [
                'Following route action parameters have no matching value in ' . $controller::class . '::unspecified: "foo".  Following route variables have no matching route action parameter in ' . $controller::class . '::unspecified: "bar"',
                $controller,
                'unspecified',
                null,
                '/:foo',
                ['bar' => 'baz']
            ],
            [
                'Following route action parameters have no matching value in ' . $controller::class . '::routeVariable: "foo".  Following route variables have no matching route action parameter in ' . $controller::class . '::routeVariable: "bar", "qux"',
                $controller,
                'routeVariable',
                null,
                '/:foo',
                ['bar' => 'baz', 'qux' => 'quz']
            ],
            [
                'Following route action parameters have no matching value in ' . $controller::class . '::multipleParameters: "foo", "bar".  Following route variables have no matching route action parameter in ' . $controller::class . '::multipleParameters: "baz"',
                $controller,
                'multipleParameters',
                null,
                '/:foo',
                ['baz' => 'qux']
            ]
        ];
    }

    /**
     * @param object $controller The route action controller
     * @param string $methodName The name of the route action method
     * @param string|null $hostTemplate The optional host template
     * @param string $pathTemplate The path template
     * @param string $expectedUri The expected URI
     * @param array<string, mixed> $routeVariables The route variables
     */
    #[DataProvider('namedAttributeParameterProvider')]
    public function testCreatingUriWithNamedAttributeParametersUsesThoseNamesInsteadOfVariableNameFromController(
        object $controller,
        string $methodName,
        ?string $hostTemplate,
        string $pathTemplate,
        string $expectedUri,
        array $routeVariables
    ): void {
        $this->addRouteWithUriTemplate($methodName, $hostTemplate, $pathTemplate, controller: $controller, methodName: $methodName);
        $this->assertSame($expectedUri, $this->uriFactory->createRouteUri($methodName, $routeVariables));
    }

    /**
     * @param string $expectedExceptionMessage The expected exception message
     * @param object $controller The route action controller
     * @param string $methodName The name of the route action method
     * @param string|null $hostTemplate The optional host template
     * @param string $pathTemplate The path template
     * @param array<string, mixed> $routeVariables The route variables
     */
    #[DataProvider('invalidRouteVariableProvider')]
    public function testCreatingUriWithRouteVariablesThatDoNotAppearAsRouteActionParametersThrowsException(
        string $expectedExceptionMessage,
        object $controller,
        string $methodName,
        ?string $hostTemplate,
        string $pathTemplate,
        array $routeVariables
    ): void {
        $this->expectException(RouteUriCreationException::class);
        $this->addRouteWithUriTemplate($methodName, $hostTemplate, $pathTemplate, controller: $controller, methodName: $methodName);

        try {
            $this->uriFactory->createRouteUri($methodName, $routeVariables);
        } catch (RouteUriCreationException $ex) {
            $this->assertSame($expectedExceptionMessage, $ex->getPrevious()->getMessage());
            throw $ex;
        }
    }

    public function testCreatingUriWithInvalidRouteActionThrowsException(): void
    {
        $this->expectException(RouteUriCreationException::class);
        $this->expectExceptionMessage('Failed to create route action parameters for ' . $this::class . '::__doesNotExist');
        $this->addRouteWithUriTemplate('foo', null, '', controller: $this, methodName: '__doesNotExist');
        $this->uriFactory->createRouteUri('foo');
    }

    public function testCreatingUriForUnregisteredRouteThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Route "bar" does not exist');
        $this->uriFactory->createRouteUri('bar');
    }

    public function testCreatingUriThatThrowsLexingExceptionRethrowsException(): void
    {
        $this->expectException(RouteUriCreationException::class);
        $this->expectExceptionMessage('Failed to lex URI template');
        $lexer = $this->createMock(IUriTemplateLexer::class);
        $lexer->expects($this->once())
            ->method('lex')
            ->with('example.com/')
            ->willThrowException(new LexingException());
        $this->addRouteWithUriTemplate('foo', 'example.com', '');
        $uriFactory = new AstRouteUriFactory($this->routes, uriTemplateLexer: $lexer);
        $uriFactory->createRouteUri('foo');
    }

    public function testCreatingUriThatThrowsParsingExceptionRethrowsException(): void
    {
        $this->expectException(RouteUriCreationException::class);
        $this->expectExceptionMessage('Failed to parse URI template');
        $parser = $this->createMock(IUriTemplateParser::class);
        $parser->expects($this->once())
            ->method('parse')
            ->with($this->anything())
            ->willThrowException(new UnexpectedTokenException());
        $this->addRouteWithUriTemplate('foo', 'example.com', '');
        $uriFactory = new AstRouteUriFactory($this->routes, $parser);
        $uriFactory->createRouteUri('foo');
    }

    public function testCreatingUriWithHostAndEmptyPathStripsTrailingSlash(): void
    {
        $this->addRouteWithUriTemplate('foo', 'example.com', '');
        $this->assertSame('https://example.com', $this->uriFactory->createRouteUri('foo'));
    }

    public function testCreatingUriWithHttpsOnlyHostUsesHttpsPrefix(): void
    {
        $this->addRouteWithUriTemplate('foo', 'example.com', '/foo');
        $this->assertSame('https://example.com/foo', $this->uriFactory->createRouteUri('foo'));
    }

    public function testCreatingUriWithMultipleHostVarsPopulatesThemFromArgs(): void
    {
        $controller = new class () {
            public function foo(string $foo, string $bar): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', ':foo.:bar.example.com', '', controller: $controller, methodName: 'foo');
        $this->assertSame(
            'https://dave.young.example.com',
            $this->uriFactory->createRouteUri('foo', ['foo' => 'dave', 'bar' => 'young'])
        );
    }

    public function testCreatingUriWithMultiplePathVarsPopulatesThemFromArgs(): void
    {
        $controller = new class () {
            public function foo(string $foo, string $bar): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', null, '/:foo/:bar', controller: $controller, methodName: 'foo');
        $this->assertSame(
            '/dave/young',
            $this->uriFactory->createRouteUri('foo', ['foo' => 'dave', 'bar' => 'young'])
        );
    }

    public function testCreatingUriWithNonHttpsOnlyHostUsesHttpPrefix(): void
    {
        $this->addRouteWithUriTemplate('foo', 'example.com', '/foo', false);
        $this->assertSame('http://example.com/foo', $this->uriFactory->createRouteUri('foo'));
    }

    public function testCreatingUriWithOptionalHostVarDoesNotSetItIfValueDoesNotExist(): void
    {
        $this->addRouteWithUriTemplate('foo', '[:foo.]example.com', '');
        $this->assertSame('https://example.com', $this->uriFactory->createRouteUri('foo'));

        $this->addRouteWithUriTemplate('bar', '[:foo.[:bar.]]example.com', '');
        $this->assertSame('https://example.com', $this->uriFactory->createRouteUri('bar'));
    }

    public function testCreatingUriWithOptionalHostVarSetsItIfValueExists(): void
    {
        $controller = new class () {
            public function foo(string $foo): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', '[:foo.]example.com', '', controller: $controller, methodName: 'foo');
        $this->assertSame(
            'https://bar.example.com',
            $this->uriFactory->createRouteUri('foo', ['foo' => 'bar'])
        );
    }

    public function testCreatingUriWithOptionalHostWithTextOnlyInOptionalPartIncludesThatText(): void
    {
        $this->addRouteWithUriTemplate('foo', '[foo.]example.com', '');
        $this->assertSame('https://foo.example.com', $this->uriFactory->createRouteUri('foo'));
    }

    public function testCreatingUriWithOptionalNestedHostsDoesNotIncludeOuterPartIfInnerPartIsSpecified(): void
    {
        $controller = new class () {
            public function foo(?string $foo, ?string $bar): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', '[[:foo.]:bar.]example.com', '', controller: $controller, methodName: 'foo');
        $this->assertSame('https://example.com', $this->uriFactory->createRouteUri('foo', ['foo' => '1']));
    }

    public function testCreatingUriWithOptionalNestedHostsWithDefinedVarsIncludesThem(): void
    {
        $controller = new class () {
            public function foo(?string $foo, ?string $bar): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', '[[:foo.]:bar.]example.com', '', controller: $controller, methodName: 'foo');
        $this->assertSame(
            'https://1.example.com',
            $this->uriFactory->createRouteUri('foo', ['bar' => '1'])
        );
        $this->assertSame(
            'https://2.1.example.com',
            $this->uriFactory->createRouteUri('foo', ['bar' => '1', 'foo' => '2'])
        );
    }

    public function testCreatingUriWithOptionalNestedPathsDoesNotIncludeOuterPartIfInnerPartIsSpecified(): void
    {
        $controller = new class () {
            public function foo(?string $bar, ?string $baz): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', 'example.com', '/foo[/:bar[/:baz]]', controller: $controller, methodName: 'foo');
        $this->assertSame('https://example.com/foo', $this->uriFactory->createRouteUri('foo', ['baz' => '1']));
    }

    public function testCreatingUriWithOptionalNestedPathsWithDefinedVarsIncludesThem(): void
    {
        $controller = new class () {
            public function foo(?string $bar, ?string $baz): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', 'example.com', 'foo[/:bar[/:baz]]', controller: $controller, methodName: 'foo');
        $this->assertSame(
            'https://example.com/foo/1',
            $this->uriFactory->createRouteUri('foo', ['bar' => '1'])
        );
        $this->assertSame(
            'https://example.com/foo/1/2',
            $this->uriFactory->createRouteUri('foo', ['bar' => '1', 'baz' => '2'])
        );
    }

    public function testCreatingUriWithOptionalPathVarDoesNotSetItIfValueDoesNotExist(): void
    {
        $controller = new class () {
            public function foo(?string $bar): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', 'example.com', '/foo[/:bar]', controller: $controller, methodName: 'foo');
        $this->assertSame('https://example.com/foo', $this->uriFactory->createRouteUri('foo'));

        $this->addRouteWithUriTemplate('bar', 'example.com', '/foo[/:bar[/:baz]]');
        $this->assertSame('https://example.com/foo', $this->uriFactory->createRouteUri('bar'));
    }

    public function testCreatingUriWithOptionalPathVarIncludesItIfSet(): void
    {
        $controller = new class () {
            public function foo(string $bar): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', 'example.com', '/foo[/:bar]', controller: $controller, methodName: 'foo');
        $this->assertSame(
            'https://example.com/foo/baz',
            $this->uriFactory->createRouteUri('foo', ['bar' => 'baz'])
        );
    }

    public function testCreatingUriWithOptionalPathWithTextOnlyInOptionalPartIncludesThatText(): void
    {
        $this->addRouteWithUriTemplate('foo', 'example.com', 'foo[/bar]');
        $this->assertSame('https://example.com/foo/bar', $this->uriFactory->createRouteUri('foo'));
    }

    public function testCreatingUriWithoutEnoughHostVarsThrowsException(): void
    {
        $this->expectException(RouteUriCreationException::class);
        $this->expectExceptionMessage('No value set for foo in host');
        $this->addRouteWithUriTemplate('foo', ':foo.example.com', '');
        $this->uriFactory->createRouteUri('foo');
    }

    public function testCreatingUriWithoutEnoughPathVarsThrowsException(): void
    {
        $this->expectException(RouteUriCreationException::class);
        $this->expectExceptionMessage('No value set for foo in path');
        $this->addRouteWithUriTemplate('foo', null, '/:foo');
        $this->uriFactory->createRouteUri('foo');
    }

    public function testCreatingUriWithRelativePathHasLeadingSlash(): void
    {
        $this->addRouteWithUriTemplate('foo', null, '/foo/bar');
        $this->assertSame('/foo/bar', $this->uriFactory->createRouteUri('foo'));
    }

    public function testCreatingUriWithRouteVariableAttributeWillUseItsValueForHost(): void
    {
        $controller = new class () {
            public function foo(#[RouteVariable] string $foo): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', ':foo.example.com', '', controller: $controller, methodName: 'foo');
        $this->assertSame('https://bar.example.com', $this->uriFactory->createRouteUri('foo', ['foo' => 'bar']));
    }

    public function testCreatingUriWithRouteVariableAttributeWillUseItsValueForPath(): void
    {
        $controller = new class () {
            public function foo(#[RouteVariable] string $foo): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', null, '/:foo', controller: $controller, methodName: 'foo');
        $this->assertSame('/bar', $this->uriFactory->createRouteUri('foo', ['foo' => 'bar']));
    }

    public function testCreatingUriWithMultipleQueryStringAttributesWillUseValuesInQueryString(): void
    {
        $controller = new class () {
            public function foo(#[QueryString] string $foo, #[QueryString] string $bar): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', null, '/foo', controller: $controller, methodName: 'foo');
        $this->assertSame('/foo?foo=1&bar=2', $this->uriFactory->createRouteUri('foo', ['foo' => '1', 'bar' => '2']));
    }

    public function testCreatingUriWithQueryStringAttributeWillUseItsValueInQueryString(): void
    {
        $controller = new class () {
            public function foo(#[QueryString] string $foo): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', null, '/foo', controller: $controller, methodName: 'foo');
        $this->assertSame('/foo?foo=bar', $this->uriFactory->createRouteUri('foo', ['foo' => 'bar']));
    }

    public function testCreatingUriWithNoAttributeWillUseItsValueInQueryString(): void
    {
        $controller = new class () {
            public function foo(string $foo): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', null, '/foo', controller: $controller, methodName: 'foo');
        $this->assertSame('/foo?foo=bar', $this->uriFactory->createRouteUri('foo', ['foo' => 'bar']));
    }

    public function testCreatingUriWithQueryStringAttributeWillUrlEncodeItsValueInQueryString(): void
    {
        $controller = new class () {
            public function foo(#[QueryString] string $foo): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', null, '/foo', controller: $controller, methodName: 'foo');
        // We are specifically using spaces as the special characters here to ensure they're being encoded to "%20" (what's used in URLs), not "+" (what's used in form data)
        $this->assertSame('/foo?foo=%20bar%20', $this->uriFactory->createRouteUri('foo', ['foo' => ' bar ']));
    }

    public function testCreatingUriWithHeaderAttributeSimplyIgnoresIt(): void
    {
        $controller = new class () {
            public function foo(#[Header] string $foo): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', null, '', controller: $controller, methodName: 'foo');
        $this->assertSame('/', $this->uriFactory->createRouteUri('foo'));
    }

    public function testCreatingUriWillThrowExceptionWhenQueryStringAttributeParamIsOnlyParamThatMatchesRequiredRouteVariable(): void
    {
        $this->expectException(RouteUriCreationException::class);
        $this->expectExceptionMessage('No value set for foo in path');
        $controller = new class () {
            public function foo(#[QueryString] $foo): void
            {
            }
        };
        $this->addRouteWithUriTemplate('foo', null, '/:foo', controller: $controller, methodName: 'foo');
        $this->uriFactory->createRouteUri('foo', ['foo' => 'bar']);
    }

    /**
     * Adds a route with a URI template
     *
     * @param string $name The name of the route to add
     * @param string|null $hostTemplate The host template
     * @param string $pathTemplate The path template
     * @param bool $isHttpsOnly Whether or not the URI is HTTPS-only
     * @param object|null $controller The optional controller to map the route action to
     * @param string|null $methodName The optional controller method name to map the route action to
     */
    private function addRouteWithUriTemplate(
        string $name,
        ?string $hostTemplate,
        string $pathTemplate,
        bool $isHttpsOnly = true,
        ?object $controller = null,
        ?string $methodName = null
    ): void {
        if ($controller === null && $methodName === null) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $methodName = 'bar';
        }

        $this->routes->add(new Route(
            new UriTemplate($pathTemplate, $hostTemplate, $isHttpsOnly),
            new RouteAction($controller::class, $methodName),
            [],
            [],
            $name
        ));
    }
}
