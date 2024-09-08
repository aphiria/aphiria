<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Defaults;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\Formatters\PaddingFormatter;
use Aphiria\Console\Output\IOutput;

/**
 * Defines the about command handler
 */
final class AboutCommandHandler implements ICommandHandler
{
    /** @var string The template for the output */
    private static string $template = <<<EOF
{{hr}}
<b>Aphiria</b>
{{hr}}
{{commands}}
EOF;
    /**
     * @param CommandRegistry $commands The commands
     * @param PaddingFormatter $paddingFormatter The space padding formatter to use
     */
    public function __construct(
        private readonly CommandRegistry $commands,
        private readonly PaddingFormatter $paddingFormatter = new PaddingFormatter()
    ) {
    }

    /**
     * @inheritdoc
     *
     * @return void
     */
    public function handle(Input $input, IOutput $output)
    {
        // Compile the template
        $compiledTemplate = \str_replace(
            ['{{commands}}', '{{hr}}'],
            [$this->getCommandText(), \str_repeat('-', $output->getDriver()->cliWidth)],
            self::$template
        );

        $output->writeln($compiledTemplate);
    }

    /**
     * Converts commands to text
     *
     * @return string The commands as text
     */
    private function getCommandText(): string
    {
        $commands = $this->commands->getAllCommands();

        if (\count($commands) === 0) {
            return '  <info>No commands</info>';
        }

        /**
         * Sorts the commands by name
         * Uncategorized (commands without ":" in their names) always come first
         *
         * @param Command $a
         * @param Command $b
         * @return int The result of the comparison
         */
        $sort = static function (Command $a, Command $b): int {
            if (!\str_contains($a->name, ':')) {
                if (!\str_contains($b->name, ':')) {
                    // They're both uncategorized
                    return $a->name < $b->name ? -1 : 1;
                }

                // B is categorized
                return -1;
            }

            if (!\str_contains($b->name, ':')) {
                // A is categorized
                return 1;
            }

            // They're both categorized
            return $a->name < $b->name ? -1 : 1;
        };

        \usort($commands, $sort);
        $categorizedCommandNames = [];
        $commandTexts = [];
        $firstCommandNamesToCategories = [];

        foreach ($commands as $command) {
            $commandNameParts = \explode(':', $command->name);

            if (\count($commandNameParts) > 1 && !\in_array($commandNameParts[0], $firstCommandNamesToCategories, true)) {
                $categorizedCommandNames[] = $command->name;
                $firstCommandNamesToCategories[$command->name] = $commandNameParts[0];
            }

            $commandTexts[] = [$command->name, $command->description];
        }

        return $this->paddingFormatter->format(
            $commandTexts,
            function (array $row) use ($categorizedCommandNames, $firstCommandNamesToCategories): string {
                $output = '';

                // If this is the first command of its category, display the category
                if (
                    isset($firstCommandNamesToCategories[\trim((string)$row[0])])
                    && \in_array(\trim((string)$row[0]), $categorizedCommandNames, true)
                ) {
                    $output .= "<comment>{$firstCommandNamesToCategories[\trim((string)$row[0])]}</comment>" . PHP_EOL;
                }

                $output .= "  <info>{$row[0]}</info>";

                // Only append the description if it's set
                if (!empty($row[1])) {
                    $output .= " - {$row[1]}";
                }

                return $output;
            }
        );
    }
}
