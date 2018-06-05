<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Controller;

use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\RequestParser;

/**
 * Defines the base class for controllers to extend
 */
abstract class Controller
{
    /** @var IHttpRequestMessage The current request */
    protected $request;
    /** @var RequestParser The parser to use to get data from the current request */
    protected $requestParser;

    /**
     * Sets the current request
     *
     * @param IHttpRequestMessage $request The current request
     * @internal
     */
    public function setRequest(IHttpRequestMessage $request): void
    {
        $this->request = $request;
    }

    /**
     * Sets the request parser
     *
     * @param RequestParser $requestParser The request parser
     * @internal
     */
    public function setRequestParser(RequestParser $requestParser): void
    {
        $this->requestParser = $requestParser;
    }
}
