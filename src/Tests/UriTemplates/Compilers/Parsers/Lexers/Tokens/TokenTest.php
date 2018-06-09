<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\UriTemplates\Compilers\Parsers\Lexers\Tokens;

use Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers\Tokens\Token;

/**
 * Tests a lexer token
 */
class TokenTest extends \PHPUnit\Framework\TestCase
{
    public function testGettingTypeReturnsCorrectValue(): void
    {
        $expectedType = 'foo';
        $this->assertEquals($expectedType, (new Token('foo', 'bar'))->getType());
    }

    public function testGettingValueReturnsCorrectValue(): void
    {
        $expectedValue = 'bar';
        $this->assertEquals($expectedValue, (new Token('foo', 'bar'))->getValue());
    }
}
