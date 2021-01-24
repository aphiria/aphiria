<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Parsers;

use Aphiria\Console\Output\Parsers\TagAstNode;
use PHPUnit\Framework\TestCase;

class TagNodeTest extends TestCase
{
    public function testIsTag(): void
    {
        $node = new TagAstNode('foo');
        $this->assertTrue($node->isTag());
    }
}
