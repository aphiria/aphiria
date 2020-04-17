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
        // TODO: Still need to check the encoders to see if the right formatter was registered
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
