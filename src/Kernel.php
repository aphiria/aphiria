<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console;

use Aphiria\Console\Commands\CommandBus;
use Aphiria\Console\Commands\CommandHandlerBinding;
use Aphiria\Console\Commands\CommandHandlerBindingRegistry;
use Aphiria\Console\Commands\Defaults\AboutCommand;
use Aphiria\Console\Commands\Defaults\AboutCommandHandler;
use Aphiria\Console\Commands\Defaults\HelpCommand;
use Aphiria\Console\Commands\Defaults\HelpCommandHandler;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\Console\Requests\Compilers\ArgvRequestCompiler;
use Aphiria\Console\Requests\Compilers\IRequestCompiler;
use Aphiria\Console\Requests\Request;
use Aphiria\Console\Responses\Compilers\ResponseCompiler;
use Aphiria\Console\Responses\ConsoleResponse;
use Aphiria\Console\Responses\IResponse;
use Exception;
use InvalidArgumentException;
use Throwable;

/**
 * Defines the console kernel
 */
final class Kernel
{
    /** @var ICommandBus The command bus that can handle commands */
    private $commandBus;
    /** @var IRequestCompiler The request compiler to use */
    private $requestCompiler;

    /**
     * @param CommandHandlerBindingRegistry $commandHandlerBindings The command handler bindings
     * @param IRequestCompiler|null $requestCompiler The request compiler to use
     */
    public function __construct(
        CommandHandlerBindingRegistry $commandHandlerBindings,
        IRequestCompiler $requestCompiler = null
    ) {
        // Set up our default command handlers
        $commandHandlerBindings->registerCommandHandlerBinding(
            new CommandHandlerBinding(new HelpCommand(), new HelpCommandHandler($commandHandlerBindings))
        );
        $commandHandlerBindings->registerCommandHandlerBinding(
            new CommandHandlerBinding(new AboutCommand(), new AboutCommandHandler($commandHandlerBindings))
        );
        $this->commandBus = new CommandBus($commandHandlerBindings);
        $this->requestCompiler = $requestCompiler ?? new ArgvRequestCompiler();
    }

    /**
     * Handles a console command
     *
     * @param mixed $input The raw input to parse
     * @param IResponse $response The response to write to
     * @return int The status code
     */
    public function handle($input, IResponse $response = null): int
    {
        if ($response === null) {
            $response = new ConsoleResponse(new ResponseCompiler());
        }

        try {
            $request = $this->requestCompiler->compile($input);

            // Handle no command name being invoked as the same thing as invoking the about command
            if ($request->commandName === '') {
                $aboutRequest = new Request('about', $request->argumentValues, $request->options);

                return $this->commandBus->handle($aboutRequest, $response);
            }

            return $this->commandBus->handle($request, $response);
        } catch (InvalidArgumentException $ex) {
            $response->writeln("<error>{$ex->getMessage()}</error>");

            return StatusCodes::ERROR;
        } catch (Exception | Throwable $ex) {
            $response->writeln("<fatal>{$ex->getMessage()}</fatal>");

            return StatusCodes::FATAL;
        }
    }
}
