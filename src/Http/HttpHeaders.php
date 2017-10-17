<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\Collections\HashTable;

/**
 * Defines HTTP headers
 */
class HttpHeaders extends HashTable
{
    /**
     * Headers are allowed to have multiple values, so we must add support for that
     *
     * @inheritdoc
     * @param string|array $values The value or values
     * @param bool $append Whether or not to append the value to to the other header values
     */
    public function add($name, $values, bool $append = false) : void
    {
        $normalizedName = $this->getHashKey($name);

        if (!$append || !$this->containsKey($normalizedName)) {
            parent::add($normalizedName, (array)$values);
        } else {
            $currentValues = $this->get($name, []);
            parent::add($normalizedName, array_merge($currentValues, (array)$values));
        }
    }

    /**
     * Gets the first values for a header
     *
     * @param string $name The name of the header whose value we want
     * @param mixed|null $default The default value, if none was found
     * @return mixed The first value of the header
     */
    public function getFirst($name, $default = null)
    {
        $value = $this->get($name, []);

        if (count($value) === 0) {
            return $default;
        }

        return $value[0];
    }

    /**
     * @inheritdoc
     * Normalizes the name of the header
     */
    protected function getHashKey($name) : string
    {
        $normalizedName = ucwords(strtr(strtolower($name), '_', '-'), '-');

        return parent::getHashKey($normalizedName);
    }
}
