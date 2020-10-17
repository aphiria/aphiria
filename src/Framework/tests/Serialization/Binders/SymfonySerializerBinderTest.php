<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Serialization\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Serialization\Binders\SymfonySerializerBinder;
use Aphiria\Framework\Serialization\Normalizers\ProblemDetailsNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SymfonySerializerBinderTest extends TestCase
{
    private IContainer|MockObject $container;
    private SymfonySerializerBinder $binder;

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

    public function testJsonEncoderIsInstantiated(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['encoders'][] = JsonEncoder::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
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
     * @return array The base config
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
