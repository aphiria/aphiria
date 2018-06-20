<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Exceptions;

use Exception;
use Opulence\Net\Http\ResponseWriter;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Defines the exception handler
 */
class ExceptionHandler implements IExceptionHandler
{
    /** @var LoggerInterface The logger */
    protected $logger;
    /** @var IExceptionResponseFactory The exception response factory */
    protected $exceptionResponseFactory;
    /** @var ResponseWriter What to use to write a response */
    protected $responseWriter;
    /** @var array The list of exception classes to not log */
    protected $exceptionsNotLogged;

    /**
     * @param LoggerInterface $logger The logger
     * @param IExceptionResponseFactory $exceptionResponseFactory The exception response factory
     * @param ResponseWriter $responseWriter What to use to write a response
     * @param string|array $exceptionsNotLogged The exception or list of exceptions to not log when thrown
     */
    public function __construct(
        LoggerInterface $logger,
        IExceptionRenderer $exceptionResponseFactory,
        ResponseWriter $responseWriter = null,
        $exceptionsNotLogged = []
    ) {
        $this->logger = $logger;
        $this->exceptionResponseFactory = $exceptionResponseFactory;
        $this->responseWriter = $responseWriter ?? new ResponseWriter();
        $this->exceptionsNotLogged = (array)$exceptionsNotLogged;
    }

    /**
     * @inheritdoc
     */
    public function handle($ex): void
    {
        // It's Throwable, but not an Exception
        if (!$ex instanceof Exception) {
            $ex = new FatalThrowableError($ex);
        }

        if ($this->shouldLog($ex)) {
            $this->logger->error($ex);
        }

        $response = $this->exceptionResponseFactory->createResponseFromException($ex);
        $this->responseWriter->writeResponse($response);
    }

    /**
     * @inheritdoc
     */
    public function register(): void
    {
        set_exception_handler([$this, 'handle']);
    }

    /**
     * Determines whether or not an exception should be logged
     *
     * @param Throwable|Exception $ex The exception to check
     * @return bool True if the exception should be logged, otherwise false
     */
    protected function shouldLog($ex): bool
    {
        return !in_array(get_class($ex), $this->exceptionsNotLogged);
    }
}
