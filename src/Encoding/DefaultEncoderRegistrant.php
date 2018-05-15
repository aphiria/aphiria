<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Encoding;

use DateTime;
use DateTimeImmutable;

/**
 * Defines the default encoders' registrant
 */
class DefaultEncoderRegistrant
{
    /** @var string The format to use for DateTimes */
    private $dateTimeFormat;

    /**
     * @param string $dateTimeFormat The format to use for DateTimes
     */
    public function __construct(string $dateTimeFormat = DateTime::ISO8601)
    {
        $this->dateTimeFormat = $dateTimeFormat;
    }

    /**
     * Registers default encoders
     *
     * @param EncoderRegistry $encoders The encoders to register to
     */
    public function registerEncoder(EncoderRegistry $encoders): void
    {
        $this->registerBoolEncoder($encoders);
        $this->registerDateTimeEncoder($encoders);
        $this->registerDateTimeImmutableEncoder($encoders);
        $this->registerFloatEncoder($encoders);
        $this->registerIntEncoder($encoders);
        $this->registerStringEncoder($encoders);
    }

    /**
     * Register a boolean encoder
     *
     * @param EncoderRegistry $encoders The encoder registry to register to
     */
    protected function registerBoolEncoder(EncoderRegistry $encoders): void
    {
        $encoders->registerStructEncoder(
            'bool',
            function ($value) {
                return (bool)$value;
            },
            function (bool $value) {
                return $value;
            }
        );
    }

    /**
     * Register a DateTime encoder
     *
     * @param EncoderRegistry $encoders The encoder registry to register to
     */
    protected function registerDateTimeEncoder(EncoderRegistry $encoders): void
    {
        $encoders->registerStructEncoder(
            DateTime::class,
            function ($value) {
                return DateTime::createFromFormat($this->dateTimeFormat, $value);
            },
            function (DateTime $value) {
                return $value->format($this->dateTimeFormat);
            }
        );
    }

    /**
     * Register a DateTimeImmutable encoder
     *
     * @param EncoderRegistry $encoders The encoder registry to register to
     */
    protected function registerDateTimeImmutableEncoder(EncoderRegistry $encoders): void
    {
        $encoders->registerStructEncoder(
            DateTimeImmutable::class,
            function ($value) {
                return DateTimeImmutable::createFromFormat($this->dateTimeFormat, $value);
            },
            function (DateTimeImmutable $value) {
                return $value->format($this->dateTimeFormat);
            }
        );
    }

    /**
     * Register a float encoder
     *
     * @param EncoderRegistry $encoders The encoder registry to register to
     */
    protected function registerFloatEncoder(EncoderRegistry $encoders): void
    {
        $encoders->registerStructEncoder(
            'float',
            function ($value) {
                return (float)$value;
            },
            function (float $value) {
                return $value;
            }
        );
    }

    /**
     * Register an integer encoder
     *
     * @param EncoderRegistry $encoders The encoder registry to register to
     */
    protected function registerIntEncoder(EncoderRegistry $encoders): void
    {
        $encoders->registerStructEncoder(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
    }

    /**
     * Register a string encoder
     *
     * @param EncoderRegistry $encoders The encoder registry to register to
     */
    protected function registerStringEncoder(EncoderRegistry $encoders): void
    {
        $encoders->registerStructEncoder(
            'string',
            function ($value) {
                return (string)$value;
            },
            function (string $value) {
                return $value;
            }
        );
    }
}
