<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses\Compilers;

use Aphiria\Console\Responses\Compilers\Elements\Style;

/**
 * Defines a mock console compiler (useful for silent responses)
 */
class MockCompiler implements ICompiler
{
    /**
     * @inheritdoc
     */
    public function compile(string $message): string
    {
        return $message;
    }

    public function registerElement(string $name, Style $style): void
    {
        // Don't do anything
    }

    /**
     * @inheritdoc
     */
    public function setStyled(bool $isStyled): void
    {
        // Don't do anything
    }
}
