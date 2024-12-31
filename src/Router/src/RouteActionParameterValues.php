<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

use Aphiria\Routing\Attributes\Header;
use Aphiria\Routing\Attributes\QueryString;
use Aphiria\Routing\Attributes\RouteVariable;
use InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Defines the collection for route action parameter values
 */
final class RouteActionParameterValues
{
    /** @var array<string, mixed> The mapping of implicit parameter names to values */
    private array $implicitParameterNamesToValues = [];
    /** @var array<string, mixed> The mapping of query string parameter names to values */
    private array $queryStringNamesToValues = [];
    /** @var array<string, mixed> The mapping of route variable parameter names to values */
    private array $routeVariableNamesToValues = [];
    /** @var array<string, true> The map of unused implicit parameter names to true */
    private array $unusedImplicitParameterNames = [];
    /** @var array<string, true> The map of unused query string parameter names to true */
    private array $unusedQueryStringParameterNames = [];
    /** @var array<string, true> The map of unused route variable parameter names to true */
    private array $unusedRouteVariableParameterNames = [];

    /**
     * @param RouteAction $routeAction The route action whose parameters we are collecting
     * @param array<string, mixed> $routeVariables The mapping of route variables to values
     * @throws ReflectionException Thrown if there was an error reflecting the route action
     * @throws InvalidArgumentException Thrown if not all route variables needed to create the URI were specified
     */
    public function __construct(RouteAction $routeAction, array $routeVariables)
    {
        $reflectionMethod = new ReflectionMethod($routeAction->className, $routeAction->methodName);
        $parametersWithMissingValues = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            // Grab any route values marked as route variable attributes
            if (\count($routeVariableAttributes = $parameter->getAttributes(RouteVariable::class)) === 1) {
                $parameterName = $routeVariableAttributes[0]->newInstance()->name ?? $parameterName;

                if ($this->tryAddParameter($this->routeVariableNamesToValues, $parameter, $parameterName, $routeVariables)) {
                    $this->unusedRouteVariableParameterNames[$parameterName] = true;
                } else {
                    $parametersWithMissingValues[] = $parameterName;
                }

                continue;
            }

            // Grab any route values marked as query string attributes
            if (\count($queryStringAttributes = $parameter->getAttributes(QueryString::class)) === 1) {
                $parameterName = $queryStringAttributes[0]->newInstance()->name ?? $parameterName;

                if ($this->tryAddParameter($this->queryStringNamesToValues, $parameter, $parameterName, $routeVariables)) {
                    $this->unusedQueryStringParameterNames[$parameterName] = true;
                } else {
                    $parametersWithMissingValues[] = $parameterName;
                }

                continue;
            }

            if (\count($headerAttributes = $parameter->getAttributes(Header::class)) === 1) {
                $parameterName = $headerAttributes[0]->newInstance()->name ?? $parameterName;

                if (isset($routeVariables[$parameterName])) {
                    unset($routeVariables[$parameterName]);
                }

                continue;
            }

            // Put the rest of the variables into an implicit list
            if ($this->tryAddParameter($this->implicitParameterNamesToValues, $parameter, $parameterName, $routeVariables)) {
                $this->unusedImplicitParameterNames[$parameterName] = true;
            } else {
                $parametersWithMissingValues[] = $parameterName;
            }
        }

