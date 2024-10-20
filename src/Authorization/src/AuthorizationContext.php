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
    /**
     * Whether or not all requirements have passed
     * @see AuthorizationContext::anyRequirementsFailed() Unlike that method, this requires all requirements to explicitly succeed
     *
     * @var bool
     */
    public bool $allRequirementsPassed {
        get => \count($this->pendingRequirements) === 0 && !$this->anyRequirementsFailed;
    }
    /** @var bool Whether or not any requirements were explicitly marked as having failed */
    public private(set) bool $anyRequirementsFailed = false;
    /** @var HashSet<object> The list of requirements that haven't passed yet */
    public private(set) iterable $pendingRequirements;

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
