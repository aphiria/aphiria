<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Binders;

use Aphiria\Api\ApiGateway;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Api\Binders\RequestHandlerBinder;
use Aphiria\Net\Http\IRequestHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestHandlerBinderTest extends TestCase
{
    private RequestHandlerBinder $binder;
    private IContainer&MockObject $container;

    protected function setUp(): void
    {
        $this->binder = new RequestHandlerBinder();
        $this->container = $this->createMock(IContainer::class);
    }

    public function testApiGatewayIsBoundAsRequestHandler(): void
    {
        $this->container->expects($this->once())
            ->method('bindClass')
            ->with(IRequestHandler::class, ApiGateway::class);
        $this->binder->bind($this->container);
    }
}
