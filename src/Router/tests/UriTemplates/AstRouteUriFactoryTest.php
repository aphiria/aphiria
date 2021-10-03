<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates;

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
        $uriFactory = new AstRouteUriFactory($this->routes, null, $lexer);
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
        $this->addRouteWithUriTemplate('foo', ':foo.:bar.example.com', '');
        $this->assertSame(
            'https://dave.young.example.com',
            $this->uriFactory->createRouteUri('foo', ['foo' => 'dave', 'bar' => 'young'])
        );
    }

    public function testCreatingUriWithMultiplePathVarsPopulatesThemFromArgs(): void
    {
        $this->addRouteWithUriTemplate('foo', null, '/:foo/:bar');
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

    public function testCreatingUriWithOptionalHostWithTextOnlyInOptionalPartIncludesThatText(): void
    {
        $this->addRouteWithUriTemplate('foo', '[foo.]example.com', '');
        $this->assertSame('https://foo.example.com', $this->uriFactory->createRouteUri('foo'));
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
        $this->addRouteWithUriTemplate('foo', '[:foo.]example.com', '');
        $this->assertSame(
            'https://bar.example.com',
            $this->uriFactory->createRouteUri('foo', ['foo' => 'bar'])
        );
    }

    public function testCreatingUriWithOptionalNestedHostsDoesNotIncludeOuterPartIfInnerPartIsSpecified(): void
    {
        $this->addRouteWithUriTemplate('foo', '[[:foo.]:bar.]example.com', '');
        $this->assertSame('https://example.com', $this->uriFactory->createRouteUri('foo', ['foo' => '1']));
    }

    public function testCreatingUriWithOptionalNestedHostsWithDefinedVarsIncludesThem(): void
    {
        $this->addRouteWithUriTemplate('foo', '[[:foo.]:bar.]example.com', '');
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
        $this->addRouteWithUriTemplate('foo', 'example.com', '/foo[/:bar[/:baz]]');
        $this->assertSame('https://example.com/foo', $this->uriFactory->createRouteUri('foo', ['baz' => '1']));
    }

    public function testCreatingUriWithOptionalNestedPathsWithDefinedVarsIncludesThem(): void
    {
        $this->addRouteWithUriTemplate('foo', 'example.com', 'foo[/:bar[/:baz]]');
        $this->assertSame(
            'https://example.com/foo/1',
            $this->uriFactory->createRouteUri('foo', ['bar' => '1'])
        );
        $this->assertSame(
            'https://example.com/foo/1/2',
            $this->uriFactory->createRouteUri('foo', ['bar' => '1', 'baz' => '2'])
        );
    }

    public function testCreatingUriWithOptionalPathWithTextOnlyInOptionalPartIncludesThatText(): void
    {
        $this->addRouteWithUriTemplate('foo', 'example.com', 'foo[/bar]');
        $this->assertSame('https://example.com/foo/bar', $this->uriFactory->createRouteUri('foo'));
    }

    public function testCreatingUriWithOptionalPathVarDoesNotSetItIfValueDoesNotExist(): void
    {
        $this->addRouteWithUriTemplate('foo', 'example.com', '/foo[/:bar]');
        $this->assertSame('https://example.com/foo', $this->uriFactory->createRouteUri('foo'));

        $this->addRouteWithUriTemplate('bar', 'example.com', '/foo[/:bar[/:baz]]');
        $this->assertSame('https://example.com/foo', $this->uriFactory->createRouteUri('bar'));
    }

    public function testCreatingUriWithOptionalPathVarIncludesItIfSet(): void
    {
        $this->addRouteWithUriTemplate('foo', 'example.com', '/foo[/:bar]');
        $this->assertSame(
            'https://example.com/foo/baz',
            $this->uriFactory->createRouteUri('foo', ['bar' => 'baz'])
        );
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

    /**
     * Adds a route with a URI template
     *
     * @param string $name The name of the route to add
     * @param string|null $hostTemplate The host template
     * @param string $pathTemplate The path template
     * @param bool $isHttpsOnly Whether or not the URI is HTTPS-only
     */
    private function addRouteWithUriTemplate(
        string $name,
        ?string $hostTemplate,
        string $pathTemplate,
        bool $isHttpsOnly = true
    ): void {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routes->add(new Route(
            new UriTemplate($pathTemplate, $hostTemplate, $isHttpsOnly),
            new RouteAction($controller::class, 'bar'),
            [],
            [],
            $name
        ));
    }
}
