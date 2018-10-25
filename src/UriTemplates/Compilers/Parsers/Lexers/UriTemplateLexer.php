<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers;

use InvalidArgumentException;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers\Tokens\Token;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenStream;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenTypes;

/**
 * Defines the lexer for URI templates
 */
class UriTemplateLexer implements IUriTemplateLexer
{
    /** @const The list of punctuation characters */
    private const PUNCTUATION = '()[],=';
    /** @const The regex for finding a number */
    private const NUMBER_REGEX = '/\d+(?:\.\d+)?/A';
    /** @const The regex for finding a quoted string */
    private const QUOTED_STRING_REGEX = '/\s*"([^#"\\\\]*(?:\\\\.[^#"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'\s*/A';
    /** @const The regex for finding a variable name and default value */
    private const VARIABLE_NAME_REGEX = '/:[a-zA-Z_][\w]*/A';
    /** @const The maximum length of a variable name */
    private const VARIABLE_NAME_MAX_LENGTH = 32;

    /**
     * @inheritdoc
     */
    public function lex(string $template): TokenStream
    {
        $cursor = 0;
        $templateLength = mb_strlen($template);
        $tokens = [];
        $textBuffer = '';

        while ($cursor < $templateLength) {
            $matches = [];

            if ($template[$cursor] === ' ') {
                $cursor++;
            } elseif (strpos(self::PUNCTUATION, $template[$cursor]) !== false) {
                $this->flushTextBuffer($textBuffer, $tokens);
                $this->lexPunctuation($template[$cursor], $tokens, $cursor);
            } elseif (preg_match(self::VARIABLE_NAME_REGEX, $template, $matches, 0, $cursor) === 1) {
                $this->flushTextBuffer($textBuffer, $tokens);
                $this->lexVariableName($matches[0], $tokens, $cursor);
            } elseif (preg_match(self::NUMBER_REGEX, $template, $matches, 0, $cursor) === 1) {
                $this->flushTextBuffer($textBuffer, $tokens);
                $this->lexNumber($matches[0], $tokens, $cursor);
            } elseif (preg_match(self::QUOTED_STRING_REGEX, $template, $matches, 0, $cursor) === 1) {
                $this->flushTextBuffer($textBuffer, $tokens);
                $this->lexQuotedString($matches[0], $tokens, $cursor);
            } else {
                $this->lexTextChar($template[$cursor], $textBuffer, $cursor);
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
     * @param Token[] $tokens The list of tokens to add to
     */
    private function flushTextBuffer(string &$textBuffer, array &$tokens): void
    {
        if ($textBuffer !== '') {
            $tokens[] = new Token(TokenTypes::T_TEXT, $textBuffer);
            $textBuffer = '';
        }
    }

    /**
     * Lexes a number lexeme
     *
     * @param string $number The lexeme to add
     * @param Token[] $tokens The list of tokens to add to
     * @param int $cursor The current cursor
     */
    private function lexNumber(string $number, array &$tokens, int &$cursor): void
    {
        $floatVal = (float)$number;
        $intVal = (int)$number;

        // Determine if this was a float or not
        if ($floatVal && $intVal != $floatVal) {
            $tokens[] = new Token(TokenTypes::T_NUMBER, $floatVal);
        } else {
            $tokens[] = new Token(TokenTypes::T_NUMBER, $intVal);
        }

        $cursor += mb_strlen($number);
    }

    /**
     * Lexes a punctuation lexeme
     *
     * @param string $punctuation The lexeme to add
     * @param Token[] $tokens The list of tokens to add to
     * @param int $cursor The current cursor
     */
    private function lexPunctuation(string $punctuation, array &$tokens, int &$cursor): void
    {
        $tokens[] = new Token(TokenTypes::T_PUNCTUATION, $punctuation);
        $cursor++;
    }

    /**
     * Lexes a quoted string lexeme
     *
     * @param string $quotedString The lexeme to add
     * @param Token[] $tokens The list of tokens to add to
     * @param int $cursor The current cursor
     */
    private function lexQuotedString(string $quotedString, array &$tokens, int &$cursor): void
    {
        $tokens[] = new Token(TokenTypes::T_QUOTED_STRING, stripcslashes(substr(trim($quotedString), 1, -1)));
        $cursor += mb_strlen($quotedString);
    }

    /**
     * Lexes a text character lexeme
     *
     * @param string $char The lexeme to add
     * @param string $textBuffer The text buffer to add to
     * @param int $cursor The current cursor
     */
    private function lexTextChar(string $char, string &$textBuffer, int &$cursor): void
    {
        $textBuffer .= $char;
        $cursor++;
    }

    /**
     * Lexes a variable name lexeme
     *
     * @param string $variableName The lexeme to add
     * @param Token[] $tokens The list of tokens to add to
     * @param int $cursor The current cursor
     */
    private function lexVariableName(string $variableName, array &$tokens, int &$cursor): void
    {
        // Remove the colon before the variable name
        $trimmedVariableName = substr($variableName, 1);

        if (mb_strlen($trimmedVariableName) > self::VARIABLE_NAME_MAX_LENGTH) {
            throw new InvalidArgumentException("Variable name \"$trimmedVariableName\" exceeds the max length limit");
        }

        $tokens[] = new Token(TokenTypes::T_VARIABLE, $trimmedVariableName);
        // We have to advance the cursor the length of the untrimmed variable name
        $cursor += mb_strlen($variableName);
    }
}
