<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Attributes;

use Aphiria\Validation\Constraints\EachConstraint;
use Attribute;
use InvalidArgumentException;

/**
 * Defines the each constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Each extends ConstraintAttribute
{
    /**
     * @inheritdoc
     * @param list<IConstraintAttribute> $constraints The list of constraint attributes that mucst be passed
     * @throws InvalidArgumentException Thrown if the list of constraints is empty
     */
    public function __construct(public readonly array $constraints, string $errorMessageId = null)
    {
        parent::__construct($errorMessageId);

        if (empty($this->constraints)) {
            throw new InvalidArgumentException('Must specify at least one constraint');
        }
    }

    /**
     * @inheritdoc
     */
    public function createConstraintFromAttribute(): EachConstraint
    {
        $constraints = [];

        foreach ($this->constraints as $constraintAttributes) {
            $constraints[] = $constraintAttributes->createConstraintFromAttribute();
        }

        if (isset($this->errorMessageId)) {
            return new EachConstraint($constraints, $this->errorMessageId);
        }

        return new EachConstraint($constraints);
    }
}
