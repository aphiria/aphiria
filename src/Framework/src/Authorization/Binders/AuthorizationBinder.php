<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Authorization\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Authorization\Authority;
use Aphiria\Authorization\AuthorizationPolicyRegistry;
use Aphiria\Authorization\AuthorizationRequirementHandlerRegistry;
use Aphiria\Authorization\IAuthority;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines the authorization binder
 */
class AuthorizationBinder extends Binder
{
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        $policies = new AuthorizationPolicyRegistry();
        $container->bindInstance(AuthorizationPolicyRegistry::class, $policies);
        $requirementHandlers = new AuthorizationRequirementHandlerRegistry();
        $container->bindInstance(AuthorizationRequirementHandlerRegistry::class, $requirementHandlers);
        $continueOnError = null;
        GlobalConfiguration::tryGetBool('authorization.continueOnFailure', $continueOnError);
        /** @var bool|null $continueOnError */
        $authority = new Authority($policies, $requirementHandlers, $continueOnError ?? true);
        $container->bindInstance(IAuthority::class, $authority);
    }
}
