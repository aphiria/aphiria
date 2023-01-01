<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Binders;

use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\ConsoleGateway;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Console\Binders\CommandHandlerBinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommandHandlerBinderTest extends TestCase
{
    private CommandHandlerBinder $binder;
    private IContainer&MockObject $container;

    protected function setUp(): void
    {
        $this->binder = new CommandHandlerBinder();
        $this->container = $this->createMock(IContainer::class);
    }

    public function testApiGatewayIsBoundAsRequestHandler(): void
    {
        $this->container->expects($this->once())
            ->method('bindClass')
            ->with(ICommandHandler::class, ConsoleGateway::class);
        $this->binder->bind($this->container);
    }
}
