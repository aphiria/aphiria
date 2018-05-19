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
 * Defines the default metadata registrant
 */
class DefaultMetadataRegistrant
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
     * Registers default metadata
     *
     * @param TypeMetadataRegistry $metadataRegistry The metadata registry to register to
     */
    public function registerMetadata(TypeMetadataRegistry $metadataRegistry): void
    {
        $this->registerBoolMetadata($metadataRegistry);
        $this->registerDateTimeMetadata($metadataRegistry);
        $this->registerDateTimeImmutableMetadata($metadataRegistry);
        $this->registerFloatMetadata($metadataRegistry);
        $this->registerIntMetadata($metadataRegistry);
        $this->registerStringMetadata($metadataRegistry);
    }

    /**
     * Register the boolean metadata
     *
     * @param TypeMetadataRegistry $metadataRegistry The metadata registry to register to
     */
    protected function registerBoolMetadata(TypeMetadataRegistry $metadataRegistry): void
    {
        $metadataRegistry->registerStructMetadata(
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
     * Register the DateTime metadata
     *
     * @param TypeMetadataRegistry $metadataRegistry The metadata registry to register to
     */
    protected function registerDateTimeMetadata(TypeMetadataRegistry $metadataRegistry): void
    {
        $metadataRegistry->registerStructMetadata(
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
     * Register the DateTimeImmutable metadata
     *
     * @param TypeMetadataRegistry $metadataRegistry The metadata registry to register to
     */
    protected function registerDateTimeImmutableMetadata(TypeMetadataRegistry $metadataRegistry): void
    {
        $metadataRegistry->registerStructMetadata(
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
     * Register the float metadata
     *
     * @param TypeMetadataRegistry $metadataRegistry The metadata registry to register to
     */
    protected function registerFloatMetadata(TypeMetadataRegistry $metadataRegistry): void
    {
        $metadataRegistry->registerStructMetadata(
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
     * Register the integer metadata
     *
     * @param TypeMetadataRegistry $metadataRegistry The metadata registry to register to
     */
    protected function registerIntMetadata(TypeMetadataRegistry $metadataRegistry): void
    {
        $metadataRegistry->registerStructMetadata(
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
     * Register the string metadata
     *
     * @param TypeMetadataRegistry $metadataRegistry The metadata registry to register to
     */
    protected function registerStringMetadata(TypeMetadataRegistry $metadataRegistry): void
    {
        $metadataRegistry->registerStructMetadata(
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
