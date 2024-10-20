<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication;

use Aphiria\Authentication\Schemes\IAuthenticationSchemeHandler;

/**
 * Defines a scheme used for authentication
 *
 * @template T of AuthenticationSchemeOptions
 */
final readonly class AuthenticationScheme
{
    /**
     * @param string $name The name of this scheme
     * @param class-string<IAuthenticationSchemeHandler<T>> $handlerClassName The name of the authentication scheme handler class used by this scheme
     * @param T $options The options for this scheme
     */
    public function __construct(
        public string $name,
        public string $handlerClassName,
        public AuthenticationSchemeOptions $options = new AuthenticationSchemeOptions()
    ) {
    }
}
