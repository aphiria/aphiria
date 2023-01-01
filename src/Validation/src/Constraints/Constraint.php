<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines a base constraint
 */
abstract class Constraint implements IConstraint
{
    /**
     * @param string $errorMessageId The ID of the error message associated with this constraint
     */
    protected function __construct(protected readonly string $errorMessageId)
    {
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessageId(): string
    {
        return $this->errorMessageId;
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessagePlaceholders($value): array
    {
        if (\is_scalar($value)) {
            $serializedValue = (string)$value;
        } elseif (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                $serializedValue = (string)$value;
            } else {
                $serializedValue = $value::class . ' object';
            }
        } else {
            $serializedValue = 'value';
        }

        return ['value' => $serializedValue];
    }
}
