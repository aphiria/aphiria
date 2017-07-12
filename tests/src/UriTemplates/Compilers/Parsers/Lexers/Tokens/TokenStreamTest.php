<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\UriTemplates\Compilers\Parsers\Lexers\Tokens;

use InvalidArgumentException;

/**
 * Tests the lexer token stream
 */
class TokenStreamTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests checking the next token's type always returns the next's type
     */
    public function testCheckingNextTypeAlwaysReturnsNextType() : void
    {
        $token1 = new Token('foo', 'bar');
        $token2 = new Token('baz', 'blah');
        $token3 = new Token('dave', 'young');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertTrue($stream->nextIfType('foo'));
        $this->assertFalse($stream->nextIfType('badtype'));
        $this->assertTrue($stream->nextIfType('baz'));
        $this->assertFalse($stream->nextIfType('badtype'));
        $this->assertTrue($stream->nextIfType('dave'));
        $this->assertFalse($stream->nextIfType('badtype'));
    }

    /**
     * Tests expect does not throw an exception on a match
     */
    public function testExpectDoesNotThrowExceptionOnMatch() : void
    {
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $stream->expect('foo');
        $stream->expect('foo', 'bar');
        // Just verify we've gotten here
        $this->assertTrue(true);
    }

    /**
     * Tests expect throws an exception on a miss
     */
    public function testExpectThrowsExceptionOnMiss() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $stream->expect('baz');
    }

    /**
     * Tests getting the current token when at the end returns null
     */
    public function testGettingCurrentTokenWhenAtEndReturnsNull() : void
    {
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $stream->next();
        $this->assertNull($stream->getCurrent());
    }

    /**
     * Tests getting the current token always returns the current token
     */
    public function testGettingCurrentAlwaysReturnsCurrentToken() : void
    {
        $token1 = new Token('foo', 'bar');
        $token2 = new Token('baz', 'blah');
        $stream = new TokenStream([$token1, $token2]);
        $this->assertSame($token1, $stream->getCurrent());
        $stream->next();
        $this->assertSame($token2, $stream->getCurrent());
    }

    /**
     * Tests getting the next token always returns the next token
     */
    public function testGettingNextAlwaysReturnsNextToken() : void
    {
        $token1 = new Token('foo', 'bar');
        $token2 = new Token('baz', 'blah');
        $token3 = new Token('dave', 'young');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertSame($token2, $stream->next());
        $this->assertSame($token3, $stream->next());
    }

    /**
     * Tests getting the next token when at the end returns null
     */
    public function testGettingNextWhenAtEndReturnsNull() : void
    {
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $this->assertNull($stream->next());
    }

    /**
     * Tests peeking always returns the next token
     */
    public function testPeekingAlwaysReturnsNextToken() : void
    {
        $token1 = new Token('foo', 'bar');
        $token2 = new Token('baz', 'blah');
        $token3 = new Token('dave', 'young');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertSame($token2, $stream->peek());
        $stream->next();
        $this->assertSame($token3, $stream->peek());
    }

    /**
     * Tests peeking when skipping always returns the correct token
     */
    public function testPeekingWhileSkippingReturnsTheCorrectToken() : void
    {
        $token1 = new Token('foo', 'bar');
        $token2 = new Token('baz', 'blah');
        $token3 = new Token('dave', 'young');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertSame($token3, $stream->peek(2));
    }

    /**
     * Tests peeking when at the end returns null
     */
    public function testPeekingWhenAtEndReturnsNull() : void
    {
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $this->assertNull($stream->peek());
    }

    /**
     * Tests testing the next token's type
     */
    public function testTestingNextTokensType() : void
    {
        $token1 = new Token('foo', 'bar');
        $token2 = new Token('baz', 'blah');
        $token3 = new Token('dave', 'young');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertTrue($stream->test('foo'));
        $stream->next();
        $this->assertFalse($stream->test('badtype'));
        $this->assertTrue($stream->test('baz'));
        $stream->next();
        $this->assertFalse($stream->test('badtype'));
        $this->assertTrue($stream->test('dave'));
    }
}
