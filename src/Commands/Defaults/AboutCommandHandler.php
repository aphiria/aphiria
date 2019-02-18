<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands\Defaults;

use Aphiria\Console\Commands\CommandHandlerBinding;
use Aphiria\Console\Commands\CommandHandlerBindingRegistry;
use Aphiria\Console\Commands\CommandInput;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Output\Formatters\PaddingFormatter;
use Aphiria\Console\Output\IOutput;

/**
 * Defines the about command handler
 */
final class AboutCommandHandler implements ICommandHandler
{
    /** @var string The template for the output */
    private static $template = <<<EOF
-----------------------------
About <b>Aphiria</b>
-----------------------------
{{commands}}
EOF;
    /** @var CommandHandlerBindingRegistry The command handler bindings */
    private $commandHandlerBindings;
    /** @var PaddingFormatter The space padding formatter to use */
    private $paddingFormatter;

    /**
     * @param CommandHandlerBindingRegistry $commandHandlerBindings The command handler bindings
     * @param PaddingFormatter|null $paddingFormatter The space padding formatter to use
     */
    public function __construct(
        CommandHandlerBindingRegistry $commandHandlerBindings,
        PaddingFormatter $paddingFormatter = null
    ) {
        $this->commandHandlerBindings = $commandHandlerBindings;
        $this->paddingFormatter = $paddingFormatter ?? new PaddingFormatter();
    }

    /**
     * @inheritDoc
     */
    public function handle(CommandInput $commandInput, IOutput $output)
    {
        // Compile the template
        $compiledTemplate = self::$template;
        $compiledTemplate = str_replace('{{commands}}', $this->getCommandText(), $compiledTemplate);

        $output->writeln($compiledTemplate);
    }

    /**
     * Converts commands to text
     *
     * @return string The commands as text
     */
    private function getCommandText(): string
    {
        $bindings = $this->commandHandlerBindings->getAllCommandHandlerBindings();

        if (count($bindings) === 0) {
            return '  <info>No commands</info>';
        }

        /**
         * Sorts the commands by name
         * Uncategorized (commands without ":" in their names) always come first
         *
         * @param CommandHandlerBinding $a
         * @param CommandHandlerBinding $b
         * @return int The result of the comparison
         */
        $sort = function ($a, $b) {
            if (strpos($a->command->name, ':') === false) {
                if (strpos($b->command->name, ':') === false) {
                    // They're both uncategorized
                    return $a->command->name < $b->command->name ? -1 : 1;
                }

                // B is categorized
                return -1;
            }

            if (strpos($b->command->name, ':') === false) {
                // A is categorized
                return 1;
            }

            // They're both categorized
            return $a->command->name < $b->command->name ? -1 : 1;
        };

        usort($bindings, $sort);
        $categorizedCommandNames = [];
        $commandTexts = [];
        $firstCommandNamesToCategories = [];

        foreach ($bindings as $binding) {
            $commandName = $binding->command->name;
            $commandNameParts = explode(':', $commandName);

            if (count($commandNameParts) > 1 && !in_array($commandNameParts[0], $firstCommandNamesToCategories, true)) {
                $categorizedCommandNames[] = $commandName;

                // If this is the first command for this category
                if (!in_array($commandNameParts[0], $firstCommandNamesToCategories, true)) {
                    $firstCommandNamesToCategories[$commandName] = $commandNameParts[0];
                }
            }

            $commandTexts[] = [$commandName, $binding->command->description];
        }

        return $this->paddingFormatter->format(
            $commandTexts,
            function ($row) use ($categorizedCommandNames, $firstCommandNamesToCategories) {
                $output = '';

                // If this is the first command of its category, display the category
                if (
                    isset($firstCommandNamesToCategories[trim($row[0])])
                    && in_array(trim($row[0]), $categorizedCommandNames, true)
                ) {
                    $output .= "<comment>{$firstCommandNamesToCategories[trim($row[0])]}</comment>" . PHP_EOL;
                }

                return $output . "  <info>{$row[0]}</info> - {$row[1]}";
            }
        );
    }
}
