<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Exceptions;

use ErrorException;
use Opulence\Api\RequestContext;
use Opulence\Net\Http\IHttpResponseMessage;
use Throwable;

/**
 * Defines the interface for exception handlers to implement
 */
interface IExceptionHandler
{
    /**
     * Handles a caught exception and creates a response from it
     *
     * @param Throwable $ex The caught exception to create a request from
     * @return IHttpResponseMessage The response
     */
    public function handleCaughtException(Throwable $ex): IHttpResponseMessage;

    /**
     * Handles an error
     *
     * @param int $level The level of the error
     * @param string $message The message
     * @param string $file The file the error occurred in
     * @param int $line The line number the error occurred at
     * @param array $context The symbol table
     * @throws ErrorException Thrown because the error is converted to an exception
     */
    public function handleUncaughtError(
        int $level,
        string $message,
        string $file = '',
        int $line = 0,
        array $context = []
    ): void;

    /**
     * Handles an exception
     *
     * @param Throwable $ex The exception to handle
     */
    public function handleUncaughtException(Throwable $ex): void;

    /**
     * Handles a PHP shutdown
     */
    public function handleShutdown(): void;

    /**
     * Registers the exception and error handlers with PHP
     */
    public function register(): void;

    /**
     * Sets the request context for use when handling a request
     * This can't be set via a constructor because it's not known until a little way into the app pipeline
     *
     * @param RequestContext $requestContext The current request context
     */
    public function setRequestContext(RequestContext $requestContext): void;
}
