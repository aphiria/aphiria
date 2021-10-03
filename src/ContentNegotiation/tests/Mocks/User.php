<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests\Mocks;

/**
 * Defines a simple use model for use in tests
 */
class User
{
    /**
     * @param int $id The user's ID
     * @param string $email The user's email
     */
    public function __construct(public readonly int $id, public readonly string $email)
    {
    }
}
