<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Controllers;

use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\ContentNegotiation\NegotiatedResponseFactory;
use Opulence\Net\Http\Formatting\RequestParser;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

class ControllerRequestHandler implements IRequestHandler
{
    /** @var Controller The controller */
    private $controller;
    /** @var callable Theroute action delegate */
    private $routeActionDelegate;
    /** @var array The route variables */
    private $routeVariables;
    /** @var IContentNegotiator The content negotiator */
    private $contentNegotiator;
    /** @var IRouteActionInvoker The route action invoker */
    private $routeActionInvoker;

    /**
     * @param Controller $controller The controller
     * @param callable $routeActionDelegate The route action delegate
     * @param array $routeVariables The route variables
     * @param IContentNegotiator $contentNegotiator The content negotiator
     * @param IRouteActionInvoker|null $routeActionInvoker The route action invoker to use
     */
    public function __construct(
        Controller $controller,
        callable $routeActionDelegate,
        array $routeVariables,
        IContentNegotiator $contentNegotiator,
        IRouteActionInvoker $routeActionInvoker = null
    ) {
        $this->controller = $controller;
        $this->routeActionDelegate = $routeActionDelegate;
        $this->routeVariables = $routeVariables;
        $this->contentNegotiator = $contentNegotiator;
        $this->routeActionInvoker = $routeActionInvoker ?? new RouteActionInvoker($this->contentNegotiator);
    }

    /**
     * @inheritdoc
     */
    public function handle(IHttpRequestMessage $request): IHttpResponseMessage
    {
        $this->controller->setRequest($request);
        $this->controller->setRequestParser(new RequestParser);
        $this->controller->setContentNegotiator($this->contentNegotiator);
        $this->controller->setNegotiatedResponseFactory(new NegotiatedResponseFactory($this->contentNegotiator));

        return $this->routeActionInvoker->invokeRouteAction(
            $this->routeActionDelegate,
            $request,
            $this->routeVariables
        );
    }
}