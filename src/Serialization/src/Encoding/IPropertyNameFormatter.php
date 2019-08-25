<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Encoding;

/**
 * Defines the interface for property name formatters to implement
 */
interface IPropertyNameFormatter
{
    /**
     * Formats a property name
     *
     * @param string $propertyName The property name to format
     * @return string The formatted property name
     */
    public function formatPropertyName(string $propertyName): string;
}
