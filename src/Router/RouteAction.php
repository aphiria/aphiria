<?php
namespace Opulence\Router\Dispatchers;

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
    /** @var string|null The name of the class the route routes to */
    private $className = null;
    /** @var string|null The name of the method the route routes to */
    private $methodName = null;
    /** @var Closure|null The closure the route routes to */
    private $closure = null;
    /** @var bool Whether or not the action uses a method or a closure */
    private $usesClass = false;
    /** @var SerializerInterface The serializer to use for closures */
    private $serializer = null;
    /** @var string The serialized closure */
    private $serializedClosure = '';

    /**
     * @param string|null $className The name of the class the route routes to
     * @param string|null $methodName The name of the method the route routes to
     * @param Closure|null $closure The closure the route routes to
     * @param bool $usesClass Whether or not the action uses a method or a closure
     * @param SerializerInterface The serializer to use for this action
     * @throws InvalidArgumentException Thrown if no valid class or closure is specified
     */
    public function __construct(
        ?string $className,
        ?string $methodName,
        Closure $closure = null,
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
        $this->usesClass = $closure === null;
        $this->serializer = $serializer ?? new Serializer(new AstAnalyzer());
    }

    /**
     * Serializes the actions
     *
     * @return array The list of properties to store
     */
    public function __sleep() : array
    {
        $this->serializedClosure = $this->serializer->serialize($this->closure);
        $this->closure = null;

        return array_keys(get_object_vars($this));
    }

    /**
     * Deserializes the actions
     */
    public function __wakeup()
    {
        $this->closure = $this->serializer->unserialize($this->serializedClosure);
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
     * Gets the closure the route takes
     *
     * @return Closure The action the route takes
     */
    public function getClosure() : Closure
    {
        return $this->action;
    }

    /**
     * Gets whether or not this action used a class instance
     *
     * @return bool True if the action used a class instance, otherwise false
     */
    public function usesClass() : bool
    {
        return $this->usesClass;
    }
}
