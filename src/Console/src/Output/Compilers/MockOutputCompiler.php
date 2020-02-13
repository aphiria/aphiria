<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

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
