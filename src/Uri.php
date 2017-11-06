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
    /** @var string|null The URI scheme if set, otherwise null */
    private $scheme = null;
    /** @var string|null The URI user if set, otherwise null */
    private $user = null;
    /** @var string|null The URI password if set, otherwise null */
    private $password = null;
    /** @var string|null The URI host if set, otherwise null */
    private $host = null;
    /** @var int|null The URI port if set, otherwise null */
    private $port = null;
    /** @var string The URI path */
    private $path = '';
    /** @var string|null The URI query string (excludes '?') if set, otherwise null */
    private $queryString = '';
    /** @var string|null The URI fragment (excludes '#') if set, otherwise null */
    private $fragment = '';

    /**
     * @param string|null $scheme The URI scheme
     * @param string|null $user The URI user if set, otherwise null
     * @param string|null $password The URI password if set, otherwise null
     * @param string|null $host The URI host if set, otherwise null
     * @param int|null $port The URI host if set, otherwise null
     * @param string $path The URI path
     * @param string|null $queryString The URI query string (excludes '?') if set, otherwise null
     * @param string|null $fragment The URI fragment (excludes '#') if set, otherwise null
     * @throws InvalidArgumentException Thrown if the port is out of range
     */
    public function __construct(
        ?string $scheme,
        ?string $user,
        ?string $password,
        ?string $host,
        ?int $port,
        string $path,
        ?string $queryString,
        ?string $fragment
    ) {
        $this->scheme = $scheme;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;

        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new InvalidArgumentException("Port $port must be between 1 and 65535, inclusive");
        }

        $this->port = $port;
        $this->path = $path;
        $this->queryString = $queryString;
        $this->fragment = $fragment;
    }

    /**
     * Converts the URI to a string
     *
     * @return string The URI as a string
     */
    public function __toString() : string
    {
        $stringUri = '';

        if ($this->scheme !== null) {
            $stringUri .= "{$this->scheme}:";
        }

        if (($authority = $this->getAuthority()) !== null) {
            $stringUri .= "//$authority";
        }

        $stringUri .= $this->path;

        if ($this->queryString !== null) {
            $stringUri .= "?{$this->queryString}";
        }

        if ($this->fragment !== null) {
            $stringUri .= "#{$this->fragment}";
        }

        return $stringUri;
    }

    /**
     * Gets the authority portion of the URI, eg user:password@host:port
     * Note: The port is only included if it is non-standard for the scheme
     *
     * @return string|null The URI authority if set, otherwise null
     */
    public function getAuthority() : ?string
    {
        $authority = '';

        if ($this->user !== null && $this->password !== null) {
            $authority .= "{$this->user}:{$this->password}@";
        }

        if ($this->host !== null) {
            $authority .= $this->host;
        }

        if (!$this->isUsingStandardPort()) {
            $authority .= ":{$this->port}";
        }

        return $authority === '' ? null : $authority;
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
     * @return string|null The host if set, otherwise null
     */
    public function getHost() : ?string
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
     * @return string|null The scheme if set, otherwise null
     */
    public function getScheme() : ?string
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
     * Gets whether or not a standard port is being used for the scheme
     *
     * @return bool True if using a standard port, otherwise false
     */
    private function isUsingStandardPort() : bool
    {
        return $this->port === null ||
            (($this->scheme === 'http' && $this->port === 80) || ($this->scheme === 'https' && $this->port === 443));
    }
}
