<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses;

use Aphiria\Console\Responses\Compilers\IResponseCompiler;

/**
 * Defines the console response
 */
final class ConsoleResponse extends StreamResponse
{
    /**
     * @param IResponseCompiler $responseCompiler The response compiler to use
     */
    public function __construct(IResponseCompiler $responseCompiler)
    {
        parent::__construct(fopen('php://stdout', 'wb'), $responseCompiler);
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        $this->write(chr(27) . '[2J' . chr(27) . '[;H');
    }
}
