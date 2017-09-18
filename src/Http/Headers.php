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
class Headers implements IHttpHeaders
{
    /** @var array The mapping of header names to values */
    private $headers = [];

    /**
     * @inheritdoc
     */
    public function get(string $name, $default = null, bool $onlyReturnFirst = true)
    {
        $normalizedName = $this->normalizeName($name);

        if (!$this->headers($normalizedName)) {
            return $default;
        }

        $values = $this->headers[$normalizedName];

        if ($onlyReturnFirst) {
            return $values[0];
        }

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function getAll() : array
    {
        return $this->headers;
    }

    /**
     * @inheritdoc
     */
    public function has(string $name) : bool
    {
        $normalizedName = $this->normalizeName($name);

        return isset($this->headers[$normalizedName]);
    }

    /**
     * @inheritdoc
     */
    public function remove(string $name) : void
    {
        $normalizedName = $this->normalizeName($name);

        unset($this->headers[$normalizedName]);
    }

    /**
     * @inheritdoc
     */
    public function set(string $name, $values, bool $shouldReplace = true) : void
    {
        $normalizedName = $this->normalizeName($name);

        if ($shouldReplace || !isset($this->headers[$normalizedName])) {
            $this->headers[$normalizedName] = (array)$values;
        } else {
            $this->headers[$normalizedName] = array_merge($this->headers[$normalizedName], (array)$values);
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
