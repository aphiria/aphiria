<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Compilers;

use Aphiria\Console\Output\Compilers\Elements\ElementRegistry;
use Aphiria\Console\Output\Compilers\Parsers\AstNode;
use Aphiria\Console\Output\Compilers\Parsers\IOutputParser;
use Aphiria\Console\Output\Compilers\Parsers\Lexers\IOutputLexer;
use Aphiria\Console\Output\Compilers\Parsers\Lexers\OutputLexer;
use Aphiria\Console\Output\Compilers\Parsers\OutputParser;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines an element compiler
 */
final class OutputCompiler implements IOutputCompiler
{
    /** @var ElementRegistry The registry of elements */
    private ElementRegistry $elements;
    /** @var IOutputLexer The lexer to use */
    private IOutputLexer $lexer;
    /** @var IOutputParser The parser to use */
    private IOutputParser $parser;

    /**
     * @param ElementRegistry $elements The registry of elements
     * @param IOutputLexer|null $lexer The lexer to use
     * @param IOutputParser|null $parser The parser to use
     */
    public function __construct(
        ElementRegistry $elements = null,
        IOutputLexer $lexer = null,
        IOutputParser $parser = null
    ) {
        $this->elements = $elements ?? new ElementRegistry();
        $this->lexer = $lexer ?? new OutputLexer();
        $this->parser = $parser ?? new OutputParser();
    }

    /**
     * @inheritdoc
     */
    public function compile(string $message, bool $includeStyles = true): string
    {
        if (!$includeStyles) {
            return strip_tags($message);
        }

        try {
            $tokens = $this->lexer->lex($message);
            $ast = $this->parser->parse($tokens);

            return $this->compileNode($ast);
        } catch (InvalidArgumentException $ex) {
            throw new RuntimeException('Failed to compile console output', 0, $ex);
        }
    }

    /**
     * Recursively compiles a node and its children
     *
     * @param AstNode $node The node to compile
     * @return string The compiled node
     * @throws RuntimeException Thrown if there was an error compiling the node
     * @throws InvalidArgumentException Thrown if there is no matching element for a particular tag
     */
    private function compileNode(AstNode $node): string
    {
        if ($node->isLeaf()) {
            // Don't compile a leaf that is a tag because that means it doesn't have any content
            if ($node->isTag()) {
                return '';
            }

            return (string)$node->value ?: '';
        }

        $output = '';

        foreach ($node->children as $childNode) {
            if ($node->isTag()) {
                $style = $this->elements->getElement((string)$node->value)->style;
                $output .= $style->format($this->compileNode($childNode));
            } else {
                $output .= $this->compileNode($childNode);
            }
        }

        return $output;
    }
}
