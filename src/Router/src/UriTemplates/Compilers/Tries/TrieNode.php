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

use Aphiria\Routing\Route;
use InvalidArgumentException;

/**
 * Defines the base class for trie nodes to extend
 */
abstract class TrieNode
{
    /** @var TrieNode|null The host trie, if there is one */
    public ?TrieNode $hostTrie;
    /** @var Route[] The list of routes for this node, if there are any */
    public array $routes;
    /** @var VariableTrieNode[] The child variable nodes */
    public array $variableChildren = [];
    /** @var LiteralTrieNode[] The mapping of literal child node values to child nodes */
    public array $literalChildrenByValue = [];

    /**
     * @param TrieNode[] $children The list of children
     * @param Route[]|Route $routes The list of routes for this segment if there are any
     * @param TrieNode|null $hostTrie The host trie, if there is one
     * @throws InvalidArgumentException Thrown if the routes are not the expected type
     */
    protected function __construct(array $children, $routes, ?TrieNode $hostTrie)
    {
        if (\is_array($routes)) {
            $this->routes = $routes;
        } elseif ($routes instanceof Route) {
            $this->routes = [$routes];
        } else {
            throw new InvalidArgumentException('Routes must be a route or an array of routes');
        }

        foreach ($children as $child) {
            $this->addChild($child);
        }

        $this->hostTrie = $hostTrie;
    }

    /**
     * Adds a child node and recursively merges all its children, too
     *
     * @param $childNode $node The node to add
     * @return self For chaining
     * @throws InvalidArgumentException Thrown if the node was an invalid type
     */
    public function addChild(TrieNode $childNode): self
    {
        if ($childNode instanceof LiteralTrieNode) {
            $this->addLiteralChildNode($childNode);
        } elseif ($childNode instanceof VariableTrieNode) {
            $this->addVariableChildNode($childNode);
        } else {
            throw new InvalidArgumentException('Unexpected trie node type ' . \get_class($childNode));
        }

        return $this;
    }

    /**
     * Gets all the child nodes
     *
     * @return TrieNode[] The child nodes
     */
    public function getAllChildren(): array
    {
        $children = [];

        foreach ($this->literalChildrenByValue as $childNode) {
            $children[] = $childNode;
        }

        foreach ($this->variableChildren as $childNode) {
            $children[] = $childNode;
        }

        return $children;
    }

    /**
     * Adds a literal child node
     *
     * @param LiteralTrieNode $childNode The child node to add
     */
    private function addLiteralChildNode(LiteralTrieNode $childNode): void
    {
        // Stringify the value in case it's a number and we don't want PHP getting confused
        $valueAsString = \strtolower((string)$childNode->value);

        if (isset($this->literalChildrenByValue[$valueAsString])) {
            // A literal child already exists with this value, so merge the routes and add all its children
            $matchingChildNode = $this->literalChildrenByValue[$valueAsString];
            $matchingChildNode->routes = [...$matchingChildNode->routes, ...$childNode->routes];

            foreach ($childNode->getAllChildren() as $grandChildNode) {
                $matchingChildNode->addChild($grandChildNode);
            }
        } else {
            $this->literalChildrenByValue[$valueAsString] = $childNode;
        }
    }

    /**
     * Adds a variable child node
     *
     * @param VariableTrieNode $childNode The child node to add
     */
    private function addVariableChildNode(VariableTrieNode $childNode): void
    {
        // Try to find a variable child whose parts match the input child's parts
        // If we find one, then we merge its routes and add all its children
        $matchingChildNode = null;

        foreach ($this->variableChildren as $variableChildNode) {
            // Purposely doing a loose check here because we don't care about reference equality
            if ($variableChildNode->parts == $childNode->parts) {
                $matchingChildNode = $variableChildNode;
                $variableChildNode->routes = [...$variableChildNode->routes, ...$childNode->routes];
                break;
            }
        }

        if ($matchingChildNode === null) {
            $this->variableChildren[] = $childNode;
        } else {
            foreach ($childNode->getAllChildren() as $grandChildNode) {
                $matchingChildNode->addChild($grandChildNode);
            }
        }
    }
}
