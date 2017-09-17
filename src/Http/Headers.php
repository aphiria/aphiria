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
        if (!$this->headers($name)) {
            return $default;
        }
        
        $values = $this->headers[$name];
        
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
        return isset($this->headers[$name]);
    }

    /**
     * @inheritdoc
     */
    public function remove(string $name) : void
    {
        unset($this->headers[$name]);
    }

    /**
     * @inheritdoc
     */
    public function set(string $name, $values, bool $shouldReplace = true) : void
    {
        if ($shouldReplace || !isset($this->headers['name'])) {
            $this->headers[$name] = (array)$values;
        } else {
            $this->headers[$name] = array_merge($this->headers['name'], (array)$values);
        }
    }
}
