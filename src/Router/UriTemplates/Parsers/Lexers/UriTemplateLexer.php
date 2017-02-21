<?php
namespace Opulence\Router\UriTemplates\Parsers\Lexers;

use InvalidArgumentException;
use Opulence\Router\UriTemplates\Parsers\Lexers\Tokens\Token;
use Opulence\Router\UriTemplates\Parsers\Lexers\Tokens\TokenTypes;

/**
 * Defines the lexer for URI templates
 */
class UriTemplateLexer implements IUriTemplateLexer
{
    /** @var string The state representing literal text */
    private const STATE_TEXT = "TEXT";
    /** @var string The state representing a variable */
    private const STATE_VARIABLE = "VARIABLE";
    /** @var string The regex for finding a number */
    private const NUMBER_REGEX = '#[0-9]+(?:\.[0-9]+)?#A';
    /** @var string The regex for finding a quoted string */
    private const QUOTED_STRING_REGEX = '/"([^#"\\\\]*(?:\\\\.[^#"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/As';
    /** @var string The regex for finding a variable name and default value */
    private const VARIABLE_NAME_REGEX = '#([a-zA-Z_][a-zA-Z0-9_]*)(?:=([^:\[\]/]+))?#';
    /** @var string The regex for finding a variable rule slug */
    private const VARIABLE_RULE_SLUG_REGEX = '#\s*[a-zA-Z_][a-zA-Z0-9_]*\s*#';
    /** @var The maximum length of a variable name */
    private const VARIABLE_NAME_MAX_LENGTH = 32;
    
    /**
     * @inheritdoc
     */
    public function lex(string $template) : array
    {
        $cursor = 0;
        $templateLength = mb_strlen($template);
        $states = [self::STATE_TEXT];
        $tokens = [];
        
        while ($cursor < $templateLength) {
            switch (end($states)) {
                case self::STATE_VARIABLE:
                    $this->lexVariable($template, $cursor, $states, $tokens);
                    break;
                default:
                    $this->lexText($template, $cursor, $states, $tokens);
            }
        }
        
        return $tokens;
    }
    
    /**
     * Lexes plain text
     * 
     * @param string $template The template being lexed
     * @param int $cursor The current cursor position
     * @param array $states The stack of states the lexer is in
     * @param array $tokens The list of tokens lexed from the template
     */
    private function lexText(string $template, int &$cursor, array &$states, array &$tokens) : void
    {
        $buffer = '';
        $templateLength = mb_strlen($template);
        
        while ($cursor < $templateLength && $template[$cursor] !== ':') {
            $buffer .= $template[$cursor];
            $cursor++;
        }
        
        if ($buffer !== '') {
            $tokens[] = new Token(TokenTypes::T_TEXT, $buffer);
        }
        
        // We must've exited the loop because we hit a variable
        if ($cursor < $templateLength) {
            // Increment for the colon
            $cursor++;
            $states[] = self::STATE_VARIABLE;
        }
    }
    
