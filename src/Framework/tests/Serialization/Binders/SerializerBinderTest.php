<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Serialization\Binders;

use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\Configuration\HashTableConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Serialization\Binders\SerializerBinder;
use Aphiria\Serialization\Encoding\CamelCasePropertyNameFormatter;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\Encoding\EncodingContext;
use Aphiria\Serialization\Encoding\IPropertyNameFormatter;
use Aphiria\Serialization\FormUrlEncodedSerializer;
use Aphiria\Serialization\ISerializer;
use Aphiria\Serialization\JsonSerializer;
use Aphiria\Serialization\TypeResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the serializer binder
 */
class SerializerBinderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    private SerializerBinder $binder;
    private EncoderRegistry $encoders;

    protected function setUp(): void
    {
        $this->binder = new SerializerBinder();
        $this->container = $this->createMock(IContainer::class);
        GlobalConfiguration::resetConfigurationSources();

        // Some universal assertions
        $this->container->expects($this->at(0))
            ->method('bindInstance')
            ->with(EncoderRegistry::class, $this->callback(function (EncoderRegistry $encoders) {
                $this->encoders = $encoders;

                return true;
            }));
    }

    public function testCamelCasePropertyNameFormatterIsInstantiatedDirectly(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['propertyNameFormatter'] = CamelCasePropertyNameFormatter::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
        $classWithSnakeCaseProperties = new class() {
            public string $snake_case = 'foo';
        };
        $encodedClass = $this->encoders->getEncoderForType(TypeResolver::resolveType($classWithSnakeCaseProperties))
            ->encode($classWithSnakeCaseProperties, new EncodingContext());
        $this->assertEquals(['snakeCase' => 'foo'], $encodedClass);
    }

    public function testJsonSerializerIsInstantiatedDirectly(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['serializers'][] = JsonSerializer::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(JsonSerializer::class, $this->isInstanceOf(JsonSerializer::class));
        $this->binder->bind($this->container);
    }

    public function testFormUrlEncodedSerializerIsInstantiatedDirectly(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['serializers'][] = FormUrlEncodedSerializer::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(FormUrlEncodedSerializer::class, $this->isInstanceOf(FormUrlEncodedSerializer::class));
        $this->binder->bind($this->container);
    }

    public function testNoPropertyNameFormatterBeingSpecifiedDoesNotUseADefaultOne(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->binder->bind($this->container);
        $classWithSnakeCaseProperties = new class() {
            public string $snake_case = 'foo';
        };
        $encodedClass = $this->encoders->getEncoderForType(TypeResolver::resolveType($classWithSnakeCaseProperties))
            ->encode($classWithSnakeCaseProperties, new EncodingContext());
        $this->assertEquals(['snake_case' => 'foo'], $encodedClass);
    }

    public function testUnknownPropertyNameFormatterIsResolved(): void
    {
        $propertyNameFormatter = $this->createMock(IPropertyNameFormatter::class);
        $propertyNameFormatter->expects($this->once())
            ->method('formatPropertyName')
            ->with('snake_case')
            ->willReturn('SNAKECASE');
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['propertyNameFormatter'] = \get_class($propertyNameFormatter);
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->container->expects($this->at(1))
            ->method('resolve')
            ->with(\get_class($propertyNameFormatter))
            ->willReturn($propertyNameFormatter);
        $this->binder->bind($this->container);
        $classWithSnakeCaseProperties = new class() {
            public string $snake_case = 'foo';
        };
        $encodedClass = $this->encoders->getEncoderForType(TypeResolver::resolveType($classWithSnakeCaseProperties))
            ->encode($classWithSnakeCaseProperties, new EncodingContext());
        $this->assertEquals(['SNAKECASE' => 'foo'], $encodedClass);
    }

    public function testUnknownSerializerIsResolved(): void
    {
        $serializer = $this->createMock(ISerializer::class);
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['serializers'][] = \get_class($serializer);
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->container->expects($this->at(1))
            ->method('resolve')
            ->with(\get_class($serializer))
            ->willReturn($serializer);
        $this->container->expects($this->at(2))
            ->method('bindInstance')
            ->with(\get_class($serializer), $serializer);
        $this->binder->bind($this->container);
    }

    /**
     * Gets the base config
     *
     * @return array The base config
     */
    private static function getBaseConfig(): array
    {
        return [
            'aphiria' => [
                'serialization' => [
                    'dateFormat' => 'Ymd',
                    'serializers' => []
                ]
            ]
        ];
    }
}
