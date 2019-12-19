<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Caching;

use Aphiria\Validation\Constraints\AggregateObjectConstraintRegistrant;
use Aphiria\Validation\Constraints\IObjectConstraintRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintRegistry;

/**
 * Defines the cached constraint registrant
 */
final class CachedConstraintRegistrant extends AggregateObjectConstraintRegistrant
{
    /** @var IConstraintRegistryCache The constraint cache to store constraints in */
    private IConstraintRegistryCache $constraintCache;

    /**
     * @inheritdoc
     * @param IConstraintRegistryCache $constraintCache The constraint cache
     */
    public function __construct(IConstraintRegistryCache $constraintCache, IObjectConstraintRegistrant $initialConstraintRegistrant = null)
    {
        parent::__construct($initialConstraintRegistrant);

        $this->constraintCache = $constraintCache;
    }

    /**
     * @inheritdoc
     */
    public function registerConstraints(ObjectConstraintRegistry $objectConstraints): void
    {
        if (($cachedConstraints = $this->constraintCache->get()) !== null) {
            $objectConstraints->copy($cachedConstraints);

            return;
        }

        parent::registerConstraints($objectConstraints);

        // Save this to cache for next time
        $this->constraintCache->set($objectConstraints);
    }
}
