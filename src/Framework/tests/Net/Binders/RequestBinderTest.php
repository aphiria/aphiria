<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Net\Binders;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Net\Binders\RequestBinder;
use Aphiria\Net\Http\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class RequestBinderTest extends TestCase
{
    private IContainer|MockObject $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
    }

    protected function tearDown(): void
    {
        // Make sure to reset the overriding request after each test
        $reflectionProperty = new ReflectionProperty(RequestBinder::class, 'overridingRequest');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null);
    }

    public function testOverriddenRequestIsUsedIfSpecified(): void
    {
        $request = $this->createMock(IRequest::class);
        $this->container->expects($this->once())
            ->method('bindInstance')
            ->with(IRequest::class, $request);
        RequestBinder::setOverridingRequest($request);
        (new RequestBinder())->bind($this->container);
    }

    public function testRequestDefaultsToLocalhostUriWhenRunningFromCli(): void
    {
        $this->container->expects($this->once())
            ->method('bindInstance')
            ->with(IRequest::class, $this->callback(function (IRequest $request) {
                return (string)$request->getUri() === 'http://localhost';
            }));
        (new RequestBinder())->bind($this->container);
    }

    public function testRequestHasLocalhostUriWhenRunningFromCli(): void
    {
        $this->container->expects($this->once())
            ->method('bindInstance')
            ->with(IRequest::class, $this->callback(function (IRequest $request) {
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
            ->with(IRequest::class, $this->callback(function (IRequest $request) {
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
