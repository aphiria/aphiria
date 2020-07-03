<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Serialization\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\MissingConfigurationValueException;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Defines the serializer binder
 */
final class SymfonySerializerBinder extends Binder
{
    /**
     * @inheritdoc
     * @throws MissingConfigurationValueException Thrown if the config is missing values
     */
    public function bind(IContainer $container): void
    {
        $encoders = $normalizers = [];

        foreach (GlobalConfiguration::getArray('aphiria.serialization.encoders') as $encoderName) {
            switch ($encoderName) {
                case XmlEncoder::class:
                    $encoders[] = new XmlEncoder([
                        XmlEncoder::ROOT_NODE_NAME => GlobalConfiguration::getString('aphiria.serialization.xml.rootNodeName'),
                        XmlEncoder::REMOVE_EMPTY_TAGS => GlobalConfiguration::getBool('aphiria.serialization.xml.removeEmptyTags')
                    ]);
                    break;
                case JsonEncoder::class:
                    $encoders[] = new JsonEncoder();
                    break;
            }
        }

        foreach (GlobalConfiguration::getArray('aphiria.serialization.normalizers') as $normalizerName) {
            switch ($normalizerName) {
                case DateTimeNormalizer::class:
                    $normalizers[] = new DateTimeNormalizer([
                        DateTimeNormalizer::FORMAT_KEY => GlobalConfiguration::getString('aphiria.serialization.dateFormat')
                    ]);
                    break;
                case ObjectNormalizer::class:
                    $nameConverter = $nameConverterName = null;

                    if (GlobalConfiguration::tryGetString('aphiria.serialization.nameConverter', $nameConverterName)) {
                        switch ($nameConverterName) {
                            case CamelCaseToSnakeCaseNameConverter::class:
                                $nameConverter = new CamelCaseToSnakeCaseNameConverter();
                                break;
                        }
                    }

                    $normalizers[] = new ObjectNormalizer(null, $nameConverter);
                    break;
                case ArrayDenormalizer::class:
                    $normalizers[] = new ArrayDenormalizer();
                    break;
            }
        }

        $serializer = new Serializer($normalizers, $encoders);
        $container->bindInstance([SerializerInterface::class, Serializer::class], $serializer);
    }
}
