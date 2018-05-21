<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Encoding;

/**
 * Defines the snake-case property name formatter
 */
class SnakeCasePropertyNameFormatter implements IPropertyNameFormatter
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
