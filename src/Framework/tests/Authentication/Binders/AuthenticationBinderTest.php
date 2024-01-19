<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Authentication\Binders;

use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\Authentication\Authenticator;
use Aphiria\Authentication\ContainerAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\IAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\IAuthenticator;
use Aphiria\Authentication\IMockAuthenticator;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authentication\MockAuthenticator;
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
    private string $currAppEnv;

    protected function setUp(): void
    {
        // Grab the current app environment so we can reset it when done
        $this->currAppEnv = ($appEnv = \getenv('APP_ENV')) === false ? '' : $appEnv;
        $this->container = Mockery::mock(IContainer::class);
        $this->binder = new AuthenticationBinder();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        // Reset the app environment
        \putenv("APP_ENV=$this->currAppEnv");
    }

    public function testMockAuthenticatorAndOtherInstancesAreBoundToContainerWhenInTestingEnvironment(): void
    {
        $this->setUpContainerMock(true);
        \putenv('APP_ENV=testing');
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testRealAuthenticatorAndOtherInstancesAreBoundToContainerWhenNotInTestingEnvironment(): void
    {
        $this->setUpContainerMock(false);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    /**
     * Sets up the container mock
     *
     * @param bool $inTestingEnvironment Whether we're in the testing environment
     */
    private function setUpContainerMock(bool $inTestingEnvironment): void
    {
        $parameters = [
            [AuthenticationSchemeRegistry::class, AuthenticationSchemeRegistry::class],
            [IAuthenticationSchemeHandlerResolver::class, ContainerAuthenticationSchemeHandlerResolver::class],
            [IUserAccessor::class, RequestPropertyUserAccessor::class]
        ];

        if ($inTestingEnvironment) {
            $parameters[] = [IMockAuthenticator::class, MockAuthenticator::class];
            $parameters[] = [IAuthenticator::class, MockAuthenticator::class];
        } else {
            $parameters[] = [IAuthenticator::class, Authenticator::class];
        }

        foreach ($parameters as $parameter) {
            $this->container->shouldReceive('bindInstance')
                ->with($parameter[0], Mockery::type($parameter[1]));
        }
    }
}
