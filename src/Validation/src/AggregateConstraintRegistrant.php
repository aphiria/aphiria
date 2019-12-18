<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

/**
 * Defines a constraint registrant that aggregates multiple sub-registrants
 */
class AggregateConstraintRegistrant implements IConstraintRegistrant
{
    /** @var IConstraintRegistrant[] The list of registrants that will actually register the constraints */
    protected array $constraintRegistrants = [];

    /**
     * @param IConstraintRegistrant|null $initialConstraintRegistrant The initial registrant to register, or null
     */
    public function __construct(IConstraintRegistrant $initialConstraintRegistrant = null)
    {
        if ($initialConstraintRegistrant !== null) {
            $this->constraintRegistrants[] = $initialConstraintRegistrant;
        }
    }

    /**
     * Adds a constraint registrant
     *
     * @param IConstraintRegistrant $constraintRegistrant The registrant to add
     */
    public function addConstraintRegistrant(IConstraintRegistrant $constraintRegistrant): void
    {
        $this->constraintRegistrants[] = $constraintRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function registerConstraints(ConstraintRegistry $constraints): void
    {
        foreach ($this->constraintRegistrants as $constraintRegistrant) {
            $constraintRegistrant->registerConstraints($constraints);
        }
    }
}
