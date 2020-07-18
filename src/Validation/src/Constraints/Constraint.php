<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines a base constraint
 */
abstract class Constraint implements IConstraint
{
    /** @var string The ID of the error message associated with this constraint */
    protected string $errorMessageId;

    /**
     * @param string $errorMessageId The ID of the error message associated with this constraint
     */
    protected function __construct(string $errorMessageId)
    {
        $this->errorMessageId = $errorMessageId;
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
            $serializedValue = $value;
        } elseif (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                $serializedValue = (string)$value;
            } else {
                $serializedValue = \get_class($value) . ' object';
            }
        } else {
            $serializedValue = 'value';
        }

        return ['value' => $serializedValue];
    }
}
