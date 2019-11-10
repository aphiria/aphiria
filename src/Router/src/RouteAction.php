<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

use Closure;
use InvalidArgumentException;

/**
 * Defines a route action
 */
class RouteAction
{
    // Note - These are protected rather than private for serialization purposes
    /** @var string|null The name of the class the route routes to */
    public ?string $className;
    /** @var string|null The name of the method the route routes to */
    public ?string $methodName;
    /** @var Closure|null The closure the route routes to */
    public ?Closure $closure;
    /** @var string The serialized closure */
    protected string $serializedClosure = '';

    /**
     * @param string|null $className The name of the class the route routes to
     * @param string|null $methodName The name of the method the route routes to
     * @param Closure|null $closure The closure the route routes to
     * @throws InvalidArgumentException Thrown if no valid class or closure is specified
     */
    public function __construct(?string $className, ?string $methodName, ?Closure $closure)
    {
        // Check if everything was set or nothing was set
        if (
            ($className !== null && $closure !== null)
            || (($className === null || $methodName === null) && $closure === null)
        ) {
            throw new InvalidArgumentException('Must specify either a class name or closure');
        }

        $this->className = $className;
        $this->methodName = $methodName;
        $this->closure = $closure;
    }

    /**
     * Performs a deep clone of object properties
     */
    public function __clone()
    {
        if ($this->closure !== null) {
            $this->closure = clone $this->closure;
        }
    }

    /**
     * Serializes the action
     *
     * @return array The list of properties to store
     */
    public function __sleep(): array
    {
        if ($this->closure === null) {
            $this->serializedClosure = '';
        } else {
            $this->serializedClosure = \Opis\Closure\serialize($this->closure);
            $this->closure = null;
        }

        return \array_keys(\get_object_vars($this));
    }

    /**
     * Deserializes the action
     */
    public function __wakeup()
    {
        if ($this->serializedClosure === '') {
            $this->closure = null;
        } else {
            $this->closure = \Opis\Closure\unserialize($this->serializedClosure);
        }

        $this->serializedClosure = '';
    }

    /**
     * Gets whether or not this action uses a method
     *
     * @return bool True if this uses a method, otherwise false
     */
    public function usesMethod(): bool
    {
        return $this->closure === null;
    }
}
