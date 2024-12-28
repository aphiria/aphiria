<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

namespace Aphiria\Routing;

use Aphiria\Routing\Attributes\Header;
use Aphiria\Routing\Attributes\QueryString;
use Aphiria\Routing\Attributes\RouteVariable;
use InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;

/**
 * Defines the collection for route action parameter values
 */
class RouteActionParameterValues
{
    /** @var array<string, mixed> The map of unused query string parameters to values */
    public array $unusedQueryStringParameters {
        get {
            $unusedParameters = [];

            foreach ($this->unusedQueryStringParameterNames as $name => $bool) {
                $unusedParameters[$name] = $this->queryStringNamesToValues[$name];
            }

            return $unusedParameters;
        }
    }
    /** @var array<string, mixed> The map of unused unspecified parameters to values */
    public array $unusedUnspecifiedParameters {
        get {
            $unusedParameters = [];

            foreach ($this->unusedUnspecifiedParameterNames as $name => $bool) {
                $unusedParameters[$name] = $this->unspecifiedParameterNamesToValues[$name];
            }

            return $unusedParameters;
        }
    }
    /** @var array<string, mixed> The mapping of header parameter names to values */
    private array $headerNamesToValues = [];
    /** @var array<string, mixed> The mapping of query string parameter names to values */
    private array $queryStringNamesToValues = [];
    /** @var array<string, mixed> The mapping of route variable parameter names to values */
    private array $routeVariableNamesToValues = [];
    /** @var array<string, mixed> The mapping of unspecified parameter names to values */
    private array $unspecifiedParameterNamesToValues = [];
    /** @var array<string, true> The map of unused query string parameter names to true */
    private array $unusedQueryStringParameterNames = [];
    /** @var array<string, true> The map of unused unspecified parameter names to true */
    private array $unusedUnspecifiedParameterNames = [];

    /**
     * @param RouteAction $routeAction The route action whose parameters we are collecting
     * @param array<string, mixed> $routeVariables The mapping of route variables to values
     * @throws ReflectionException Thrown if there was an error reflecting the route action
     * @throws InvalidArgumentException Thrown if not all route variables needed to create the route were specified
     */
    public function __construct(RouteAction $routeAction, array $routeVariables)
    {
        $reflectionMethod = new ReflectionMethod($routeAction->className, $routeAction->methodName);

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            if (\count($routeVariableAttributes = $parameter->getAttributes(RouteVariable::class)) === 1) {
                $parameterName = $routeVariableAttributes[0]->newInstance()->name ?? $parameterName;

                if (isset($routeVariables[$parameterName])) {
                    $this->routeVariableNamesToValues[$parameterName] = $routeVariables[$parameterName];
                    unset($routeVariables[$parameterName]);
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $this->routeVariableNamesToValues[$parameterName] = $parameter->getDefaultValue();
                } elseif ($parameter->allowsNull()) {
                    $this->routeVariableNamesToValues[$parameterName] = null;
                } else {
                    throw new InvalidArgumentException("Route variable \"$parameterName\" is not optional and does not have a default value");
                }

                continue;
            }

            if (\count($queryStringAttributes = $parameter->getAttributes(QueryString::class)) === 1) {
                $parameterName = $queryStringAttributes[0]->newInstance()->name ?? $parameterName;

                if (isset($routeVariables[$parameterName])) {
                    $this->queryStringNamesToValues[$parameterName] = $routeVariables[$parameterName];
                    unset($routeVariables[$parameterName]);
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $this->queryStringNamesToValues[$parameterName] = $parameter->getDefaultValue();
                } elseif ($parameter->allowsNull()) {
                    $this->queryStringNamesToValues[$parameterName] = null;
                } else {
                    throw new InvalidArgumentException("Route variable \"$parameterName\" is not optional and does not have a default value");
                }

                $this->unusedQueryStringParameterNames[$parameterName] = true;

                continue;
            }

            if (\count($headerAttributes = $parameter->getAttributes(Header::class)) === 1) {
                $parameterName = $headerAttributes[0]->newInstance()->name ?? $parameterName;

                if (isset($routeVariables[$parameterName])) {
                    $this->headerNamesToValues[$parameterName] = $routeVariables[$parameterName];
                    unset($routeVariables[$parameterName]);
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $this->headerNamesToValues[$parameterName] = $parameter->getDefaultValue();
                } elseif ($parameter->allowsNull()) {
                    $this->headerNamesToValues[$parameterName] = null;
                } else {
                    throw new InvalidArgumentException("Route variable \"$parameterName\" is not optional and does not have a default value");
                }

                continue;
            }

            if (isset($routeVariables[$parameterName])) {
                $this->unspecifiedParameterNamesToValues[$parameterName] = $routeVariables[$parameterName];
                unset($routeVariables[$parameterName]);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $this->unspecifiedParameterNamesToValues[$parameterName] = $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                $this->unspecifiedParameterNamesToValues[$parameterName] = null;
            } else {
                throw new InvalidArgumentException("Route variable \"$parameterName\" is not optional and does not have a default value");
            }

            $this->unusedUnspecifiedParameterNames[$parameterName] = true;
        }

        // Throw an error if any extra route variables were passed in because they may indicate a logic flaw
        if (!empty($routeVariables)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Following route variables have no matching parameter in route action %s::%s: "%s"',
                    $routeAction->className,
                    $routeAction->methodName,
                    \implode("\", \"", $routeVariables),
                )
            );
        }
    }

    /**
     * Gets the value of a header parameter
     *
     * @param string $name The name of the parameter whose value we want
     * @param mixed $value The value of the parameter
     * @param-out mixed $value
     * @return True if the parameter had a value, otherwise false
     */
    public function tryGetHeaderParameterValue(string $name, mixed &$value): bool
    {
        if (\array_key_exists($name, $this->headerNamesToValues)) {
            $value = $this->headerNamesToValues[$name];

            return true;
        }

        return false;
    }

    /**
     * Gets the value of a query string parameter
     *
     * @param string $name The name of the parameter whose value we want
     * @param mixed $value The value of the parameter
     * @param-out mixed $value
     * @return True if the parameter had a value, otherwise false
     */
    public function tryGetQueryStringParameterValue(string $name, mixed &$value): bool
    {
        if (\array_key_exists($name, $this->queryStringNamesToValues)) {
            $value = $this->queryStringNamesToValues[$name];
            unset($this->unusedQueryStringParameterNames[$name]);

            return true;
        }

        return false;
    }

    /**
     * Gets the value of a route variable parameter
     *
     * @param string $name The name of the parameter whose value we want
     * @param mixed $value The value of the parameter
     * @param-out mixed $value
     * @return True if the parameter had a value, otherwise false
     */
    public function tryGetRouteVariableParameterValue(string $name, mixed &$value): bool
    {
        if (\array_key_exists($name, $this->routeVariableNamesToValues)) {
            $value = $this->routeVariableNamesToValues[$name];

            return true;
        }

        return false;
    }

    /**
     * Gets the value of an unspecified parameter
     *
     * @param string $name The name of the parameter whose value we want
     * @param mixed $value The value of the parameter
     * @param-out mixed $value
     * @return True if the parameter had a value, otherwise false
     */
    public function tryGetUnspecifiedParameterValue(string $name, mixed &$value): bool
    {
        if (\array_key_exists($name, $this->unspecifiedParameterNamesToValues)) {
            $value = $this->unspecifiedParameterNamesToValues[$name];
            // Mark this as used
            unset($this->unusedUnspecifiedParameterNames[$name]);

            return true;
        }

        return false;
    }
}
