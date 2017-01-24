<?php
namespace Opulence\Router\Dispatchers;

use Closure;
use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Serializer;
use SuperClosure\SerializerInterface;

/**
 * Defines a route action
 */
class RouteAction
{
    /** @var Closure The action a route takes */
    private $action = null;
    /** @var bool Whether or not the action uses a method or a closure */
    private $usesClass = false;
    /** @var object The instantiated class, if this action used one */
    private $classInstance = null;
    /** @var SerializerInterface The serializer to use for this action */
    private $serializer = null;
    /** @var string The serialized action */
    private $serializedAction = '';

    /**
     * @param Closure $action The action a route takes
     * @param bool $usesClass Whether or not the action uses a method or a closure
     * @param SerializerInterface The serializer to use for this action
     */
    public function __construct(Closure $action, bool $usesClass = false, SerializerInterface $serializer = null)
    {
        $this->action = $action;
        $this->usesClass = $usesClass;
        $this->serializer = $serializer ?? new Serializer(new AstAnalyzer());
    }

    /**
     * Serializes the actions
     *
     * @return array The list of properties to store
     */
    public function __sleep() : array
    {
        $this->serializedAction = $this->serializer->serialize($this->action);
        $this->action = null;

        return array_keys(get_object_vars($this));
    }

    /**
     * Deserializes the actions
     */
    public function __wakeup()
    {
        $this->action = $this->serializer->unserialize($this->serializedAction);
        $this->serializedAction = '';
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
     * Gets the instance of the class that was used in this action
     *
     * @return object|null The instance of the class that was used in this action if one was set, otherwise null
     */
    public function getClassInstance()
    {
        return $this->classInstance;
    }

    /**
     * Sets the instance of the class that was used in this action
     *
     * @param object $instance
     */
    public function setClassInstance($instance)
    {
        $this->classInstance = $instance;
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
