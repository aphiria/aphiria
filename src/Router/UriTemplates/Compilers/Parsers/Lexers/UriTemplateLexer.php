<?php
namespace Opulence\Router\UriTemplates\Compilers\Parsers\Lexers;

use InvalidArgumentException;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\Tokens\Token;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenStream;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenTypes;

/**
 * Defines the lexer for URI templates
 */
class UriTemplateLexer implements IUriTemplateLexer
{
    /** @var string The list of punctuation characters */
    private const PUNCTUATION = '()[],=';
    /** @var string The regex for finding a number */
    private const NUMBER_REGEX = '/[0-9]+(?:\.[0-9]+)?/A';
    /** @var string The regex for finding a quoted string */
    private const QUOTED_STRING_REGEX = '/\s*"([^#"\\\\]*(?:\\\\.[^#"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'\s*/A';
    /** @var string The regex for finding a variable name and default value */
    private const VARIABLE_NAME_REGEX = '/:([a-zA-Z_][a-zA-Z0-9_]*)/A';
    /** @var The maximum length of a variable name */
    private const VARIABLE_NAME_MAX_LENGTH = 32;

    /**
     * @inheritdoc
     */
    public function lex(string $template) : TokenStream
    {
        $cursor = 0;
        $templateLength = mb_strlen($template);
        $tokens = [];
        $textBuffer = '';

        while ($cursor < $templateLength) {
            $matches = [];

            if (strpos(self::PUNCTUATION, $template[$cursor]) !== false) {
                $this->flushTextBuffer($textBuffer, $tokens);
                $tokens[] = new Token(TokenTypes::T_PUNCTUATION, $template[$cursor]);
                $cursor++;
            } elseif (preg_match(self::VARIABLE_NAME_REGEX, $template, $matches, 0, $cursor) === 1) {
                $this->flushTextBuffer($textBuffer, $tokens);
                $variableName = $matches[1];

                if (mb_strlen($variableName) > self::VARIABLE_NAME_MAX_LENGTH) {
                    throw new InvalidArgumentException("Variable name \"$variableName\" exceeds the max length limit");
                }

                $tokens[] = new Token(TokenTypes::T_VARIABLE, $matches[1]);
                $cursor += mb_strlen($matches[0]);
            } elseif (preg_match(self::NUMBER_REGEX, $template, $matches, 0, $cursor) === 1) {
                $this->flushTextBuffer($textBuffer, $tokens);
                $floatVal = floatval($matches[0]);
                $intVal = intval($matches[0]);

                // Determine if this was a float or not
                if ($floatVal && $intVal !== $floatVal) {
                    $tokens[] = new Token(TokenTypes::T_NUMBER, $floatVal);
                } else {
                    $tokens[] = new Token(TokenTypes::T_NUMBER, $intVal);
                }

                $cursor += mb_strlen($matches[0]);
            } elseif (preg_match(self::QUOTED_STRING_REGEX, $template, $matches, 0, $cursor) === 1) {
                $this->flushTextBuffer($textBuffer, $tokens);
                $tokens[] = new Token(TokenTypes::T_QUOTED_STRING, stripcslashes(substr(trim($matches[0]), 1, -1)));
                $cursor += mb_strlen($matches[0]);
            } else {
                $textBuffer .= $template[$cursor];
                $cursor++;
            }
        }

        // In case there's anything left in the buffer, flush it
        $this->flushTextBuffer($textBuffer, $tokens);

        return new TokenStream($tokens);
    }

    /**
     * Flushes any text from the buffer
     *
     * @param string $textBuffer The current text buffer
     * @param array $tokens The current list of tokens
     */
    private function flushTextBuffer(string &$textBuffer, array &$tokens) : void
    {
        if ($textBuffer !== '') {
            $tokens[] = new Token(TokenTypes::T_TEXT, $textBuffer);
            $textBuffer = '';
        }
    }
}
