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

use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Api\Builders\ApiApplicationBuilder;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;

/**
 * Defines a request client for tests
 */
class ApplicationClient
{
    /** @var ApiApplicationBuilder The API application builder */
    protected ApiApplicationBuilder $appBuilder;
    /** @var IContainer The DI container */
    protected IContainer $container;

    /**
     * @param ApiApplicationBuilder $appBuilder The API application builder
     * @param IContainer $container The DI container
     */
    public function __construct(ApiApplicationBuilder $appBuilder, IContainer $container)
    {
        $this->appBuilder = $appBuilder;
        $this->container = $container;
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
        /**
         * We build the app every time so that we can ensure that the correct request is bound and set in all
         * classes that depend on it
         */
        $this->container->bindInstance(IRequest::class, $request);

        return $this->appBuilder->build()
            ->handle($request);
    }
}
