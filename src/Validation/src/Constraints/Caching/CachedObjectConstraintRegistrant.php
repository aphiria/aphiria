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

use Aphiria\Validation\Constraints\AggregateObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\IObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;

/**
 * Defines the cached constraint registrant
 */
final class CachedObjectConstraintRegistrant extends AggregateObjectConstraintsRegistrant
{
    /** @var IObjectConstraintRegistryCache The constraint cache to store constraints in */
    private IObjectConstraintRegistryCache $constraintCache;

    /**
     * @inheritdoc
     * @param IObjectConstraintRegistryCache $constraintCache The constraint cache
     */
    public function __construct(IObjectConstraintRegistryCache $constraintCache, IObjectConstraintsRegistrant $initialConstraintRegistrant = null)
    {
        parent::__construct($initialConstraintRegistrant);

        $this->constraintCache = $constraintCache;
    }

    /**
     * @inheritdoc
     */
    public function registerConstraints(ObjectConstraintsRegistry $objectConstraints): void
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
