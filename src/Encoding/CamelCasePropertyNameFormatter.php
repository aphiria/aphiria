<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Encoding;

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
