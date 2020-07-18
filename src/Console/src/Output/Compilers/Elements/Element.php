<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Compilers\Elements;

/**
 * Defines an output element
 */
final class Element
{
    /** @var string The name of the element */
    public string $name;
    /** @var Style The style of the element */
    public Style $style;

    /**
     * @param string $name The name of the element
     * @param Style $style The style of the element
     */
    public function __construct(string $name, Style $style)
    {
        $this->name = $name;
        $this->style = $style;
    }
}
