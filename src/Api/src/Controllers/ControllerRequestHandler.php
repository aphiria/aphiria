<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

use Aphiria\Net\Http\ContentNegotiation\ContentNegotiator;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\Formatting\ResponseFormatter;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;

final class ControllerRequestHandler implements IRequestHandler
{
    /** @var Controller The controller */
    private Controller $controller;
    /** @var callable The route action delegate */
    private $routeActionDelegate;
    /** @var array The route variables */
    private array $routeVariables;
    /** @var IContentNegotiator The content negotiator */
    private IContentNegotiator $contentNegotiator;
    /** @var IRouteActionInvoker The route action invoker */
    private IRouteActionInvoker $routeActionInvoker;

    /**
     * @param Controller $controller The controller
     * @param callable $routeActionDelegate The route action delegate
     * @param array $routeVariables The route variables
     * @param IContentNegotiator|null $contentNegotiator The content negotiator, or null if using the default negotiator
     * @param IRouteActionInvoker|null $routeActionInvoker The route action invoker to use
     */
    public function __construct(
        Controller $controller,
        callable $routeActionDelegate,
        array $routeVariables,
        IContentNegotiator $contentNegotiator = null,
        IRouteActionInvoker $routeActionInvoker = null
    ) {
        $this->controller = $controller;
        $this->routeActionDelegate = $routeActionDelegate;
        $this->routeVariables = $routeVariables;
        $this->contentNegotiator = $contentNegotiator ?? new ContentNegotiator();
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
