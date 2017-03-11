<?php
namespace Opulence\Router\UriTemplates\Parsers\Lexers\Tokens;

/**
 * Defines a token stream
 */
class TokenStream
{
    /** @var Token[] The list of tokens */
    private $tokens = [];
    /** @var int The current cursor */
    private $cursor = 0;
    
    /**
     * @param Token[] $tokens The list of tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }
    
    /**
     * Gets the current token
     * 
     * @return Token|null The current token
     */
    public function getCurrentToken() : ?Token
    {
        return count($this->tokens) > $this->cursor ? $this->tokens[$this->cursor] : null;
    }
    
    /**
     * Gets the next token, if there is one
     * 
     * @return Token|null The next token, if there is one, otherwise false
     */
    public function next() : ?Token
    {
        return count($this->tokens) > ++$this->cursor ? $this->tokens[$this->cursor] : null;
    }
    
    /**
     * Checks whether the next token is of a particular type
     * 
     * @param string $type The type to check for
     * @return bool True if the next token is of the input type, otherwise false
     */
    public function nextTokenIsType(string $type) : bool
    {
        $nextToken = $this->peek();
        
        return $nextToken !== null && $nextToken->getType() === $type;
    }
    
    /**
     * Peeks ahead at the next token
     * 
     * @param int $lookahead The number of tokens to look ahead
     * @return Token|null The token
     */
    public function peek(int $lookahead = 1) : ?Token
    {
        if ($this->cursor + $lookahead >= count($this->tokens)) {
            return null;
        }
        
        return $this->tokens[$this->cursor + $lookahead];
    }
}
