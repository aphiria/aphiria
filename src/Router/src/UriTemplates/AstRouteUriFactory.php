<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates;

use Aphiria\Routing\RouteActionParameterValues;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\Lexers\IUriTemplateLexer;
use Aphiria\Routing\UriTemplates\Lexers\LexingException;
use Aphiria\Routing\UriTemplates\Lexers\UnexpectedTokenException;
use Aphiria\Routing\UriTemplates\Lexers\UriTemplateLexer;
use Aphiria\Routing\UriTemplates\Parsers\AstNode;
use Aphiria\Routing\UriTemplates\Parsers\AstNodeType;
use Aphiria\Routing\UriTemplates\Parsers\IUriTemplateParser;
use Aphiria\Routing\UriTemplates\Parsers\UriTemplateParser;
use InvalidArgumentException;
use OutOfBoundsException;
use ReflectionException;

/**
 * Defines the route URI factory that uses an abstract syntax tree to create URIs
 */
final class AstRouteUriFactory implements IRouteUriFactory
{
    /**
     * @param RouteCollection $routes The list of routes
     * @param IUriTemplateParser $uriTemplateParser The URI template parser, or null if using the default one
     * @param IUriTemplateLexer $uriTemplateLexer The URI template lexer, or null if using the default one
     */
    public function __construct(
        private readonly RouteCollection $routes,
        private readonly IUriTemplateParser $uriTemplateParser = new UriTemplateParser(),
        private readonly IUriTemplateLexer $uriTemplateLexer = new UriTemplateLexer()
    ) {
    }

    /**
     * @inheritdoc
     */
    public function createRouteUri(string $routeName, array $routeVariables = []): string
    {
        if (($route = $this->routes->getNamedRoute($routeName)) === null) {
            throw new OutOfBoundsException("Route \"$routeName\" does not exist");
        }

        try {
            $routeActionParameters = new RouteActionParameterValues($route->action, $routeVariables);
        } catch (ReflectionException|InvalidArgumentException $ex) {
            throw new RouteUriCreationException("Failed to create route action parameters for {$route->action->className}::{$route->action->methodName}", 0, $ex);
        }

        try {
            $ast = $this->uriTemplateParser->parse($this->uriTemplateLexer->lex((string)$route->uriTemplate));
        } catch (LexingException $ex) {
            throw new RouteUriCreationException('Failed to lex URI template', 0, $ex);
        } catch (UnexpectedTokenException $ex) {
            throw new RouteUriCreationException('Failed to parse URI template', 0, $ex);
        }

        $host = null;
        $path = '';

        foreach ($ast->children as $childAstNode) {
            switch ($childAstNode->type) {
                case AstNodeType::Host:
                    $host = \implode('', \array_reverse($this->compileNode(true, $childAstNode, $routeActionParameters)));
                    break;
                case AstNodeType::Path:
                    $path = \implode('', $this->compileNode(false, $childAstNode, $routeActionParameters));
                    break;
            }
        }

        // See if we need to append any query string parameters from the unused variables
        $queryString = \http_build_query($routeActionParameters->queryStringParameters, encoding_type: PHP_QUERY_RFC3986);
        $path .= empty($queryString) ? '' : "?$queryString";

        if ($host === null) {
            return $path;
        }

        $host = ($route->uriTemplate->isHttpsOnly ? 'https://' : 'http://') . $host;
        $path = \ltrim($path, '/');

        if (empty($path)) {
            return $host;
        }

        return "$host/$path";
    }

    /**
     * Compiles a node of our AST tree into an array of compiled parts
     *
     * @param bool $compilingHost Whether or not we're compiling the host (vs the path)
     * @param AstNode $node The host AST node
     * @param RouteActionParameterValues $routeActionParameters The collection of route action parameters
     * @param bool $inUndefinedOptionalRoutePart Whether or not we're in an undefined optional route part
     * @return list<string> The list of compiled parts of the URI template
     */
    private function compileNode(
        bool $compilingHost,
        AstNode $node,
        RouteActionParameterValues $routeActionParameters,
        bool $inUndefinedOptionalRoutePart = false
    ): array {
        $parts = [];
        $inOptionalRoutePart = $node->type === AstNodeType::OptionalRoutePart;
        $optionalSegmentBuffer = '';

        foreach ($compilingHost ? \array_reverse($node->children) : $node->children as $childNode) {
            // If we're in an undefined optional route part, keep stepping through the tree and unset any variables
            // This prevents us from using the "bar" value in the case of [/:foo[/:bar]] if "foo" was not specified but "bar" was
            if ($inUndefinedOptionalRoutePart) {
                if ($childNode->type === AstNodeType::Variable) {
                    // Use up any unspecified parameter so that it does not get marked for use in the query string
                    $routeActionParameters->tryGetUnspecifiedParameterValue((string)$childNode->value, $routeVariable);
                } elseif ($childNode->type === AstNodeType::OptionalRoutePart) {
                    // Keep stepping through the tree, but don't bother capturing the path because we're not going to use any of it anyway
                    $this->compileNode($compilingHost, $childNode, $routeActionParameters, $inUndefinedOptionalRoutePart);
                }

                continue;
            }

            switch ($childNode->type) {
                case AstNodeType::SegmentDelimiter:
                    // If we're in an optional part, we don't want to include it unless it contains text or a defined variable
                    if ($inOptionalRoutePart) {
                        $optionalSegmentBuffer .= (string)$childNode->value;
                    } else {
                        $parts[] = (string)$childNode->value;
                    }

                    break;
                case AstNodeType::Text:
                    if (!empty($optionalSegmentBuffer)) {
                        $parts[] = $optionalSegmentBuffer;
                        $optionalSegmentBuffer = '';
                    }

                    $parts[] = (string)$childNode->value;
                    break;
                case AstNodeType::OptionalRoutePart:
                    $inOptionalRoutePart = true;
                    $parts = \array_merge($parts, $this->compileNode($compilingHost, $childNode, $routeActionParameters, $inUndefinedOptionalRoutePart));
                    break;
                case AstNodeType::Variable:
                    $routeVariable = null;

                    $routeActionParameters->tryGetRouteVariableParameterValue((string)$childNode->value, $routeVariable)
                    || $routeActionParameters->tryGetUnspecifiedParameterValue((string)$childNode->value, $routeVariable);

                    if ($routeVariable !== null) {
                        // Check if we've hit a defined variable, eg "[:foo.]bar.com", flush the buffer, eg "."
                        if (!empty($optionalSegmentBuffer)) {
                            $parts[] = $optionalSegmentBuffer;
                            $optionalSegmentBuffer = '';
                        }

                        $parts[] = $routeVariable;
                        break;
                    }

                    if (!$inOptionalRoutePart) {
                        throw new RouteUriCreationException("No value set for $childNode->value in " . ($compilingHost ? 'host' : 'path'));
                    }

                    // We have an undefined, optional variable
                    $inUndefinedOptionalRoutePart = true;
                    break;
            }
        }

        // The delimiters are in the host parts, so just glue it together with an empty string
        return $parts;
    }
}
