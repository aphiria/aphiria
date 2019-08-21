<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
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
        $registry->registerElement(new Element('success', new Style(Colors::BLACK, Colors::GREEN)));
        $registry->registerElement(new Element('info', new Style(Colors::GREEN)));
        $registry->registerElement(new Element('error', new Style(Colors::BLACK, Colors::YELLOW)));
        $registry->registerElement(new Element('fatal', new Style(Colors::WHITE, Colors::RED)));
        $registry->registerElement(new Element('question', new Style(Colors::WHITE, Colors::BLUE)));
        $registry->registerElement(new Element('comment', new Style(Colors::YELLOW)));
        $registry->registerElement(new Element('b', new Style(null, null, [TextStyles::BOLD])));
        $registry->registerElement(new Element('u', new Style(null, null, [TextStyles::UNDERLINE])));
    }
}
