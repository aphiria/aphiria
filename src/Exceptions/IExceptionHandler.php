<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Exceptions;

use Throwable;

/**
 * Defines the interface for exception handlers to implement
 */
interface IExceptionHandler
{
    /**
     * Handles an exception
     *
     * @param Throwable $ex The exception to handle
     */
    public function handle($ex): void;

    /**
     * Registers the handler with PHP
     */
    public function register(): void;
}
