<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\UriTemplates\Compilers\Parsers;

use Opulence\Routing\UriTemplates\Compilers\Parsers\Nodes\Node;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Nodes\NodeTypes;

/**
 * Defines a view abstract syntax tree
 */
class AbstractSyntaxTree
{
    /** @var Node The root node */
    private $rootNode;
    /** @var Node The current node */
    private $currentNode;

    public function __construct()
    {
        $this->clearNodes();
    }

    /**
     * Clears all the non-root nodes
     */
    public function clearNodes(): void
    {
        $this->rootNode = new Node(NodeTypes::ROOT, null);
        $this->setCurrentNode($this->rootNode);
    }

    /**
     * Gets the current node
     *
     * @return Node The current node
     */
    public function getCurrentNode(): Node
    {
        return $this->currentNode;
    }

    /**
     * Gets the root node
     *
     * @return Node The root node
     */
    public function getRootNode(): Node
    {
        return $this->rootNode;
    }

    /**
     * Sets the current node
     *
     * @param Node $node The node to set
     * @return Node The current node
     */
    public function setCurrentNode(Node $node): Node
    {
        $this->currentNode = $node;

        return $this->currentNode;
    }
}
