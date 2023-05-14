<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication;

use Aphiria\Security\IPrincipal;
use Exception;
use InvalidArgumentException;

/**
 * Defines the result of authentication
 *
 * @psalm-consistent-constructor
 */
readonly class AuthenticationResult
{
    /**
     * @param bool $passed Whether or not authentication passed
     * @param string $schemeName The name of the authentication scheme used
     * @param IPrincipal|null $user The authenticated user if one was found, otherwise null
     * @param Exception|null $failure The failure that occurred, or null if none did
     * @throws InvalidArgumentException Thrown if the result is in an invalid state
     */
    protected function __construct(
        public bool $passed,
        public string $schemeName,
        public ?IPrincipal $user = null,
        public ?Exception $failure = null
    ) {
        if (!$this->passed && $this->failure === null) {
            throw new InvalidArgumentException('Failed authentication results must specify a failure reason');
        }

        if ($this->passed && $this->user === null) {
            throw new InvalidArgumentException('Passing authentication results must specify a user');
        }
    }

    /**
     * Creates a failed authentication result
     *
     * @param Exception|string $failure The exception that occurred or a failure message
     * @param string $schemeName The name of the authentication scheme that failed
     * @return static A failed authentication result
     */
    public static function fail(Exception|string $failure, string $schemeName): static
    {
        return new static(false, $schemeName, failure: \is_string($failure) ? new Exception($failure) : $failure);
    }

    /**
     * Creates a passing authentication result
     *
     * @param IPrincipal $user The authenticated user
     * @param string $schemeName The name of the authentication scheme that failed
     * @return static A passing authentication result
     */
    public static function pass(IPrincipal $user, string $schemeName): static
    {
        return new static(true, $schemeName, $user);
    }
}
