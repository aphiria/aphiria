<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Lexers;

/**
 * Defines the lexer for URI templates
 */
final class UriTemplateLexer implements IUriTemplateLexer
{
    /** @const The maximum length of a variable name */
    private const int VARIABLE_NAME_MAX_LENGTH = 32;
    /** @const The regex for finding a number */
    private const string NUMBER_REGEX = '/\d+(?:\.\d+)?/A';
    /** @const The list of punctuation characters */
    private const string PUNCTUATION = '.()[],/';
    /** @const The regex for finding a quoted string */
    private const string QUOTED_STRING_REGEX = '/\s*"([^#"\\\\]*(?:\\\\.[^#"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'\s*/A';
    /** @const The regex for finding a variable name and default value */
    private const string VARIABLE_NAME_REGEX = '/:[a-zA-Z_][\w]*/A';

    /**
     * @inheritdoc
     */
    public function lex(string $uriTemplate): TokenStream
    {
        $cursor = 0;
        $templateLength = \mb_strlen($uriTemplate);
        $tokens = [];
        $textBuffer = '';

        while ($cursor < $templateLength) {
            $matches = [];

            if ($uriTemplate[$cursor] === ' ') {
                $cursor++;
            } elseif (\str_contains(self::PUNCTUATION, $uriTemplate[$cursor])) {
                self::flushTextBuffer($textBuffer, $tokens);
                self::lexPunctuation($uriTemplate[$cursor], $tokens, $cursor);
            } elseif (\preg_match(self::VARIABLE_NAME_REGEX, $uriTemplate, $matches, 0, $cursor) === 1) {
                self::flushTextBuffer($textBuffer, $tokens);
                self::lexVariableName($matches[0], $tokens, $cursor);
            } elseif (\preg_match(self::NUMBER_REGEX, $uriTemplate, $matches, 0, $cursor) === 1) {
                self::flushTextBuffer($textBuffer, $tokens);
                self::lexNumber($matches[0], $tokens, $cursor);
            } elseif (\preg_match(self::QUOTED_STRING_REGEX, $uriTemplate, $matches, 0, $cursor) === 1) {
                self::flushTextBuffer($textBuffer, $tokens);
                self::lexQuotedString($matches[0], $tokens, $cursor);
            } else {
                self::lexTextChar($uriTemplate[$cursor], $textBuffer, $cursor);
            }
        }

        // In case there's anything left in the buffer, flush it
        self::flushTextBuffer($textBuffer, $tokens);

        return new TokenStream($tokens);
    }

    /**
     * Flushes any text from the buffer
     *
     * @param string $textBuffer The current text buffer
     * @param list<Token> $tokens The list of tokens to add to
     */
    private static function flushTextBuffer(string &$textBuffer, array &$tokens): void
    {
        if ($textBuffer !== '') {
            $tokens[] = new Token(TokenType::Text, $textBuffer);
            $textBuffer = '';
        }
    }

    /**
     * Lexes a number lexeme
     *
     * @param string $number The lexeme to add
     * @param list<Token> $tokens The list of tokens to add to
     * @param int $cursor The current cursor
     */
    private static function lexNumber(string $number, array &$tokens, int &$cursor): void
    {
        $floatVal = (float)$number;
        $intVal = (int)$number;

        // Determine if this was a float or not
        if ($floatVal && $intVal != $floatVal) {
            $tokens[] = new Token(TokenType::Number, $floatVal);
        } else {
            $tokens[] = new Token(TokenType::Number, $intVal);
        }

        $cursor += \mb_strlen($number);
    }

    /**
     * Lexes a punctuation lexeme
     *
     * @param string $punctuation The lexeme to add
     * @param list<Token> $tokens The list of tokens to add to
     * @param int $cursor The current cursor
     */
    private static function lexPunctuation(string $punctuation, array &$tokens, int &$cursor): void
    {
        $tokens[] = new Token(TokenType::Punctuation, $punctuation);
        $cursor++;
    }

    /**
     * Lexes a quoted string lexeme
     *
     * @param string $quotedString The lexeme to add
     * @param list<Token> $tokens The list of tokens to add to
     * @param int $cursor The current cursor
     */
    private static function lexQuotedString(string $quotedString, array &$tokens, int &$cursor): void
    {
        $tokens[] = new Token(TokenType::QuotedString, \stripcslashes(\substr(\trim($quotedString), 1, -1)));
        $cursor += \mb_strlen($quotedString);
    }

    /**
     * Lexes a text character lexeme
     *
     * @param string $char The lexeme to add
     * @param string $textBuffer The text buffer to add to
     * @param int $cursor The current cursor
     */
    private static function lexTextChar(string $char, string &$textBuffer, int &$cursor): void
    {
        $textBuffer .= $char;
        $cursor++;
    }

    /**
     * Lexes a variable name lexeme
     *
     * @param string $variableName The lexeme to add
     * @param list<Token> $tokens The list of tokens to add to
     * @param int $cursor The current cursor
     * @throws LexingException Thrown if the variable name exceeded the max length
     */
    private static function lexVariableName(string $variableName, array &$tokens, int &$cursor): void
    {
        // Remove the colon before the variable name
        $trimmedVariableName = \substr($variableName, 1);

        if (\mb_strlen($trimmedVariableName) > self::VARIABLE_NAME_MAX_LENGTH) {
            throw new LexingException("Variable name \"$trimmedVariableName\" exceeds the max length limit");
        }

        $tokens[] = new Token(TokenType::Variable, $trimmedVariableName);
        // We have to advance the cursor the length of the untrimmed variable name
        $cursor += \mb_strlen($variableName);
    }
}
