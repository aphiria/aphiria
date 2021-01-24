<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting\Mocks;

/**
 * Mocks a user object for use in tests
 */
class User
{
    /** @var int The user's ID */
    private int $id;
    /** @var string The user's email address */
    private string $email;

    /**
     * @param int $id The user's ID
     * @param string $email The user's email address
     */
    public function __construct(int $id, string $email)
    {
        $this->id = $id;
        $this->email = $email;
    }

    /**
     * Gets the user's email address
     *
     * @return string The user's email address
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Gets the user's ID
     *
     * @return int The user's ID
     */
    public function getId(): int
    {
        return $this->id;
    }
}
