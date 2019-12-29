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
use DateTime;
use InvalidArgumentException;

/**
 * Defines the date constraint
 */
final class DateConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field is not in the correct date format';
    /** @var array The list of acceptable date formats */
    private array $acceptableFormats;

    /**
     * @inheritdoc
     * @param string[] $acceptableFormats The acceptable date formats
     * @throws InvalidArgumentException Thrown if the formats were empty
     */
    public function __construct(array $acceptableFormats, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        if (count($acceptableFormats) === 0) {
            throw new InvalidArgumentException('Must specify at least one acceptable format');
        }

        $this->acceptableFormats = $acceptableFormats;
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        foreach ($this->acceptableFormats as $format) {
            $dateTime = DateTime::createFromFormat($format, $value);

            if ($dateTime !== false && $value == $dateTime->format($format)) {
                return true;
            }
        }

        return false;
    }
}
