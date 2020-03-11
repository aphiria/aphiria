<?php
/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Serialization\Components;

use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Framework\Serialization\Components\SerializerComponent;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\Encoding\IEncoder;
use PHPUnit\Framework\TestCase;

/**
 * Tests the serializer component
 */
class SerializerComponentTest extends TestCase
{
    private SerializerComponent $serializerComponent;
    private EncoderRegistry $encoders;

    protected function setUp(): void
    {
        $this->encoders = new EncoderRegistry();
        $serviceResolver = $this->createMock(IServiceResolver::class);
        $serviceResolver->expects($this->once())
            ->method('resolve')
            ->with(EncoderRegistry::class)
            ->willReturn($this->encoders);
        $this->serializerComponent = new SerializerComponent($serviceResolver);
    }

    public function testBuildWithEncoderRegistersEncoder(): void
    {
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->serializerComponent->withEncoder('foo', $expectedEncoder);
        $this->serializerComponent->build();
        $this->assertEquals($expectedEncoder, $this->encoders->getEncoderForType('foo'));
    }
}
