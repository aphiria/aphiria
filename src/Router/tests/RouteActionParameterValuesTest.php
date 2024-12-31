<?php

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\Attributes\Header;
use Aphiria\Routing\Attributes\QueryString;
use Aphiria\Routing\Attributes\RouteVariable;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\RouteActionParameterValues;
use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RouteActionParameterValuesTest extends TestCase
{

    public static function defaultValueParameterProvider(): array
    {
        $controller = new class() {
            public function implicit(string $foo = 'bar'): void
            {
            }
            public function queryString(#[QueryString] string $foo = 'bar'): void
            {
            }
            public function routeVariable(#[RouteVariable] string $foo = 'bar'): void
            {
            }
        };

        return [
            [
                $controller,
                'implicit',
                fn (RouteActionParameterValues $parameters, mixed &$value) => $parameters->tryUseImplicitParameterValue('foo', $value),
                'bar'
            ],
            [
                $controller,
                'queryString',
                fn (RouteActionParameterValues $parameters, mixed &$value) => $parameters->tryUseQueryStringParameterValue('foo', $value),
                'bar'
            ],
            [
                $controller,
                'routeVariable',
                fn (RouteActionParameterValues $parameters, mixed &$value) => $parameters->tryUseRouteVariableParameterValue('foo', $value),
                'bar'
            ]
        ];
    }


    public static function nullableParameterProvider(): array
    {
        $controller = new class() {
            public function implicit(?string $foo): void
            {
            }
            public function queryString(#[QueryString] ?string $foo): void
            {
            }
            public function routeVariable(#[RouteVariable] ?string $foo): void
            {
            }
        };

        return [
            [
                $controller,
                'implicit',
                fn (RouteActionParameterValues $parameters, mixed &$value) => $parameters->tryUseImplicitParameterValue('foo', $value)
            ],
            [
                $controller,
                'queryString',
                fn (RouteActionParameterValues $parameters, mixed &$value) => $parameters->tryUseQueryStringParameterValue('foo', $value)
            ],
            [
                $controller,
                'routeVariable',
                fn (RouteActionParameterValues $parameters, mixed &$value) => $parameters->tryUseRouteVariableParameterValue('foo', $value)
            ]
        ];
    }

    public static function missingRouteVariableProvider(): array
    {
        $controller = new class() {
            public function implicitMultiple(string $foo, string $bar): void
            {
            }

            public function implicitSingle(string $foo): void
            {
            }

            public function queryStringMultiple(#[QueryString] string $foo, #[QueryString] string $bar): void
            {
            }

            public function queryStringSingle(#[QueryString] string $foo): void
            {
            }

            public function routeVariableMultiple(#[RouteVariable] string $foo, #[RouteVariable] string $bar): void
            {
            }

            public function routeVariableSingle(#[RouteVariable] string $foo): void
            {
            }
        };

        return [
            [$controller, 'implicitMultiple', ['foo', 'bar']],
            [$controller, 'implicitSingle', ['foo']],
            [$controller, 'queryStringMultiple', ['foo', 'bar']],
            [$controller, 'queryStringSingle', ['foo']],
            [$controller, 'routeVariableMultiple', ['foo', 'bar']],
            [$controller, 'routeVariableSingle', ['foo']],
        ];
    }

    public function testUsingRemainingQueryStringAttributeParametersWithNamesReturnsThoseNames(): void
    {
        $controller = new class() {
            public function foo(#[QueryString('baz')] string $foo, #[QueryString('quz')] string $bar): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['baz' => '1', 'quz' => '2']);
        $this->assertSame(['baz' => '1', 'quz' => '2'], $parameters->useRemainingQueryStringParameterValues());
    }

    public function testUsingRemainingQueryStringAttributeParametersWithoutNamesReturnsNamesOfParameters(): void
    {
        $controller = new class() {
            public function foo(#[QueryString] string $foo, #[QueryString] string $bar): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['foo' => '1', 'bar' => '2']);
        $this->assertSame(['foo' => '1', 'bar' => '2'], $parameters->useRemainingQueryStringParameterValues());
    }

    public function testUsingRemainingImplicitParametersOnlyReturnsValuesThatAreUnused(): void
    {
        $controller = new class() {
            public function foo(string $foo, string $bar): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['foo' => '1', 'bar' => '2']);
        $this->assertSame(['foo' => '1', 'bar' => '2'], $parameters->useRemainingImplicitParameterValues());
        $this->assertEmpty($parameters->useRemainingImplicitParameterValues());
    }

    public function testUsingRemainingQueryStringParametersOnlyReturnsValuesThatAreUnused(): void
    {
        $controller = new class() {
            public function foo(#[QueryString] string $foo, #[QueryString] string $bar): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['foo' => '1', 'bar' => '2']);
        $this->assertSame(['foo' => '1', 'bar' => '2'], $parameters->useRemainingQueryStringParameterValues());
        $this->assertEmpty($parameters->useRemainingQueryStringParameterValues());
    }

    public function testHeaderAttributeParametersAreNotIncludedInAnyCollection(): void
    {
        $controller = new class() {
            public function foo(#[Header] string $foo): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, []);
        $this->assertFalse($parameters->tryUseRouteVariableParameterValue('foo', $value));
        $this->assertNull($value);
        $this->assertFalse($parameters->tryUseImplicitParameterValue('foo', $value));
        $this->assertNull($value);
        $this->assertEmpty($parameters->useRemainingQueryStringParameterValues());
        $this->assertEmpty($parameters->useRemainingImplicitParameterValues());
    }

    public function testTryingToUseImplicitParametersOnlyReturnsValuesThatAreUnused(): void
    {
        $controller = new class() {
            public function foo(string $foo): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['foo' => '1']);
        $value = null;
        $this->assertTrue($parameters->tryUseImplicitParameterValue('foo', $value));
        $this->assertSame('1', $value);
        $value = null;
        $this->assertFalse($parameters->tryUseQueryStringParameterValue('foo', $value));
        $this->assertNull($value);
    }

    public function testTryingToUseQueryStringParametersOnlyReturnsValuesThatAreUnused(): void
    {
        $controller = new class() {
            public function foo(#[QueryString] string $foo): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['foo' => '1']);
        $value = null;
        $this->assertTrue($parameters->tryUseQueryStringParameterValue('foo', $value));
        $this->assertSame('1', $value);
        $value = null;
        $this->assertFalse($parameters->tryUseQueryStringParameterValue('foo', $value));
        $this->assertNull($value);
    }

    public function testTryingToUseRouteVariableParametersOnlyReturnsValuesThatAreUnused(): void
    {
        $controller = new class() {
            public function foo(#[RouteVariable] string $foo): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['foo' => '1']);
        $value = null;
        $this->assertTrue($parameters->tryUseRouteVariableParameterValue('foo', $value));
        $this->assertSame('1', $value);
        $value = null;
        $this->assertFalse($parameters->tryUseRouteVariableParameterValue('foo', $value));
        $this->assertNull($value);
    }

    public function testTryingToUseExistingRouteVariableAttributeParameterReturnsTrue(): void
    {
        $controller = new class() {
            public function foo(#[RouteVariable] string $foo): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['foo' => 'bar']);
        $this->assertTrue($parameters->tryUseRouteVariableParameterValue('foo', $value));
        $this->assertSame('bar', $value);
    }

    public function testTryingToUseExistingRouteVariableAttributeParameterWithNameReturnsTrue(): void
    {
        $controller = new class() {
            public function foo(#[RouteVariable('bar')] string $foo): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['bar' => 'baz']);
        $this->assertTrue($parameters->tryUseRouteVariableParameterValue('bar', $value));
        $this->assertSame('baz', $value);
    }

    public function testTryingToUseNonExistentRouteVariableAttributeParameterReturnsFalseAndUnsetsValue(): void
    {
        $controller = new class() {
            public function foo(#[RouteVariable] string $foo): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['foo' => 'bar']);
        // Set the value to a dummy value so we can test that it's set back to null
        $value = 'foo';
        $this->assertFalse($parameters->tryUseRouteVariableParameterValue('baz', $value));
        $this->assertNull($value);
    }

    public function testTryingToUseNonExistentRouteVariableAttributeParameterWithNameReturnsFalseAndUnsetsValue(): void
    {
        $controller = new class() {
            public function foo(#[RouteVariable('bar')] string $foo): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['bar' => 'baz']);
        // Set the value to a dummy value so we can test that it's set back to null
        $value = 'foo';
        $this->assertFalse($parameters->tryUseRouteVariableParameterValue('foo', $value));
        $this->assertNull($value);
    }

    public function testTryingToUseExistingImplicitParameterReturnsTrue(): void
    {
        $controller = new class() {
            public function foo(string $foo): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, ['foo' => 'bar']);
        $this->assertTrue($parameters->tryUseImplicitParameterValue('foo', $value));
        $this->assertSame('bar', $value);
    }

    public function testTryingToUseNonExistentImplicitParameterReturnsFalseAndUnsetsValue(): void
    {
        $controller = new class() {
            public function foo(): void
            {
            }
        };
        $routeAction = new RouteAction($controller::class, 'foo');
        $parameters = new RouteActionParameterValues($routeAction, []);
        // Set the value to a dummy value so we can test that it's set back to null
        $value = 'foo';
        $this->assertFalse($parameters->tryUseImplicitParameterValue('foo', $value));
        $this->assertNull($value);
    }

    public function testCreatingCollectionWithMultipleRouteVariableWithNoCorrespondingParametersThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $controller = new class() {
            public function foo(): void
            {
            }
        };
        $this->expectExceptionMessage('Following route variables have no matching route action parameter in ' . $controller::class . '::foo: "foo", "baz"');
        $routeAction = new RouteAction($controller::class, 'foo');
        new RouteActionParameterValues($routeAction, ['foo' => 'bar', 'baz' => 'quz']);
    }

    public function testCreatingCollectionWithSingleRouteVariableWithNoCorrespondingParametersThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $controller = new class() {
            public function foo(): void
            {
            }
        };
        $this->expectExceptionMessage('Following route variables have no matching route action parameter in ' . $controller::class . '::foo: "foo"');
        $routeAction = new RouteAction($controller::class, 'foo');
        new RouteActionParameterValues($routeAction, ['foo' => 'bar']);
    }

    /**
     * @param object $controller The route action controller
     * @param string $methodName The name of the route action method
     * @param Closure(RouteActionParameterValues, mixed): bool $valueGetter The getter callback to retrieve the parameter value
     * @param mixed $expectedValue The expected value
     */
    #[DataProvider('defaultValueParameterProvider')]
    public function testCreatingCollectionWithParametersWithoutRouteVariableCanUseDefaultValueIfAvailable(
        object $controller,
        string $methodName,
        Closure $valueGetter,
        mixed $expectedValue
    ): void
    {
        $routeAction = new RouteAction($controller::class, $methodName);
        $parameters = new RouteActionParameterValues($routeAction, []);
        $value = null;
        $this->assertTrue($valueGetter($parameters, $value));
        $this->assertSame($expectedValue, $value);
    }

    /**
     * @param object $controller The route action controller
     * @param string $methodName The name of the route action method
     * @param Closure(RouteActionParameterValues, mixed): bool $valueGetter The getter callback to retrieve the parameter value
     */
    #[DataProvider('nullableParameterProvider')]
    public function testCreatingCollectionWithParametersWithoutRouteVariableCanUseNullIfNullable(
        object $controller,
        string $methodName,
        Closure $valueGetter
    ): void
    {
        $routeAction = new RouteAction($controller::class, $methodName);
        $parameters = new RouteActionParameterValues($routeAction, []);
        $value = null;
        $this->assertTrue($valueGetter($parameters, $value));
        $this->assertNull($value);
    }

    /**
     * @param object $controller The route action controller
     * @param string $methodName The name of the route action method
     * @param list<string> $missingParameterNames The list of missing parameter names
     */
    #[DataProvider('missingRouteVariableProvider')]
    public function testCreatingCollectionWithParameterWithNoRouteVariableThrowsException(
        object $controller,
        string $methodName,
        array $missingParameterNames
    ): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Following route action parameters have no matching value in ' . $controller::class . '::' . $methodName . ': "' . \implode('", "', $missingParameterNames) . '"');
        $routeAction = new RouteAction($controller::class, $methodName);
        new RouteActionParameterValues($routeAction, []);
    }
}
