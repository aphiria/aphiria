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
use Aphiria\Framework\Serialization\Binders\SymfonySerializerBinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SymfonySerializerBinderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    private SymfonySerializerBinder $binder;

    protected function setUp(): void
    {
        $this->binder = new SymfonySerializerBinder();
        $this->container = $this->createMock(IContainer::class);
        GlobalConfiguration::resetConfigurationSources();
        // Set up some universal expectations
        $this->container->expects($this->at(0))
            ->method('bindInstance')
            ->with([SerializerInterface::class, Serializer::class], $this->isInstanceOf(Serializer::class));
    }

    public function testDateTimeNormalizerIsInstantiatedWithCorrectFormat(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = DateTimeNormalizer::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
    }

    public function testJsonEncoderIsInstantiated(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['encoders'][] = JsonEncoder::class;
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
    }

    public function testObjectNormalizerIsInstantiatedWithoutNameConverterIfNoneExists(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['normalizers'][] = ObjectNormalizer::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
    }

    public function testXmlEncoderIsInstantiatedWithSupportedParameters(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['serialization']['encoders'][] = XmlEncoder::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
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
