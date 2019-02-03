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
 * Defines the camel-case property name formatter
 */
class CamelCasePropertyNameFormatter implements IPropertyNameFormatter
{
    /**
     * @inheritdoc
     */
    public function formatPropertyName(string $propertyName): string
    {
        $upperCasedWords = ucwords(str_replace(['-', '_'], ' ', $propertyName));

        return lcfirst(str_replace(' ', '', $upperCasedWords));
    }
}
