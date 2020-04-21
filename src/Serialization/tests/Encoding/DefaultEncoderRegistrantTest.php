<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Tests\Encoding;

use Aphiria\Serialization\Encoding\ArrayEncoder;
use Aphiria\Serialization\Encoding\DateTimeEncoder;
use Aphiria\Serialization\Encoding\DefaultEncoderRegistrant;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\Encoding\ObjectEncoder;
use Aphiria\Serialization\Encoding\ScalarEncoder;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DefaultEncoderRegistrantTest extends TestCase
{
    public function testCorrectEncodersAreRegistered(): void
    {
        $encoders = new EncoderRegistry();
        (new DefaultEncoderRegistrant())->registerDefaultEncoders($encoders);
        $this->assertInstanceOf(ObjectEncoder::class, $encoders->getEncoderForValue($this));
        $this->assertInstanceOf(ScalarEncoder::class, $encoders->getEncoderForValue(1));
        $this->assertInstanceOf(ArrayEncoder::class, $encoders->getEncoderForValue(['foo']));
        $this->assertInstanceOf(DateTimeEncoder::class, $encoders->getEncoderForValue(new DateTime()));
        $this->assertInstanceOf(DateTimeEncoder::class, $encoders->getEncoderForValue(new DateTimeImmutable()));
    }
}
