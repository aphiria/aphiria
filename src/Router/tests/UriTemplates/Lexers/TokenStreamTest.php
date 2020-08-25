<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Lexers;

use Aphiria\Routing\UriTemplates\Lexers\Token;
use Aphiria\Routing\UriTemplates\Lexers\TokenStream;
use Aphiria\Routing\UriTemplates\Lexers\UnexpectedTokenException;
use PHPUnit\Framework\TestCase;

class TokenStreamTest extends TestCase
{
    public function testCheckingNextTypeAlwaysReturnsNextType(): void
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

    public function testExpectDoesNotThrowExceptionOnMatch(): void
    {
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $stream->expect('foo');
        $stream->expect('foo', 'bar');
        // Just verify we've gotten here
        $this->assertTrue(true);
    }

    public function testExpectThrowsExceptionOnMiss(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage('Expected token type baz, got foo with value \"bar\"');
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $stream->expect('baz');
    }

    public function testExpectThrowsExceptionOnEndOfStream(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage('Expected token type bar, got end of stream');
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $stream->next();
        $stream->expect('bar');
    }

    public function testGettingCurrentTokenWhenAtEndReturnsNull(): void
    {
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $stream->next();
        $this->assertNull($stream->getCurrent());
    }

    public function testGettingCurrentAlwaysReturnsCurrentToken(): void
    {
        $token1 = new Token('foo', 'bar');
        $token2 = new Token('baz', 'blah');
        $stream = new TokenStream([$token1, $token2]);
        $this->assertSame($token1, $stream->getCurrent());
        $stream->next();
        $this->assertSame($token2, $stream->getCurrent());
    }

    public function testGettingNextAlwaysReturnsNextToken(): void
    {
        $token1 = new Token('foo', 'bar');
        $token2 = new Token('baz', 'blah');
        $token3 = new Token('dave', 'young');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertSame($token2, $stream->next());
        $this->assertSame($token3, $stream->next());
    }

    public function testGettingNextWhenAtEndReturnsNull(): void
    {
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $this->assertNull($stream->next());
    }

    public function testLengthReturnsCountOfTokens(): void
    {
        $this->assertSame(1, (new TokenStream([new Token('foo', 'bar')]))->length);
        $this->assertSame(2, (new TokenStream([new Token('foo', 'bar'), new Token('baz', 'blah')]))->length);
    }

    public function testPeekingAlwaysReturnsNextToken(): void
    {
        $token1 = new Token('foo', 'bar');
        $token2 = new Token('baz', 'blah');
        $token3 = new Token('dave', 'young');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertSame($token2, $stream->peek());
        $stream->next();
        $this->assertSame($token3, $stream->peek());
    }

    public function testPeekingWhileSkippingReturnsTheCorrectToken(): void
    {
        $token1 = new Token('foo', 'bar');
        $token2 = new Token('baz', 'blah');
        $token3 = new Token('dave', 'young');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertSame($token3, $stream->peek(2));
    }

    public function testPeekingWhenAtEndReturnsNull(): void
    {
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $this->assertNull($stream->peek());
    }

    public function testTestingNextTokensType(): void
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
