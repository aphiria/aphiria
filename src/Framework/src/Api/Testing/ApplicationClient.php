<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Testing;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Framework\Net\Binders\RequestBinder;
use Aphiria\Net\Http\IHttpClient;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;

/**
 * Defines a request client for tests
 */
class ApplicationClient implements IHttpClient
{
    /**
     * @param IRequestHandler$app The application
     * @param IContainer $container The DI container
     */
    public function __construct(protected IRequestHandler $app, private IContainer $container)
    {
    }

    /**
     * @inheritdoc
     * @throws ResolutionException Thrown if the request could not be resolved
     */
    public function send(IRequest $request): IResponse
    {
        /**
         * We override the request binder's request with this one that it is the same one that's resolved/used by
         * other classes, eg controllers and exception renderers.  We explicitly resolve the request so that it
         * dispatches the request binder, which in turn dispatches any other binders that resolved IRequest.
         */
        RequestBinder::setOverridingRequest($request);
        $resolvedRequest = $this->container->resolve(IRequest::class);

        if ($resolvedRequest !== $request) {
            $this->container->bindInstance(IRequest::class, $request);
        }

        return $this->app->handle($request);
    }
}
