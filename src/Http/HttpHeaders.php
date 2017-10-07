<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\Net\Collection;

/**
 * Defines HTTP headers
 */
class HttpHeaders extends Collection
{
    /**
     * Headers are allowed to have multiple values, so we must add support for that
     *
     * @inheritdoc
     * @param string|array $values The value or values
     * @param bool $append Whether or not to append the value to to the other header values
     */
    public function add(string $name, $values, bool $append = false) : void
    {
        $normalizedName = $this->normalizeName($name);

        if (!$append || !isset($this->values[$normalizedName])) {
            $this->values[$normalizedName] = (array)$values;
        } else {
            $this->values[$normalizedName] = array_merge($this->values[$normalizedName], (array)$values);
        }
    }

    /**
     * Gets a header value
     *
     * @param string $name The name of the header whose value we want
     * @param mixed|null $default The default value, if none was found
     * @param bool $onlyReturnFirst Whether or not to return only the first value
     * @return mixed The value of the header
     */
    public function get(string $name, $default = null, bool $onlyReturnFirst = true)
    {
        if ($this->has($name)) {
            $value = $this->values[$this->normalizeName($name)];

            if ($onlyReturnFirst) {
                return $value[0];
            }
        } else {
            $value = $default;
        }

        return $value;
    }

    /**
     * Gets the parameters (semi-colon delimited values for a header) as a collection
     *
     * @param string $name The name of the header whose parameters we want
     * @param bool $onlyReturnFirst Whether or not to return only the first value's parameters
     * @return Collection|Collection[] The list of parameters or an array of lists of parameters if returning all
     */
    public function getParameters(string $name, bool $onlyReturnFirst = true)
    {
        $normalizedName = $this->normalizeName($name);

        if (!$this->has($normalizedName)) {
            return new Collection;
        }

        $parameters = [];
        $trimmedChars = "\"'  \n\t\r";

        foreach ($this->get($normalizedName, [], false) as $value) {
            $parameter = [];

            // Match all parameters
            foreach (preg_split('/;(?=([^"]*"[^"]*")*[^"]*$)/', $value) as $kvp) {
                $matches = [];

                // Split the parameters into names and values
                if (preg_match_all('/<[^>]+>|[^=]+/', $kvp, $matches)) {
                    if (isset($matches[0][1])) {
                        $parameter[trim($matches[0][0], $trimmedChars)] = trim($matches[0][1], $trimmedChars);
                    } else {
                        $parameter[trim($matches[0][0], $trimmedChars)] = null;
                    }
                }
            }

            if (count($parameter) !== 0) {
                $parameters[] = new Collection($parameter);
            }
        }

        if ($onlyReturnFirst) {
            return $parameters[0];
        }

        return $parameters;
    }

    /**
     * Gets whether or not a header has a value
     *
     * @param string $name The name of the header to search for
     * @return bool True if the header has a value, otherwise false
     */
    public function has(string $name) : bool
    {
        return parent::has($this->normalizeName($name));
    }

    /**
     * Removes a header
     *
     * @param string $name The name of the header to remove
     */
    public function remove(string $name) : void
    {
        parent::remove($this->normalizeName($name));
    }

    /**
     * Normalizes the name of the header
     *
     * @param string $name The header name to normalize
     * @return string The normalized name
     */
    protected function normalizeName(string $name) : string
    {
        return ucwords(strtr(strtolower($name), '_', '-'), '-');
    }
}
