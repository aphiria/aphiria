<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Output\Compilers\Parsers\Nodes;

use Aphiria\Console\Output\Compilers\Parsers\Nodes\WordNode;
use PHPUnit\Framework\TestCase;

/**
 * Tests the word node
 */
class WordNodeTest extends TestCase
{
    public function testIsTag(): void
    {
        $node = new WordNode('foo');
        $this->assertFalse($node->isTag());
    }
}
