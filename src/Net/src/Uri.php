<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net;

use InvalidArgumentException;

/**
 * Defines a URI
 */
readonly class Uri
{
    /** @var string|null The URI scheme if set, otherwise null */
    public ?string $scheme;
    /** @var string|null The URI user if set, otherwise null */
    public ?string $user;
    /** @var string|null The URI password if set, otherwise null */
    public ?string $password;
    /** @var string|null The URI host if set, otherwise null */
    public ?string $host;
    /** @var int|null The URI port if set, otherwise null */
    public ?int $port;
    /** @var string|null The URI path if set, otherwise null */
    public ?string $path;
    /** @var string|null The URI query string (excludes '?') if set, otherwise null */
    public ?string $queryString;
    /** @var string|null The URI fragment (excludes '#') if set, otherwise null */
    public ?string $fragment;

    /**
     * @param string $uri The raw URI
     * @throws InvalidArgumentException Thrown if the URI is malformed
     */
    public function __construct(string $uri)
    {
        if (($parsedUri = \parse_url($uri)) === false) {
            throw new InvalidArgumentException("URI $uri is malformed");
        }

        $this->scheme = self::filterScheme($parsedUri['scheme'] ?? null);
        $this->user = $parsedUri['user'] ?? null;
        $this->password = $parsedUri['pass'] ?? null;
        $this->host = self::filterHost($parsedUri['host'] ?? null);
        $this->port = $parsedUri['port'] ?? null;
        $this->path = self::filterPath($parsedUri['path'] ?? null);
        $this->queryString = self::filterQueryString($parsedUri['query'] ?? null);
        $this->fragment = self::filterFragment($parsedUri['fragment'] ?? null);
        $this->validateProperties();
    }

    /**
     * Converts the URI to a string
     *
     * @return string The URI as a string
     */
    public function __toString(): string
    {
        $stringUri = '';

        if ($this->scheme !== null) {
            $stringUri .= "{$this->scheme}:";
        }

        if (($authority = $this->getAuthority()) !== null) {
            $stringUri .= "//$authority";
        }

        if ($this->path !== null) {
            $stringUri .= $this->path;
        }

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
     * @param bool $includeUserInfo Whether or not to include the user info
     * @return string|null The URI authority if set, otherwise null
     */
    public function getAuthority(bool $includeUserInfo = true): ?string
    {
        $authority = '';

        if ($includeUserInfo && $this->user !== null) {
            // The password can be empty
            $authority = $this->user;

            if ($this->password !== null && $this->password !== '') {
                $authority .= ":{$this->password}";
            }

            $authority .= '@';
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
     * Filters the URI fragment and percent-encodes reserved characters
     *
     * @param string|null $fragment The fragment to filter if one is set, otherwise null
     * @return string|null The filtered fragment, or null if the fragment was already null
     */
    private static function filterFragment(?string $fragment): ?string
    {
        return self::filterQueryString($fragment);
    }

    /**
     * Filters the URI host
     *
     * @param string|null $host The host to filter if one is set, otherwise null
     * @return string|null The filtered host, or null if the host was already null
     */
    private static function filterHost(?string $host): ?string
    {
        if ($host === null) {
            return null;
        }

        /** @link https://tools.ietf.org/html/rfc3986#section-3.2.2 */
        return \strtolower($host);
    }

    /**
     * Filters the URI path and percent-encodes reserved characters
     *
     * @param string|null $path The path to filter if one is set, otherwise null
     * @return string|null The filtered path, or null if the path was already null
     */
    private static function filterPath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        /** @link https://tools.ietf.org/html/rfc3986#section-3.3 */
        return \preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            static function (array $match): string {
                return \rawurlencode((string)$match[0]);
            },
            $path
        );
    }

    /**
     * Filters the URI query string and percent-encodes reserved characters
     *
     * @param string|null $queryString The query string to filter if one is set, otherwise null
     * @return string|null The filtered query string, or null if the query string was already null
     */
    private static function filterQueryString(?string $queryString): ?string
    {
        if ($queryString === null) {
            return null;
        }

        /** @link https://tools.ietf.org/html/rfc3986#section-3.4 */
        return \preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            static function (array $match): string {
                return \rawurlencode((string)$match[0]);
            },
            $queryString
        );
    }

    /**
     * Filters the URI scheme
     *
     * @param string|null $scheme The scheme to filter if one is set, otherwise null
     * @return string|null The filtered scheme, or null if the scheme was already null
     */
    private static function filterScheme(?string $scheme): ?string
    {
        if ($scheme === null) {
            return null;
        }

        /** @link https://tools.ietf.org/html/rfc3986#section-3.1 */
        return \strtolower($scheme);
    }

    /**
     * Gets whether or not a standard port is being used for the scheme
     *
     * @return bool True if using a standard port, otherwise false
     */
    private function isUsingStandardPort(): bool
    {
        return $this->port === null ||
            (($this->scheme === 'http' && $this->port === 80) || ($this->scheme === 'https' && $this->port === 443));
    }

    /**
     * Validates some properties that parse_url() does not
     *
     * @throws InvalidArgumentException Thrown if any of the properties are invalid
     */
    private function validateProperties(): void
    {
        match ($this->scheme) {
            null, '', 'about', 'data', 'file', 'ftp', 'git', 'http', 'https', 'sftp', 'ssh', 'svn' => true,
            default => throw new InvalidArgumentException("Scheme \"{$this->scheme}\" is invalid")
        };
    }
}
