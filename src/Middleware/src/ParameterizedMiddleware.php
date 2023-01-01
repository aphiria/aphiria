<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware;

/**
 * Defines the parameterized middleware
 */
abstract class ParameterizedMiddleware implements IMiddleware
{
    /** @var array The middleware parameters */
    private array $parameters = [];

    /**
     * Sets the parameters
     *
     * @param array<string, mixed> $parameters The parameters to set
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Gets the value of a parameter
     *
     * @param string $name The name of the parameter to get
     * @param mixed $default The default value
     * @return mixed The parameter's value if it is set, otherwise null
     */
    protected function getParameter(string $name, mixed $default = null): mixed
    {
        if (!\array_key_exists($name, $this->parameters)) {
            return $default;
        }

        return $this->parameters[$name];
    }
}
