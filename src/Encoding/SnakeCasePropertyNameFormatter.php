<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Encoding;

/**
 * Defines the snake-case property name formatter
 */
final class SnakeCasePropertyNameFormatter implements IPropertyNameFormatter
{
    /** @var string The delimiter to use */
    private $delimiter;

    /**
     * @param string $delimiter The delimiter to use
     */
    public function __construct(string $delimiter = '_')
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @inheritdoc
     */
    public function formatPropertyName(string $propertyName): string
    {
        $snakeCaseValue = $propertyName;

        if (! ctype_lower($snakeCaseValue)) {
            $snakeCaseValue = preg_replace('/\s+/u', '', ucwords($snakeCaseValue));
            $snakeCaseValue = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $this->delimiter, $snakeCaseValue));
        }

        return str_replace(['-', '_'], [$this->delimiter, $this->delimiter], $snakeCaseValue);
    }
}
