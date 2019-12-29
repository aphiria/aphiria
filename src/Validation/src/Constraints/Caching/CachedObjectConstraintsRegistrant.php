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

use Aphiria\Validation\Constraints\IObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;

/**
 * Defines the cached constraint registrant
 */
final class CachedObjectConstraintsRegistrant implements IObjectConstraintsRegistrant
{
    /** @var IObjectConstraintsRegistryCache The constraint cache to store constraints in */
    private IObjectConstraintsRegistryCache $constraintCache;
    /** @var ObjectConstraintsRegistrantCollection The collection of registrants to run on cache miss */
    private ObjectConstraintsRegistrantCollection $constraintsRegistrants;

    /**
     * @param IObjectConstraintsRegistryCache $constraintCache The constraint cache
     * @param ObjectConstraintsRegistrantCollection $constraintsRegistrants The collection of registrants to run on cache miss
     */
    public function __construct(
        IObjectConstraintsRegistryCache $constraintCache,
        ObjectConstraintsRegistrantCollection $constraintsRegistrants
    ) {
        $this->constraintCache = $constraintCache;
        $this->constraintsRegistrants = $constraintsRegistrants;
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

        $this->constraintsRegistrants->registerConstraints($objectConstraints);

        // Save this to cache for next time
        $this->constraintCache->set($objectConstraints);
    }
}
