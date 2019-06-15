<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Parsers;

/**
 * Defines an abstract syntax tree node
 */
final class AstNode
{
    /** @var string The node type */
    public string $type;
    /** @var mixed|null The value of the node */
    public $value;
    /** @var AstNode|null The parent node */
    public ?AstNode $parent = null;
    /** @var AstNode[] The child nodes */
    public array $children = [];

    /**
     * @param string $type The node type
     * @param mixed $value The value of the node
     */
    public function __construct(string $type, $value = null)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Adds a child to this node
     *
     * @param AstNode $node The child to add
     * @return AstNode Returns this for chaining
     */
    public function addChild(AstNode $node): AstNode
    {
        $node->parent = $this;
        $this->children[] = $node;

        return $this;
    }

    /**
     * Gets whether or not the node has children
     *
     * @return bool True if the node has children, otherwise false
     */
    public function hasChildren(): bool
    {
        return \count($this->children) > 0;
    }

    /**
     * Gets whether or not this node is the root
     *
     * @return bool True if this is a root node, otherwise false
     */
    public function isRoot(): bool
    {
        return $this->parent === null;
    }
}
