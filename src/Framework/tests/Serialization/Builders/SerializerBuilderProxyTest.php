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

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\Framework\Serialization\Builders\SerializerBuilder;
use Aphiria\Framework\Serialization\Builders\SerializerBuilderProxy;
use Aphiria\Serialization\Encoding\IEncoder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the serializer builder proxy
 */
class SerializerBuilderProxyTest extends TestCase
{
    private SerializerBuilderProxy $serializerBuilderProxy;
    /** @var SerializerBuilder|MockObject */
    private SerializerBuilder $serializerBuilder;

    protected function setUp(): void
    {
        $this->serializerBuilder = $this->createMock(SerializerBuilder::class);
        $this->serializerBuilderProxy = new SerializerBuilderProxy(
            fn () => $this->serializerBuilder
        );
    }

    public function testBuildRegistersObjectConstraintsToProxiedComponentBuilder(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $expectedEncoder = $this->createMock(IEncoder::class);
        $this->serializerBuilder->expects($this->at(0))
            ->method('withEncoder')
            ->with('foo', $expectedEncoder);
        $this->serializerBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->serializerBuilderProxy->withEncoder('foo', $expectedEncoder);
        $this->serializerBuilderProxy->build($expectedAppBuilder);
    }

    public function testGetProxiedTypeReturnsCorrectType(): void
    {
        $this->assertEquals(SerializerBuilder::class, $this->serializerBuilderProxy->getProxiedType());
    }
}
