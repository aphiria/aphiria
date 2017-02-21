<?php
namespace Opulence\Router\UriTemplates\Parsers\Lexers\Tokens;

/**
 * Defines a token created by a lexer
 */
class Token
{
    /** @var string The token type */
    private $type = '';
    /** @var string The token value */
    private $value = '';

    /**
     * @param string $type The token type
     * @param string $value The token value
     */
    public function __construct(string $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }
}
