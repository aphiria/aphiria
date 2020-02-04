<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Serialization\Bootstrappers;

use Aphiria\Configuration\Configuration;
use Aphiria\Configuration\ConfigurationException;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Serialization\Encoding\CamelCasePropertyNameFormatter;
use Aphiria\Serialization\Encoding\DefaultEncoderRegistrant;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\FormUrlEncodedSerializer;
use Aphiria\Serialization\JsonSerializer;

/**
 * Defines the serializer bootstrapper
 */
final class SerializerBootstrapper extends Bootstrapper
{
    /**
     * @inheritdoc
     * @throws ConfigurationException Thrown if the config is missing values
     */
    public function registerBindings(IContainer $container): void
    {
        $encoders = new EncoderRegistry();

        $propertyNameFormatterName = null;
        Configuration::tryGetString('serialization.propertyNameFormatter', $propertyNameFormatterName);

        if ($propertyNameFormatterName === CamelCasePropertyNameFormatter::class) {
            $propertyNameFormatter = new CamelCasePropertyNameFormatter();
        } elseif ($propertyNameFormatterName === null) {
            $propertyNameFormatter = null;
        } else {
            $propertyNameFormatter = $container->resolve($propertyNameFormatterName);
        }

        (new DefaultEncoderRegistrant(
            $propertyNameFormatter,
            Configuration::getString('serialization.dateFormat')
        ))->registerDefaultEncoders($encoders);

        $container->bindInstance(EncoderRegistry::class, $encoders);

        foreach (Configuration::getArray('serialization.serializers') as $serializerName) {
            switch ($serializerName) {
                case JsonSerializer::class:
                    $container->bindInstance(JsonSerializer::class, new JsonSerializer($encoders));
                    break;
                case FormUrlEncodedSerializer::class:
                    $container->bindInstance(FormUrlEncodedSerializer::class, new FormUrlEncodedSerializer($encoders));
                    break;
                default:
                    $container->bindInstance($serializerName, $container->resolve($serializerName));
            }
        }
    }
}
