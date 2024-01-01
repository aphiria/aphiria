<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Headers;

use InvalidArgumentException;

/**
 * Defines an HTTP cookie
 */
final class Cookie
{
    /**
     * @param string $name The name of the cookie
     * @param mixed $value The value of the cookie
     * @param int|null $maxAge The expiration (in seconds from now) the cookie is valid for, or null if it's a session cookie
     * @param string|null $path The path the cookie applies to
     * @param string|null $domain The domain the cookie applies to
     * @param bool $isSecure Whether or not this cookie is HTTPS-only
     * @param bool $isHttpOnly Whether or not this cookie can be read client-side
     * @param SameSiteMode|null $sameSite The same-site setting to use (defaults to lax), or null if none is specified
     * @throws InvalidArgumentException Thrown if the name or expiration is in the incorrect format
     */
    public function __construct(
        private string $name,
        public mixed $value,
        public ?int $maxAge = null,
        public ?string $path = null,
        public ?string $domain = null,
        public bool $isSecure = false,
        public bool $isHttpOnly = true,
        public ?SameSiteMode $sameSite = SameSiteMode::Lax
    ) {
        $this->setName($name);
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
     * Sets the name of the cookie
     *
     * @param string $name The name of the cookie
     * @throws InvalidArgumentException Thrown if the name contains invalid characters
     */
    public function setName(string $name): void
    {
        if (\preg_match('/[\x00-\x20\x22\x28-\x29\x2c\x2f\x3a-\x40\x5b-\x5d\x7b\x7d\x7f]/', $name) === 1) {
            throw new InvalidArgumentException("Cookie name \"$name\" contains invalid characters");
        }

        $this->name = $name;
    }
}
