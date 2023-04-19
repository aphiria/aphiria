<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Authentication\Binders;

use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\Authentication\Authenticator;
use Aphiria\Authentication\ContainerAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\IAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\IAuthenticator;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authentication\RequestPropertyUserAccessor;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Authentication\Binders\AuthenticationBinder;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class AuthenticationBinderTest extends TestCase
{
    private AuthenticationBinder $binder;
    private IContainer&MockInterface $container;

    protected function setUp(): void
    {
        $this->container = Mockery::mock(IContainer::class);
        $this->binder = new AuthenticationBinder();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testInstancesAreBoundToContainer(): void
    {
        $this->setUpContainerMock();
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    /**
     * Sets up the container mock
     */
    private function setUpContainerMock(): void
    {
        $parameters = [
            [AuthenticationSchemeRegistry::class, AuthenticationSchemeRegistry::class],
            [IAuthenticationSchemeHandlerResolver::class, ContainerAuthenticationSchemeHandlerResolver::class],
            [IUserAccessor::class, RequestPropertyUserAccessor::class],
            [IAuthenticator::class, Authenticator::class]
        ];

        foreach ($parameters as $parameter) {
            $this->container->shouldReceive('bindInstance')
                ->with($parameter[0], Mockery::type($parameter[1]));
        }
    }
}
