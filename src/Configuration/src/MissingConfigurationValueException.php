<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration;

use Exception;
use Throwable;

/**
 * Defines the exception that's thrown when a configuration value is missing
 */
class MissingConfigurationValueException extends Exception
{
    /**
     * @inheritdoc
     * @param string $path The path to the missing configuration value
     */
    public function __construct(string $path, int $code = 0, Throwable $previous = null)
    {
        parent::__construct("No configuration value at $path", $code, $previous);
    }
}
