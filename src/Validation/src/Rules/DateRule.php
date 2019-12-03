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
use DateTime;
use InvalidArgumentException;

/**
 * Defines the date rule
 */
final class DateRule implements IRuleWithArgs
{
    /** @var array The expected date formats */
    protected array $formats = [];

    /**
     * @inheritdoc
     */
    public function getSlug(): string
    {
        return 'date';
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

    /**
     * @inheritdoc
     */
    public function setArgs(array $args): void
    {
        if (count($args) !== 1 || (!is_string($args[0]) && !is_array($args[0]))) {
            throw new InvalidArgumentException('Must pass an expected date format');
        }

        $this->formats = (array)$args[0];
    }
}
