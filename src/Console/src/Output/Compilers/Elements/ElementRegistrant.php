<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Compilers\Elements;

/**
 * Defines the element registrant
 */
final class ElementRegistrant
{
    /**
     * Registers the built-in elements
     *
     * @param ElementRegistry $registry The registry to register to
     */
    public function registerElements(ElementRegistry $registry): void
    {
        $registry->registerElement(new Element('success', new Style(Color::Black, Color::Green)));
        $registry->registerElement(new Element('info', new Style(Color::Green)));
        $registry->registerElement(new Element('error', new Style(Color::Black, Color::Yellow)));
        $registry->registerElement(new Element('fatal', new Style(Color::White, Color::Red)));
        $registry->registerElement(new Element('question', new Style(Color::White, Color::Blue)));
        $registry->registerElement(new Element('comment', new Style(Color::Yellow)));
        $registry->registerElement(new Element('b', new Style(null, null, [TextStyle::Bold])));
        $registry->registerElement(new Element('u', new Style(null, null, [TextStyle::Underline])));
    }
}
