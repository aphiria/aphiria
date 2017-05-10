<?php
namespace Opulence\Routing\Matchers;

use Closure;
use SuperClosure\SerializerInterface;

/**
 * Defines a route action that uses a closure
 */
class ClosureRouteAction extends RouteAction
{
    /**
     * @param Closure $closure The closure the route routes to
     * @param SerializerInterface|null The serializer to use for this action
     */
    public function __construct(Closure $closure, SerializerInterface $serializer = null)
    {
        parent::__construct(null, null, $closure, $serializer);
    }

    /**
     * @inheritdoc
     */
    public function usesMethod() : bool
    {
        return false;
    }
}
