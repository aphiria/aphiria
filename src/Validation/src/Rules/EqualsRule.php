<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Rules;

use Aphiria\Validation\ValidationContext;
use InvalidArgumentException;

/**
 * Defines the equals rule
 */
final class EqualsRule implements IRuleWithArgs
{
    /** @var mixed The value to compare against */
    protected $value;

    /**
     * @inheritdoc
     */
    public function getSlug(): string
    {
        return 'equals';
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        return $value === $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setArgs(array $args): void
    {
        if (count($args) !== 1) {
            throw new InvalidArgumentException('Must pass a value to compare against');
        }

        $this->value = $args[0];
    }
}
