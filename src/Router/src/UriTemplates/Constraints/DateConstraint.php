<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
    /** @var array The list of acceptable formats */
    private array $formats;

    /**
     * @param array|string $formats The format or list of acceptable formats
     */
    public function __construct(string|array $formats)
    {
        $this->formats = (array)$formats;

        if (\count($this->formats) === 0) {
            throw new InvalidArgumentException('No formats specified for ' . self::class);
        }
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
    public function passes(mixed $value): bool
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
