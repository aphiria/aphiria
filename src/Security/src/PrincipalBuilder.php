<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Security;

use Closure;

/**
 * Defines a principal builder
 */
class PrincipalBuilder
{
    /** @var list<IIdentity> The list of identities to add to the principal */
    private array $identities = [];
    /** @var ?Closure(list<IIdentity>): ?IIdentity The primary identity selector */
    private ?Closure $primaryIdentitySelector = null;

    /**
     * @param string|null $defaultClaimsIssuer The default claims issuer to use if no issuer is specified for a claim in any of the identities
     */
    public function __construct(private readonly ?string $defaultClaimsIssuer = null)
    {
    }

    /**
     * Builds the principal
     *
     * @return IPrincipal The built principal
     */
    public function build(): IPrincipal
    {
        return new User($this->identities, $this->primaryIdentitySelector);
    }

    /**
     * Adds either an identity or an identity builder to the principal
     *
     * @param IIdentity|Closure(IdentityBuilder): void $identity
     * @return static For chaining
     */
    public function withIdentity(IIdentity|Closure $identity): static
    {
        if ($identity instanceof IIdentity) {
            $this->identities[] = $identity;
        } else {
            $identityBuilder = new IdentityBuilder($this->defaultClaimsIssuer);
            $identity($identityBuilder);
            $this->identities[] = $identityBuilder->build();
        }

        return $this;
    }

    /**
     * Adds a primary identity selector to the principal
     *
     * @param Closure(list<IIdentity>): ?IIdentity $primaryIdentitySelector The primary identity selector
     * @return static For chaining
     */
    public function withPrimaryIdentitySelector(Closure $primaryIdentitySelector): static
    {
        $this->primaryIdentitySelector = $primaryIdentitySelector;

        return $this;
    }
}
