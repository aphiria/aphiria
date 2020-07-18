<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use Aphiria\Validation\Constraints\Caching\IObjectConstraintsRegistryCache;

/**
 * Defines a collection of registrants that can be called in serial
 */
class ObjectConstraintsRegistrantCollection implements IObjectConstraintsRegistrant
{
    /** @var IObjectConstraintsRegistrant[] The collection of registrants */
    protected array $registrants = [];
    /** @var IObjectConstraintsRegistryCache|null The optional cache of constraints */
    private ?IObjectConstraintsRegistryCache $objectConstraintsCache;

    /**
     * @param IObjectConstraintsRegistryCache|null $objectConstraintsCache The optional cache of constraints
     */
    public function __construct(IObjectConstraintsRegistryCache $objectConstraintsCache = null)
    {
        $this->objectConstraintsCache = $objectConstraintsCache;
    }

    /**
     * Adds a registrant to the collection
     *
     * @param IObjectConstraintsRegistrant $objectConstraintsRegistrant The registrant to add
     */
    public function add(IObjectConstraintsRegistrant $objectConstraintsRegistrant): void
    {
        $this->registrants[] = $objectConstraintsRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function registerConstraints(ObjectConstraintsRegistry $objectConstraints): void
    {
        if ($this->objectConstraintsCache !== null && ($cachedObjectConstraints = $this->objectConstraintsCache->get()) !== null) {
            $objectConstraints->copy($cachedObjectConstraints);

            return;
        }

        foreach ($this->registrants as $registrant) {
            $registrant->registerConstraints($objectConstraints);
        }

        if ($this->objectConstraintsCache !== null) {
            $this->objectConstraintsCache->set($objectConstraints);
        }
    }
}
