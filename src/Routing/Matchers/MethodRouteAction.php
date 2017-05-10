<?php
namespace Opulence\Routing\Matchers;

use SuperClosure\SerializerInterface;

/**
 * Defines a route action that uses a method
 */
class MethodRouteAction extends RouteAction
{
    /**
     * @param string $className The name of the class the route routes to
     * @param string $methodName The name of the method the route routes to
     * @param SerializerInterface|null The serializer to use for this action
     */
    public function __construct(string $className, string $methodName, SerializerInterface $serializer = null)
    {
        parent::__construct($className, $methodName, null, $serializer);
    }

    /**
     * @inheritdoc
     */
    public function usesMethod() : bool
    {
        return true;
    }
}
