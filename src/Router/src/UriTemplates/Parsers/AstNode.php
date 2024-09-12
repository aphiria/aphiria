<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Parsers;

/**
 * Defines an abstract syntax tree node
 */
final class AstNode
{
    /** @var list<AstNode> The child nodes */
    public array $children = [];
    /** @var bool Whether or not the node has children */
    public bool $hasChildren {
        get => \count($this->children) > 0;
    }
    /** @var bool Whether or not the node is the root node */
    public bool $isRoot {
        get => $this->parent === null;
    }
    /** @var AstNode|null The parent node */
    public ?AstNode $parent = null;

    /**
     * @param AstNodeType $type The node type
     * @param mixed $value The value of the node
     */
    public function __construct(public AstNodeType $type, public mixed $value = null)
    {
    }

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
}
