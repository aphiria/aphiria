<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Ids;

/**
 * Defines the interface for session ID generators to implement
 */
interface IIdGenerator
{
    /** The minimum length Id that is cryptographically secure */
    public const MIN_LENGTH = 16;
    /** The maximum length Id that PHP allows */
    public const MAX_LENGTH = 128;

    /**
     * Generates an Id
     *
     * @return int|string The Id
     */
    public function generate(): int|string;

    /**
     * Gets whether or not an Id is valid
     *
     * @param mixed $id The Id to validate
     * @return bool True if the Id is valid, otherwise false
     */
    public function idIsValid(mixed $id): bool;
}
