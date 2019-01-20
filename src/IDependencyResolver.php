<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api;

/**
 * Defines the interface for dependency resolvers to implement
 */
interface IDependencyResolver
{
    /**
     * Resolves an instance of a class
     *
     * @param string $className The name of the class to resolve
     * @return \object An instance of the class
     * @throws DependencyResolutionException Thrown if the dependency could not be resolved
     */
    public function resolve(string $className): object;
}
