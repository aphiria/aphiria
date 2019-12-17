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
final class DateConstraint extends ValidationConstraint
{
    /** @var array The list of acceptable date formats */
    private array $acceptableFormats;

    /**
     * @inheritDoc
     * @param string[] $acceptableFormats The acceptable date formats
     * @throws InvalidArgumentException Thrown if the formats were empty
     */
    public function __construct(array $acceptableFormats, string $errorMessageId)
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
