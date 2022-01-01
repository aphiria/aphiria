<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
    /**
     * @param RouteCollection $routes The list of routes
     * @param ITrieCache|null $trieCache The cache for tries, or null if not using a cache
     * @param ITrieCompiler $trieCompiler The trie compiler
     */
    public function __construct(
        private readonly RouteCollection $routes,
        private readonly ?ITrieCache $trieCache = null,
        private readonly ITrieCompiler $trieCompiler = new TrieCompiler()
    ) {
    }

    /**
     * Creates a trie
     *
     * @return TrieNode The trie
     * @throws InvalidUriTemplateException Thrown if the URI template is invalid
     */
    public function createTrie(): TrieNode
    {
        if (($trie = $this->trieCache?->get()) !== null) {
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
