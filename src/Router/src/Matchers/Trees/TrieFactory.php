<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers\Trees;

use Aphiria\Routing\IRouteFactory;
use Aphiria\Routing\Matchers\Trees\Caching\ITrieCache;
use Aphiria\Routing\Matchers\Trees\Compilers\ITrieCompiler;
use Aphiria\Routing\Matchers\Trees\Compilers\TrieCompiler;

/**
 * Defines a factory for tries
 */
final class TrieFactory
{
    /**
     * The factory that will create the routes
     * The benefit of using a factory instead of the already-instantiated routes is that we can defer the potentially
     * slow/costly overhead of creating those routes when there's a cache hit
     *
     * @var IRouteFactory
     */
    private IRouteFactory $routeFactory;
    /** @var ITrieCache|null The cache for tries, or null if not using a cache */
    private ?ITrieCache $trieCache;
    /** @var ITrieCompiler The trie compiler */
    private ITrieCompiler $trieCompiler;

    /**
     * @param IRouteFactory $routeFactory The factory that will create the routes when not found in cache
     * @param ITrieCache|null $trieCache The cache for tries, or null if not using a cache
     * @param ITrieCompiler|null $trieCompiler The trie compiler
     */
    public function __construct(
        IRouteFactory $routeFactory,
        ITrieCache $trieCache = null,
        ITrieCompiler $trieCompiler = null
    ) {
        $this->routeFactory = $routeFactory;
        $this->trieCache = $trieCache;
        $this->trieCompiler = $trieCompiler ?? new TrieCompiler();
    }

    /**
     * Creates a trie
     *
     * @return TrieNode The trie
     */
    public function createTrie(): TrieNode
    {
        if ($this->trieCache !== null && ($trie = $this->trieCache->get()) !== null) {
            return $trie;
        }

        // Need to generate the trie
        $trie = new RootTrieNode();

        foreach ($this->routeFactory->createRoutes()->getAll() as $route) {
            foreach ($this->trieCompiler->compile($route)->getAllChildren() as $childNode) {
                $trie->addChild($childNode);
            }
        }

        // Save this to cache for next time
        if ($this->trieCache !== null) {
            $this->trieCache->set($trie);
        }

        return $trie;
    }
}
