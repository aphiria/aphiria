<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Binders;

use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\ConsoleGateway;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines the command handler binder
 */
final class CommandHandlerBinder extends Binder
{
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        // This will make it easier to grab the console gateway for integration tests
        $container->bindClass(ICommandHandler::class, ConsoleGateway::class);
    }
}
