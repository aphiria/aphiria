<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Attributes;

use Aphiria\Console\Input\OptionType;
use Attribute;

/**
 * Defines the attribute for command options
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Option
{
    /** @var list<OptionType> The type of option this is */
    public array $type;

    /**
     * @param string $name The name of the option
     * @param list<OptionType>|OptionType $type The type of option this is
     * @param string|null $shortName The short name of the option if it has one, otherwise null
     * @param string|null $description A brief description of the option, or null if there is none
     * @param mixed|null $defaultValue The default value for the option if it's optional
     */
    public function __construct(
        public string $name,
        array|OptionType $type,
        public ?string $shortName = null,
        public ?string $description = null,
        public mixed $defaultValue = null
    ) {
        $this->type = \is_array($type) ? $type : [$type];
    }
}
