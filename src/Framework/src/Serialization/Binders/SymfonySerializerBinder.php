<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Serialization\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\MissingConfigurationValueException;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Framework\Serialization\Normalizers\ProblemDetailsNormalizer;
use InvalidArgumentException;
use ReflectionClass;
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

/**
 * Defines the serializer binder
 */
final class SymfonySerializerBinder extends Binder
{
    /**
     * @inheritdoc
     * @throws MissingConfigurationValueException Thrown if the config is missing values
     * @throws InvalidArgumentException Thrown if any of the config values are invalid
     * @throws ResolutionException Thrown if a normalizer could not be resolved
     */
    public function bind(IContainer $container): void
    {
        $encoders = $normalizers = [];
        /** @var array<class-string> $encoderNames */
        $encoderNames = GlobalConfiguration::getArray('aphiria.serialization.encoders');

        foreach ($encoderNames as $encoderName) {
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
                default:
                    $encoder = $container->resolve($encoderName);

                    if (!$encoder instanceof EncoderInterface && !$encoder instanceof DecoderInterface) {
                        throw new InvalidArgumentException("Encoder $encoderName must implement " . EncoderInterface::class . ' or ' . DecoderInterface::class);
                    }

                    $encoders[] = $encoder;
            }
        }

        /** @var array<class-string> $normalizerNames */
        $normalizerNames = GlobalConfiguration::getArray('aphiria.serialization.normalizers');

        foreach ($normalizerNames as $normalizerName) {
            switch ($normalizerName) {
                case DateTimeNormalizer::class:
                    $normalizers[] = new DateTimeNormalizer([
                        DateTimeNormalizer::FORMAT_KEY => GlobalConfiguration::getString('aphiria.serialization.dateFormat')
                    ]);
                    break;
                case ObjectNormalizer::class:
                    $nameConverter = $nameConverterName = null;

                    if (GlobalConfiguration::tryGetString('aphiria.serialization.nameConverter', $nameConverterName)) {
                        $nameConverter = match ($nameConverterName) {
                            CamelCaseToSnakeCaseNameConverter::class => new CamelCaseToSnakeCaseNameConverter(),
                            default => null
                        };
                    }

                    $normalizers[] = new ObjectNormalizer(null, $nameConverter);
                    break;
                case ArrayDenormalizer::class:
                    $normalizers[] = new ArrayDenormalizer();
                    break;
                case ProblemDetailsNormalizer::class:
                    $normalizers[] = new ProblemDetailsNormalizer();
                    break;
                case BackedEnumNormalizer::class:
                    $normalizers[] = new BackedEnumNormalizer();
                    break;
                default:
                    $normalizer = $container->resolve($normalizerName);

                    if (!$normalizer instanceof NormalizerInterface && !$normalizer instanceof DenormalizerInterface) {
                        throw new InvalidArgumentException("Normalizer $normalizerName must implement " . NormalizerInterface::class . ' or ' . DenormalizerInterface::class);
                    }

                    $normalizers[] = $normalizer;
            }
        }

        $serializer = new Serializer($normalizers, $encoders);
        $container->bindInstance([SerializerInterface::class, Serializer::class], $serializer);
    }
}
