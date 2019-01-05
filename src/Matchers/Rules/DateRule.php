<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Rules;

use DateTime;
use InvalidArgumentException;

/**
 * Defines the date rule
 */
class DateRule
{
    /** @var array The list of acceptable date formats */
    private $formats;

    /**
     * @param array|string $formats The format or list of acceptable formats
     */
    public function __construct($formats)
    {
        $formatArray = (array)$formats;

        if (\count($formatArray) === 0) {
            throw new InvalidArgumentException('No formats specified for ' . static::class);
        }

        $this->formats = (array)$formats;
    }

    /**
     * @inheritdoc
     */
    public static function getSlug(): string
    {
        return 'date';
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        foreach ($this->formats as $format) {
            $dateTime = DateTime::createFromFormat($format, $value);

            if ($dateTime !== false && $value === $dateTime->format($format)) {
                return true;
            }
        }

        return false;
    }
}
