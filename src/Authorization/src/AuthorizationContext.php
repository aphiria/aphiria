<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization;

use Aphiria\Collections\HashSet;
use Aphiria\Security\IPrincipal;

/**
 * Defines the context for authorizing a user
 *
 * @template TResource of ?object
 */
final class AuthorizationContext
{
    /** @var HashSet<object> The list of requirements that haven't passed yet */
    public iterable $pendingRequirements;
    /** @var bool Whether or not any requirements were explicitly marked as having failed */
    private bool $anyRequirementsFailed = false;

    /**
     * @param IPrincipal $user The current user being authorized
     * @param list<object> $requirements The list of requirements to pass
     * @param TResource $resource The resource whose use we're authorizing
     */
    public function __construct(
        public readonly IPrincipal $user,
        public readonly array $requirements,
        public readonly ?object $resource
    ) {
        $this->pendingRequirements = new HashSet($this->requirements);
    }

    /**
     * Gets whether or not all requirements have passed
     * @see AuthorizationContext::anyRequirementsFailed() Unlike that method, this requires all requirements to explicitly succeed
     *
     * @return bool True if authorization was successful, otherwise false
     */
    public function allRequirementsPassed(): bool
    {
        return \count($this->pendingRequirements) === 0 && !$this->anyRequirementsFailed();
    }

    /**
     * Gets whether or not any requirements failed
     *
     *
     * @return bool True if any requirements have failed, otherwise false
     */
    public function anyRequirementsFailed(): bool
    {
        return $this->anyRequirementsFailed;
    }

    /**
     * Marks the context as having at least one failed requirement
     */
    public function fail(): void
    {
        $this->anyRequirementsFailed = true;
    }

    /**
     * Marks a requirement as having succeeded
     *
     * @param object $requirement The requirement that succeeded
     */
    public function requirementPassed(object $requirement): void
    {
        $this->pendingRequirements->removeValue($requirement);
    }
}
