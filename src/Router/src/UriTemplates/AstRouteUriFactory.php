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

use Aphiria\Routing\Attributes\Header;
use Aphiria\Routing\Attributes\QueryString;
use Aphiria\Routing\Attributes\RouteVariable;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\Lexers\IUriTemplateLexer;
use Aphiria\Routing\UriTemplates\Lexers\LexingException;
use Aphiria\Routing\UriTemplates\Lexers\UnexpectedTokenException;
use Aphiria\Routing\UriTemplates\Lexers\UriTemplateLexer;
use Aphiria\Routing\UriTemplates\Parsers\AstNode;
use Aphiria\Routing\UriTemplates\Parsers\AstNodeType;
use Aphiria\Routing\UriTemplates\Parsers\IUriTemplateParser;
use Aphiria\Routing\UriTemplates\Parsers\UriTemplateParser;
use OutOfBoundsException;
use ReflectionException;
use ReflectionMethod;

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

        /** @var array{routeVariables: array<string, mixed>, queryString: array<string, mixed>, unspecified: array<string, mixed>} $routeVariablesBySource */
        $routeVariablesBySource = ['routeVariables' => [], 'queryString' => [], 'unspecified' => []];

        try {
            $reflectionMethod = new ReflectionMethod($route->action->className, $route->action->methodName);
        } catch (ReflectionException $ex) {
            throw new RouteUriCreationException("Failed to reflect route action {$route->action->className}::{$route->action->methodName}", 0, $ex);
        }

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            if (\count($routeVariableAttributes = $parameter->getAttributes(RouteVariable::class)) === 1) {
                $parameterName = $routeVariableAttributes[0]->newInstance()->name ?? $parameterName;
                $routeVariablesBySource['routeVariables'][$parameterName] = $routeVariables[$parameterName] ?? null;
                continue;
            }

            if (\count($queryStringAttributes = $parameter->getAttributes(QueryString::class)) === 1) {
                $parameterName = $queryStringAttributes[0]->newInstance()->name ?? $parameterName;
                $routeVariablesBySource['queryString'][$parameterName] = $routeVariables[$parameterName] ?? null;
                continue;
            }

            // Don't include #[Header] parameters at all in the URI
            if (\count($parameter->getAttributes(Header::class)) > 0) {
                continue;
            }

            $routeVariablesBySource['unspecified'][$parameterName] = $routeVariables[$parameterName] ?? null;
        }

        $invalidRouteVariables = [];

        foreach ($routeVariables as $name => $value) {
            if (
                !array_key_exists($name, $routeVariablesBySource['routeVariables'])
                && !array_key_exists($name, $routeVariablesBySource['queryString'])
                && !array_key_exists($name, $routeVariablesBySource['unspecified'])
            ) {
                $invalidRouteVariables[] = $name;
            }
        }

        if (!empty($invalidRouteVariables)) {
            throw new RouteUriCreationException(
                \sprintf(
                    'Invalid route variable%s "%s"%s',
                    \count($invalidRouteVariables) > 1 ? 's' : '',
                    \implode("\", \"", $invalidRouteVariables),
                    \count($routeVariablesBySource['routeVariables']) > 0 || \count($routeVariablesBySource['queryString']) > 0 || \count($routeVariablesBySource['unspecified']) > 0
                        ? ', expected "' . \implode("\", \"", \array_merge(\array_keys($routeVariablesBySource['routeVariables']), \array_keys($routeVariablesBySource['queryString']), \array_keys($routeVariablesBySource['unspecified']))) . '"'
                        : ''
                )
            );
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
                    $host = $this->compileHost($childAstNode, $routeVariablesBySource, $reflectionMethod);
                    break;
                case AstNodeType::Path:
                    $path = $this->compilePath($childAstNode, $routeVariablesBySource, $reflectionMethod);
                    break;
            }
        }

        // See if we need to append any query string parameters from the unused variables
        $queryString = \http_build_query(
            $routeVariablesBySource['queryString'] + $routeVariablesBySource['unspecified'],
            encoding_type: PHP_QUERY_RFC3986
        );
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
     * Compiles the host from the AST
     *
     * @param AstNode $node The host AST node
     * @param array{routeVariables: array<string, mixed>, queryString: array<string, mixed>, unspecified: array<string, mixed>} $routeVariablesBySource The route variables
     * @param ReflectionMethod $reflectionMethod The reflected route action
     * @param bool $inUndefinedOptionalRoutePart Whether or not we're in an undefined optional route part
     * @return string The compiled host portion of the URI
     */
    private function compileHost(AstNode $node, array &$routeVariablesBySource, ReflectionMethod $reflectionMethod, bool $inUndefinedOptionalRoutePart = false): string
    {
        $hostParts = [];
        $inOptionalRoutePart = $node->type === AstNodeType::OptionalRoutePart;
        $optionalSegmentBuffer = '';

        foreach (\array_reverse($node->children) as $childNode) {
            // If we're in an undefined optional route part, keep stepping through the tree and unset any variables
            // This prevents us from using the "bar" value in the case of [/:foo[/:bar]] if "foo" was not specified but "bar" was
            if ($inUndefinedOptionalRoutePart) {
                if ($childNode->type === AstNodeType::Variable) {
                    unset(
                        $routeVariablesBySource['routeVariables'][(string)$childNode->value],
                        $routeVariablesBySource['queryString'][(string)$childNode->value],
                        $routeVariablesBySource['unspecified'][(string)$childNode->value]
                    );
                } elseif ($childNode->type === AstNodeType::OptionalRoutePart) {
                    // Keep stepping through the tree, but don't bother capturing the path because we're not going to use any of it anyway
                    $this->compileHost($childNode, $routeVariablesBySource, $reflectionMethod, $inUndefinedOptionalRoutePart);
                }

                continue;
            }

            switch ($childNode->type) {
                case AstNodeType::SegmentDelimiter:
                    // If we're in an optional part, we don't want to include it unless it contains text or a defined variable
                    if ($inOptionalRoutePart) {
                        $optionalSegmentBuffer .= (string)$childNode->value;
                    } else {
                        $hostParts[] = (string)$childNode->value;
                    }

                    break;
                case AstNodeType::Text:
                    if (!empty($optionalSegmentBuffer)) {
                        $hostParts[] = $optionalSegmentBuffer;
                        $optionalSegmentBuffer = '';
                    }

                    $hostParts[] = (string)$childNode->value;
                    break;
                case AstNodeType::OptionalRoutePart:
                    $inOptionalRoutePart = true;
                    $hostParts[] = $this->compileHost($childNode, $routeVariablesBySource, $reflectionMethod, $inUndefinedOptionalRoutePart);
                    break;
                case AstNodeType::Variable:
                    $routeVariable = null;

                    if (isset($routeVariablesBySource['routeVariables'][(string)$childNode->value])) {
                        $routeVariable = (string)$routeVariablesBySource['routeVariables'][(string)$childNode->value];
                        unset($routeVariablesBySource['routeVariables'][(string)$childNode->value]);
                    }

                    if (isset($routeVariablesBySource['unspecified'][(string)$childNode->value])) {
                        $routeVariable = (string)$routeVariablesBySource['unspecified'][(string)$childNode->value];
                        unset($routeVariablesBySource['unspecified'][(string)$childNode->value]);
                    }

                    if ($routeVariable !== null) {
                        // Check if we've hit a defined variable, eg "[:foo.]bar.com", flush the buffer, eg "."
                        if (!empty($optionalSegmentBuffer)) {
                            $hostParts[] = $optionalSegmentBuffer;
                            $optionalSegmentBuffer = '';
                        }

                        $hostParts[] = $routeVariable;
                        break;
                    }

                    if (!$inOptionalRoutePart) {
                        throw new RouteUriCreationException("No value set for {$childNode->value} in host");
                    }

                    // We have an undefined, optional variable
                    $inUndefinedOptionalRoutePart = true;
                    break;
            }
        }

        // The delimiters are in the host parts, so just glue it together with an empty string
        return \implode('', \array_reverse($hostParts));
    }

    /**
     * Compiles the path from the AST
     *
     * @param AstNode $node The path AST node
     * @param array{routeVariables: array<string, mixed>, queryString: array<string, mixed>, unspecified: array<string, mixed>} $routeVariablesBySource The route variables
     * @param ReflectionMethod $reflectionMethod The reflected route action
     * @param bool $inUndefinedOptionalRoutePart Whether or not we're in an undefined optional route part
     * @return string The compiled path portion of the URI
     */
    private function compilePath(AstNode $node, array &$routeVariablesBySource, ReflectionMethod $reflectionMethod, bool $inUndefinedOptionalRoutePart = false): string
    {
        $path = '';
        $inOptionalRoutePart = $node->type === AstNodeType::OptionalRoutePart;
        $optionalSegmentBuffer = '';

        foreach ($node->children as $childNode) {
            // If we're in an undefined optional route part, keep stepping through the tree and unset any variables
            // This prevents us from using the "bar" value in the case of [/:foo[/:bar]] if "foo" was not specified but "bar" was
            if ($inUndefinedOptionalRoutePart) {
                if ($childNode->type === AstNodeType::Variable) {
                    unset(
                        $routeVariablesBySource['routeVariables'][(string)$childNode->value],
                        $routeVariablesBySource['queryString'][(string)$childNode->value],
                        $routeVariablesBySource['unspecified'][(string)$childNode->value]
                    );
                } elseif ($childNode->type === AstNodeType::OptionalRoutePart) {
                    // Keep stepping through the tree, but don't bother capturing the path because we're not going to use any of it anyway
                    $this->compilePath($childNode, $routeVariablesBySource, $reflectionMethod, $inUndefinedOptionalRoutePart);
                }

                continue;
            }

            switch ($childNode->type) {
                case AstNodeType::SegmentDelimiter:
                    // If we're in an optional part, we don't want to include it unless it contains text or a defined variable
                    if ($inOptionalRoutePart) {
                        $optionalSegmentBuffer .= (string)$childNode->value;
                    } else {
                        $path .= (string)$childNode->value;
                    }

                    break;
                case AstNodeType::Text:
                    if (!empty($optionalSegmentBuffer)) {
                        $path .= $optionalSegmentBuffer;
                        $optionalSegmentBuffer = '';
                    }

                    $path .= (string)$childNode->value;
                    break;
                case AstNodeType::OptionalRoutePart:
                    $path .= $this->compilePath($childNode, $routeVariablesBySource, $reflectionMethod, $inUndefinedOptionalRoutePart);
                    break;
                case AstNodeType::Variable:
                    $routeVariable = null;

                    if (isset($routeVariablesBySource['routeVariables'][(string)$childNode->value])) {
                        $routeVariable = (string)$routeVariablesBySource['routeVariables'][(string)$childNode->value];
                        unset($routeVariablesBySource['routeVariables'][(string)$childNode->value]);
                    }

                    if (isset($routeVariablesBySource['unspecified'][(string)$childNode->value])) {
                        $routeVariable = (string)$routeVariablesBySource['unspecified'][(string)$childNode->value];
                        unset($routeVariablesBySource['unspecified'][(string)$childNode->value]);
                    }

                    if ($routeVariable !== null) {
                        // Check if we've hit a defined variable, eg "/foo[/:bar]", flush the buffer, eg "/"
                        if (!empty($optionalSegmentBuffer)) {
                            $path .= $optionalSegmentBuffer;
                            $optionalSegmentBuffer = '';
                        }

                        $path .= $routeVariable;
                        break;
                    }

                    if (!$inOptionalRoutePart) {
                        throw new RouteUriCreationException("No value set for {$childNode->value} in path");
                    }

                    // We have an undefined, optional variable
                    $inUndefinedOptionalRoutePart = true;
                    break;
            }
        }

        return $path;
    }
}
