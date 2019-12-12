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

use Aphiria\Validation\ValidationContext;

/**
 * Defines the equals constraint
 */
final class EqualsConstraint extends ValidationConstraint
{
    /** @var mixed The value to compare against */
    private $value;

    /**
     * @inheritDoc
     * @param mixed $value The value to compare against
     */
    public function __construct($value, string $errorMessageId)
    {
        parent::__construct($errorMessageId);

        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        return $value === $this->value;
    }
}
