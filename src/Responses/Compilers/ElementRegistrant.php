<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses\Compilers;

use Aphiria\Console\Responses\Compilers\Elements\Colors;
use Aphiria\Console\Responses\Compilers\Elements\Style;
use Aphiria\Console\Responses\Compilers\Elements\TextStyles;

/**
 * Defines the element registrant
 */
class ElementRegistrant
{
    /**
     * Registers the Apex elements
     *
     * @param ICompiler $compiler The compiler to register to
     */
    public function registerElements(ICompiler $compiler): void
    {
        $compiler->registerElement('success', new Style(Colors::BLACK, Colors::GREEN));
        $compiler->registerElement('info', new Style(Colors::GREEN));
        $compiler->registerElement('error', new Style(Colors::BLACK, Colors::YELLOW));
        $compiler->registerElement('fatal', new Style(Colors::WHITE, Colors::RED));
        $compiler->registerElement('question', new Style(Colors::WHITE, Colors::BLUE));
        $compiler->registerElement('comment', new Style(Colors::YELLOW));
        $compiler->registerElement('b', new Style(null, null, [TextStyles::BOLD]));
        $compiler->registerElement('u', new Style(null, null, [TextStyles::UNDERLINE]));
    }
}
