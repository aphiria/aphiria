<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Caching;

use Aphiria\Validation\AggregateConstraintRegistrant;
use Aphiria\Validation\ConstraintRegistry;
use Aphiria\Validation\IConstraintRegistrant;

/**
 * Defines the cached constraint registrant
 */
final class CachedConstraintRegistrant extends AggregateConstraintRegistrant
{
    /** @var IConstraintRegistryCache The constraint cache to store constraints in */
    private IConstraintRegistryCache $constraintCache;

    /**
     * @inheritdoc
     * @param IConstraintRegistryCache $constraintCache The constraint cache
     */
    public function __construct(IConstraintRegistryCache $constraintCache, IConstraintRegistrant $initialConstraintRegistrant = null)
    {
        parent::__construct($initialConstraintRegistrant);

        $this->constraintCache = $constraintCache;
    }

    /**
     * @inheritdoc
     */
    public function registerConstraints(ConstraintRegistry $constraints): void
    {
        if (($cachedConstraints = $this->constraintCache->get()) !== null) {
            $constraints->copy($cachedConstraints);

            return;
        }

        parent::registerConstraints($constraints);

        // Save this to cache for next time
        $this->constraintCache->set($constraints);
    }
}
