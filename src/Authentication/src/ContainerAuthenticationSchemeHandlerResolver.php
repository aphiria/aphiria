<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication;

use Aphiria\Authentication\Schemes\IAuthenticationSchemeHandler;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use RuntimeException;

/**
 * Defines the DI container-based authentication scheme handler resolver
 */
final class ContainerAuthenticationSchemeHandlerResolver implements IAuthenticationSchemeHandlerResolver
{
    /**
     * @param IServiceResolver $serviceResolver The resolver to use
     */
    public function __construct(private readonly IServiceResolver $serviceResolver)
    {
    }

    /**
     * @inheritdoc
     */
    public function resolve(string $authenticationHandlerClassName): IAuthenticationSchemeHandler
    {
        try {
            return $this->serviceResolver->resolve($authenticationHandlerClassName);
        } catch (ResolutionException $ex) {
            throw new RuntimeException("Failed to resolve $authenticationHandlerClassName", 0, $ex);
        }
    }
}
