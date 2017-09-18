<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net;

use InvalidArgumentException;

/**
 * Defines a URI
 */
class Uri
{
    /** @var string The URI scheme */
    private $scheme = '';
    /** @var string|null The URI user if set, otherwise null */
    private $user = null;
    /** @var string|null The URI password if set, otherwise null */
    private $password = null;
    /** @var string The URI host */
    private $host = '';
    /** @var int|nulll The URI port if set, otherwise null */
    private $port = null;
    /** @var string The URI path */
    private $path = '';
    /** @var string|null The URI query string (excludes '?') if set, otherwise null */
    private $queryString = '';
    /** @var string|null The URI fragment (excludes '#') if set, otherwise null */
    private $fragment = '';

    /**
     * @param string $scheme The URI scheme
     * @param string|null $user The URI user if set, otherwise null
     * @param string|null $password The URI password if set, otherwise null
     * @param string $host The URI host
     * @param int|null $port The URI host if set, otherwise null
     * @param string $path The URI path
     * @param string|null $queryString The URI query string (excludes '?') if set, otherwise null
     * @param string|null $fragment The URI fragment (excludes '#') if set, otherwise null
     */
    public function __construct(
        string $scheme,
        ?string $user,
        ?string $password,
        string $host,
        ?int $port,
        string $path,
        ?string $queryString,
        ?string $fragment
    ) {
        $this->scheme = $scheme;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->queryString = $queryString;
        $this->fragment = $fragment;
    }

    /**
     * Creates a URI from a string
     *
     * @param string $uri The string URI to convert
     * @return Uri The created URI
     */
    public static function createFromString(string $uri) : Uri
    {
        if (($parsedUri = parse_url($uri)) === false) {
            throw new InvalidArgumentException("Uri $uri is malformed");
        }

        return new Uri(
            $parsedUri['scheme'] ?? 'http',
            $parsedUri['user'] ?? null,
            $parsedUri['pass'] ?? null,
            $parsedUri['host'] ?? '',
            $parsedUri['port'] ?? null,
            $parsedUri['path'] ?? null,
            $parsedUri['query'] ?? null,
            $parsedUri['fragment'] ?? null
        );
    }

    /**
     * Converts the URI to a string
     *
     * @return string The URI as a string
     */
    public function __toString() : string
    {
        $stringUri = "{$this->scheme}://";

        if ($this->user !== null && $this->password !== null) {
            $stringUri .= "{$this->user}:{$this->password}@";
        }

        $stringUri .= "{$this->host}";

        // Only include the port if not using standard ports for the scheme
        if (
            $this->port !== null &&
            (($this->scheme === 'http' && $this->port !== 80) || ($this->scheme === 'https' && $this->port !== 443))
        ) {
            $stringUri .= ":{$this->port}";
        }

        $stringUri .= '/' . ltrim($this->path, '/');

        if ($this->queryString !== null) {
            $stringUri .= "?{$this->queryString}";
        }

        if ($this->fragment !== null) {
            $stringUri .= "#{$this->fragment}";
        }

        return $stringUri;
    }

    /**
     * Gets the fragment
     *
     * @return string|null The fragment if set, otherwise null
     */
    public function getFragment() : ?string
    {
        return $this->fragment;
    }

    /**
     * Gets the host
     *
     * @return string The host
     */
    public function getHost() : string
    {
        return $this->host;
    }

    /**
     * Gets the password
     *
     * @return string|null The password if set, otherwise null
     */
    public function getPassword() : ?string
    {
        return $this->password;
    }

    /**
     * Gets the path
     *
     * @return string The path
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Gets the port
     *
     * @return int|null The port if set, otherwise null
     */
    public function getPort() : ?int
    {
        return $this->port;
    }

    /**
     * Gets the query string
     *
     * @return string|null The query string if set, otherwise null
     */
    public function getQueryString() : ?string
    {
        return $this->queryString;
    }

    /**
     * Gets the scheme
     *
     * @return string The scheme
     */
    public function getScheme() : string
    {
        return $this->scheme;
    }

    /**
     * Gets the user
     *
     * @return string|null The user if set, otherwise null
     */
    public function getUser() : ?string
    {
        return $this->user;
    }

    /**
     * Gets whether or not the URI matches a path
     *
     * @param string $path The path to match
     * @param bool $isRegex Whether or not the path is a regex
     * @return bool True if the URI matches the path, otherwise false
     */
    public function matchesPath(string $path, bool $isRegex = false) : bool
    {
        // Does this belong in this class?
    }

    /**
     * Gets whether or not the URI matches a string URI
     *
     * @param string $uri The string URI to match
     * @param bool $isRegex Whether or not the string URI is a regex
     * @return bool True if the URI matches the string URI, otherwise false
     */
    public function matchesUri(string $uri, bool $isRegex = false) : bool
    {
        // Does this belong in this class?
    }
}
