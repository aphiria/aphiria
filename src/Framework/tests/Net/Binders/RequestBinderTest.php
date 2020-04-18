<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Net\Binders;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Net\Binders\RequestBinder;
use Aphiria\Net\Http\IHttpRequestMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestBinderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
    }

    public function testRequestDefaultsToLocalhostUriWhenRunningFromCli(): void
    {
        $this->container->expects($this->once())
            ->method('bindInstance')
            ->with(IHttpRequestMessage::class, $this->callback(function (IHttpRequestMessage $request) {
                return (string)$request->getUri() === 'http://localhost';
            }));
        (new RequestBinder())->bind($this->container);
    }

    public function testRequestHasLocalhostUriWhenRunningFromCli(): void
    {
        $this->container->expects($this->once())
            ->method('bindInstance')
            ->with(IHttpRequestMessage::class, $this->callback(function (IHttpRequestMessage $request) {
                return (string)$request->getUri() === 'http://localhost';
            }));
        $binder = new class() extends RequestBinder {
            protected function isRunningInConsole(): bool
            {
                return true;
            }
        };
        $binder->bind($this->container);
    }

    public function testRequestHasLocalhostUriWhenRunningFromHttp(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        // argv was causing issues in the test
        unset($_SERVER['argv']);
        $this->container->expects($this->once())
            ->method('bindInstance')
            ->with(IHttpRequestMessage::class, $this->callback(function (IHttpRequestMessage $request) {
                return (string)$request->getUri() === 'http://example.com';
            }));
        $binder = new class() extends RequestBinder {
            protected function isRunningInConsole(): bool
            {
                return false;
            }
        };
        $binder->bind($this->container);
    }
}
