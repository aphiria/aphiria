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
 * Defines the Windows OS terminal driver
 */
class WindowsTerminalDriver extends TerminalDriver
{
    /**
     * @inheritdoc
     */
    public function readHiddenInput(IOutput $output): ?string
    {
        $hiddenInputExePath = __DIR__ . '/../../bin/hiddeninput.exe';

        // Check if we're running from a PHAR
        if (strpos(__FILE__, 'phar:') === 0) {
            $hiddenInputExeTempPath = sys_get_temp_dir() . '/hiddeninput.exe';
            copy($hiddenInputExePath, $hiddenInputExeTempPath);
            $input = shell_exec($hiddenInputExeTempPath);
            unlink($hiddenInputExeTempPath);
        } else {
            $input = shell_exec($hiddenInputExePath);
        }

        // Break to a new line so we don't continue on the previous line
        $output->writeln('');

        return $input;
    }

    /**
     * Gets the terminal dimensions from the console mode as a tuple
     *
     * @return array|null The dimensions (width x height) as a tuple if found, otherwise null
     */
    protected function getTerminalDimensionsFromConsoleMode(): ?array
    {
        $modeOutput = $this->runProcess('mode CON');

        if (
            $modeOutput === null
            || !preg_match('/--------+\r?\n.+?(\d+)\r?\n.+?(\d+)\r?\n/', $modeOutput, $matches)
        ) {
            return null;
        }

        return [(int)$matches[2], (int)$matches[1]];
    }

    /**
     * @inheritdoc
     */
    protected function getTerminalDimensionsFromOS(): ?array
    {
        if (
            \is_string($ansicon = getenv('ANSICON'))
            && preg_match('/^(\d+)x(\d+)(?: \((\d+)x(\d+)\))?$/', trim($ansicon), $matches)
        ) {
            return [(int)$matches[1], (int)($matches[4] ?? $matches[2])];
        }

        if (
            (!\function_exists('sapi_windows_vt100_support') || !\sapi_windows_vt100_support(fopen('php://stdout', 'wb')))
            && $this->supportsStty()
        ) {
            return $this->getTerminalDimensionsFromStty();
        }

        if (($consoleModeDimensions = $this->getTerminalDimensionsFromConsoleMode()) !== null) {
            return $consoleModeDimensions;
        }

        return null;
    }
}
