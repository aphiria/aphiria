<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Ids;

/**
 * Defines a UUID V4 ID generator
 */
final class UuidV4IdGenerator implements IIdGenerator
{
    /**
     * @inheritdoc
     */
    public function generate(): int|string
    {
        $string = \random_bytes(16);
        $string[6] = \chr(\ord($string[6]) & 0x0f | 0x40);
        $string[8] = \chr(\ord($string[8]) & 0x3f | 0x80);

        return \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex($string), 4));
    }

    /**
     * Gets whether or not an Id is valid
     *
     * @param mixed $id The Id to validate
     * @return bool True if the Id is valid, otherwise false
     */
    public function idIsValid(mixed $id): bool
    {
        if (!\is_string($id)) {
            return false;
        }

        return \preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $id) === 1;
    }
}
