<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console;

use Aphiria\Application\IApplication;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\ConsoleOutput;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use Exception;
use RuntimeException;

/**
 * Defines a console application
 */
class ConsoleApplication implements IApplication
{
    /**
     * @param ICommandHandler $consoleGateway The top-most command handler that acts as a gateway into the console application
     * @param Input $input The input to the console application
     * @param IOutput $output The output of the console application
     */
    public function __construct(
        private readonly ICommandHandler $consoleGateway,
        private readonly Input $input,
        private readonly IOutput $output = new ConsoleOutput()
    ) {
    }

    /**
     * @inheritdoc
     */
    public function run(): int
    {
        try {
            $statusCode = $this->consoleGateway->handle($this->input, $this->output);

            if ($statusCode === null) {
                return StatusCode::Ok->value;
            }

            return $statusCode instanceof StatusCode ? $statusCode->value : $statusCode;
        } catch (Exception $ex) {
            throw new RuntimeException('Failed to run the application', 0, $ex);
        }
    }
}
