<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Output\Compilers;

/**
 * Defines a mock console compiler (useful for silent outputs)
 */
final class MockOutputCompiler implements IOutputCompiler
{
    /**
     * @inheritdoc
     */
    public function compile(string $message, bool $includeStyles = true): string
    {
        return $message;
    }
}
