<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Trees;

use Opulence\Routing\Matchers\Trees\Caching\ITrieCache;
use Opulence\Routing\Matchers\Trees\Compilers\ITrieCompiler;
use Opulence\Routing\Matchers\Trees\Compilers\TrieCompiler;
use Opulence\Routing\RouteFactory;

/**
 * Defines a factory for tries
 */
class TrieFactory
{
    /** @var RouteFactory The route factory to use in case the trie needs to be generated */
    private $routeFactory;
    /** @var ITrieCache|null The cache for tries, or null if not using a cache */
    private $trieCache;
    /** @var ITrieCompiler The trie compiler */
    private $trieCompiler;

    /**
     * @param RouteFactory $routeFactory The route factory to use in case the trie needs to be generated
     * @param ITrieCache|null $trieCache The cache for tries, or null if not using a cache
     * @param ITrieCompiler|null $trieCompiler The trie compiler
     */
    public function __construct(RouteFactory $routeFactory, ?ITrieCache $trieCache, ITrieCompiler $trieCompiler = null)
    {
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
        $routeCollection = $this->routeFactory->createRoutes();
        $trie = new RootTrieNode();

        foreach ($routeCollection->getAll() as $route) {
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