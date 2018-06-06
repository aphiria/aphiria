<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests;

use Opulence\Api\Controller;
use Opulence\Api\ControllerContext;
use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\ContentNegotiation\IMediaTypeFormatter;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Routing\Matchers\MatchedRoute;
use Opulence\Routing\Matchers\RouteAction;

/**
 * Tests the controller context
 */
class ControllerContextTest extends \PHPUnit\Framework\TestCase
{
    /** @var ControllerContext The context to use in tests */
    private $context;
    /** @var Controller The controller to use in tests */
    private $controller;
    /** @var IHttpRequestMessage The request to use in tests */
    private $request;
    /** @var ContentNegotiationResult The request content negotiation result to use in tests */
    private $requestContentNegotiationResult;
    /** @var ContentNegotiationResult The response content negotiation result to use in tests */
    private $responseContentNegotiationResult;
    /** @var MatchedRoute The matched route to use in tests */
    private $matchedRoute;

    public function setUp(): void
    {
        $this->controller = $this->createMock(Controller::class);
        $this->request = $this->createMock(IHttpRequestMessage::class);
        $this->requestContentNegotiationResult = new ContentNegotiationResult(
            $this->createMock(IMediaTypeFormatter::class),
            null,
            null,
            null
        );
        $this->responseContentNegotiationResult = new ContentNegotiationResult(
            $this->createMock(IMediaTypeFormatter::class),
            null,
            null,
            null
        );
        /** @var RouteAction $routeAction */
        $routeAction = $this->createMock(RouteAction::class);
        $this->matchedRoute = new MatchedRoute($routeAction, [], []);
        $this->context = new ControllerContext(
            $this->controller,
            $this->request,
            $this->requestContentNegotiationResult,
            $this->responseContentNegotiationResult,
            $this->matchedRoute
        );
    }

    public function testGettingControllerReturnsSameOneSetInConstructor(): void
    {
        $this->assertSame($this->controller, $this->context->getController());
    }

    public function testGettingMatchedRouteReturnsSameOneSetInConstructor(): void
    {
        $this->assertSame($this->matchedRoute, $this->context->getMatchedRoute());
    }

    public function testGettingRequestReturnsSameOneSetInConstructor(): void
    {
        $this->assertSame($this->request, $this->context->getRequest());
    }

    public function testGettingRequestContentNegotiationResultReturnsSameOneSetInConstructor(): void
    {
        $this->assertSame($this->requestContentNegotiationResult, $this->context->getRequestContentNegotiationResult());
    }

    public function testGettingResponseContentNegotiationResultReturnsSameOneSetInConstructor(): void
    {
        $this->assertSame(
            $this->responseContentNegotiationResult,
            $this->context->getResponseContentNegotiationResult()
        );
    }
}
