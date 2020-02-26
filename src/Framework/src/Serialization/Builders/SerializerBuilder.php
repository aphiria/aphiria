<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Serialization\Builders;

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\ApplicationBuilders\IComponentBuilder;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\Encoding\IEncoder;

/**
 * Defines the serialization component builder
 */
class SerializerBuilder implements IComponentBuilder
{
    /** @var EncoderRegistry The encoder registry */
    private EncoderRegistry $encoders;

    /**
     * @param EncoderRegistry $encoders The encoder registry
     */
    public function __construct(EncoderRegistry $encoders)
    {
        $this->encoders = $encoders;
    }

    /**
     * @inheritdoc
     */
    public function build(IApplicationBuilder $appBuilder): void
    {
        // Don't need to actually do anything - the encoders got registered in another method
    }

    /**
     * Registers an encoder
     *
     * @param string $class The name of the class whose encoder we're registering
     * @param IEncoder $encoder The encoder to register
     * @return SerializerBuilder For chaining
     */
    public function withEncoder(string $class, IEncoder $encoder): self
    {
        $this->encoders->registerEncoder($class, $encoder);

        return $this;
    }
}
