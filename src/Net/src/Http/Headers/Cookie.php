<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Headers;

use InvalidArgumentException;

/**
 * Defines an HTTP cookie
 */
final class Cookie
{
    /** @const The lax same-site value */
    public const SAME_SITE_LAX = 'lax';
    /** @const The strict same-site value */
    public const SAME_SITE_STRICT = 'strict';
    /** @const The none same-site value (different than a null value, which doesn't set the SameSite parameter at all) */
    public const SAME_SITE_NONE = 'none';
    /** @var string The name of the cookie */
    private string $name = '';
    /** @var mixed The value of the cookie */
    private $value;
    /** @var int|null The max age of the cookie if set, otherwise null */
    private ?int $maxAge = null;
    /** @var string|null The path the cookie is valid on if set, otherwise null */
    private ?string $path;
    /** @var string|null The domain the cookie is valid on if set, otherwise null */
    private ?string $domain;
    /** @var bool Whether or not this cookie is on HTTPS */
    private bool $isSecure;
    /** @var bool Whether or not this cookie is HTTP only */
    private bool $isHttpOnly;
    /** @var string|null The same-site setting to use, or null if none is specified */
    private ?string $sameSite;

    /**
     * @param string $name The name of the cookie
     * @param mixed $value The value of the cookie
     * @param int|null $maxAge The expiration (in seconds from now) the cookie is valid for, or null if it's a session cookie
     * @param string|null $path The path the cookie applies to
     * @param string|null $domain The domain the cookie applies to
     * @param bool $isSecure Whether or not this cookie is HTTPS-only
     * @param bool $isHttpOnly Whether or not this cookie can be read client-side
     * @param string|null $sameSite The same-site setting to use (defaults to lax), or null if none is specified
     * @throws InvalidArgumentException Thrown if the name or expiration is in the incorrect format
     */
    public function __construct(
        string $name,
        $value,
        int $maxAge = null,
        ?string $path = null,
        ?string $domain = null,
        bool $isSecure = false,
        bool $isHttpOnly = true,
        ?string $sameSite = self::SAME_SITE_LAX
    ) {
        $this->setName($name);
        $this->value = $value;
        $this->maxAge = $maxAge;
        $this->path = $path;
        $this->domain = $domain;
        $this->isSecure = $isSecure;
        $this->isHttpOnly = $isHttpOnly;

        if (
            $sameSite !== null
            && !\in_array($sameSite, [self::SAME_SITE_LAX, self::SAME_SITE_STRICT, self::SAME_SITE_STRICT], true)
        ) {
            throw new InvalidArgumentException('Acceptable values for SameSite are "lax", "strict", "none", or null');
        }

        $this->sameSite = $sameSite;
    }

    /**
     * Gets the domain of the cookie
     *
     * @return string|null The domain if set, otherwise null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Gets the max age of the cookie
     *
     * @return int|null The max age of the cookie if set, otherwise null
     */
    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    /**
     * Gets the name of the cookie
     *
     * @return string The name of the cookie
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the path of the cookie
     *
     * @return string|null The path if set, otherwise null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Gets the same-site value
     *
     * @return string|null The same-site value, or null if not set
     */
    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }

    /**
     * Gets the value of the cookie
     *
     * @return mixed The value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Gets whether or not the cookie is HTTP-only
     *
     * @return bool True if the cookie is HTTP-only, otherwise false
     */
    public function isHttpOnly(): bool
    {
        return $this->isHttpOnly;
    }

    /**
     * Gets whether or not the cookie is secure
     *
     * @return bool True if the cookie is secure, otherwise false
     */
    public function isSecure(): bool
    {
        return $this->isSecure;
    }

    /**
     * Sets the domain of the cookie
     *
     * @param string $domain The domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * Sets whether or not the cookie is HTTP-only
     *
     * @param bool $isHttpOnly True if the cookie is HTTP-only, otherwise false
     */
    public function setHttpOnly(bool $isHttpOnly): void
    {
        $this->isHttpOnly = $isHttpOnly;
    }

    /**
     * Sets the max age of the cookie
     *
     * @param int $maxAge The max age of the cookie
     */
    public function setMaxAge(int $maxAge): void
    {
        $this->maxAge = $maxAge;
    }

    /**
     * Sets the name of the cookie
     *
     * @param string $name The name of the cookie
     * @throws InvalidArgumentException Thrown if the name contains invalid characters
     */
    public function setName(string $name): void
    {
        if (preg_match('/[\x00-\x20\x22\x28-\x29\x2c\x2f\x3a-\x40\x5b-\x5d\x7b\x7d\x7f]/', $name) === 1) {
            throw new InvalidArgumentException("Cookie name \"$name\" contains invalid characters");
        }

        $this->name = $name;
    }

    /**
     * Sets the path of the cookie
     *
     * @param string $path The path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Sets whether or not the cookie is HTTPS
     *
     * @param bool $isSecure True if the cookie is HTTPS, otherwise false
     */
    public function setSecure(bool $isSecure): void
    {
        $this->isSecure = $isSecure;
    }

    /**
     * Sets the value of the cookie
     *
     * @param mixed $value The value of the cookie
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}
