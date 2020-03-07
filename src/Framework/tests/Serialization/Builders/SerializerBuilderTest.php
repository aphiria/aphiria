<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Serialization\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Framework\Serialization\Builders\SerializerBuilder;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\Encoding\IEncoder;
use PHPUnit\Framework\TestCase;

/**
 * Tests the serializer builder
 */
class SerializerBuilderTest extends TestCase
{
    private SerializerBuilder $serializerBuilder;
    private EncoderRegistry $encoders;

    protected function setUp(): void
    {
        $this->encoders = new EncoderRegistry();
        $this->serializerBuilder = new SerializerBuilder($this->encoders);
    }

    public function testWithEncoderRegistersEncoder(): void
    {
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->serializerBuilder->withEncoder('foo', $expectedEncoder);
        $this->serializerBuilder->build($this->createMock(IApplicationBuilder::class));
        $this->assertEquals($expectedEncoder, $this->encoders->getEncoderForType('foo'));
    }
}
