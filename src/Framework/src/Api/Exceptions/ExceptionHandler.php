<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
    /**
     * @param IApiExceptionRenderer $exceptionRenderer The exception response renderer
     * @param LoggerInterface $logger The application logger
     * @param LogLevelFactory $logLevelFactory The PSR-3 log level factory
     */
    public function __construct(
        private readonly IApiExceptionRenderer $exceptionRenderer,
        private readonly LoggerInterface $logger,
        private readonly LogLevelFactory $logLevelFactory = new LogLevelFactory()
    ) {
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
