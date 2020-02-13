<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Parsers;

use Aphiria\Console\Output\Parsers\WordAstNode;
use PHPUnit\Framework\TestCase;

/**
 * Tests the word node
 */
class WordNodeTest extends TestCase
{
    public function testIsTag(): void
    {
        $node = new WordAstNode('foo');
        $this->assertFalse($node->isTag());
    }
}
