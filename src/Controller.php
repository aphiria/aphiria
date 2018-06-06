<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api;

use Opulence\Net\Http\Formatting\RequestParser;

/**
 * Defines the base class for controllers to extend
 */
abstract class Controller
{
    /** @var ControllerContext The current controller context */
    protected $context;
    /** @var RequestParser The parser to use to get data from the current request */
    protected $requestParser;

    /**
     * Sets the current controller context
     *
     * @param ControllerContext $context The current controller context
     * @internal
     */
    public function setControllerContext(ControllerContext $context): void
    {
        $this->context = $context;
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
