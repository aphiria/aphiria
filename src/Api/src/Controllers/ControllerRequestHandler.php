<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

use Aphiria\ContentNegotiation\ContentNegotiator;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\Formatting\ResponseFormatter;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Closure;

final class ControllerRequestHandler implements IRequestHandler
{
    /** @var IRouteActionInvoker The route action invoker */
    private readonly IRouteActionInvoker $routeActionInvoker;

    /**
     * @param Controller $controller The controller
     * @param Closure $routeActionDelegate The route action delegate
     * @param array<string, mixed> $routeVariables The route variables
     * @param IContentNegotiator $contentNegotiator The content negotiator
     * @param IRouteActionInvoker|null $routeActionInvoker The route action invoker to use
     */
    public function __construct(
        private readonly Controller $controller,
        private readonly Closure $routeActionDelegate,
        private readonly array $routeVariables,
        private readonly IContentNegotiator $contentNegotiator = new ContentNegotiator(),
        IRouteActionInvoker $routeActionInvoker = null
    ) {
        $this->routeActionInvoker = $routeActionInvoker ?? new RouteActionInvoker($this->contentNegotiator);
    }

    /**
     * @inheritdoc
     */
    public function handle(IRequest $request): IResponse
    {
        $this->controller->setRequest($request);
        $this->controller->setRequestParser(new RequestParser());
        $this->controller->setResponseFormatter(new ResponseFormatter());
        $this->controller->setContentNegotiator($this->contentNegotiator);
        $this->controller->setResponseFactory(new NegotiatedResponseFactory($this->contentNegotiator));

        return $this->routeActionInvoker->invokeRouteAction(
            $this->routeActionDelegate,
            $request,
            $this->routeVariables
        );
    }
}
