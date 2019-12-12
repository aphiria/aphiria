<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries;

use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\Compilers\Tries\Caching\ITrieCache;
use Aphiria\Routing\UriTemplates\InvalidUriTemplateException;

/**
 * Defines a factory for tries
 */
final class TrieFactory
{
    /** @var RouteCollection The routes that will be used to create the trie */
    private RouteCollection $routes;
    /** @var ITrieCache|null The cache for tries, or null if not using a cache */
    private ?ITrieCache $trieCache;
    /** @var ITrieCompiler The trie compiler */
    private ITrieCompiler $trieCompiler;

    /**
     * @param RouteCollection $routes The list of routes
     * @param ITrieCache|null $trieCache The cache for tries, or null if not using a cache
     * @param ITrieCompiler|null $trieCompiler The trie compiler
     */
    public function __construct(
        RouteCollection $routes,
        ITrieCache $trieCache = null,
        ITrieCompiler $trieCompiler = null
    ) {
        $this->routes = $routes;
        $this->trieCache = $trieCache;
        $this->trieCompiler = $trieCompiler ?? new TrieCompiler();
    }

    /**
     * Creates a trie
     *
     * @return TrieNode The trie
     * @throws InvalidUriTemplateException Thrown if the URI template is invalid
     */
    public function createTrie(): TrieNode
    {
        if ($this->trieCache !== null && ($trie = $this->trieCache->get()) !== null) {
            return $trie;
        }

        // Need to generate the trie
        $trie = new RootTrieNode();

        foreach ($this->routes->getAll() as $route) {
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