        $this->validateRouteVariablesAndRouteActionParameters($routeAction, $parametersWithMissingValues, $routeVariables);
    }

    /**
     * Tries to use the value of an implicit parameter
     *
     * @param string $name The name of the parameter whose value we want
     * @param mixed $value The value of the parameter
     * @param-out mixed $value
     * @return True if the parameter had a value, otherwise false
     */
    public function tryUseImplicitParameterValue(string $name, mixed &$value): bool
    {
        if (\array_key_exists($name, $this->implicitParameterNamesToValues) && isset($this->unusedImplicitParameterNames[$name])) {
            $value = $this->implicitParameterNamesToValues[$name];
            unset($this->unusedImplicitParameterNames[$name]);

            return true;
        }

        $value = null;

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
    public function tryUseQueryStringParameterValue(string $name, mixed &$value): bool
    {
        if (\array_key_exists($name, $this->queryStringNamesToValues) && isset($this->unusedQueryStringParameterNames[$name])) {
            $value = $this->queryStringNamesToValues[$name];
            unset($this->unusedQueryStringParameterNames[$name]);

            return true;
        }

        $value = null;

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
    public function tryUseRouteVariableParameterValue(string $name, mixed &$value): bool
    {
        if (\array_key_exists($name, $this->routeVariableNamesToValues) && isset($this->unusedRouteVariableParameterNames[$name])) {
            $value = $this->routeVariableNamesToValues[$name];
            unset($this->unusedRouteVariableParameterNames[$name]);

            return true;
        }

        $value = null;

        return false;
    }

    /**
     * Uses all remaining implicit parameter values
     *
     * @return array<string, mixed> The parameter names to values
     */
    public function useRemainingImplicitParameterValues(): array
    {
        $parameters = [];

        foreach ($this->unusedImplicitParameterNames as $name => $bool) {
            $parameters[$name] = $this->implicitParameterNamesToValues[$name];
            unset($this->unusedImplicitParameterNames[$name]);
        }

        return $parameters;
    }

    /**
     * Uses all remaining query string parameter values
     *
     * @return array<string, mixed> The parameter names to values
     */
    public function useRemainingQueryStringParameterValues(): array
    {
        $parameters = [];

        foreach ($this->unusedQueryStringParameterNames as $name => $bool) {
            $parameters[$name] = $this->queryStringNamesToValues[$name];
            unset($this->unusedQueryStringParameterNames[$name]);
        }

        return $parameters;
    }

    /**
     * Validates the route variables against the route action parameters to ensure no values were missing or extra variables were passed in that do not match any route action parameters
     *
     * @param RouteAction $routeAction The route action being validated
     * @param array<string, true> $parametersWithMissingValues The map of route action parameter names that were missing values to true
     * @param array<string, mixed> $routeVariables The map of route variable names to values
     * @throws InvalidArgumentException Thrown if the route action parameters did not match the route variables
     */
    private function validateRouteVariablesAndRouteActionParameters(
        RouteAction $routeAction,
        array $parametersWithMissingValues,
        array $routeVariables
    ): void {
        $exceptionMessage = '';

        if (!empty($parametersWithMissingValues)) {
            $exceptionMessage = \sprintf(
                'Following route action parameters have no matching value in %s::%s: "%s"',
                $routeAction->className,
                $routeAction->methodName,
                \implode("\", \"", $parametersWithMissingValues)
            );
        }

        // Throw an error if any extra route variables were passed in because they may indicate a logic flaw
        if (!empty($routeVariables)) {
            $exceptionMessage .= \sprintf(
                '%sollowing route variables have no matching route action parameter in %s::%s: "%s"',
                empty($exceptionMessage) ? 'F' : ', f',
                $routeAction->className,
                $routeAction->methodName,
                \implode("\", \"", \array_keys($routeVariables))
            );
        }

        if (!empty($exceptionMessage)) {
            throw new InvalidArgumentException($exceptionMessage);
        }
    }

    /**
     * Tries to add a parameter to a collection
     *
     * @param array<string, mixed> $collection The collection to add the parameter to
     * @param ReflectionParameter $parameter The reflected parameter
     * @param string $parameterName The name of the parameter (could have been retrieved from an attribute, eg #[RouteVariable('foo')])
     * @return bool True if the parameter was successfully added, otherwise false
     */
    private function tryAddParameter(
        array &$collection,
        ReflectionParameter $parameter,
        string $parameterName,
        array &$routeVariables
    ): bool {
        $successful = true;

        if (\array_key_exists($parameterName, $routeVariables)) {
            $collection[$parameterName] = $routeVariables[$parameterName];
            unset($routeVariables[$parameterName]);
        } elseif ($parameter->isDefaultValueAvailable()) {
            $collection[$parameterName] = $parameter->getDefaultValue();
        } elseif ($parameter->allowsNull()) {
            $collection[$parameterName] = null;
        } else {
            $successful = false;
        }

        return $successful;
    }
}
