<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Exceptions;

use Aphiria\Exceptions\LogLevelFactory;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Defines the exception handler middleware
 */
class ExceptionHandler implements IMiddleware
{
    /** @var IApiExceptionRenderer The exception response renderer */
    private IApiExceptionRenderer $exceptionRenderer;
    /** @var LoggerInterface The application logger */
    private LoggerInterface $logger;
    /** @var LogLevelFactory The factory that creates PSR-3 log levels from exceptions */
    private LogLevelFactory $logLevelFactory;

    /**
     * @param IApiExceptionRenderer $exceptionRenderer The exception response renderer
     * @param LoggerInterface $logger The application logger
     * @param LogLevelFactory|null $logLevelFactory The PSR-3 log level factory
     */
    public function __construct(
        IApiExceptionRenderer $exceptionRenderer,
        LoggerInterface $logger,
        LogLevelFactory $logLevelFactory = null
    ) {
        $this->exceptionRenderer = $exceptionRenderer;
        $this->logger = $logger;
        $this->logLevelFactory = $logLevelFactory ?? new LogLevelFactory();
    }

    /**
     * @inheritdoc
     */
    public function handle(IRequest $request, IRequestHandler $next): IResponse
    {
        try {
            return $next->handle($request);
        } catch (Exception $ex) {
            $logLevel = $this->logLevelFactory->createLogLevel($ex);
            $this->logger->{$logLevel}($ex);
            $this->exceptionRenderer->setRequest($request);

            return $this->exceptionRenderer->createResponse($ex);
        }
    }
}
