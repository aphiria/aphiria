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
    /** @var array<string, mixed> The map of query string parameters to values */
    public array $queryStringParameters {
        get {
            $unusedParameters = $this->queryStringNamesToValues;

            foreach ($this->unusedUnspecifiedParameters as $name => $bool) {
                $unusedParameters[$name] = $this->unspecifiedParameterNamesToValues[$name];
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
    /** @var array<string, mixed> The mapping of query string parameter names to values */
    private array $queryStringNamesToValues = [];
    /** @var array<string, mixed> The mapping of route variable parameter names to values */
    private array $routeVariableNamesToValues = [];
    /** @var array<string, mixed> The mapping of unspecified parameter names to values */
    private array $unspecifiedParameterNamesToValues = [];
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
        $parametersWithMissingValues = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            // Grab any route values marked as route variable attributes
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
                    $parametersWithMissingValues[] = $parameterName;
                }

                continue;
            }

            // Grab any route values marked as query string attributes
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
                    $parametersWithMissingValues[] = $parameterName;
                }

                $this->unusedQueryStringParameterNames[$parameterName] = true;

                continue;
            }

            if (\count($parameter->getAttributes(Header::class)) > 0) {
                // We specifically do not care about header values because they're not used to construct URIs
                continue;
            }

            // Put the rest of the variables into an unspecified list
            if (isset($routeVariables[$parameterName])) {
                $this->unspecifiedParameterNamesToValues[$parameterName] = $routeVariables[$parameterName];
                unset($routeVariables[$parameterName]);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $this->unspecifiedParameterNamesToValues[$parameterName] = $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                $this->unspecifiedParameterNamesToValues[$parameterName] = null;
            } else {
                $parametersWithMissingValues[] = $parameterName;
            }

            $this->unusedUnspecifiedParameterNames[$parameterName] = true;
        }

        $this->validateRouteVariablesAndRouteActionParameters($routeAction, $parametersWithMissingValues, $routeVariables);
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
            if (!empty($exceptionMessage)) {
                $exceptionMessage .= '.  ';
            }

            $exceptionMessage .= \sprintf(
                'Following route variables have no matching route action parameter in %s::%s: "%s"',
                $routeAction->className,
                $routeAction->methodName,
                \implode("\", \"", \array_keys($routeVariables))
            );
        }

        if (!empty($exceptionMessage)) {
            throw new InvalidArgumentException($exceptionMessage);
        }
    }
}
