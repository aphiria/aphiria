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

use Aphiria\Configuration\ConfigurationException;
use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Serialization\Encoding\CamelCasePropertyNameFormatter;
use Aphiria\Serialization\Encoding\DefaultEncoderRegistrant;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\FormUrlEncodedSerializer;
use Aphiria\Serialization\JsonSerializer;

/**
 * Defines the serializer binder
 */
final class SerializerBinder extends Binder
{
    /**
     * @inheritdoc
     * @throws ConfigurationException Thrown if the config is missing values
     */
    public function bind(IContainer $container): void
    {
        $encoders = new EncoderRegistry();

        $propertyNameFormatterName = null;
        GlobalConfiguration::tryGetString('aphiria.serialization.propertyNameFormatter', $propertyNameFormatterName);

        if ($propertyNameFormatterName === CamelCasePropertyNameFormatter::class) {
            $propertyNameFormatter = new CamelCasePropertyNameFormatter();
        } elseif ($propertyNameFormatterName === null) {
            $propertyNameFormatter = null;
        } else {
            $propertyNameFormatter = $container->resolve($propertyNameFormatterName);
        }

        (new DefaultEncoderRegistrant(
            $propertyNameFormatter,
            GlobalConfiguration::getString('aphiria.serialization.dateFormat')
        ))->registerDefaultEncoders($encoders);

        $container->bindInstance(EncoderRegistry::class, $encoders);

        foreach (GlobalConfiguration::getArray('aphiria.serialization.serializers') as $serializerName) {
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
