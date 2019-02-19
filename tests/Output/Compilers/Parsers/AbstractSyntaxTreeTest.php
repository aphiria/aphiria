<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Output\Compilers\Parsers;

use Aphiria\Console\Output\Compilers\Parsers\AbstractSyntaxTree;
use Aphiria\Console\Output\Compilers\Parsers\Nodes\RootNode;
use Aphiria\Console\Tests\Output\Compilers\Parsers\Nodes\Mocks\Node;
use PHPUnit\Framework\TestCase;

/**
 * Tests the output abstract syntax tree
 */
class AbstractSyntaxTreeTest extends TestCase
{
    /** @var AbstractSyntaxTree */
    private $tree;

    public function setUp(): void
    {
        $this->tree = new AbstractSyntaxTree();
    }

    public function testGettingCurrentNodeWhenNoneIsSet(): void
    {
        $this->assertEquals(new RootNode(), $this->tree->getCurrentNode());
    }

    public function testGettingRootNode(): void
    {
        $this->assertEquals(new RootNode(), $this->tree->getRootNode());
    }

    public function testSettingCurrentNode(): void
    {
        $currentNode = new Node('foo');
        $this->assertSame($currentNode, $this->tree->setCurrentNode($currentNode));
        $this->assertSame($currentNode, $this->tree->getCurrentNode());
    }
}
