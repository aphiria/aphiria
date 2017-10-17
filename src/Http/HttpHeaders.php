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
use Opulence\Collections\IImmutableDictionary;
use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;

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
            $currentValues = $this->get($name, [], false);
            parent::add($normalizedName, array_merge($currentValues, (array)$values));
        }
    }

    /**
     * Gets whether or not a header has a value
     *
     * @param string $name The name of the header to search for
     * @return bool True if the header has a value, otherwise false
     */
    public function containsKey($name) : bool
    {
        return parent::containsKey($this->getHashKey($name));
    }

    /**
     * Gets a header value
     *
     * @param string $name The name of the header whose value we want
     * @param mixed|null $default The default value, if none was found
     * @param bool $onlyReturnFirst Whether or not to return only the first value
     * @return mixed The value of the header
     */
    public function get($name, $default = null, bool $onlyReturnFirst = true)
    {
        $normalizedName = $this->getHashKey($name);

        if ($this->containsKey($normalizedName)) {
            $value = parent::get($normalizedName);

            if ($onlyReturnFirst) {
                return $value[0];
            }
        } else {
            $value = $default;
        }

        return $value;
    }

    /**
     * Gets the parameters (semi-colon delimited values for a header) as a dictionary
     * If returning only the first value's parameters, then a dictionary will be returned
     * If returning all the values' parameters, then an array of dictionaries will be returned
     *
     * @param string $name The name of the header whose parameters we want
     * @param bool $onlyReturnFirst Whether or not to return only the first value's parameters
     * @return IImmutableDictionary|IImmutableDictionary[] The dictionary of parameters
     */
    public function getParameters(string $name, bool $onlyReturnFirst = true)
    {
        $normalizedName = $this->getHashKey($name);

        if (!$this->containsKey($normalizedName)) {
            return new ImmutableHashTable([]);
        }

        $parameters = [];
        $trimmedChars = "\"'  \n\t\r";

        foreach ($this->get($normalizedName, [], false) as $value) {
            $kvps = [];

            // Match all parameters
            foreach (preg_split('/;(?=([^"]*"[^"]*")*[^"]*$)/', $value) as $kvp) {
                $matches = [];

                // Split the parameters into names and values
                if (preg_match_all('/<[^>]+>|[^=]+/', $kvp, $matches)) {
                    $key = trim($matches[0][0], $trimmedChars);

                    if (isset($matches[0][1])) {
                        $value = trim($matches[0][1], $trimmedChars);
                    } else {
                        $value = null;
                    }

                    $kvps[] = new KeyValuePair($key, $value);
                }
            }

            if (count($kvps) !== 0) {
                $parameters[] = new ImmutableHashTable($kvps);
            }
        }

        if ($onlyReturnFirst) {
            return $parameters[0];
        }

        return $parameters;
    }

    /**
     * Removes a header
     *
     * @param string $name The name of the header to remove
     */
    public function removeKey($name) : void
    {
        parent::removeKey($this->getHashKey($name));
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
