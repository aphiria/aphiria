<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output;

use Aphiria\Console\Output\Compilers\MockOutputCompiler;

/**
 * Defines the silent output, which does not write anything
 */
final class SilentOutput extends Output
{
    public function __construct()
    {
        parent::__construct(new MockOutputCompiler());
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        // Don't do anything
    }

    /**
     * @inheritdoc
     */
    public function readLine(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function write($messages): void
    {
        // Don't do anything
    }

    /**
     * @inheritdoc
     */
    public function writeln($messages): void
    {
        // Don't do anything
    }

    /**
     * @inheritdoc
     */
    protected function doWrite(string $message, bool $includeNewLine): void
    {
        // Don't do anything
    }
}
