<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Commands;

use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;

/**
 * Defines the serve command handler
 */
class ServeCommandHandler implements ICommandHandler
{
    /**
     * @inheritdoc
     *
     * @return void
     */
    public function handle(Input $input, IOutput $output)
    {
        $domain = (string)$input->options['domain'];
        $port = (int)$input->options['port'];
        $output->writeln("<info>Running at http://$domain:$port</info>");
        $command = sprintf(
            '%s -S %s:%d -t %s %s',
            PHP_BINARY,
            $domain,
            $port,
            (string)$input->options['docroot'],
            (string)$input->options['router']
        );
        $this->runPhpCommand($command);
    }

    /**
     * Runs the PHP command
     *
     * @param string $command The command to run
     * @codeCoverageIgnore
     */
    protected function runPhpCommand(string $command): void
    {
        \passthru($command);
    }
}
