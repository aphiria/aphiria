<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use RuntimeException;

/**
 * Defines the command handler resolver that uses the DI container
 */
final class ContainerCommandHandlerResolver implements ICommandHandlerResolver
{
    /** @var IContainer The container to use to resolve handlers */
    private IContainer $container;

    /**
     * @param IContainer $container The container to use to resolve handlers
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function resolve(string $commandHandlerClassName): ICommandHandler
    {
        try {
            $commandHandler = $this->container->resolve($commandHandlerClassName);

            if (!$commandHandler instanceof ICommandHandler) {
                throw new RuntimeException("$commandHandlerClassName does not implement " . ICommandHandler::class);
            }

            return $commandHandler;
        } catch (ResolutionException $ex) {
            throw new DependencyResolutionException(
                $commandHandlerClassName,
                'Could not resolve command handler',
                0,
                $ex
            );
        }
    }
}
