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
class Headers extends Collection implements IHttpHeaders
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
     * @param bool $shouldReplace Whether or not to replace the value
     */
    public function add(string $name, $values, bool $shouldReplace = true) : void
    {
        $this->set($name, $values, $shouldReplace);
    }

    /**
     * @inheritdoc
     * @param bool $onlyReturnFirst True if we only want the first header, otherwise we'll return all of them
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
     * @inheritdoc
     */
    public function has(string $name) : bool
    {
        return parent::has($this->normalizeName($name));
    }

    /**
     * @inheritdoc
     */
    public function remove(string $name) : void
    {
        parent::remove($this->normalizeName($name));
    }

    /**
     * @inheritdoc
     */
    public function set(string $name, $values, bool $shouldReplace = true) : void
    {
        $normalizedName = $this->normalizeName($name);

        if ($shouldReplace || !isset($this->values[$normalizedName])) {
            $this->values[$normalizedName] = (array)$values;
        } else {
            $this->values[$normalizedName] = array_merge($this->values[$normalizedName], (array)$values);
        }
    }

    /**
     * Normalizes the name of the header
     *
     * @param string $name The header name to normalize
     * @return string The normalized name
     */
    protected function normalizeName(string $name) : string
    {
        return strtr(strtoupper($name), '_', '-');
    }
}
