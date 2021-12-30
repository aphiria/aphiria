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
use RuntimeException;

/**
 * Defines the interface for authentication scheme handler resolvers to implement
 */
interface IAuthenticationSchemeHandlerResolver
{
    /**
     * Resolves an authentication handler
     *
     * @template TSchemeOptions of AuthenticationSchemeOptions
     * @template THandler of IAuthenticationSchemeHandler<TSchemeOptions>
     * @param class-string<THandler> $authenticationHandlerClassName
     * @return THandler The resolved authentication handler
     * @throws RuntimeException Thrown if the handler could not be resolved
     */
    public function resolve(string $authenticationHandlerClassName): IAuthenticationSchemeHandler;
}
