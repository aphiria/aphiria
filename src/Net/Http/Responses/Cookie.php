<?php
namespace Opulence\Net\Http\Responses\Formatters;

/**
 * Defines an HTTP cookie
 */
class Cookie
{
    /** @var string The name of the cookie */
    private $name = '';
    /** @var mixed The value of the cookie */
    private $value = '';
    /** @var int The expiration timestamp of the cookie */
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

    public function getDomain() : string
    {
        return $this->domain;
    }

    public function getExpiration() : int
    {
        return $this->expiration;
    }

    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isHttpOnly() : bool
    {
        return $this->isHttpOnly;
    }

    public function isSecure() : bool
    {
        return $this->isSecure;
    }

    public function setDomain(string $domain) : void
    {
        $this->domain = $domain;
    }

    public function setExpiration($expiration) : void
    {
        if ($expiration instanceof DateTime) {
            $expiration = $expiration->format('U');
        }
        
        $this->expiration = $expiration;
    }

    public function setHttpOnly(bool $isHttpOnly) : void
    {
        $this->isHttpOnly = $isHttpOnly;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function setPath(string $path) : void
    {
        $this->path = $path;
    }

    public function setSecure(bool $isSecure) : void
    {
        $this->isSecure = $isSecure;
    }

    public function setValue($value) : void
    {
        $this->value = $value;
    }
}
