<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Controllers;

use Aphiria\Api\Controllers\ControllerParameterResolver;
use Aphiria\Api\Controllers\FailedRequestContentNegotiationException;
use Aphiria\Api\Controllers\FailedScalarParameterConversionException;
use Aphiria\Api\Controllers\MissingControllerParameterValueException;
use Aphiria\Api\Controllers\RequestBodyDeserializationException;
use Aphiria\Api\Tests\Controllers\Mocks\ControllerWithEndpoints;
use Aphiria\Api\Tests\Controllers\Mocks\User;
use Aphiria\ContentNegotiation\FailedContentNegotiationException;
use Aphiria\ContentNegotiation\IBodyDeserializer;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;

class ControllerParameterResolverTest extends TestCase
{
    private IBodyDeserializer&MockObject $bodyDeserializer;
    private ControllerParameterResolver $resolver;

    protected function setUp(): void
    {
        $this->bodyDeserializer = $this->createMock(IBodyDeserializer::class);
        $this->resolver = new ControllerParameterResolver($this->bodyDeserializer);
    }

    public static function scalarParameterTestDataProvider(): array
    {
        return [
            ['intParameter', 'foo', '123', 123],
            ['boolParameter', 'foo', '1', true],
            ['floatParameter', 'foo', '1.1', 1.1],
            ['stringParameter', 'foo', 'bar', 'bar']
        ];
    }

