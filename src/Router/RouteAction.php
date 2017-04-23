<?php
namespace Opulence\Router;

use Closure;
use InvalidArgumentException;
use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Serializer;
use SuperClosure\SerializerInterface;

/**
 * Defines a route action
 */
class RouteAction
{
    // Note - These are protected rather than private for serialization purposes
    /** @var string|null The name of the class the route routes to */
    protected $className = null;
    /** @var string|null The name of the method the route routes to */
    protected $methodName = null;
    /** @var Closure|null The closure the route routes to */
    protected $closure = null;
    /** @var bool Whether or not the action uses a method or a closure */
    protected $usesMethod = false;
    /** @var SerializerInterface The serializer to use for closures */
    protected $serializer = null;
    /** @var string The serialized closure */
    protected $serializedClosure = '';

    /**
     * @param string|null $className The name of the class the route routes to
     * @param string|null $methodName The name of the method the route routes to
     * @param Closure|null $closure The closure the route routes to
     * @param SerializerInterface The serializer to use for this action
     * @throws InvalidArgumentException Thrown if no valid class or closure is specified
     */
    public function __construct(
        ?string $className,
        ?string $methodName,
        ?Closure $closure,
        SerializerInterface $serializer = null
    ) {
        // Check if everything was set or nothing was set
        if (($className !== null && $closure !== null) ||
            ($className === null || $methodName == null) && $closure === null) {
            throw new InvalidArgumentException('Must specify either a class name or closure');
        }

        $this->className = $className;
        $this->methodName = $methodName;
        $this->closure = $closure;
        $this->usesMethod = $closure === null;
        $this->serializer = $serializer ?? new Serializer(new AstAnalyzer());
    }

    /**
     * Performs a deep clone of object properties
     */
    public function __clone()
    {
        if ($this->closure !== null) {
            $this->closure = clone $this->closure;
        }

        $this->serializer = clone $this->serializer;
    }

    /**
     * Serializes the action
     *
     * @return array The list of properties to store
     */
    public function __sleep() : array
    {
        if ($this->closure === null) {
            $this->serializedClosure = '';
        } else {
            $this->serializedClosure = $this->serializer->serialize($this->closure);
            $this->closure = null;
        }

        return array_keys(get_object_vars($this));
    }

    /**
     * Deserializes the action
     */
    public function __wakeup()
    {
        if ($this->serializedClosure === '') {
            $this->closure = null;
        } else {
            $this->closure = $this->serializer->unserialize($this->serializedClosure);
        }

        $this->serializedClosure = '';
    }

    /**
     * Gets the name of the method that is used in this action
     *
     * @return string|null The name of the method that is used in this action if one was set, otherwise null
     */
    public function getMethodName() : ?string
    {
        return $this->methodName;
    }

    /**
     * Gets the name of the class that is used in this action
     *
     * @return string|null The name of the class that is used in this action if one was set, otherwise null
     */
    public function getClassName() : ?string
    {
        return $this->className;
    }

    /**
     * Gets the closure the route routes to
     *
     * @return Closure The closure the route routes to
     */
    public function getClosure() : ?Closure
    {
        return $this->closure;
    }

    /**
     * Gets whether or not this action used a method rather than a closure
     *
     * @return bool True if the action used a class, otherwise false
     */
    public function usesMethod() : bool
    {
        return $this->usesMethod;
    }
}
