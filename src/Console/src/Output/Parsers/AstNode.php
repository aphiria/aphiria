<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Parsers;

/**
 * Defines an output abstract syntax tree node
 */
abstract class AstNode
{
    /** @var mixed The value of the node if there is one, otherwise null */
    public mixed $value;
    /** @var AstNode|null The parent node */
    public ?AstNode $parent = null;
    /** @var AstNode[] The child nodes */
    public array $children = [];

    /**
     * @param mixed $value The value of the node
     */
    public function __construct(mixed $value = null)
    {
        $this->value = $value;
    }

    /**
     * Gets whether or not this is a tag node
     *
     * @return bool True if this is a tag node, otherwise false
     */
    abstract public function isTag(): bool;

    /**
     * Adds a child to this node
     *
     * @param AstNode $node The child to add
     * @return self Returns this for chaining
     */
    public function addChild(AstNode $node): self
    {
        $node->parent = $this;
        $this->children[] = $node;

        return $this;
    }

    /**
     * Gets whether or not this node is a leaf
     *
     * @return bool True if this is a leaf, otherwise false
     */
    public function isLeaf(): bool
    {
        return \count($this->children) === 0;
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