    /**
     * Lexes a variable, eg ':year(between(1900, 2000))'
     * 
     * @param string $template The template being lexed
     * @param int $cursor The current cursor position
     * @param array $tokens The list of tokens lexed from the template
     */
    private function lexVariable(string $template, int &$cursor, array &$states, array &$tokens) : void
    {
        $matches = [];
        
        if (preg_match(self::VARIABLE_NAME_REGEX, $template, $matches, null, $cursor) !== 1) {
            throw new InvalidArgumentException("Variable name can't be empty");
        }
        
        $variableName = $matches[1];
        $defaultValue = $matches[2] ?? null;
        
        if (strlen($variableName) > self::VARIABLE_NAME_MAX_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'Variable name "%s" cannot be longer than %d characters. Please use a shorter name.',
                $variableName,
                self::VARIABLE_NAME_MAX_LENGTH)
            );
        }
        
        $tokens[] = new Token(TokenTypes::T_VARIABLE_NAME, $variableName);
        
        if ($defaultValue !== null) {
            $tokens[] = new Token(TokenTypes::T_PUNCTUATION, '=');
            $tokens[] = new Token(TokenTypes::T_TEXT, $defaultValue);
        }
        
        $cursor += mb_strlen($matches[0]);
        $templateLength = mb_strlen($template);
        
        if ($cursor < $templateLength && $template[$cursor] === '(') {
            $this->lexVariableRuleGroup($template, $cursor, $tokens);
        }
        
        array_pop($states);
    }
    
    /**
     * Lexes a variable, eg 'between(1, 10)'
     * 
     * @param string $template The template being lexed
     * @param int $cursor The current cursor position
     * @param array $tokens The list of tokens lexed from the template
     */
    private function lexVariableRule(string $template, int &$cursor, array &$tokens) : void
    {
        $matches = [];
        $templateLength = mb_strlen($template);
        
        if (preg_match(self::VARIABLE_RULE_SLUG_REGEX, $template, $matches, null, $cursor) !== 1) {
            throw new InvalidArgumentException("Invalid rule slug for template $template");
        }
        
        // We order it this way so we do not lose the untrimmed length
        $cursor += mb_strlen($matches[0]);
        $slug = trim($matches[0]);
        $tokens[] = new Token(TokenTypes::T_VARIABLE_RULE_SLUG, $slug);
        
        if ($template[$cursor] === '(') {
            $tokens[] = new Token(TokenTypes::T_PUNCTUATION, '(');
            $cursor++;
            $this->lexVariableRuleParameters($template, $cursor, $tokens);
            $tokens[] = new Token(TokenTypes::T_PUNCTUATION, ')');
            $cursor++;
            
            // Iterate until we are beyond any whitespace
            while ($cursor < $templateLength && $template[$cursor] === ' ') {
                $cursor++;
            }
        }
    }
    
    /**
     * Lexes a variable rule group, eg '(int, between(1, 100))'
     * 
     * @param string $template The template being lexed
     * @param int $cursor The current cursor position
     * @param array $tokens The list of tokens lexed from the template
     */
    private function lexVariableRuleGroup(string $template, int &$cursor, array &$tokens) : void
    {
        $tokens[] = new Token(TokenTypes::T_PUNCTUATION, '(');
        $cursor++;
        $templateLength = mb_strlen($template);
        $moreRulesToLex = false;

        do {
            $this->lexVariableRule($template, $cursor, $tokens);
            $moreRulesToLex = $cursor < $templateLength && $template[$cursor] === ',';

            // If there's a comma after this rule, then we're expecting another rule
            if ($moreRulesToLex) {
                $tokens[] = new Token(TokenTypes::T_PUNCTUATION, ',');
                $cursor++;
            }

        } while ($moreRulesToLex);

        if ($template[$cursor] !== ')') {
            throw new InvalidArgumentException('Unclosed variable rule');
        }

        $tokens[] = new Token(TokenTypes::T_PUNCTUATION, ')');
        $cursor++;
    }
    
    /**
     * Lexes a variable rule parameters, eg '1, 100'
     * 
     * @param string $template The template being lexed
     * @param int $cursor The current cursor position
     * @param array $tokens The list of tokens lexed from the template
     */
    private function lexVariableRuleParameters(string $template, int &$cursor, array &$tokens) : void
    {
        $templateLength = mb_strlen($template);

        while ($cursor < $templateLength && $template[$cursor] !== ')') {
            if ($template[$cursor] === ' ') {
                // Ignore whitespace
                $cursor++;
            } elseif (preg_match(self::QUOTED_STRING_REGEX, $template, $matches, null, $cursor) === 1) {
                $tokens[] = new Token(TokenTypes::T_QUOTED_STRING, stripcslashes(substr($matches[0], 1, -1)));
                $cursor += mb_strlen($matches[0]);
            } elseif (preg_match(self::NUMBER_REGEX, $template, $matches, null, $cursor) === 1) {
                $tokens[] = new Token(TokenTypes::T_NUMBER, $matches[0]);
                $cursor += mb_strlen($matches[0]);
            } elseif ($template[$cursor] === ',') {
                $tokens[] = new Token(TokenTypes::T_PUNCTUATION, ',');
                $cursor++;
            } else {
                throw new InvalidArgumentException("Unexpected \"{$template[$cursor]}\" in rule \"$slug\"");
            }
        }

        // If we exited the loop because we ran out of characters, then the rule wasn't defined correctly
        if ($cursor >= $templateLength) {
            throw new InvalidArgumentException("Rule \"$slug\" is improperly formatted");
        }
    }
}
