<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

namespace Aphiria\Serialization\Encoding;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Defines the default encoder registrant
 */
final class DefaultEncoderRegistrant
{
    /** @var IPropertyNameFormatter|null The property name formatter to use */
    private $propertyNameFormatter;
    /** @var string The DateTime format to use */
    private $dateTimeFormat;

    /**
     * @param IPropertyNameFormatter $propertyNameFormatter The property name formatter to use
     * @param string $dateTimeFormat The DateTime format to use
     */
    public function __construct(
        IPropertyNameFormatter $propertyNameFormatter = null,
        string $dateTimeFormat = DateTime::ATOM
    ) {
        $this->propertyNameFormatter = $propertyNameFormatter;
        $this->dateTimeFormat = $dateTimeFormat;
    }

    /**
     * Registers the default encoders
     *
     * @param EncoderRegistry $encoders The encoders to register to
     * @return EncoderRegistry The registry with the default encoders registered
     */
    public function registerDefaultEncoders(EncoderRegistry $encoders): EncoderRegistry
    {
        $encoders->registerDefaultObjectEncoder(new ObjectEncoder($encoders, $this->propertyNameFormatter));
        $encoders->registerDefaultScalarEncoder(new ScalarEncoder());
        $encoders->registerEncoder('array', new ArrayEncoder($encoders));
        $dateTimeEncoder = new DateTimeEncoder($this->dateTimeFormat);
        $encoders->registerEncoder(DateTime::class, $dateTimeEncoder);
        $encoders->registerEncoder(DateTimeImmutable::class, $dateTimeEncoder);
        $encoders->registerEncoder(DateTimeInterface::class, $dateTimeEncoder);

        return $encoders;
    }
}
