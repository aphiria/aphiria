<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses\Compilers;

use Aphiria\Console\Responses\Compilers\Elements\Style;
use Aphiria\Console\Responses\Compilers\Lexers\IResponseLexer;
use Aphiria\Console\Responses\Compilers\Lexers\ResponseLexer;
use Aphiria\Console\Responses\Compilers\Parsers\IResponseParser;
use Aphiria\Console\Responses\Compilers\Parsers\Nodes\Node;
use Aphiria\Console\Responses\Compilers\Parsers\ResponseParser;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines an element compiler
 */
final class ResponseCompiler implements IResponseCompiler
{
    /** @var IResponseLexer The lexer to use */
    private $lexer;
    /** @var IResponseParser The parser to use */
    private $parser;
    /** @var Style[] The list of elements registered to the compiler */
    private $elements = [];
    /** @var bool Whether or not messages should be styled */
    private $isStyled = true;

    /**
     * @param IResponseLexer|null $lexer The lexer to use
     * @param IResponseParser|null $parser The parser to use
     */
    public function __construct(IResponseLexer $lexer = null, IResponseParser $parser = null)
    {
        $this->lexer = $lexer ?? new ResponseLexer();
        $this->parser = $parser ?? new ResponseParser();
        // Register built-in elements
        (new ElementRegistrant())->registerElements($this);
    }

    /**
     * @inheritdoc
     */
    public function compile(string $message): string
    {
        if (!$this->isStyled) {
            return strip_tags($message);
        }

        try {
            $tokens = $this->lexer->lex($message);
            $ast = $this->parser->parse($tokens);

            return $this->compileNode($ast->getRootNode());
        } catch (InvalidArgumentException $ex) {
            throw new RuntimeException('Failed to compile console response', 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerElement(string $name, Style $style): void
    {
        $this->elements[$name] = $style;
    }

    /**
     * @inheritdoc
     */
    public function setStyled(bool $isStyled): void
    {
        $this->isStyled = $isStyled;
    }

    /**
     * Recursively compiles a node and its children
     *
     * @param Node $node The node to compile
     * @return string The compiled node
     * @throws RuntimeException Thrown if there was an error compiling the node
     * @throws InvalidArgumentException Thrown if there is no matching element for a particular tag
     */
    private function compileNode(Node $node): string
    {
        if ($node->isLeaf()) {
            // Don't compile a leaf that is a tag because that means it doesn't have any content
            if ($node->isTag()) {
                return '';
            }

            return $node->value ?: '';
        }

        $output = '';

        foreach ($node->children as $childNode) {
            if ($node->isTag()) {
                if (!isset($this->elements[$node->value])) {
                    throw new InvalidArgumentException("No style registered for element \"{$node->value}\"");
                }

                $style = $this->elements[$node->value];
                $output .= $style->format($this->compileNode($childNode));
            } else {
                $output .= $this->compileNode($childNode);
            }
        }

        return $output;
    }
}
