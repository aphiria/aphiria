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

use DateTime;
use InvalidArgumentException;

/**
 * Defines the date constraint
 */
final class DateConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field is not in the correct date format';

    /**
     * @inheritdoc
     * @param list<string> $acceptableFormats The acceptable date formats
     * @throws InvalidArgumentException Thrown if the formats were empty
     */
    public function __construct(
        private readonly array $acceptableFormats,
        string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID
    ) {
        parent::__construct($errorMessageId);

        if (\count($this->acceptableFormats) === 0) {
            throw new InvalidArgumentException('Must specify at least one acceptable format');
        }
    }

    /**
     * @inheritdoc
     */
    public function passes(mixed $value): bool
    {
        foreach ($this->acceptableFormats as $format) {
            $dateTime = DateTime::createFromFormat($format, (string)$value);

            if ($dateTime !== false && $value === $dateTime->format($format)) {
                return true;
            }
        }

        return false;
    }
}
