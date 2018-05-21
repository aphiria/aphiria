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
use DateTimeInterface;

/**
 * Defines the default encoder registrant
 */
class DefaultEncoderRegistrant
{
    /** @var string The DateTime format to use */
    private $dateTimeFormat;

    /**
     * @param string $dateTimeFormat The DateTime format to use
     */
    public function __construct(string $dateTimeFormat = DateTime::ISO8601)
    {
        $this->dateTimeFormat = $dateTimeFormat;
    }

    /**
     * Registers the default encoders
     *
     * @param EncoderRegistry $encoders The encoders to register to
     */
    public function registerDefaultEncoders(EncoderRegistry $encoders): void
    {
        $encoders->registerEncoder('array', new ArrayEncoder($encoders));
        $dateTimeEncoder = new DateTimeEncoder($this->dateTimeFormat);
        $encoders->registerEncoder(DateTime::class, $dateTimeEncoder);
        $encoders->registerEncoder(DateTimeImmutable::class, $dateTimeEncoder);
        $encoders->registerEncoder(DateTimeInterface::class, $dateTimeEncoder);
    }
}
