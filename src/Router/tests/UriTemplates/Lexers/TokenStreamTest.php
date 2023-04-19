<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Lexers;

use Aphiria\Routing\UriTemplates\Lexers\Token;
use Aphiria\Routing\UriTemplates\Lexers\TokenStream;
use Aphiria\Routing\UriTemplates\Lexers\TokenType;
use Aphiria\Routing\UriTemplates\Lexers\UnexpectedTokenException;
use PHPUnit\Framework\TestCase;

class TokenStreamTest extends TestCase
{
    public function testCheckingNextTypeAlwaysReturnsNextType(): void
    {
        $token1 = new Token(TokenType::Text, 'foo');
        $token2 = new Token(TokenType::Number, 1);
        $token3 = new Token(TokenType::Punctuation, '[');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertTrue($stream->nextIfType(TokenType::Text));
        $this->assertFalse($stream->nextIfType(TokenType::Variable));
        $this->assertTrue($stream->nextIfType(TokenType::Number));
        $this->assertFalse($stream->nextIfType(TokenType::Variable));
        $this->assertTrue($stream->nextIfType(TokenType::Punctuation));
        $this->assertFalse($stream->nextIfType(TokenType::Variable));
    }

    public function testExpectDoesNotThrowExceptionOnMatch(): void
    {
        $stream = new TokenStream([new Token(TokenType::Text, 'foo')]);
        $stream->expect(TokenType::Text);
        $stream->expect(TokenType::Text, 'foo');
        // Just verify we've gotten here
        $this->assertTrue(true);
    }

    public function testExpectThrowsExceptionOnEndOfStream(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage('Expected token type ' . TokenType::Punctuation->name . ', got end of stream');
        $stream = new TokenStream([new Token(TokenType::Text, 'foo')]);
        $stream->next();
        $stream->expect(TokenType::Punctuation);
    }

    public function testExpectThrowsExceptionOnMiss(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage('Expected token type ' . TokenType::Number->name . ', got ' . TokenType::Text->name . ' with value \"foo\"');
        $stream = new TokenStream([new Token(TokenType::Text, 'foo')]);
        $stream->expect(TokenType::Number);
    }

    public function testGettingCurrentAlwaysReturnsCurrentToken(): void
    {
        $token1 = new Token(TokenType::Text, 'foo');
        $token2 = new Token(TokenType::Text, 'bar');
        $stream = new TokenStream([$token1, $token2]);
        $this->assertSame($token1, $stream->getCurrent());
        $stream->next();
        $this->assertSame($token2, $stream->getCurrent());
    }

    public function testGettingCurrentTokenWhenAtEndReturnsNull(): void
    {
        $stream = new TokenStream([new Token(TokenType::Text, 'foo')]);
        $stream->next();
        $this->assertNull($stream->getCurrent());
    }

    public function testGettingNextAlwaysReturnsNextToken(): void
    {
        $token1 = new Token(TokenType::Text, 'foo');
        $token2 = new Token(TokenType::Text, 'bar');
        $token3 = new Token(TokenType::Text, 'baz');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertSame($token2, $stream->next());
        $this->assertSame($token3, $stream->next());
    }

    public function testGettingNextWhenAtEndReturnsNull(): void
    {
        $stream = new TokenStream([new Token(TokenType::Text, 'foo')]);
        $this->assertNull($stream->next());
    }

    public function testLengthReturnsCountOfTokens(): void
    {
        $this->assertSame(1, (new TokenStream([new Token(TokenType::Text, 'foo')]))->length);
        $this->assertSame(2, (new TokenStream([new Token(TokenType::Text, 'foo'), new Token(TokenType::Text, 'bar')]))->length);
    }

    public function testPeekingAlwaysReturnsNextToken(): void
    {
        $token1 = new Token(TokenType::Text, 'foo');
        $token2 = new Token(TokenType::Text, 'bar');
        $token3 = new Token(TokenType::Text, 'baz');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertSame($token2, $stream->peek());
        $stream->next();
        $this->assertSame($token3, $stream->peek());
    }

    public function testPeekingWhenAtEndReturnsNull(): void
    {
        $stream = new TokenStream([new Token(TokenType::Text, 'foo')]);
        $this->assertNull($stream->peek());
    }

    public function testPeekingWhileSkippingReturnsTheCorrectToken(): void
    {
        $token1 = new Token(TokenType::Text, 'foo');
        $token2 = new Token(TokenType::Text, 'bar');
        $token3 = new Token(TokenType::Text, 'baz');
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertSame($token3, $stream->peek(2));
    }

    public function testTestingNextTokensType(): void
    {
        $token1 = new Token(TokenType::Text, 'foo');
        $token2 = new Token(TokenType::Punctuation, '[');
        $token3 = new Token(TokenType::Number, 1);
        $stream = new TokenStream([$token1, $token2, $token3]);
        $this->assertTrue($stream->test(TokenType::Text));
        $stream->next();
        $this->assertFalse($stream->test(TokenType::Variable));
        $this->assertTrue($stream->test(TokenType::Punctuation));
        $stream->next();
        $this->assertFalse($stream->test(TokenType::Variable));
        $this->assertTrue($stream->test(TokenType::Number));
    }
}
