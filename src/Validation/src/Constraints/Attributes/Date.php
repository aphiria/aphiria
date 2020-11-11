<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Attributes;

use Aphiria\Validation\Constraints\DateConstraint;
use Aphiria\Validation\Constraints\IConstraint;
use Attribute;
use InvalidArgumentException;

/**
 * Defines the date constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Date extends ConstraintAttribute
{
    /**
     * @inheritdoc
     * @param string[] $acceptableFormats The list of acceptable DateTime formats
     * @throws InvalidArgumentException Thrown if there were no acceptable date formats
     */
    public function __construct(public array $acceptableFormats, string $errorMessageId = null)
    {
        parent::__construct($errorMessageId);

        if (empty($this->acceptableFormats)) {
            throw new InvalidArgumentException('Must specify at least one acceptable date format');
        }
    }

    /**
     * @inheritdoc
     */
    public function createConstraintFromAttribute(): DateConstraint
    {
        if (isset($this->errorMessageId)) {
            return new DateConstraint($this->acceptableFormats, $this->errorMessageId);
        }

        return new DateConstraint($this->acceptableFormats);
    }
}
