<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Attributes;

use Attribute;

/**
 * Defines the attribute for command arguments
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Argument
{
    /**
     * @param string $name The name of the argument
     * @param int $type The type of argument this is
     * @param string|null $description A brief description of the argument, or null if there is none
     * @param mixed|null $defaultValue The default value for the argument if it's optional
     */
    public function __construct(
        public readonly string $name,
        public readonly int $type,
        public readonly ?string $description = null,
        public readonly mixed $defaultValue = null
    ) {
    }
}
