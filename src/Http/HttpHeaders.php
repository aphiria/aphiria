<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

/**
 * Defines HTTP headers
 */
class HttpHeaders extends Collection
{
    /**
     * Creates an instance with no initial values
     */
    public function __construct()
    {
        /**
         * Headers allow multiple values
         * The parent class does not have this feature, which is why we took care of it in this constructor
         * To satisfy the parent constructor, we'll simply send it an empty array
         */
        parent::__construct([]);
    }

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
