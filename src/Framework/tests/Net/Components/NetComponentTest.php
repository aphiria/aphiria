<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Net\Components;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\ExtensionMethods\ExtensionMethodRegistry;
use Aphiria\Framework\Net\Components\NetComponent;
use Aphiria\Net\Http\IRequest;
use Closure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NetComponentTest extends TestCase
{
    private IContainer|MockObject $container;
    private NetComponent $netComponent;

    protected function setUp(): void
    {
        ExtensionMethodRegistry::reset();
        $this->container = $this->createMock(IContainer::class);
        $this->netComponent = new NetComponent($this->container);
    }

    public function testEnablingExtensionMethodsRegistersThemOnBuild(): void
    {
        $this->netComponent->withExtensionMethods();
        $request = $this->createMock(IRequest::class);
        $this->assertNull(ExtensionMethodRegistry::getExtensionMethod($request, 'getActualMimeType'));
        // The previous call would've memoized a null closure, so reset it
        ExtensionMethodRegistry::reset();
        $this->netComponent->build();
        $this->assertInstanceOf(Closure::class, ExtensionMethodRegistry::getExtensionMethod($request, 'getActualMimeType'));
    }

    public function testNotEnablingExtensionMethodsDoesNotRegisterThem(): void
    {
        $this->netComponent->build();
        $request = $this->createMock(IRequest::class);
        $this->assertNull(ExtensionMethodRegistry::getExtensionMethod($request, 'getActualMimeType'));
    }
}
