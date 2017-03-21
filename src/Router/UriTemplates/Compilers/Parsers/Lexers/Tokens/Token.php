<?php
namespace Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\Tokens;

/**
 * Defines a token created by a lexer
 */
class Token
{
    /** @var string The token type */
    private $type = '';
    /** @var mixed The token value */
    private $value = null;

    /**
     * @param string $type The token type
     * @param mixed $value The token value
     */
    public function __construct(string $type, $value)
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
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
