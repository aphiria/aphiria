<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding\Mocks;

/**
 * Mocks a user object
 */
class User
{
    /** @var int The user's ID */
    private $id;
    /** @var string The user's email */
    private $email;

    /**
     * @param int $id The user's ID
     * @param string $email The user's email
     */
    public function __construct(int $id, string $email)
    {
        $this->id = $id;
        $this->email = $email;
    }

    /**
     * Gets the user's email
     *
     * @return string The user's email
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
