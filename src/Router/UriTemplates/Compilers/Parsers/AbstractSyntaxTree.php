<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Router\UriTemplates\Compilers\Parsers;

use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\Node;
use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\NodeTypes;

/**
 * Defines a view abstract syntax tree
 */
class AbstractSyntaxTree
{
    /** @var Node The root node */
    private $rootNode = null;
    /** @var Node The current node */
    private $currentNode = null;

    public function __construct()
    {
        $this->clearNodes();
    }

    /**
     * Clears all the non-root nodes
     */
    public function clearNodes()
    {
        $this->rootNode = new Node(NodeTypes::ROOT, null);
        $this->setCurrentNode($this->rootNode);
    }

    /**
     * Gets the current node
     *
     * @return Node The current node
     */
    public function getCurrentNode() : Node
    {
        return $this->currentNode;
    }

    /**
     * Gets the root node
     *
     * @return Node The root node
     */
    public function getRootNode() : Node
    {
        return $this->rootNode;
    }

    /**
     * Sets the current node
     *
     * @param Node $node The node to set
     * @return Node The current node
     */
    public function setCurrentNode(Node $node) : Node
    {
        $this->currentNode = $node;

        return $this->currentNode;
    }
}
