<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Drivers;

use Aphiria\Console\Output\IOutput;

/**
 * Defines the Windows OS CLI driver
 */
class WindowsDriver extends Driver
{
    /** @var string The path to the hidden input exe */
    private const string HIDDEN_INPUT_EXE_PATH = __DIR__ . '/../../bin/hiddeninput.exe';

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     * @psalm-suppress ForbiddenCode We purposely are running an external executable
     */
    public function readHiddenInput(IOutput $output): ?string
    {
        // Check if we're running from a PHAR
        if (\str_starts_with(__FILE__, 'phar:')) {
            $hiddenInputExeTempPath = \sys_get_temp_dir() . '/hiddeninput.exe';
            \copy(self::HIDDEN_INPUT_EXE_PATH, $hiddenInputExeTempPath);
            $input = \shell_exec($hiddenInputExeTempPath);
            \unlink($hiddenInputExeTempPath);
        } else {
            $input = \shell_exec(self::HIDDEN_INPUT_EXE_PATH);
        }

        // Break to a new line so we don't continue on the previous line
        $output->writeln('');

        return $input;
    }

    /**
     * Gets the CLI dimensions from the console mode as a tuple
     *
     * @return array|null The dimensions (width x height) as a tuple if found, otherwise null
     * @codeCoverageIgnore
     */
    protected function getCliDimensionsFromConsoleMode(): ?array
    {
        $modeOutput = $this->runProcess('mode CON');

        if (
            $modeOutput === null
            || !\preg_match('/--------+\r?\n.+?(\d+)\r?\n.+?(\d+)\r?\n/', $modeOutput, $matches)
        ) {
            return null;
        }

        return [(int)$matches[2], (int)$matches[1]];
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    protected function getCliDimensionsFromOS(): ?array
    {
        if (
            \is_string($ansicon = \getenv('ANSICON'))
            && \preg_match('/^(\d+)x(\d+)(?: \((\d+)x(\d+)\))?$/', \trim($ansicon), $matches)
        ) {
            return [(int)$matches[1], (int)($matches[4] ?? $matches[2])];
        }

        // This is too difficult to test with mocks
        // @codeCoverageIgnoreStart
        if (
            (!\function_exists('sapi_windows_vt100_support') || !\sapi_windows_vt100_support(\fopen('php://stdout', 'wb')))
            && $this->supportsStty()
        ) {
            return $this->getCliDimensionsFromStty();
        }

        if (($consoleModeDimensions = $this->getCliDimensionsFromConsoleMode()) !== null) {
            return $consoleModeDimensions;
        }

        return null;
        // @codeCoverageIgnoreEnd
    }
}
