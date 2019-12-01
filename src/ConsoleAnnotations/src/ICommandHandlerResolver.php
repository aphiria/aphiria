<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

namespace Aphiria\ConsoleAnnotations;

use Aphiria\Console\Commands\ICommandHandler;

/**
 * Defines the interface for command handler resolvers to implement
 */
interface ICommandHandlerResolver
{
    /**
     * Resolves a command handler
     *
     * @param string $commandHandlerClassName The name of the command handler to resolve
     * @return ICommandHandler The resolve command handler
     * @throws DependencyResolutionException Thrown if there was an error resolving the command handler
     */
    public function resolve(string $commandHandlerClassName): ICommandHandler;
}
