<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Encoding;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Defines the DateTime encoder
 */
class DateTimeEncoder implements IEncoder
{
    /** @var string The DateTime format to use */
    private string $format;

    /**
     * @param string $format The DateTime format to use
     */
    public function __construct(string $format = DateTime::ATOM)
    {
        $this->format = $format;
    }

    /**
     * @inheritdoc
     */
    public function decode($value, string $type, EncodingContext $context)
    {
        if ($type !== DateTime::class && $type !== DateTimeImmutable::class && $type !== DateTimeInterface::class) {
            throw new InvalidArgumentException(
                'Type must be ' . DateTime::class . ', ' . DateTimeImmutable::class . ', or ' . DateTimeInterface::class
            );
        }

        if ($type === DateTime::class) {
            return DateTime::createFromFormat($this->format, $value);
        }

        // This handles both DateTimeImmutable and DateTimeInterface
        return DateTimeImmutable::createFromFormat($this->format, $value);
    }

    /**
     * @inheritdoc
     */
    public function encode($value, EncodingContext $context)
    {
        if (!$value instanceof DateTimeInterface) {
            throw new InvalidArgumentException('Value must implement ' . DateTimeInterface::class);
        }

        return $value->format($this->format);
    }
}
