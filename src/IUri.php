<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net;

/**
 * Defines the interface for URIs to implement
 */
interface IUri
{
    /**
     * Creates a URI from a string
     *
     * @param string $uri The string URI to convert
     * @return IUri The created URI
     */
    public static function createFromString(string $uri) : IUri;

    /**
     * Converts the URI to a string
     *
     * @return string The URI as a string
     */
    public function __toString() : string;

    /**
     * Gets the fragment
     *
     * @return string The fragment
     */
    public function getFragment() : string;

    /**
     * Gets the host
     *
     * @return string The host
     */
    public function getHost() : string;

    /**
     * Gets the path
     *
     * @return string The path
     */
    public function getPath() : string;

    /**
     * Gets the port
     *
     * @return int The port
     */
    public function getPort() : int;

    /**
     * Gets the query string
     *
     * @return string The query string
     */
    public function getQueryString() : string;

    /**
     * Gets the scheme
     *
     * @return string The scheme
     */
    public function getScheme() : string;

    /**
     * Gets the user info
     *
     * @return string The user info
     */
    public function getUserInfo() : string;

    /**
     * Gets whether or not the URI is absolute
     *
     * @return bool True if the URI is absolute, otherwise false
     */
    public function isAbsoluteUri() : bool;

    /**
     * Gets whether or not the URI matches a path
     *
     * @param string $path The path to match
     * @param bool $isRegex Whether or not the path is a regex
     * @return bool True if the URI matches the path, otherwise false
     */
    public function matchesPath(string $path, bool $isRegex = false) : bool;

    /**
     * Gets whether or not the URI matches a string URI
     *
     * @param string $uri The string URI to match
     * @param bool $isRegex Whether or not the string URI is a regex
     * @return bool True if the URI matches the string URI, otherwise false
     */
    public function matchesUri(string $uri, bool $isRegex = false) : bool;
}
