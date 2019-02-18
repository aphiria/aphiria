<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses;

use Aphiria\Console\Responses\Compilers\MockResponseCompiler;

/**
 * Defines the silent response, which does not write anything
 */
final class SilentResponse extends Response
{
    public function __construct()
    {
        parent::__construct(new MockResponseCompiler());
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
