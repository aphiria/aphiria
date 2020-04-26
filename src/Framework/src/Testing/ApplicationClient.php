<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Testing;

use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;

/**
 * Defines a request client for tests
 */
class ApplicationClient
{
    /** @var IRequestHandler The API application */
    protected IRequestHandler $app;

    /**
     * @param IRequestHandler $app The API application
     */
    public function __construct(IRequestHandler $app)
    {
        $this->app = $app;
    }

    /**
     * Sends a request through the application and gets a response
     *
     * @param IRequest $request The request to send
     * @return IResponse The returned response
     * @throws HttpException Thrown if there was an error handling the request
     */
    public function send(IRequest $request): IResponse
    {
        return $this->app->handle($request);
    }
}
