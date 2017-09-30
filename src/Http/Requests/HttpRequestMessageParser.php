<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

/**
 * Defines the HTTP request message parser
 */
class HttpRequestMessageParser
{
    /**
     * Parses a request for form data in the body
     *
     * @param IHttpRequestMessage $message The message to parse
     * @return array The mapping of input names to values
     */
    public function getFormData(IHttpRequestMessage $message) : array
    {
        // Todo
    }

    /**
     * Parses a request for an input value
     *
     * @param IHttpRequestMessage $message The message to parse
     * @param string $name The name of the input whose value we want
     * @param mixed|null $default The default value, if none was found
     * @return mixed The value of the input
     */
    public function getInput(IHttpRequestMessage $message, string $name, $default = null)
    {
        // Todo
    }
}
