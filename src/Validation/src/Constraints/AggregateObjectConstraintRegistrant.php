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
class AggregateObjectConstraintRegistrant implements IObjectConstraintRegistrant
{
    /** @var IObjectConstraintRegistrant[] The list of registrants that will actually register the constraints */
    protected array $constraintRegistrants = [];

    /**
     * @param IObjectConstraintRegistrant|null $initialConstraintRegistrant The initial registrant to register, or null
     */
    public function __construct(IObjectConstraintRegistrant $initialConstraintRegistrant = null)
    {
        if ($initialConstraintRegistrant !== null) {
            $this->constraintRegistrants[] = $initialConstraintRegistrant;
        }
    }

    /**
     * Adds a constraint registrant
     *
     * @param IObjectConstraintRegistrant $constraintRegistrant The registrant to add
     */
    public function addConstraintRegistrant(IObjectConstraintRegistrant $constraintRegistrant): void
    {
        $this->constraintRegistrants[] = $constraintRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function registerConstraints(ObjectConstraintRegistry $objectConstraints): void
    {
        foreach ($this->constraintRegistrants as $constraintRegistrant) {
            $constraintRegistrant->registerConstraints($objectConstraints);
        }
    }
}
