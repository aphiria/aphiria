<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api;

use Aphiria\Application\IApplication;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponseWriter;
use Aphiria\Net\Http\StreamResponseWriter;
use Exception;
use RuntimeException;

/**
 * Defines an API application that processes requests synchronously
 */
class SynchronousApiApplication implements IApplication
{
    /**
     * @param IRequestHandler $apiGateway The top-most request handler for the API
     * @param IRequest $request The current request
     * @param IResponseWriter $responseWriter The response writer
     */
    public function __construct(
        private readonly IRequestHandler $apiGateway,
        private readonly IRequest $request,
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
            $response = $this->apiGateway->handle($this->request);
            $this->responseWriter->writeResponse($response);

            return 0;
        } catch (Exception $ex) {
            throw new RuntimeException('Failed to run the application', 0, $ex);
        }
    }
}
