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
 * Defines a console command option
 */
final class Option
{
    /**
     * @param string $name The name of the option
     * @param int $type The type of option this is
     * @param string|null $shortName The short name of the option if it has one, otherwise null
     * @param string|null $description A brief description of the option
     * @param mixed $defaultValue The default value for the option if it's optional
     * @throws InvalidArgumentException Thrown if the type is invalid
     */
    public function __construct(
        public readonly string $name,
        public readonly int $type,
        public readonly ?string $shortName = null,
        public readonly ?string $description = null,
        public readonly mixed $defaultValue = null
    ) {
        if (empty($this->name)) {
            throw new InvalidArgumentException('Option name cannot be empty');
        }

        if (($this->type & 3) === 3) {
            throw new InvalidArgumentException('Option type cannot be both optional and required');
        }

        if (($this->type & 5) === 5 || ($this->type & 6) === 6) {
            throw new InvalidArgumentException('Option cannot have a value and not have a value');
        }

        if ($this->shortName !== null) {
            if (\mb_strlen($this->shortName) !== 1) {
                throw new InvalidArgumentException('Short names must be one character in length');
            }

            if (!\ctype_alpha($this->shortName)) {
                throw new InvalidArgumentException('Short names must be an alphabet character');
            }
        }
    }

    /**
     * Gets whether or not the option value is an array
     *
     * @return bool True if the option value is an array, otherwise false
     */
    public function valueIsArray(): bool
    {
        return ($this->type & OptionType::IS_ARRAY) === OptionType::IS_ARRAY;
    }

    /**
     * Gets whether or not the option value is optional
     *
     * @return bool True if the option value is optional, otherwise false
     */
    public function valueIsOptional(): bool
    {
        return ($this->type & OptionType::OPTIONAL_VALUE) === OptionType::OPTIONAL_VALUE;
    }

    /**
     * Gets whether or not the option value is allowed
     *
     * @return bool True if the option value is allowed, otherwise false
     */
    public function valueIsPermitted(): bool
    {
        return ($this->type & OptionType::NO_VALUE) !== OptionType::NO_VALUE;
    }

    /**
     * Gets whether or not the option value is required
     *
     * @return bool True if the option value is required, otherwise false
     */
    public function valueIsRequired(): bool
    {
        return ($this->type & OptionType::REQUIRED_VALUE) === OptionType::REQUIRED_VALUE;
    }
}
