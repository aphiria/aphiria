<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines a collection of registrants that can be called in serial
 */
final class ObjectConstraintsRegistrantCollection implements IObjectConstraintsRegistrant
{
    /** @var IObjectConstraintsRegistrant[] The collection of registrants */
    private array $registrants = [];

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
        foreach ($this->registrants as $registrant) {
            $registrant->registerConstraints($objectConstraints);
        }
    }
}
