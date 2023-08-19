<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Authentication\Binders;

use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\Authentication\Authenticator;
use Aphiria\Authentication\ContainerAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\IAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\IAuthenticator;
use Aphiria\Authentication\IMockAuthenticator;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authentication\MockAuthenticator;
use Aphiria\Authentication\RequestPropertyUserAccessor;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines the authentication binder
 */
class AuthenticationBinder extends Binder
{
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        $schemes = new AuthenticationSchemeRegistry();
        $container->bindInstance(AuthenticationSchemeRegistry::class, $schemes);
        $schemeHandlerResolver = $this->getSchemeHandlerResolver($container);
        $container->bindInstance(IAuthenticationSchemeHandlerResolver::class, $schemeHandlerResolver);
        $userAccessor = $this->getUserAccessor($container);
        $container->bindInstance(IUserAccessor::class, $userAccessor);

        if ($this->inTestingEnvironment()) {
            $authenticator = new MockAuthenticator($schemes, $schemeHandlerResolver);
            $container->bindInstance(IMockAuthenticator::class, $authenticator);
        } else {
            $authenticator = new Authenticator($schemes, $schemeHandlerResolver);
        }

        $container->bindInstance(IAuthenticator::class, $authenticator);
    }

    /**
     * Gets the authentication scheme handler resolver to use
     *
     * @param IContainer $container The DI container
     * @return IAuthenticationSchemeHandlerResolver The resolver
     */
    protected function getSchemeHandlerResolver(IContainer $container): IAuthenticationSchemeHandlerResolver
    {
        return new ContainerAuthenticationSchemeHandlerResolver($container);
    }

    /**
     * Gets the user accessor to use
     *
     * @param IContainer $container The DI container
     * @return IUserAccessor The user accessor
     */
    protected function getUserAccessor(IContainer $container): IUserAccessor
    {
        return new RequestPropertyUserAccessor();
    }

    /**
     * Gets whether we're in the testing environment
     * Note: If you use different environment variable values for testing, override this
     *
     * @return bool True if we're in the testing environment, otherwise false
     */
    protected function inTestingEnvironment(): bool
    {
        return \getenv('APP_ENV') === 'testing';
    }
}
