<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Components;

use Aphiria\Application\IComponent;
use Aphiria\Console\Commands\Attributes\AttributeCommandRegistrant;
use Aphiria\Console\Commands\ClosureCommandRegistrant;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Output\Compilers\Elements\Element;
use Aphiria\Console\Output\Compilers\Elements\ElementRegistry;
use Aphiria\Console\Output\Compilers\IOutputCompiler;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use Closure;
use RuntimeException;

/**
 * Defines the command component
 */
class CommandComponent implements IComponent
{
    /** @var bool Whether or not attributes are enabled */
    private bool $attributesEnabled = false;
    /** @var list<Closure(CommandRegistry): void> The list of callbacks that can register commands */
    private array $callbacks = [];
    /** @var list<Element> The list of elements to register */
    private array $elements = [];

    /**
     * @param IServiceResolver $serviceResolver The service resolver
     */
    public function __construct(private readonly IServiceResolver $serviceResolver) {}

    /**
     * @inheritdoc
     * @throws ResolutionException Thrown if some dependencies could not be resolved
     */
    public function build(): void
    {
        $commands = $this->serviceResolver->resolve(CommandRegistry::class);
        $commandRegistrants = $this->serviceResolver->resolve(CommandRegistrantCollection::class);

        if ($this->attributesEnabled) {
            $attributeCommandRegistrant = null;

            if (!$this->serviceResolver->tryResolve(AttributeCommandRegistrant::class, $attributeCommandRegistrant)) {
                throw new RuntimeException(AttributeCommandRegistrant::class . ' cannot be null if using attributes');
            }

            /** @var AttributeCommandRegistrant $attributeCommandRegistrant */
            $commandRegistrants->add($attributeCommandRegistrant);
        }

        $commandRegistrants->add(new ClosureCommandRegistrant($this->callbacks));
        $commandRegistrants->registerCommands($commands);

        foreach ($this->elements as $element) {
            $this->serviceResolver->resolve(ElementRegistry::class)->registerElement($element);
        }
    }

    /**
     * Enables console attributes
     *
     * @return static For chaining
     */
    public function withAttributes(): static
    {
        $this->attributesEnabled = true;

        return $this;
    }

    /**
     * Adds commands to the registry
     *
     * @param Closure(CommandRegistry): void $callback The callback that takes in an instance of CommandRegistry
     * @return static For chaining
     */
    public function withCommands(Closure $callback): static
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * Registers console elements
     *
     * @param Element|list<Element> $elements The element or list of elements to register
     * @return static For chaining
     */
    public function withElement(Element|array $elements): static
    {
        foreach (\is_array($elements) ? $elements : [$elements] as $element) {
            $this->elements[] = $element;
        }

        return $this;
    }
}
