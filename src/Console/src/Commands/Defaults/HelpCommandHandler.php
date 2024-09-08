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
use Aphiria\Console\Input\Option;
use Aphiria\Console\Output\Formatters\CommandFormatter;
use Aphiria\Console\Output\Formatters\PaddingFormatter;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use InvalidArgumentException;

/**
 * Defines the help command handler
 */
final class HelpCommandHandler implements ICommandHandler
{
    /** @var string The template for the output */
    private static string $template = <<<EOF
{{hr}}
Command: <info>{{name}}</info>
{{hr}}
<b>{{command}}</b>

<comment>Description:</comment>
  {{description}}
<comment>Arguments:</comment>
{{arguments}}
<comment>Options:</comment>
{{options}}{{helpText}}
EOF;

    /**
     * @param CommandRegistry $commands The commands
     * @param CommandFormatter $commandFormatter The command formatter to use
     * @param PaddingFormatter $paddingFormatter The space padding formatter to use
     */
    public function __construct(
        private readonly CommandRegistry $commands,
        private readonly CommandFormatter $commandFormatter = new CommandFormatter(),
        private readonly PaddingFormatter $paddingFormatter = new PaddingFormatter()
    ) {
    }

    /**
     * @inheritdoc
     */
    public function handle(Input $input, IOutput $output)
    {
        try {
            if (!isset($input->arguments['command'])) {
                $output->writeln("<comment>Pass in the name of the command you'd like help with</comment>");

                return StatusCode::Ok;
            }

            /** @var Command $command */
            $command = null;

            if (!$this->commands->tryGetCommand((string)$input->arguments['command'], $command)) {
                throw new InvalidArgumentException(
                    "Command \"{$input->arguments['command']}\" is not registered"
                );
            }

            $helpText = '';

            if ($command->helpText !== null && $command->helpText !== '') {
                $helpText = PHP_EOL . '<comment>Help:</comment>' . PHP_EOL . '  ' . $command->helpText;
            }

            // Compile the template
            $compiledTemplate = \str_replace(
                ['{{hr}}', '{{command}}', '{{description}}', '{{name}}', '{{arguments}}', '{{options}}', '{{helpText}}'],
                [
                    \str_repeat('-', $output->getDriver()->cliWidth),
                    $this->commandFormatter->format($command),
                    $command->description === null || $command->description === '' ? 'No description' : $command->description,
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

            return StatusCode::Error;
        }

        return StatusCode::Ok;
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
            fn (array $row): string => "  <info>{$row[0]}</info>" . (empty($row[1]) ? '' : " - {$row[1]}")
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
            fn (array $row): string => "  <info>{$row[0]}</info>" . (empty($row[1]) ? '' : " - {$row[1]}")
        );
    }
}
