<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Exceptions;

use Exception;
use Opulence\Net\Http\IHttpResponseMessage;
use Throwable;

/**
 * Defines the interface for exception renderers to implement
 */
interface IExceptionResponseFactory
{
    /**
     * Renders an exception
     *
     * @param Throwable|Exception $ex The thrown exception
     * @return IHttpResponseMessage The rendered response
     */
    public function createResponseFromException($ex): IHttpResponseMessage;
}
