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
 * Defines a constraint registrant that aggregates multiple sub-registrants
 */
class AggregateObjectConstraintsRegistrant implements IObjectConstraintsRegistrant
{
    /** @var IObjectConstraintsRegistrant[] The list of registrants that will actually register the constraints */
    protected array $constraintRegistrants = [];

    /**
     * @param IObjectConstraintsRegistrant|null $initialConstraintRegistrant The initial registrant to register, or null
     */
    public function __construct(IObjectConstraintsRegistrant $initialConstraintRegistrant = null)
    {
        if ($initialConstraintRegistrant !== null) {
            $this->constraintRegistrants[] = $initialConstraintRegistrant;
        }
    }

    /**
     * Adds a constraint registrant
     *
     * @param IObjectConstraintsRegistrant $constraintRegistrant The registrant to add
     */
    public function addConstraintRegistrant(IObjectConstraintsRegistrant $constraintRegistrant): void
    {
        $this->constraintRegistrants[] = $constraintRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function registerConstraints(ObjectConstraintsRegistry $objectConstraints): void
    {
        foreach ($this->constraintRegistrants as $constraintRegistrant) {
            $constraintRegistrant->registerConstraints($objectConstraints);
        }
    }
}
