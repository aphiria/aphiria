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
use OutOfBoundsException;

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
            $currentValues = [];
            $this->tryGet($name, $currentValues);
            parent::add($normalizedName, array_merge($currentValues, (array)$values));
        }
    }

    /**
     * Gets the first values for a header
     *
     * @param string $name The name of the header whose value we want
     * @return mixed The first value of the header
     * @throws OutOfBoundsException Thrown if the key could not be found
     */
    public function getFirst($name)
    {
        if (!$this->containsKey($name)) {
            throw new OutOfBoundsException('Key does not exist');
        }

        return $this->get($name)[0];
    }

    /**
     * Tries to get the first value of a header
     *
     * @param mixed $name The name of the header whose value we want
     * @param mixed $value The value, if it is found
     * @return bool True if the key exists, otherwise false
     */
    public function tryGetFirst($name, &$value) : bool
    {
        try {
            $value = $this->get($name)[0];

            return true;
        } catch (OutOfBoundsException $ex) {
            return false;
        }

        return false;
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
