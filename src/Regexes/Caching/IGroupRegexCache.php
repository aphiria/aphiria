<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Regexes\Caching;

use Opulence\Routing\Regexes\GroupRegexCollection;

/**
 * Defines the interface for group regex caches to implement
 */
interface IGroupRegexCache
{
    /**
     * Flushes the group regex cache
     */
    public function flush() : void;

    /**
     * Gets the group regex collection from cache
     *
     * @return GroupRegexCollection|null The group regex collection if it existed in cache, otherwise null
     */
    public function get() : ?GroupRegexCollection;

    /**
     * Gets whether or not the group regex collection is cached
     *
     * @return bool True if the group regex collection is cached, otherwise false
     */
    public function has() : bool;

    /**
     * Sets the group regex collection in cache
     *
     * @param GroupRegexCollection $regexes The group regex collection to cache
     */
    public function set(GroupRegexCollection $regexes) : void;
}
