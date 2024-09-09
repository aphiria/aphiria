<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Parsers;

/**
 * Defines an output abstract syntax tree node
 */
abstract class AstNode
{
    /** @var bool Whether or not this is a tag node */
    abstract public bool $isTag { get; }
    /** @var bool Whether or not this is a leaf node */
    public bool $isLeaf {
        get => \count($this->children) === 0;
    }
    /** @var bool Whether or not this is a root node */
    public bool $isRoot {
        get => $this->parent === null;
    }
    /** @var list<AstNode> The child nodes */
    public array $children = [];
    /** @var AstNode|null The parent node */
    public ?AstNode $parent = null;

    /**
     * @param mixed $value The value of the node
     */
    public function __construct(public mixed $value = null)
    {
    }

    /**
     * Adds a child to this node
     *
     * @param AstNode $node The child to add
     * @return static For chaining
     */
    public function addChild(AstNode $node): static
    {
        $node->parent = $this;
        $this->children[] = $node;

        return $this;
    }
}
