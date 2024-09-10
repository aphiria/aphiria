<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
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
    /** @var list<OptionType> The type of option this is */
    public readonly array $type;
    /** @var bool Whether or not the value is an array */
    public bool $valueIsArray {
        get => ($this->typeFlag & OptionType::IsArray->value) === OptionType::IsArray->value;
    }
    /** @var bool Whether or not the value is optional */
    public bool $valueIsOptional {
        get => ($this->typeFlag & OptionType::OptionalValue->value) === OptionType::OptionalValue->value;
    }
    /** @var bool Whether or not a value is permitted */
    public bool $valueIsPermitted {
        get => ($this->typeFlag & OptionType::NoValue->value) !== OptionType::NoValue->value;
    }
    /** @var bool Whether or not a value is required */
    public bool $valueIsRequired {
        get => ($this->typeFlag & OptionType::RequiredValue->value) === OptionType::RequiredValue->value;
    }
    /** @var int The bitwise-OR'd flag representing all the types */
    private int $typeFlag = 0;

    /**
     * @param string $name The name of the option
     * @param list<OptionType>|OptionType $type The type of option this is
     * @param string|null $shortName The short name of the option if it has one, otherwise null
     * @param string|null $description A brief description of the option
     * @param mixed $defaultValue The default value for the option if it's optional
     * @throws InvalidArgumentException Thrown if the type is invalid
     */
    public function __construct(
        public readonly string $name,
        array|OptionType $type,
        public readonly ?string $shortName = null,
        public readonly ?string $description = null,
        public readonly mixed $defaultValue = null
    ) {
        if (empty($this->name)) {
            throw new InvalidArgumentException('Option name cannot be empty');
        }

        $this->type = \is_array($type) ? $type : [$type];

        foreach ($this->type as $type) {
            $this->typeFlag |= $type->value;
        }

        if (($this->typeFlag & 3) === 3) {
            throw new InvalidArgumentException('Option type cannot be both optional and required');
        }

        if (($this->typeFlag & 5) === 5 || ($this->typeFlag & 6) === 6) {
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
}
