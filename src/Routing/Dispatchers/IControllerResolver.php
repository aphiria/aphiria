<?php
namespace Opulence\Routing\Dispatchers;

/**
 * Defines the interface for controller resolvers to implement
 */
interface IControllerResolver
{
    /**
     * Resolves an instance of a controller class
     *
     * @param string $className The name of the class to resolve
     * @return mixed An instance of the controller
     * @throws ControllerResolutionException Thrown if the controller could not be resolved
     */
    public function resolveController(string $className);
}
