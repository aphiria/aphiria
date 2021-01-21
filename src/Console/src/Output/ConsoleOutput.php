<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output;

use Aphiria\Console\Output\Compilers\IOutputCompiler;

/**
 * Defines the console response
 */
class ConsoleOutput extends StreamOutput
{
    /**
     * @param IOutputCompiler|null $outputCompiler The output compiler to use
     */
    public function __construct(IOutputCompiler $outputCompiler = null)
    {
        parent::__construct(\fopen('php://stdout', 'wb'), \fopen('php://stdin', 'rb'), $outputCompiler);
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        $this->write(\chr(27) . '[2J' . \chr(27) . '[;H');
    }
}
