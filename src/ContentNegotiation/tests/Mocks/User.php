<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests\Mocks;

/**
 * Defines a simple use model for use in tests
 */
class User
{
    /** @var int The user's ID */
    private int $id;
    /** @var string The user's email */
    private string $email;

    public function __construct(int $id, string $email)
    {
        $this->id = $id;
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
