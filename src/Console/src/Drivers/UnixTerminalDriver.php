<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Drivers;

use Aphiria\Console\Output\IOutput;

/**
 * Defines the Unix-based terminal driver
 */
class UnixTerminalDriver extends TerminalDriver
{
    /**
     * @inheritdoc
     */
    public function readHiddenInput(IOutput $output): ?string
    {
        if (!$this->supportsStty()) {
            throw new HiddenInputNotSupportedException('STTY must be supported to hide input');
        }

        shell_exec('stty -echo');
        $input = fgets(STDIN, 4096);
        shell_exec('stty ' . shell_exec('stty -g'));

        // Break to a new line so we don't continue on the previous line
        $output->writeln('');

        return $input;
    }

    /**
     * @inheritdoc
     */
    protected function getTerminalDimensionsFromOS(): ?array
    {
        return $this->getTerminalDimensionsFromStty();
    }
}
