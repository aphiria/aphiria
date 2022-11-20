<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api;

use Aphiria\Application\IApplication;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponseWriter;
use Aphiria\Net\Http\StreamResponseWriter;
use Closure;
use Exception;
use RuntimeException;

/**
 * Defines an application that runs an API
 */
class ApiApplication implements IApplication
{
    /**
     * @param IRequestHandler $apiGateway The top-most request handler for the API
     * @param Closure(): IRequest $requestFactory The factory that will return the current request
     * @param IResponseWriter $responseWriter The response writer
     */
    public function __construct(
        private readonly IRequestHandler $apiGateway,
        private readonly Closure $requestFactory,
        private readonly IResponseWriter $responseWriter = new StreamResponseWriter()
    ) {
    }

    /**
     * @inheritdoc
     * @throws RuntimeException Thrown if there was an unhandled exception
     */
    public function run(): int
    {
        try {
            $response = $this->apiGateway->handle(($this->requestFactory)());
            $this->responseWriter->writeResponse($response);

            return 0;
        } catch (Exception $ex) {
            throw new RuntimeException('Failed to run the application', 0, $ex);
        }
    }
}
