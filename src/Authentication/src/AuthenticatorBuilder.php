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

use RuntimeException;

/**
 * Defines the authenticator builder
 */
class AuthenticatorBuilder
{
    /** @var IAuthenticationSchemeHandlerResolver|null The handler resolver to use, or null if none is set */
    private ?IAuthenticationSchemeHandlerResolver $handlerResolver = null;

    /**
     * @param AuthenticationSchemeRegistry $schemes The authentication schemes to use
     */
    public function __construct(
        private readonly AuthenticationSchemeRegistry $schemes = new AuthenticationSchemeRegistry()
    ) {
    }

    /**
     * Builds the authenticator
     *
     * @return IAuthenticator The built authenticator
     * @throws RuntimeException Thrown if there was an error building the authenticator
     */
    public function build(): IAuthenticator
    {
        if ($this->handlerResolver === null) {
            throw new RuntimeException('No handler resolver was specified');
        }

        return new Authenticator($this->schemes, $this->handlerResolver);
    }

    /**
     * Adds an authentication handler resolver to the authenticator
     *
     * @param IAuthenticationSchemeHandlerResolver $handlerResolver The handler resolver to use
     * @return static For chaining
     */
    public function withHandlerResolver(IAuthenticationSchemeHandlerResolver $handlerResolver): static
    {
        $this->handlerResolver = $handlerResolver;

        return $this;
    }

    /**
     * Adds an authentication scheme to the authenticator
     *
     * @template T of AuthenticationSchemeOptions
     * @param AuthenticationScheme<T> $scheme The scheme to register
     * @param bool $isDefault Whether or not the scheme is the default scheme
     * @return static For chaining
     */
    public function withScheme(AuthenticationScheme $scheme, bool $isDefault = false): static
    {
        $this->schemes->registerScheme($scheme, $isDefault);

        return $this;
    }
}