    public function testResolvingArrayParameterWithMatchingQueryStringVariableThrowsException(): void
    {
        $this->expectException(FailedScalarParameterConversionException::class);
        $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'arrayParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com/?foo=bar'),
            []
        );
    }

    public function testResolvingArrayParameterWithMatchingRouteVariableThrowsException(): void
    {
        $this->expectException(FailedScalarParameterConversionException::class);
        $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'arrayParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com'),
            ['foo' => 'bar']
        );
    }

    public function testResolvingNonNullableObjectParameterWithBodyThatCannotDeserializeToTypeThrowsException(): void
    {
        $this->expectException(RequestBodyDeserializationException::class);
        $this->expectExceptionMessage('Failed to deserialize request body when resolving parameter user');
        $request = $this->createRequestWithoutBody('http://foo.com');
        $request->body = new StringBody('dummy body');
        $this->bodyDeserializer->expects($this->once())
            ->method('readRequestBodyAs')
            ->with(User::class, $request)
            ->willThrowException(new SerializationException());
        $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'objectParameter'], 'user'),
            $request,
            []
        );
    }

    public function testResolvingNonNullableObjectParameterWithBodyThatFailedContentNegotiationRethrowsException(): void
    {
        $this->expectException(FailedRequestContentNegotiationException::class);
        $this->expectExceptionMessage('Failed to negotiate request content with type ' . User::class);
        $request = $this->createRequestWithoutBody('http://foo.com');
        $request->body = new StringBody('dummy body');
        $this->bodyDeserializer->expects($this->once())
            ->method('readRequestBodyAs')
            ->with(User::class, $request)
            ->willThrowException(new FailedContentNegotiationException());
        $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'objectParameter'], 'user'),
            $request,
            []
        );
    }

    public function testResolvingNullableObjectParameterWithBodyThatCannotDeserializeToTypePassesNull(): void
    {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $request->body = new StringBody('dummy body');
        $this->bodyDeserializer->expects($this->once())
            ->method('readRequestBodyAs')
            ->with(User::class, $request)
            ->willThrowException(new SerializationException());
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'nullableObjectParameter'], 'user'),
            $request,
            []
        );
        $this->assertNull($resolvedParameter);
    }

    public function testResolvingNullableObjectParameterWithBodyThatFailsContentNegotiationReturnsNull(): void
    {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $request->body = new StringBody('dummy body');
        $this->bodyDeserializer->expects($this->once())
            ->method('readRequestBodyAs')
            ->with(User::class, $request)
            ->willThrowException(new FailedContentNegotiationException());
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'nullableObjectParameter'], 'user'),
            $request,
            []
        );
        $this->assertNull($resolvedParameter);
    }

    public function testResolvingNullableObjectParameterWithoutBodyPassesNull(): void
    {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'nullableObjectParameter'], 'user'),
            $request,
            []
        );
        $this->assertNull($resolvedParameter);
    }

    public function testResolvingNullableScalarParameterWithNoMatchingValuePassesNull(): void
    {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'nullableScalarParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
        $this->assertNull($resolvedParameter);
    }

    public function testResolvingObjectParameterAndNoRequestBodyThrowsException(): void
    {
        $this->expectException(MissingControllerParameterValueException::class);
        $this->expectExceptionMessage('Body is null when resolving parameter user');
        $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'objectParameter'], 'user'),
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
    }

    public function testResolvingObjectParameterReadsFromRequestBodyFirst(): void
    {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $request->body = new StringBody('dummy body');
        $expectedUser = new User(123, 'foo@bar.com');
        $this->bodyDeserializer->expects($this->once())
            ->method('readRequestBodyAs')
            ->with(User::class, $request)
            ->willReturn($expectedUser);
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'objectParameter'], 'user'),
            $request,
            []
        );
        $this->assertEquals($expectedUser, $resolvedParameter);
    }

    public function testResolvingParameterWithNoTypeHintUsesVariableFromRoute(): void
    {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'noTypeHintParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com'),
            ['foo' => 'bar']
        );
        $this->assertSame('bar', $resolvedParameter);
    }

    /**
     * @param string $methodName The method name
     * @param string $parameterName The parameter name
     * @param string $rawValue The raw value
     * @param mixed $scalarValue Ths scalar value
     */
    #[DataProvider('scalarParameterTestDataProvider')]
    public function testResolvingScalarParameterUsesMatchingQueryStringVariable(
        string $methodName,
        string $parameterName,
        string $rawValue,
        mixed $scalarValue
    ): void {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, $methodName], $parameterName),
            $this->createRequestWithoutBody('http://foo.com/?' . $parameterName . '=' . $rawValue),
            []
        );
        $this->assertSame($scalarValue, $resolvedParameter);
    }

    /**
     * @param string $methodName The method name
     * @param string $parameterName The parameter name
     * @param string $rawValue The raw value
     * @param mixed $scalarValue Ths scalar value
     */
    #[DataProvider('scalarParameterTestDataProvider')]
    public function testResolvingScalarParameterUsesMatchingRouteVariableOverQueryStringVariable(
        string $methodName,
        string $parameterName,
        string $rawValue,
        mixed $scalarValue
    ): void {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, $methodName], $parameterName),
            $this->createRequestWithoutBody('http://foo.com/?' . $parameterName . '=' . $rawValue),
            [$parameterName => $rawValue]
        );
        $this->assertSame($scalarValue, $resolvedParameter);
    }

    public function testResolvingScalarParameterWithUnsupportedTypeThrowsException(): void
    {
        $this->expectException(FailedScalarParameterConversionException::class);
        $this->expectExceptionMessage('Failed to convert value to ');
        $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'callableParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com/?foo=bar'),
            []
        );
    }

    public function testResolvingStringParameterAndNoMatchingVariableThrowsException(): void
    {
        $this->expectException(MissingControllerParameterValueException::class);
        $this->expectExceptionMessage('No valid value for parameter foo');
        $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'stringParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
    }

    public function testResolvingStringParameterAndNoMatchingVariableUsesDefaultValueIfAvailable(): void
    {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'defaultValueParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
        $this->assertSame('bar', $resolvedParameter);
    }

    public function testResolvingStringParameterUsesMatchingQueryStringVariable(): void
    {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'stringParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com/?foo=bar'),
            []
        );
        $this->assertSame('bar', $resolvedParameter);
    }

    public function testResolvingStringParameterUsesMatchingRouteVariableOverQueryStringVariable(): void
    {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([ControllerWithEndpoints::class, 'stringParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com/?foo=baz'),
            ['foo' => 'dave']
        );
        $this->assertSame('dave', $resolvedParameter);
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
