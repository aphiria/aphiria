<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Output\Compilers;

use Aphiria\Console\Output\Compilers\Elements\Style;

/**
 * Defines a mock console compiler (useful for silent outputs)
 */
final class MockOutputCompiler implements IOutputCompiler
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
