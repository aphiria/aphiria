<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use Aphiria\Validation\Constraints\Caching\IObjectConstraintsRegistryCache;

/**
 * Defines a collection of registrants that can be called in serial
 */
class ObjectConstraintsRegistrantCollection implements IObjectConstraintsRegistrant
{
    /** @var list<IObjectConstraintsRegistrant> The collection of registrants */
    protected array $registrants = [];

    /**
     * @param IObjectConstraintsRegistryCache|null $objectConstraintsCache The optional cache of constraints
     */
    public function __construct(private readonly ?IObjectConstraintsRegistryCache $objectConstraintsCache = null)
    {
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
        if (($cachedObjectConstraints = $this->objectConstraintsCache?->get()) !== null) {
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
