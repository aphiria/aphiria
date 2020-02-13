<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates;

use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\Lexers\IUriTemplateLexer;
use Aphiria\Routing\UriTemplates\Lexers\UriTemplateLexer;
use Aphiria\Routing\UriTemplates\Parsers\AstNode;
use Aphiria\Routing\UriTemplates\Parsers\AstNodeTypes;
use Aphiria\Routing\UriTemplates\Parsers\IUriTemplateParser;
use Aphiria\Routing\UriTemplates\Parsers\UriTemplateParser;
use OutOfBoundsException;

/**
 * Defines the route URI factory that uses an abstract syntax tree to create URIs
 */
final class AstRouteUriFactory implements IRouteUriFactory
{
    /** @var RouteCollection The list of routes */
    private RouteCollection $routes;
    /** @var IUriTemplateLexer The URI template lexer */
    private IUriTemplateLexer $uriTemplateLexer;
    /** @var IUriTemplateParser The URI template parser */
    private IUriTemplateParser $uriTemplateParser;

    /**
     * @param RouteCollection $routes The list of routes
     * @param IUriTemplateParser $uriTemplateParser The URI template parser
     * @param IUriTemplateLexer $uriTemplateLexer The URI template lexer
     */
    public function __construct(
        RouteCollection $routes,
        IUriTemplateParser $uriTemplateParser = null,
        IUriTemplateLexer $uriTemplateLexer = null
    ) {
        $this->routes = $routes;
        $this->uriTemplateParser = $uriTemplateParser ?? new UriTemplateParser();
        $this->uriTemplateLexer = $uriTemplateLexer ?? new UriTemplateLexer();
    }

    /**
     * @inheritdoc
     */
    public function createRouteUri(string $routeName, array $routeVars = []): string
    {
        if (($route = $this->routes->getNamedRoute($routeName)) === null) {
            throw new OutOfBoundsException("Route \"$routeName\" does not exist");
        }

        $ast = $this->uriTemplateParser->parse($this->uriTemplateLexer->lex((string)$route->uriTemplate));
        $host = $path = null;

        foreach ($ast->children as $childAstNode) {
            switch ($childAstNode->type) {
                case AstNodeTypes::HOST:
                    $host = $this->compileHost($childAstNode, $routeVars);
                    break;
                case AstNodeTypes::PATH:
                    $path = $this->compilePath($childAstNode, $routeVars);
                    break;
            }
        }

        if ($host === null) {
            return $path;
        }

        $host = ($route->uriTemplate->isHttpsOnly ? 'https://' : 'http://') . $host;
        $path = ltrim($path, '/');

        if (empty($path)) {
            return $host;
        }

        return "$host/$path";
    }

    /**
     * Compiles the host from the AST
     *
     * @param AstNode $node The host AST node
     * @param array $routeVars The route variables
     * @return string The compiled host portion of the URI
     */
    private function compileHost(AstNode $node, array &$routeVars): string
    {
        $hostParts = [];
        $inOptionalRoutePart = $node->type === AstNodeTypes::OPTIONAL_ROUTE_PART;
        $optionalSegmentBuffer = '';

        foreach (array_reverse($node->children) as $childNode) {
            switch ($childNode->type) {
                case AstNodeTypes::SEGMENT_DELIMITER:
                    // If we're in an optional part, we don't want to include it unless it contains text or a defined variable
                    if ($inOptionalRoutePart) {
                        $optionalSegmentBuffer .= $childNode->value;
                    } else {
                        $hostParts[] = $childNode->value;
                    }

                    break;
                case AstNodeTypes::TEXT:
                    if (!empty($optionalSegmentBuffer)) {
                        $hostParts[] = $optionalSegmentBuffer;
                        $optionalSegmentBuffer = '';
                    }

                    $hostParts[] = $childNode->value;
                    break;
                case AstNodeTypes::OPTIONAL_ROUTE_PART:
                    $inOptionalRoutePart = true;
                    $hostParts[] = $this->compileHost($childNode, $routeVars);
                    break;
                case AstNodeTypes::VARIABLE:
                    if (isset($routeVars[$childNode->value])) {
                        if (!empty($optionalSegmentBuffer)) {
                            // We've hit a defined variable, eg "[:foo.]bar.com", flush the buffer, eg "."
                            $hostParts[] = $optionalSegmentBuffer;
                            $optionalSegmentBuffer = '';
                        }

                        $hostParts[] = $routeVars[$childNode->value];
                        unset($routeVars[$childNode->value]);
                        break;
                    }

                    if (!$inOptionalRoutePart) {
                        throw new RouteUriCreationException("No value set for {$childNode->value} in host");
                    }

                    // We have an undefined, optional variable.  So, let's just not include the optional part at all.
                    break 2;
            }
        }

        // The delimiters are in the host parts, so just glue it together with an empty string
        return implode('', \array_reverse($hostParts));
    }

    /**
     * Compiles the path from the AST
     *
     * @param AstNode $node The path AST node
     * @param array $routeVars The route variables
     * @return string The compiled path portion of the URI
     */
    private function compilePath(AstNode $node, array &$routeVars): string
    {
        $path = '';
        $inOptionalRoutePart = $node->type === AstNodeTypes::OPTIONAL_ROUTE_PART;
        $optionalSegmentBuffer = '';

        foreach ($node->children as $childNode) {
            switch ($childNode->type) {
                case AstNodeTypes::SEGMENT_DELIMITER:
                    // If we're in an optional part, we don't want to include it unless it contains text or a defined variable
                    if ($inOptionalRoutePart) {
                        $optionalSegmentBuffer .= $childNode->value;
                    } else {
                        $path .= $childNode->value;
                    }

                    break;
                case AstNodeTypes::TEXT:
                    if (!empty($optionalSegmentBuffer)) {
                        $path .= $optionalSegmentBuffer;
                        $optionalSegmentBuffer = '';
                    }

                    $path .= $childNode->value;
                    break;
                case AstNodeTypes::OPTIONAL_ROUTE_PART:
                    $path .= $this->compilePath($childNode, $routeVars);
                    break;
                case AstNodeTypes::VARIABLE:
                    if (isset($routeVars[$childNode->value])) {
                        // We've hit a defined variable, eg "/foo[/:bar]", flush the buffer, eg "/"
                        if (!empty($optionalSegmentBuffer)) {
                            $path .= $optionalSegmentBuffer;
                            $optionalSegmentBuffer = '';
                        }

                        $path .= $routeVars[$childNode->value];
                        unset($routeVars[$childNode->value]);
                        break;
                    }

                    if (!$inOptionalRoutePart) {
                        throw new RouteUriCreationException("No value set for {$childNode->value} in path");
                    }

                    // We have an undefined, optional variable.  So, let's just not include the optional part at all.
                    break 2;
            }
        }

        return $path;
    }
}
