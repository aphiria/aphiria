<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers\Trees;

use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\Matchers\Trees\Caching\ITrieCache;
use Aphiria\Routing\Matchers\Trees\Compilers\ITrieCompiler;
use Aphiria\Routing\Matchers\Trees\Compilers\TrieCompiler;

/**
 * Defines a factory for tries
 */
final class TrieFactory
{
    /** @var RouteBuilderRegistry The route builders to use in case the trie needs to be generated */
    private $routeBuilders;
    /** @var ITrieCache|null The cache for tries, or null if not using a cache */
    private $trieCache;
    /** @var ITrieCompiler The trie compiler */
    private $trieCompiler;

    /**
     * @param RouteBuilderRegistry $routeBuilders The route builders to use in case the trie needs to be generated
     * @param ITrieCache|null $trieCache The cache for tries, or null if not using a cache
     * @param ITrieCompiler|null $trieCompiler The trie compiler
     */
    public function __construct(
        RouteBuilderRegistry $routeBuilders,
        ?ITrieCache $trieCache,
        ITrieCompiler $trieCompiler = null
    ) {
        $this->routeBuilders = $routeBuilders;
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

        foreach ($this->routeBuilders->buildAll() as $route) {
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
