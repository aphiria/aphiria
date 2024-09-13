<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware;

/**
 * Defines the parameterized middleware
 */
abstract class ParameterizedMiddleware implements IMiddleware
{
    /** @var array<string, mixed> The mapping of parameter middleware names to values */
    public array $parameters {
        set => $this->_parameters = $value;
    }
    /** @var array<string, mixed> The virtualized mapping of middleware parameters names to values */
    private array $_parameters = [];

    /**
     * Gets the value of a parameter
     *
     * @param string $name The name of the parameter to get
     * @param mixed $default The default value
     * @return mixed The parameter's value if it is set, otherwise null
     */
    protected function getParameter(string $name, mixed $default = null): mixed
    {
        if (!\array_key_exists($name, $this->_parameters)) {
            return $default;
        }

        return $this->_parameters[$name];
    }
}
