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

/**
 * Defines the date constraint
 */
final class DateConstraint extends ValidationConstraint
{
    /** @var array The expected date formats */
    private array $formats;

    /**
     * @inheritDoc
     * @param string[] $formats The expected date formats
     */
    public function __construct(array $formats, string $errorMessageId)
    {
        parent::__construct($errorMessageId);

        $this->formats = $formats;
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        foreach ($this->formats as $format) {
            $dateTime = DateTime::createFromFormat($format, $value);

            if ($dateTime !== false && $value == $dateTime->format($format)) {
                return true;
            }
        }

        return false;
    }
}
