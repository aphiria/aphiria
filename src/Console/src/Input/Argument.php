<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input;

use InvalidArgumentException;

/**
 * Defines a console command argument
 */
final class Argument
{
    /**
     * @param string $name The name of the argument
     * @param int $type The type of argument this is
     * @param string|null $description A brief description of the argument
     * @param mixed $defaultValue The default value for the argument if it's optional
     * @throws InvalidArgumentException Thrown if the type is invalid
     */
    public function __construct(
        public string $name,
        public int $type,
        public ?string $description = null,
        public mixed $defaultValue = null
    ) {
        if (empty($this->name)) {
            throw new InvalidArgumentException('Argument name cannot be empty');
        }

        if (($this->type & 3) === 3) {
            throw new InvalidArgumentException('Argument type cannot be both optional and required');
        }
    }

    /**
     * Gets whether or not the argument is an array
     *
     * @return bool True if the argument is an array, otherwise false
     */
    public function isArray(): bool
    {
        return ($this->type & ArgumentTypes::IS_ARRAY) === ArgumentTypes::IS_ARRAY;
    }

    /**
     * Gets whether or not the argument is optional
     *
     * @return bool True if the argument is optional, otherwise false
     */
    public function isOptional(): bool
    {
        return ($this->type & ArgumentTypes::OPTIONAL) === ArgumentTypes::OPTIONAL;
    }

    /**
     * Gets whether or not the argument is required
     *
     * @return bool True if the argument is required, otherwise false
     */
    public function isRequired(): bool
    {
        return ($this->type & ArgumentTypes::REQUIRED) === ArgumentTypes::REQUIRED;
    }
}
