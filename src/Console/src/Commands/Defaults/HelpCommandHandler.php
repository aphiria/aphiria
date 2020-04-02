<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Defaults;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Output\Formatters\CommandFormatter;
use Aphiria\Console\Output\Formatters\PaddingFormatter;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCodes;
use InvalidArgumentException;

/**
 * Defines the help command handler
 */
final class HelpCommandHandler implements ICommandHandler
{
    /** @var string The template for the output */
    private static string $template = <<<EOF
-----------------------------
Command: <info>{{name}}</info>
-----------------------------
<b>{{command}}</b>

<comment>Description:</comment>
  {{description}}
<comment>Arguments:</comment>
{{arguments}}
<comment>Options:</comment>
{{options}}{{helpText}}
EOF;
    /** @var CommandRegistry The commands */
    private CommandRegistry $commands;
    /** @var CommandFormatter The command formatter to use */
    private CommandFormatter $commandFormatter;
    /** @var PaddingFormatter The space padding formatter to use */
    private PaddingFormatter $paddingFormatter;

    /**
     * @param CommandRegistry $commands The commands
     * @param CommandFormatter|null $commandFormatter The command formatter to use
     * @param PaddingFormatter|null $paddingFormatter The space padding formatter to use
     */
    public function __construct(
        CommandRegistry $commands,
        CommandFormatter $commandFormatter = null,
        PaddingFormatter $paddingFormatter = null
    ) {
        $this->commands = $commands;
        $this->commandFormatter = $commandFormatter ?? new CommandFormatter();
        $this->paddingFormatter = $paddingFormatter ?? new PaddingFormatter();
    }

    /**
     * @inheritdoc
     */
    public function handle(Input $input, IOutput $output)
    {
        try {
            if (!isset($input->arguments['command'])) {
                $output->writeln("<comment>Pass in the name of the command you'd like help with</comment>");

                return StatusCodes::OK;
            }

            /** @var Command $command */
            $command = null;

            if (!$this->commands->tryGetCommand($input->arguments['command'], $command)) {
                throw new InvalidArgumentException(
                    "Command \"{$input->arguments['command']}\" is not registered"
                );
            }

            $helpText = '';

            if ($command->helpText !== null && $command->helpText !== '') {
                $helpText = PHP_EOL . '<comment>Help:</comment>' . PHP_EOL . '  ' . $command->helpText;
            }

            // Compile the template
            $compiledTemplate = str_replace(
                ['{{command}}', '{{description}}', '{{name}}', '{{arguments}}', '{{options}}', '{{helpText}}'],
                [
                    $this->commandFormatter->format($command),
                    empty($command->description) ? 'No description' : $command->description,
                    $command->name,
                    $this->getArgumentText($command),
                    $this->getOptionText($command),
                    $helpText
                ],
                self::$template
            );
            $output->writeln($compiledTemplate);
        } catch (InvalidArgumentException $ex) {
            $output->writeln("<error>Command {$input->arguments['command']} does not exist</error>");

            return StatusCodes::ERROR;
        }

        return StatusCodes::OK;
    }

    /**
     * Gets the option names as a formatted string
     *
     * @param Option $option The option to convert to text
     * @return string The option names as text
     */
    private static function getOptionNames(Option $option): string
    {
        $optionNames = "--{$option->name}";

        if ($option->shortName !== null) {
            $optionNames .= "|-{$option->shortName}";
        }

        return $optionNames;
    }

    /**
     * Converts the command arguments to text
     *
     * @param Command $command The command whose argument text we want
     * @return string The arguments as text
     */
    private function getArgumentText(Command $command): string
    {
        if (\count($command->arguments) === 0) {
            return '  No arguments';
        }

        $argumentTexts = [];

        foreach ($command->arguments as $argument) {
            $argumentTexts[] = [$argument->name, $argument->description];
        }

        return $this->paddingFormatter->format(
            $argumentTexts,
            fn ($row) => "  <info>{$row[0]}</info>" . (empty($row[1]) ? '' : " - {$row[1]}")
        );
    }

    /**
     * Gets the options as text
     *
     * @param Command $command The command whose option text we want
     * @return string The options as text
     */
    private function getOptionText(Command $command): string
    {
        if (\count($command->options) === 0) {
            return '  No options';
        }

        $optionTexts = [];

        foreach ($command->options as $option) {
            $optionTexts[] = [self::getOptionNames($option), $option->description];
        }

        return $this->paddingFormatter->format(
            $optionTexts,
            fn ($row) => "  <info>{$row[0]}</info>" . (empty($row[1]) ? '' : " - {$row[1]}")
        );
    }
}
