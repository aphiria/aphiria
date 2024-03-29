<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines the in-array constraint
 */
final class InConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const string DEFAULT_ERROR_MESSAGE_ID = 'Field is invalid';

    /**
     * @inheritdoc
     * @param list<mixed> $values The values to check
     */
    public function __construct(private readonly array $values, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheritdoc
     */
    public function passes(mixed $value): bool
    {
        return \in_array($value, $this->values, false);
    }
}
