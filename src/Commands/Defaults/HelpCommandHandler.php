<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands\Defaults;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandHandlerBindingRegistry;
use Aphiria\Console\Commands\CommandInput;
use Aphiria\Console\Commands\ICommandHandler;
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
    private static $template = <<<EOF
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
    /** @var CommandHandlerBindingRegistry The command handler bindings */
    private $commandHandlerBindings;
    /** @var CommandFormatter The command formatter to use */
    private $commandFormatter;
    /** @var PaddingFormatter The space padding formatter to use */
    private $paddingFormatter;

    /**
     * @param CommandHandlerBindingRegistry $commandHandlerBindings The command handler bindings
     * @param CommandFormatter|null $commandFormatter The command formatter to use
     * @param PaddingFormatter|null $paddingFormatter The space padding formatter to use
     */
    public function __construct(
        CommandHandlerBindingRegistry $commandHandlerBindings,
        CommandFormatter $commandFormatter = null,
        PaddingFormatter $paddingFormatter = null
    ) {
        $this->commandHandlerBindings = $commandHandlerBindings;
        $this->commandFormatter = $commandFormatter ?? new CommandFormatter();
        $this->paddingFormatter = $paddingFormatter ?? new PaddingFormatter();
    }

    /**
     * @inheritDoc
     */
    public function handle(CommandInput $commandInput, IOutput $output)
    {
        try {
            if (!isset($commandInput->arguments['command'])) {
                $output->writeln("<comment>Pass in the name of the command you'd like help with</comment>");

                return StatusCodes::OK;
            }

            $binding = $this->commandHandlerBindings->getCommandHandlerBinding($commandInput->arguments['command']);
            $descriptionText = 'No description';
            $helpText = '';

            if ($binding->command->description !== '') {
                $descriptionText = $binding->command->description;
            }

            if ($binding->command->helpText !== '') {
                $helpText = PHP_EOL . '<comment>Help:</comment>' . PHP_EOL . '  ' . $binding->command->helpText;
            }

            // Compile the template
            $compiledTemplate = str_replace(
                ['{{command}}', '{{description}}', '{{name}}', '{{arguments}}', '{{options}}', '{{helpText}}'],
                [
                    $this->commandFormatter->format($binding->command),
                    $descriptionText,
                    $binding->command->name,
                    $this->getArgumentText($binding->command),
                    $this->getOptionText($binding->command),
                    $helpText
                ],
                self::$template
            );
            $output->writeln($compiledTemplate);
        } catch (InvalidArgumentException $ex) {
            $output->writeln("<error>Command {$commandInput->arguments['command']} does not exist</error>");

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
        if (count($command->arguments) === 0) {
            return '  No arguments';
        }

        $argumentTexts = [];

        foreach ($command->arguments as $argument) {
            $argumentTexts[] = [$argument->name, $argument->description];
        }

        return $this->paddingFormatter->format($argumentTexts, function ($row) {
            return "  <info>{$row[0]}</info> - {$row[1]}";
        });
    }

    /**
     * Gets the options as text
     *
     * @param Command $command The command whose option text we want
     * @return string The options as text
     */
    private function getOptionText(Command $command): string
    {
        if (count($command->options) === 0) {
            return '  No options';
        }

        $optionTexts = [];

        foreach ($command->options as $option) {
            $optionTexts[] = [self::getOptionNames($option), $option->description];
        }

        return $this->paddingFormatter->format($optionTexts, function ($row) {
            return "  <info>{$row[0]}</info> - {$row[1]}";
        });
    }
}
