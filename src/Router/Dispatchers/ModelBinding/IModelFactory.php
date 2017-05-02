<?php
namespace Opulence\Router\Dispatchers\ModelBinding;

use Closure;
use InvalidArgumentException;

/**
 * Defines the interface for model factories to implement
 */
interface IModelFactory
{
    /**
     * Creates an instance of a model from the HTTP request body
     * 
     * @param string $className The name of the model to create
     * @param array $body The HTTP request body as a dictionary of property names => values
     * @return mixed The instantiated model
     * @throws InvalidArgumentException Thrown if the model did not have a registered factory or the request was
     *      missing properties
     */
    public function createModel(string $className, array $body);
    
    /**
     * Registers a factory to create a model from an HTTP request body
     * 
     * @param string $className The name of the model class whose factory we're registering
     * @param Closure $factory The factory that will accept the HTTP request body as a dictionary of property
     *      names => values
     */
    public function registerModelFactory(string $className, Closure $factory) : void;
}
