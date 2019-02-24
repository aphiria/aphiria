<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Output;

use Aphiria\Console\Output\Compilers\IOutputCompiler;

/**
 * Defines the console response
 */
final class ConsoleOutput extends StreamOutput
{
    /**
     * @param IOutputCompiler|null $outputCompiler The output compiler to use
     */
    public function __construct(IOutputCompiler $outputCompiler = null)
    {
        parent::__construct(fopen('php://stdout', 'wb'), fopen(STDIN, 'rb'), $outputCompiler);
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        $this->write(chr(27) . '[2J' . chr(27) . '[;H');
    }
}
