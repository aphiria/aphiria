<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

use DateTime;
use InvalidArgumentException;

/**
 * Defines an HTTP cookie
 */
class Cookie
{
    /** @var string The name of the cookie */
    private $name = '';
    /** @var mixed The value of the cookie */
    private $value = '';
    /** @var DateTime The expiration timestamp of the cookie */
    private $expiration = null;
    /** @var string The path the cookie is valid on */
    private $path = '/';
    /** @var string The domain the cookie is valid on */
    private $domain = '';
    /** @var bool Whether or not this cookie is on HTTPS */
    private $isSecure = false;
    /** @var bool Whether or not this cookie is HTTP only */
    private $isHttpOnly = true;

    /**
     * @param string $name The name of the cookie
     * @param mixed $value The value of the cookie
     * @param DateTime|int $expiration The expiration of the cookie
     * @param string $path The path the cookie applies to
     * @param string $domain The domain the cookie applies to
     * @param bool $isSecure Whether or not this cookie is HTTPS-only
     * @param bool $isHttpOnly Whether or not this cookie can be read client-side
     * @throws InvalidArgumentException Thrown if the expiration is neither an integer nor a DateTime
     */
    public function __construct(
        string $name,
        $value,
        $expiration,
        string $path = '/',
        string $domain = '',
        bool $isSecure = false,
        bool $isHttpOnly = true
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->setExpiration($expiration);
        $this->path = $path;
        $this->domain = $domain;
        $this->isSecure = $isSecure;
        $this->isHttpOnly = $isHttpOnly;
    }

    /**
     * Gets the domain of the cookie
     *
     * @return string The domain
     */
    public function getDomain() : string
    {
        return $this->domain;
    }

    /**
     * Gets the expiration of the cookie
     *
     * @return DateTime The expiration
     */
    public function getExpiration() : DateTime
    {
        return $this->expiration;
    }

    /**
     * Gets the name of the cookie
     *
     * @return string The name of the cookie
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Gets the path of the cookie
     *
     * @return string The path
     */
    public function getPath() : string
    {
        return $this->path;
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
    public function isHttpOnly() : bool
    {
        return $this->isHttpOnly;
    }

    /**
     * Gets whether or not the cookie is secure
     *
     * @return bool True if the cookie is secure, otherwise false
     */
    public function isSecure() : bool
    {
        return $this->isSecure;
    }

    /**
     * Sets the domain of the cookie
     *
     * @param string $domain The domain
     */
    public function setDomain(string $domain) : void
    {
        $this->domain = $domain;
    }

    /**
     * Sets the expiration of the cookie
     *
     * @param DateTime|int $expiration The expiration
     * @throws InvalidArgumentException Thrown if the expiration is not an integer or DateTime
     */
    public function setExpiration($expiration) : void
    {
        if (is_int($expiration)) {
            $expiration = DateTime::createFromFormat('U', $expiration);
        }

        if (!$expiration instanceof DateTime) {
            throw new InvalidArgumentException('Expiration must be integer or DateTime');
        }

        $this->expiration = $expiration;
    }

    /**
     * Sets whether or not the cookie is HTTP-only
     *
     * @param bool $isHttpOnly True if the cookie is HTTP-only, otherwise false
     */
    public function setHttpOnly(bool $isHttpOnly) : void
    {
        $this->isHttpOnly = $isHttpOnly;
    }

    /**
     * Sets the name of the cookie
     *
     * @param string $name The name of the cookie
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * Sets the path of the cookie
     *
     * @param string $path The path
     */
    public function setPath(string $path) : void
    {
        $this->path = $path;
    }

    /**
     * Sets whether or not the cookie is HTTPS
     *
     * @param bool $isSecure True if the cookie is HTTPS, otherwise false
     */
    public function setSecure(bool $isSecure) : void
    {
        $this->isSecure = $isSecure;
    }

    /**
     * Sets the value of the cookie
     *
     * @param mixed $value The value of the cookie
     */
    public function setValue($value) : void
    {
        $this->value = $value;
    }
}
