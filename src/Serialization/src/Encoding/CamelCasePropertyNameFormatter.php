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
 * Defines the camel-case property name formatter
 */
final class CamelCasePropertyNameFormatter implements IPropertyNameFormatter
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
