<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

/**
 * Defines the interface for all HTTP headers to implement
 */
interface IHttpHeaders
{
    /**
     * Gets a header value
     *
     * @param string $name The name of the header whose value we want
     * @param mixed|null $default The default value, if none was found
     * @param bool $onlyReturnFirst Whether or not to return only the first value
     * @return mixed The value of the header
     */
    public function get(string $name, $default = null, bool $onlyReturnFirst = true);
    
    /**
     * Gets all the header names to values
     * 
     * @return array The header names to values
     */
    public function getAll() : array;

    /**
     * Gets whether or not a header has a value
     *
     * @param string $name The name of the header to search for
     * @return bool True if the header has a value, otherwise false
     */
    public function has(string $name) : bool;

    /**
     * Removes a header
     *
     * @param string $name The name of the header to remove
     */
    public function remove(string $name) : void;

    /**
     * Sets a header value
     *
     * @param string $name The name of the header to set
     * @param mixed $values The value of the header
     * @param bool $shouldReplace True if we should overwrite previously-set values, otherwise false
     */
    public function set(string $name, $values, bool $shouldReplace = true) : void;
}
