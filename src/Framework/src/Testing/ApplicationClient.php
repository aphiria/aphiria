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

use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Framework\Net\Binders\RequestBinder;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IHttpClient;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;

/**
 * Defines a request client for tests
 */
class ApplicationClient implements IHttpClient
{
    /** @var IRequestHandler The application */
    private IRequestHandler $app;
    /** @var IServiceResolver The DI service resolver */
    protected IServiceResolver $serviceResolver;

    /**
     * @param IRequestHandler$app The application
     * @param IServiceResolver $serviceResolver The DI service resolver
     */
    public function __construct(IRequestHandler $app, IServiceResolver $serviceResolver)
    {
        $this->app = $app;
        $this->serviceResolver = $serviceResolver;
    }

    /**
     * @inheritdoc
     */
    public function send(IRequest $request): IResponse
    {
        /**
         * We override the request binder's request with this one that it is the same one that's resolved/used by
         * other classes, eg controllers and exception renderers.  We explicitly resolve the request so that it
         * dispatches the request binder, which in turn dispatches any other binders that resolved IRequest.
         */
        RequestBinder::setOverridingRequest($request);

        return $this->app->handle($this->serviceResolver->resolve(IRequest::class));
    }
}
