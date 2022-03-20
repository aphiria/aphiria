<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization;

use Aphiria\Security\IPrincipal;
use OutOfBoundsException;
use Psalm\Type\Atomic\TResource;

/**
 * Defines the default authority
 */
class Authority implements IAuthority
{
    /**
     * @param AuthorizationPolicyRegistry $policies The policies to use
     * @param AuthorizationRequirementHandlerRegistry $requirementHandlers The requirement handlers
     * @param bool $continueOnFailure Whether or not to continue on failure
     */
    public function __construct(
        private readonly AuthorizationPolicyRegistry $policies,
        private readonly AuthorizationRequirementHandlerRegistry $requirementHandlers,
        private readonly bool $continueOnFailure = true
    ) {
    }

    /**
     * @inheritdoc
     * @template TResource of ?object
     * @param TResource $object
     */
    public function authorize(IPrincipal $user, AuthorizationPolicy|string $policy, object $resource = null): AuthorizationResult
    {
        if (\is_string($policy)) {
            $policyName = $policy;

            try {
                $policy = $this->policies->getPolicy($policy);
            } catch (OutOfBoundsException $ex) {
                throw new PolicyNotFoundException("No policy with name \"$policyName\" found", 0, $ex);
            }
        }

        $authorizationContext = new AuthorizationContext($user, $policy->requirements, $resource);

        foreach ($policy->requirements as $requirement) {
            try {
                /** @var IAuthorizationRequirementHandler<object, TResource> $requirementHandler */
                $requirementHandler = $this->requirementHandlers->getRequirementHandler($requirement::class);
            } catch (OutOfBoundsException $ex) {
                throw new RequirementHandlerNotFoundException('No requirement handler for requirement type ' . $requirement::class . ' found', 0, $ex);
            }

            $requirementHandler->handle($user, $requirement, $authorizationContext);

            if (!$this->continueOnFailure && $authorizationContext->anyRequirementsFailed()) {
                break;
            }
        }

        if ($authorizationContext->allRequirementsPassed()) {
            return AuthorizationResult::pass();
        }

        return AuthorizationResult::fail($authorizationContext->pendingRequirements->toArray());
    }
}
