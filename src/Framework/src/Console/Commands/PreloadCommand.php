<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentType;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionType;

/**
 * Defines the command that generates a PHP preload script from OPcache
 */
final class PreloadCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'app:preload',
            [
                new Argument(
                    'urls',
                    [ArgumentType::Optional, ArgumentType::IsArray],
                    'URLs to warm up before generating the preload script',
                ),
            ],
            [
                new Option(
                    'output',
                    OptionType::RequiredValue,
                    'o',
                    'Output path for the preload script',
                    'preload.php',
                ),
                new Option(
                    'exclude',
                    [OptionType::IsArray, OptionType::OptionalValue],
                    'e',
                    'Patterns to exclude from the preload script',
                ),
                new Option(
                    'min-hits',
                    OptionType::RequiredValue,
                    null,
                    'Minimum OPcache hits required to include a file',
                    '1',
                ),
                new Option(
                    'dry-run',
                    OptionType::NoValue,
                    null,
                    'Show files that would be included without generating the script',
                ),
            ],
            'Generates a PHP preload script from OPcache',
        );
    }
}
