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
 * Defines a token stream
 */
final class TokenStream
{
    /** @var Token|null The current token, or null if the cursor does not point to a token */
    public ?Token $current {
        get => \count($this->tokens) > $this->cursor ? $this->tokens[$this->cursor] : null;
    }
    /** @var int The length of the stream */
    public readonly int $length;
    /** @var int The current cursor */
    private int $cursor = 0;

    /**
     * @param list<Token> $tokens The list of tokens
     */
    public function __construct(public readonly array $tokens)
    {
        $this->length = \count($this->tokens);
    }

    /**
     * Tests the current token to see if it matches the input type, and optionally the input value, and throws an
     * exception if the token did not match
     *
     * @param TokenType $type The type to check for
     * @param mixed $value The optional value to match against
     * @param string|null $message The exception message to use, otherwise a default one is generated
     *      Any '%s' in the message is first populated with the expected type, and then with the expected value
     * @throws UnexpectedTokenException Thrown if the current token didn't match the expected type and value
     */
    public function expect(TokenType $type, mixed $value = null, ?string $message = null): void
    {
        if ($this->test($type, $value)) {
            return;
        }

        $currentToken = $this->current;

        if ($message === null) {
            // Let's create a default message
            $formattedMessage = \sprintf(
                'Expected token type %s%s',
                $type->name,
                $value === null ? '' : " with value \"$value\""
            );

            if ($currentToken === null) {
                $formattedMessage .= ', got end of stream';
            } else {
                $formattedMessage .= \sprintf(
                    ', got %s with value \"%s\"',
                    $currentToken->type->name,
                    (string)$currentToken->value
                );
            }
        } else {
            $formattedMessage = \sprintf(
                $message,
                $currentToken === null ? TokenType::Eof->name : $currentToken->type->name,
                (string)($currentToken === null ? 'end of stream' : $currentToken->value)
            );
        }

        throw new UnexpectedTokenException($formattedMessage);
    }

    /**
     * Gets the next token, if there is one
     *
     * @return Token|null The next token, if there is one, otherwise false
     */
    public function next(): ?Token
    {
        return \count($this->tokens) > ++$this->cursor ? $this->tokens[$this->cursor] : null;
    }

    /**
     * Gets the next token if the current one matches the input type, and optionally performs a value check
     *
     * @param TokenType $type The type to check for
     * @param mixed $value The optional value to match against
     * @return bool True if the current token is of the input type, otherwise false
     */
    public function nextIfType(TokenType $type, mixed $value = null): bool
    {
        $currentToken = $this->current;
        $typeMatches = $currentToken?->type === $type;

        if ($typeMatches && ($value === null || $currentToken?->value === $value)) {
            $this->next();

            return true;
        }

        return false;
    }

    /**
     * Peeks ahead at the next token
     *
     * @param int $lookahead The number of tokens to look ahead
     * @return Token|null The token
     */
    public function peek(int $lookahead = 1): ?Token
    {
        if ($this->cursor + $lookahead >= \count($this->tokens)) {
            return null;
        }

        return $this->tokens[$this->cursor + $lookahead];
    }

    /**
     * Tests the current token to see if it matches the input type, and optionally the input value
     *
     * @param TokenType $type The type to check for
     * @param mixed $value The optional value to match against
     * @return bool True if the current token is of the input type, otherwise false
     */
    public function test(TokenType $type, mixed $value = null): bool
    {
        $currentToken = $this->current;
        $typeMatches = $currentToken !== null && $currentToken->type === $type;

        return $typeMatches && ($value === null || $currentToken->value === $value);
    }
}
