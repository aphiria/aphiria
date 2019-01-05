<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Handlers;

use Opulence\Api\Handlers\ControllerParameterResolver;
use Opulence\Api\Handlers\MissingControllerParameterValueException;
use Opulence\Api\Tests\Handlers\Mocks\Controller;
use Opulence\Api\Tests\Handlers\Mocks\User;
use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\Request;
use Opulence\Net\Http\StringBody;
use Opulence\Net\Uri;
use Opulence\Routing\Matchers\RouteMatchingResult;
use Opulence\Routing\Route;
use Opulence\Routing\RouteAction;
use Opulence\Routing\UriTemplates\UriTemplate;
use Opulence\Serialization\SerializationException;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionParameter;

/**
 * Tests the controller parameter resolver
 */
class ControllerParameterResolverTest extends \PHPUnit\Framework\TestCase
{
    /** @var ControllerParameterResolver The resolver to use in tests */
    private $resolver;
    /** @var IContentNegotiator|MockObject The content negotiator */
    private $contentNegotiator;

    public function setUp(): void
    {
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->resolver = new ControllerParameterResolver($this->contentNegotiator);
    }

    public function testResolvingParameterWithNoTypeHintUsesVariableFromRoute(): void
    {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([Controller::class, 'noTypeHintParameter'], 'foo'),
            $this->createMock(IHttpRequestMessage::class),
            new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'noTypeHintParameter', null),
                    []
                ),
                ['foo' => 'bar'],
                []
            )
        );
        $this->assertEquals('bar', $resolvedParameter);
    }

    public function testResolvingNullableObjectParameterWithBodyThatCannotDeserializeToTypePassesNull(): void
    {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $request->setBody(new StringBody('dummy body'));
        /** @var IMediaTypeFormatter|MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->expects($this->once())
            ->method('readFromStream')
            ->with($request->getBody()->readAsStream(), User::class)
            ->willThrowException(new SerializationException);
        $this->contentNegotiator->expects($this->once())
            ->method('negotiateRequestContent')
            ->with(User::class, $request)
            ->willReturn(new ContentNegotiationResult($mediaTypeFormatter, null, null, null));
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([Controller::class, 'nullableObjectParameter'], 'user'),
            $request,
            new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'nullableObjectParameter', null),
                    []
                ),
                [],
                []
            )
        );
        $this->assertNull($resolvedParameter);
    }

    public function testResolvingNullableObjectParameterWithoutBodyPassesNull(): void
    {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([Controller::class, 'nullableObjectParameter'], 'user'),
            $request,
            new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'nullableObjectParameter', null),
                    []
                ),
                [],
                []
            )
        );
        $this->assertNull($resolvedParameter);
    }

    public function testResolvingNullableScalarParameterWithNoMatchingValuePassesNull(): void
    {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([Controller::class, 'nullableScalarParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com'),
            new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'nullableScalarParameter', null),
                    []
                ),
                [],
                []
            )
        );
        $this->assertNull($resolvedParameter);
    }

    public function testResolvingObjectParameterAndNoRequestBodyThrowsException(): void
    {
        $this->expectException(MissingControllerParameterValueException::class);
        $this->resolver->resolveParameter(
            new ReflectionParameter([Controller::class, 'objectParameter'], 'user'),
            $this->createRequestWithoutBody('http://foo.com'),
            new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'objectParameter', null),
                    []
                ),
                [],
                []
            )
        );
    }

    public function testResolvingObjectParameterReadsFromRequestBodyFirst(): void
    {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $request->setBody(new StringBody('dummy body'));
        $expectedUser = new User(123, 'foo@bar.com');
        /** @var IMediaTypeFormatter|MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->expects($this->once())
            ->method('readFromStream')
            ->with($request->getBody()->readAsStream(), User::class)
            ->willReturn($expectedUser);
        $this->contentNegotiator->expects($this->once())
            ->method('negotiateRequestContent')
            ->with(User::class, $request)
            ->willReturn(new ContentNegotiationResult($mediaTypeFormatter, null, null, null));
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([Controller::class, 'objectParameter'], 'user'),
            $request,
            new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'objectParameter', null),
                    []
                ),
                [],
                []
            )
        );
        $this->assertEquals($expectedUser, $resolvedParameter);
    }

    public function testResolvingScalarParameterAndNoMatchingVariableThrowsException(): void
    {
        $this->expectException(MissingControllerParameterValueException::class);
        $this->resolver->resolveParameter(
            new ReflectionParameter([Controller::class, 'stringParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com'),
            new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'stringParameter', null),
                    []
                ),
                [],
                []
            )
        );
    }

    public function testResolvingScalarParameterAndNoMatchingVariableUsesDefaultValueIfAvailable(): void
    {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([Controller::class, 'defaultValueParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com'),
            new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'defaultValueParameter', null),
                    []
                ),
                [],
                []
            )
        );
        $this->assertEquals('bar', $resolvedParameter);
    }

    public function testResolvingScalarParameterUsesMatchingQueryStringVariable(): void
    {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([Controller::class, 'stringParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com/?foo=bar'),
            new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'stringParameter', null),
                    []
                ),
                [],
                []
            )
        );
        $this->assertEquals('bar', $resolvedParameter);
    }

    public function testResolvingScalarParameterUsesMatchingRouteVariableOverQueryStringVariable(): void
    {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([Controller::class, 'stringParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com/?foo=baz'),
            new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'stringParameter', null),
                    []
                ),
                ['foo' => 'dave'],
                []
            )
        );
        $this->assertEquals('dave', $resolvedParameter);
    }

    /**
     * Creates a request with the input URI and no body
     *
     * @param string $uri The URI to use
     * @return Request The request
     */
    private function createRequestWithoutBody(string $uri): Request
    {
        return new Request('GET', new Uri($uri));
    }
}
