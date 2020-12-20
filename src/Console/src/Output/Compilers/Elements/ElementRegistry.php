<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Compilers\Elements;

use InvalidArgumentException;

/**
 * Defines a registry of elements
 */
final class ElementRegistry
{
    /** @var Element[] The list of elements registered to the compiler */
    private array $elements = [];

    public function __construct()
    {
        // Register built-in elements
        (new ElementRegistrant())->registerElements($this);
    }

    /**
     * Gets the element with the input name
     *
     * @param string $name The name to search for
     * @return Element The element with the input name
     * @throws InvalidArgumentException Thrown if no element with that name exists
     */
    public function getElement(string $name): Element
    {
        if (!isset($this->elements[$name])) {
            throw new InvalidArgumentException("No element exists with name $name");
        }

        return $this->elements[$name];
    }

    /**
     * Registers an element
     *
     * @param Element $element The element to register
     */
    public function registerElement(Element $element): void
    {
        $this->elements[$element->name] = $element;
    }
}
