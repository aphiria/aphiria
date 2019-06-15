<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api;

use Aphiria\Middleware\AttributeMiddleware;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewarePipelineFactory;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use InvalidArgumentException;

/**
 * Defines the top-level request handler that makes up an application
 */
class App implements IRequestHandler
{
    /** @var IDependencyResolver The dependency resolver */
    private IDependencyResolver $dependencyResolver;
    /** @var IRequestHandler The request handler that will be the last to be executed in the middleware pipeline */
    private IRequestHandler $kernel;
    /** @var MiddlewarePipelineFactory The middleware pipeline factory */
    private ?MiddlewarePipelineFactory $middlewarePipelineFactory;
    /** @var IMiddleware[] The list of middleware */
    private array $middleware = [];

    /**
     * @param IDependencyResolver $dependencyResolver The dependency resolver
     * @param IRequestHandler $kernel The request handler that will be the last to be executed in the middleware pipeline
     * @param MiddlewarePipelineFactory|null $middlewarePipelineFactory THe middleware pipeline factory
     */
    public function __construct(
        IDependencyResolver $dependencyResolver,
        IRequestHandler $kernel,
        MiddlewarePipelineFactory $middlewarePipelineFactory = null
    ) {
        $this->dependencyResolver = $dependencyResolver;
        $this->kernel = $kernel;
        $this->middlewarePipelineFactory = $middlewarePipelineFactory ?? new MiddlewarePipelineFactory();
    }

    /**
     * @param string $middlewareClass The name of the middleware class to add
     * @param array $attributes The optional list of attributes to set on the middleware
     * @throws DependencyResolutionException Thrown if the middleware could not be created
     * @throws InvalidArgumentException Thrown if the middleware class does not implement IMiddleware
     */
    public function addMiddleware(string $middlewareClass, array $attributes = []): void
    {
        $middleware = $this->dependencyResolver->resolve($middlewareClass);

        if (!$middleware instanceof IMiddleware) {
            throw new InvalidArgumentException(
                sprintf('Middleware %s does not implement %s', get_class($middleware), IMiddleware::class)
            );
        }

        if ($middleware instanceof AttributeMiddleware) {
            $middleware->setAttributes($attributes);
        }

        $this->middleware[] = $middleware;
    }

    /**
     * @inheritdoc
     */
    public function handle(IHttpRequestMessage $request): IHttpResponseMessage
    {
        $middlewarePipeline = $this->middlewarePipelineFactory->createPipeline($this->middleware, $this->kernel);

        return $middlewarePipeline->handle($request);
    }
}
