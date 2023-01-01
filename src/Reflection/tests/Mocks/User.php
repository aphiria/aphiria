<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection\Tests\Mocks;

/**
 * Mocks a user object
 */
readonly class User
{
    /**
     * @param int $id The user's ID
     * @param string $email The user's email
     */
    public function __construct(public int $id, public string $email)
    {
    }
}
