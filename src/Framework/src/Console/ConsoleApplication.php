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
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\Console\StatusCode;
use Closure;
use Exception;
use RuntimeException;

/**
 * Defines a console application
 */
class ConsoleApplication implements IApplication
{
    /**
     * @param ICommandBus $consoleGateway The top-most command bus that acts as a gateway into the console application
     * @param Closure(): array $argvFactory The factory that will return the raw arguments passed into the application
     */
    public function __construct(private readonly ICommandBus $consoleGateway, private readonly Closure $argvFactory)
    {
    }

    /**
     * @inheritdoc
     */
    public function run(): int
    {
        try {
            $statusCode = $this->consoleGateway->handle(($this->argvFactory)());

            return $statusCode instanceof StatusCode ? $statusCode->value : $statusCode;
        } catch (Exception $ex) {
            throw new RuntimeException('Failed to run the application', 0, $ex);
        }
    }
}
