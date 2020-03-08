<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Serialization\Components;

use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IDependencyResolver;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\Encoding\IEncoder;

/**
 * Defines the serializer component
 */
class SerializerComponent implements IComponent
{
    /** @var IDependencyResolver The dependency resolver */
    private IDependencyResolver $dependencyResolver;
    /** @var IEncoder[] The mapping of class names to their encoders */
    private array $encoders = [];

    /**
     * @param IDependencyResolver $dependencyResolver The dependency resolver
     */
    public function __construct(IDependencyResolver $dependencyResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        $encoders = $this->dependencyResolver->resolve(EncoderRegistry::class);

        foreach ($this->encoders as $class => $encoder) {
            $encoders->registerEncoder($class, $encoder);
        }
    }

    /**
     * Registers an encoder
     *
     * @param string $class The name of the class whose encoder we're registering
     * @param IEncoder $encoder The encoder to register
     * @return self For chaining
     */
    public function withEncoder(string $class, IEncoder $encoder): self
    {
        $this->encoders[$class] = $encoder;

        return $this;
    }
}
