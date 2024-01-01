<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting\Mocks;

/**
 * Mocks a user object for use in tests
 */
readonly class User
{
    /**
     * @param int $id The user's ID
     * @param string $email The user's email address
     */
    public function __construct(public int $id, public string $email)
    {
    }
}
