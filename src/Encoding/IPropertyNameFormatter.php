<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

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
