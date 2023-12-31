<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Serialization\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\DependencyInjection\UniversalContext;
use Aphiria\Framework\Serialization\Binders\SymfonySerializerBinder;
use Aphiria\Framework\Serialization\Normalizers\ProblemDetailsNormalizer;
use Aphiria\Framework\Tests\Serialization\Binders\Mocks\MockEncoder;
use Aphiria\Framework\Tests\Serialization\Binders\Mocks\MockNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SymfonySerializerBinderTest extends TestCase
{
    private SymfonySerializerBinder $binder;
    private IContainer&MockObject $container;

    protected function setUp(): void
    {
        $this->binder = new SymfonySerializerBinder();
        $this->container = $this->createMock(IContainer::class);
        GlobalConfiguration::resetConfigurationSources();
        // Set up some universal expectations
        $this->container->method('bindInstance')
            ->with([SerializerInterface::class, Serializer::class], $this->isInstanceOf(Serializer::class));
    }

    public function testArrayDenormalizerIsInstantiatedWithCorrectFormat(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = ArrayDenormalizer::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testDateTimeNormalizerIsInstantiatedWithCorrectFormat(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = DateTimeNormalizer::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testEncoderThatDoesNotImplementEncoderOrDecoderInterfaceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Encoder ' . $this::class . ' must implement ' . EncoderInterface::class . ' or ' . DecoderInterface::class);
        $this->container->method('resolve')
            ->with($this::class)
            ->willReturn($this);
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['encoders'][] = $this::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
    }

    public function testEnumBackedNormalizerIsInstantiated(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = BackedEnumNormalizer::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        // Make sure the container wasn't used to resolve this normalizer
        $this->container->expects($this->never())
            ->method('resolve')
            ->with(BackedEnumNormalizer::class);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testJsonEncoderIsInstantiated(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['encoders'][] = JsonEncoder::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testNormalizerThatDoesNotImplementNormalizerOrDenormalizerInterfaceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Normalizer ' . $this::class . ' must implement ' . NormalizerInterface::class . ' or ' . DenormalizerInterface::class);
        $this->container->method('resolve')
            ->with($this::class)
            ->willReturn($this);
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = $this::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
    }

    public function testObjectNormalizerIsInstantiatedWithNameConverterIfItExists(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = ObjectNormalizer::class;
        $config['aphiria']['serialization']['nameConverter'] = CamelCaseToSnakeCaseNameConverter::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testObjectNormalizerIsInstantiatedWithoutNameConverterIfItIsNotSupported(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = ObjectNormalizer::class;
        $config['aphiria']['serialization']['nameConverter'] = self::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testObjectNormalizerIsInstantiatedWithoutNameConverterIfNoneExists(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = ObjectNormalizer::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testProblemDetailsNormalizerIsInstantiated(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = ProblemDetailsNormalizer::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testUnsupportedEncoderThatCanBeResolvedIsAddedToSerializer(): void
    {
        $encoder = new MockEncoder();
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['encoders'][] = $encoder::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->container->method('resolve')
            ->with($encoder::class)
            ->willReturn($encoder);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testUnsupportedEncoderThatCannotBeResolvedThrowsException(): void
    {
        $expectedExceptionMessage = 'Foo';
        $this->expectException(ResolutionException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->container->method('resolve')
            ->with($this::class)
            ->willThrowException(new ResolutionException($this::class, new UniversalContext(), $expectedExceptionMessage));
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['encoders'][] = $this::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
    }

    public function testUnsupportedNormalizerThatCanBeResolvedIsAddedToSerializer(): void
    {
        $normalizer = new MockNormalizer();
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = $normalizer::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->container->method('resolve')
            ->with($normalizer::class)
            ->willReturn($normalizer);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testUnsupportedNormalizerThatCannotBeResolvedThrowsException(): void
    {
        $expectedExceptionMessage = 'Foo';
        $this->expectException(ResolutionException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->container->method('resolve')
            ->with($this::class)
            ->willThrowException(new ResolutionException($this::class, new UniversalContext(), $expectedExceptionMessage));
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = $this::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
    }

    public function testXmlEncoderIsInstantiatedWithSupportedParameters(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['encoders'][] = XmlEncoder::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    /**
     * Gets the base config
     *
     * @return array<string, mixed> The base config
     */
    private static function getBaseConfig(): array
    {
        return [
            'aphiria' => [
                'serialization' => [
                    'dateFormat' => 'Ymd',
                    'encoders' => [],
                    'nameConverter' => null,
                    'normalizers' => [],
                    'xml' => [
                        'removeEmptyTags' => true,
                        'rootNodeName' => 'response'
                    ]
                ]
            ]
        ];
    }
}
