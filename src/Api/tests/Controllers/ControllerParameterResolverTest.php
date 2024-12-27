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

use Aphiria\Api\Controllers\Controller;
use Aphiria\Api\Controllers\ControllerParameterResolver;
use Aphiria\Api\Controllers\FailedRequestContentNegotiationException;
use Aphiria\Api\Controllers\FailedScalarParameterConversionException;
use Aphiria\Api\Controllers\MissingControllerParameterValueException;
use Aphiria\Api\Controllers\RequestBodyDeserializationException;
use Aphiria\Api\Tests\Controllers\Mocks\User;
use Aphiria\ContentNegotiation\FailedContentNegotiationException;
use Aphiria\ContentNegotiation\IBodyDeserializer;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use Aphiria\Routing\Attributes\Header;
use Aphiria\Routing\Attributes\QueryString;
use Aphiria\Routing\Attributes\RouteVariable;
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

    public static function scalarParameterWithQueryStringValuesDataProvider(): array
    {
        $controller = new class () extends Controller {
            public function boolParameterWithName(#[QueryString('bar')] bool $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function boolParameterWithNoName(#[QueryString] bool $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function defaultValueWithName(#[QueryString('bar')] string $foo = 'bar'): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            public function defaultValueWithNoName(#[QueryString] string $foo = 'bar'): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            public function floatParameterWithName(#[QueryString('bar')] float $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function floatParameterWithNoName(#[QueryString] float $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function intParameterWithName(#[QueryString('bar')] int $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function intParameterWithNoName(#[QueryString] int $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function noTypeWithName(#[QueryString('bar')] $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function noTypeWithNoName(#[QueryString] $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function stringParameterWithName(#[QueryString('bar')] string $foo): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            public function stringParameterWithNoName(#[QueryString] string $foo): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            private function createResponseWithBody(string $body): Response
            {
                return new Response(HttpStatusCode::Ok, body: new StringBody($body));
            }
        };

        return [
            [$controller, 'boolParameterWithNoName', 'foo', '1', true],
            [$controller, 'defaultValueWithNoName', 'foo', 'bar', 'bar'],
            [$controller, 'floatParameterWithNoName', 'foo', '1.1', 1.1],
            [$controller, 'intParameterWithNoName', 'foo', '123', 123],
            [$controller, 'noTypeWithNoName', 'foo', 'bar', 'bar'],
            [$controller, 'stringParameterWithNoName', 'foo', 'bar', 'bar'],
            [$controller, 'boolParameterWithName', 'foo', '1', true, 'bar'],
            [$controller, 'defaultValueWithName', 'foo', 'bar', 'bar', 'bar'],
            [$controller, 'floatParameterWithName', 'foo', '1.1', 1.1, 'bar'],
            [$controller, 'intParameterWithName', 'foo', '123', 123, 'bar'],
            [$controller, 'noTypeWithName', 'foo', 'bar', 'bar', 'bar'],
            [$controller, 'stringParameterWithName', 'foo', 'bar', 'bar', 'bar']
        ];
    }

    public static function scalarParameterWithHeaderValuesDataProvider(): array
    {
        $controller = new class () extends Controller {
            public function boolParameterWithName(#[Header('bar')] bool $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function boolParameterWithNoName(#[Header] bool $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function defaultValueWithName(#[QueryString('bar')] string $foo = 'bar'): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            public function defaultValueWithNoName(#[QueryString] string $foo = 'bar'): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            public function floatParameterWithName(#[Header('bar')] float $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function floatParameterWithNoName(#[Header] float $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function intParameterWithName(#[Header('bar')] int $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function intParameterWithNoName(#[Header] int $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function noTypeWithName(#[Header('bar')] $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function noTypeWithNoName(#[Header] $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function stringParameterWithName(#[Header('bar')] string $foo): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            public function stringParameterWithNoName(#[Header] string $foo): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            private function createResponseWithBody(string $body): Response
            {
                return new Response(HttpStatusCode::Ok, body: new StringBody($body));
            }
        };

        return [
            [$controller, 'boolParameterWithNoName', 'foo', '1', true],
            [$controller, 'defaultValueWithNoName', 'foo', 'bar', 'bar'],
            [$controller, 'floatParameterWithNoName', 'foo', '1.1', 1.1],
            [$controller, 'intParameterWithNoName', 'foo', '123', 123],
            [$controller, 'noTypeWithNoName', 'foo', 'bar', 'bar'],
            [$controller, 'stringParameterWithNoName', 'foo', 'bar', 'bar'],
            [$controller, 'boolParameterWithName', 'foo', '1', true, 'bar'],
            [$controller, 'defaultValueWithName', 'foo', 'bar', 'bar', 'bar'],
            [$controller, 'floatParameterWithName', 'foo', '1.1', 1.1, 'bar'],
            [$controller, 'intParameterWithName', 'foo', '123', 123, 'bar'],
            [$controller, 'noTypeWithName', 'foo', 'bar', 'bar', 'bar'],
            [$controller, 'stringParameterWithName', 'foo', 'bar', 'bar', 'bar']
        ];
    }

    public static function scalarParameterWithRouteVariableValuesDataProvider(): array
    {
        $controller = new class () extends Controller {
            public function boolParameterWithName(#[RouteVariable('bar')] bool $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function boolParameterWithNoName(#[RouteVariable] bool $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function defaultValueWithName(#[QueryString('bar')] string $foo = 'bar'): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            public function defaultValueWithNoName(#[QueryString] string $foo = 'bar'): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            public function floatParameterWithName(#[RouteVariable('bar')] float $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function floatParameterWithNoName(#[RouteVariable] float $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function intParameterWithName(#[RouteVariable('bar')] int $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function intParameterWithNoName(#[RouteVariable] int $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function noTypeWithName(#[RouteVariable('bar')] $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function noTypeWithNoName(#[RouteVariable] $foo): IResponse
            {
                return $this->createResponseWithBody((string)$foo);
            }

            public function stringParameterWithName(#[RouteVariable('bar')] string $foo): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            public function stringParameterWithNoName(#[RouteVariable] string $foo): IResponse
            {
                return $this->createResponseWithBody($foo);
            }

            private function createResponseWithBody(string $body): Response
            {
                return new Response(HttpStatusCode::Ok, body: new StringBody($body));
            }
        };

        return [
            [$controller, 'boolParameterWithNoName', 'foo', '1', true],
            [$controller, 'defaultValueWithNoName', 'foo', 'bar', 'bar'],
            [$controller, 'floatParameterWithNoName', 'foo', '1.1', 1.1],
            [$controller, 'intParameterWithNoName', 'foo', '123', 123],
            [$controller, 'noTypeWithNoName', 'foo', 'bar', 'bar'],
            [$controller, 'stringParameterWithNoName', 'foo', 'bar', 'bar'],
            [$controller, 'boolParameterWithName', 'foo', '1', true, 'bar'],
            [$controller, 'defaultValueWithName', 'foo', 'bar', 'bar', 'bar'],
            [$controller, 'floatParameterWithName', 'foo', '1.1', 1.1, 'bar'],
            [$controller, 'intParameterWithName', 'foo', '123', 123, 'bar'],
            [$controller, 'noTypeWithName', 'foo', 'bar', 'bar', 'bar'],
            [$controller, 'stringParameterWithName', 'foo', 'bar', 'bar', 'bar']
        ];
    }

    public static function headerWithNoValidValuesDataProvider(): array
    {
        $controller = new class () extends Controller {
            public function fooWithAttribute(#[Header] string $foo): IResponse
            {
                return new Response();
            }
            public function fooWithNamedAttribute(#[Header('bar')] string $foo): IResponse
            {
                return new Response();
            }

            public function fooWithNoAttribute(string $foo): IResponse
            {
                return new Response();
            }
        };

        return [
            [$controller, 'fooWithAttribute', 'foo'],
            [$controller, 'fooWithNamedAttribute', 'foo'],
            [$controller, 'fooWithNoAttribute', 'foo'],
        ];
    }

    public static function queryStringWithNoValidValuesDataProvider(): array
    {
        $controller = new class () extends Controller {
            public function fooWithAttribute(#[QueryString] string $foo): IResponse
            {
                return new Response();
            }
            public function fooWithNamedAttribute(#[QueryString('bar')] string $foo): IResponse
            {
                return new Response();
            }

            public function fooWithNoAttribute(string $foo): IResponse
            {
                return new Response();
            }
        };

        return [
            [$controller, 'fooWithAttribute', 'foo'],
            [$controller, 'fooWithNamedAttribute', 'foo'],
            [$controller, 'fooWithNoAttribute', 'foo'],
        ];
    }

    public static function routeVariableWithNoValidValuesDataProvider(): array
    {
        $controller = new class () extends Controller {
            public function fooWithAttribute(#[RouteVariable] string $foo): IResponse
            {
                return new Response();
            }
            public function fooWithNamedAttribute(#[RouteVariable('bar')] string $foo): IResponse
            {
                return new Response();
            }

            public function fooWithNoAttribute(string $foo): IResponse
            {
                return new Response();
            }
        };

        return [
            [$controller, 'fooWithAttribute', 'foo'],
            [$controller, 'fooWithNamedAttribute', 'foo'],
            [$controller, 'fooWithNoAttribute', 'foo'],
        ];
    }

    public function testResolvingArrayParameterWithMatchingQueryStringVariableThrowsException(): void
    {
        $this->expectException(FailedScalarParameterConversionException::class);
        $controller = new class () extends Controller
        {
            public function arrayParameter(array $foo): IResponse
            {
                return new Response();
            }
        };
        $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'arrayParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com/?foo=bar'),
            []
        );
    }

    public function testResolvingArrayParameterWithMatchingRouteVariableThrowsException(): void
    {
        $this->expectException(FailedScalarParameterConversionException::class);
        $controller = new class () extends Controller
        {
            public function arrayParameter(array $foo): IResponse
            {
                return new Response();
            }
        };
        $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'arrayParameter'], 'foo'),
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
        $controller = new class () extends Controller
        {
            public function objectParameter(User $user): IResponse
            {
                return new Response(body: new StringBody("id:{$user->id}, email:{$user->email}"));
            }
        };
        $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'objectParameter'], 'user'),
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
        $controller = new class () extends Controller
        {
            public function objectParameter(User $user): IResponse
            {
                return new Response(body: new StringBody("id:{$user->id}, email:{$user->email}"));
            }
        };
        $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'objectParameter'], 'user'),
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
        $controller = new class () extends Controller
        {
            public function nullableObjectParameter(?User $user): IResponse
            {
                return new Response(body: new StringBody($user === null ? 'null' : 'notnull'));
            }
        };
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'nullableObjectParameter'], 'user'),
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
        $controller = new class () extends Controller
        {
            public function nullableObjectParameter(?User $user): IResponse
            {
                return new Response(body: new StringBody($user === null ? 'null' : 'notnull'));
            }
        };
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'nullableObjectParameter'], 'user'),
            $request,
            []
        );
        $this->assertNull($resolvedParameter);
    }

    public function testResolvingNullableObjectParameterWithoutBodyPassesNull(): void
    {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $controller = new class () extends Controller
        {
            public function nullableObjectParameter(?User $user): IResponse
            {
                return new Response(body: new StringBody($user === null ? 'null' : 'notnull'));
            }
        };
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'nullableObjectParameter'], 'user'),
            $request,
            []
        );
        $this->assertNull($resolvedParameter);
    }

    public function testResolvingNullableScalarParameterWithNoMatchingValuePassesNull(): void
    {
        $controller = new class () extends Controller
        {
            public function nullableScalarParameter(?int $foo): IResponse
            {
                return new Response(body: new StringBody($foo === null ? 'null' : 'notnull'));
            }
        };
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'nullableScalarParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
        $this->assertNull($resolvedParameter);
    }

    public function testResolvingObjectParameterAndNoRequestBodyThrowsException(): void
    {
        $this->expectException(MissingControllerParameterValueException::class);
        $this->expectExceptionMessage('Body is null when resolving parameter user');
        $controller = new class () extends Controller
        {
            public function objectParameter(User $user): IResponse
            {
                return new Response(body: new StringBody("id:{$user->id}, email:{$user->email}"));
            }
        };
        $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'objectParameter'], 'user'),
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
        $controller = new class () extends Controller
        {
            public function objectParameter(User $user): IResponse
            {
                return new Response(body: new StringBody("id:{$user->id}, email:{$user->email}"));
            }
        };
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'objectParameter'], 'user'),
            $request,
            []
        );
        $this->assertEquals($expectedUser, $resolvedParameter);
    }

    /**
     * @param Controller $controller The controller
     * @param string $methodName The method name
     * @param string $parameterName The parameter name
     * @param string $rawValue The raw value
     * @param mixed $scalarValue Ths scalar value
     * @param string|null $parameterNameFromAttribute The parameter name used in the attribute
     */
    #[DataProvider('scalarParameterWithQueryStringValuesDataProvider')]
    public function testResolvingScalarParametersWithQueryStringAttributeUsesQueryString(
        Controller $controller,
        string $methodName,
        string $parameterName,
        string $rawValue,
        mixed $scalarValue,
        ?string $parameterNameFromAttribute = null
    ): void {
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, $methodName], $parameterName),
            $this->createRequestWithoutBody('http://foo.com/?' . ($parameterNameFromAttribute ?? $parameterName) . '=' . $rawValue),
            // Add this parameter to the route variables just to ensure it's not being used
            [$parameterName => 'doNotUse']
        );
        $this->assertSame($scalarValue, $resolvedParameter);
    }

    /**
     * @param Controller $controller The controller
     * @param string $methodName The method name
     * @param string $parameterName The parameter name
     * @param string $rawValue The raw value
     * @param mixed $scalarValue Ths scalar value
     * @param string|null $parameterNameFromAttribute The parameter name used in the attribute
     */
    #[DataProvider('scalarParameterWithRouteVariableValuesDataProvider')]
    public function testResolvingScalarParametersWithRouteVariableAttributeUsesRouteVariable(
        Controller $controller,
        string $methodName,
        string $parameterName,
        string $rawValue,
        mixed $scalarValue,
        ?string $parameterNameFromAttribute = null
    ): void {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $request->headers->add($parameterNameFromAttribute ?? $parameterName, $rawValue);

        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, $methodName], $parameterName),
            $this->createRequestWithoutBody('http://foo.com/?' . ($parameterNameFromAttribute ?? $parameterName) . '=' . $rawValue),
            // Add this parameter to the route variables just to ensure it's not being used
            [$parameterNameFromAttribute ?? $parameterName => $rawValue]
        );
        $this->assertSame($scalarValue, $resolvedParameter);
    }

    /**
     * @param Controller $controller The controller
     * @param string $methodName The method name
     * @param string $parameterName The parameter name
     * @param string $rawValue The raw value
     * @param mixed $scalarValue Ths scalar value
     * @param string|null $parameterNameFromAttribute The parameter name used in the attribute
     */
    #[DataProvider('scalarParameterWithHeaderValuesDataProvider')]
    public function testResolvingScalarParametersWithHeaderAttributeUsesHeader(
        Controller $controller,
        string $methodName,
        string $parameterName,
        string $rawValue,
        mixed $scalarValue,
        ?string $parameterNameFromAttribute = null
    ): void {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $request->headers->add($parameterNameFromAttribute ?? $parameterName, $rawValue);

        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, $methodName], $parameterName),
            $request,
            // Add this parameter to the route variables just to ensure it's not being used
            [$parameterName => 'doNotUse']
        );
        $this->assertSame($scalarValue, $resolvedParameter);
    }

    public function testResolvingScalarParameterWillPullValueFromQueryStringIfNotAvailableInRoute(): void
    {
        $controller = new class () extends Controller {
            public function foo(string $foo): IResponse
            {
                return new Response(body: new StringBody($foo));
            }
        };
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'foo'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com/?foo=bar'),
            []
        );
        $this->assertSame('bar', $resolvedParameter);
    }

    public function testResolvingScalarParameterWillPullValueFromRouteIfItIsAvailableEvenIfItIsAlsoAvailableInQueryString(): void
    {
        $controller = new class () extends Controller {
            public function foo(string $foo): IResponse
            {
                return new Response(body: new StringBody($foo));
            }
        };
        $resolvedParameter = $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'foo'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com/?foo=bar'),
            ['foo' => 'baz']
        );
        $this->assertSame('baz', $resolvedParameter);
    }

    public function testResolvingScalarParameterWithUnsupportedTypeThrowsException(): void
    {
        $this->expectException(FailedScalarParameterConversionException::class);
        $this->expectExceptionMessage('Failed to convert value to ');
        $controller = new class () extends Controller
        {
            public function callableParameter(callable $foo): IResponse
            {
                return new Response(body: new StringBody((string)$foo));
            }
        };
        $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, 'callableParameter'], 'foo'),
            $this->createRequestWithoutBody('http://foo.com/?foo=bar'),
            []
        );
    }

    /**
     * @param Controller $controller The controller
     * @param string $methodName The method name
     * @param string $parameterName The parameter name
     */
    #[DataProvider('headerWithNoValidValuesDataProvider')]
    public function testResolvingHeaderParametersWithoutValidValueThrowsException(
        Controller $controller,
        string $methodName,
        string $parameterName
    ): void {
        $this->expectException(MissingControllerParameterValueException::class);
        $this->expectExceptionMessage("No valid value for parameter $parameterName");
        $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, $methodName], $parameterName),
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
    }

    /**
     * @param Controller $controller The controller
     * @param string $methodName The method name
     * @param string $parameterName The parameter name
     */
    #[DataProvider('queryStringWithNoValidValuesDataProvider')]
    public function testResolvingQueryStringParametersWithoutValidValueThrowsException(
        Controller $controller,
        string $methodName,
        string $parameterName
    ): void {
        $this->expectException(MissingControllerParameterValueException::class);
        $this->expectExceptionMessage("No valid value for parameter $parameterName");
        $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, $methodName], $parameterName),
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
    }

    /**
     * @param Controller $controller The controller
     * @param string $methodName The method name
     * @param string $parameterName The parameter name
     */
    #[DataProvider('routeVariableWithNoValidValuesDataProvider')]
    public function testResolvingRouteParametersWithoutValidValueThrowsException(
        Controller $controller,
        string $methodName,
        string $parameterName
    ): void {
        $this->expectException(MissingControllerParameterValueException::class);
        $this->expectExceptionMessage("No valid value for parameter $parameterName");
        $this->resolver->resolveParameter(
            new ReflectionParameter([$controller, $methodName], $parameterName),
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
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
