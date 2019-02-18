<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Output\Compilers\Parsers\Lexers\Tokens;

use Aphiria\Console\Output\Compilers\Parsers\Lexers\Tokens\OutputToken;
use Aphiria\Console\Output\Compilers\Parsers\Lexers\Tokens\OutputTokenTypes;
use PHPUnit\Framework\TestCase;

/**
 * Tests the output token
 */
class OutputTokenTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $token = new OutputToken(OutputTokenTypes::T_WORD, 'foo', 24);
        $this->assertEquals(OutputTokenTypes::T_WORD, $token->type);
        $this->assertEquals('foo', $token->value);
        $this->assertEquals(24, $token->position);
    }
}
