<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

use DateTime;
use InvalidArgumentException;

/**
 * Defines the date constraint
 */
final class DateConstraint implements IRouteVariableConstraint
{
    /** @var array The list of acceptable date formats */
    private array $formats;

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
     * Gets the slug that will be used to actually add a constraint in a URI template
     *
     * @return string The slug used in the URI template
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
