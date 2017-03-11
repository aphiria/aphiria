<?php
namespace Opulence\Router\UriTemplates\Parsers\Lexers\Tokens;

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
        $this->assertTrue($stream->nextTokenIsType('baz'));
        $this->assertFalse($stream->nextTokenIsType('badtype'));
        $stream->next();
        $this->assertTrue($stream->nextTokenIsType('dave'));
        $this->assertFalse($stream->nextTokenIsType('badtype'));
        $stream->next();
        $this->assertFalse($stream->nextTokenIsType('badtype'));
    }
    
    /**
     * Tests getting the current token when at the end returns null
     */
    public function testGettingCurrentTokenWhenAtEndReturnsNull() : void
    {
        $stream = new TokenStream([new Token('foo', 'bar')]);
        $stream->next();
        $this->assertNull($stream->getCurrentToken());
    }
    
    /**
     * Tests getting the current token always returns the current token
     */
    public function testGettingCurrentAlwaysReturnsCurrentToken() : void
    {
        $token1 = new Token('foo', 'bar');
        $token2 = new Token('baz', 'blah');
        $stream = new TokenStream([$token1, $token2]);
        $this->assertSame($token1, $stream->getCurrentToken());
        $stream->next();
        $this->assertSame($token2, $stream->getCurrentToken());
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
}
