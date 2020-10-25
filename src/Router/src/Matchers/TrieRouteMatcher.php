<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers;

use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieNode;
use Generator;

/**
 * Defines the route matcher that uses a trie structure for matching
 */
final class TrieRouteMatcher implements IRouteMatcher
{
    /**
     * @param TrieNode $rootNode The root node
     */
    public function __construct(private TrieNode $rootNode)
    {
    }

    /**
     * @inheritdoc
     */
    public function matchRoute(string $httpMethod, string $host, string $path, array $headers = []): RouteMatchingResult
    {
        $hostSegments = $host === '' ? [] : \array_reverse(\explode('.', $host));
        $pathSegments =  \explode('/', \trim($path, '/'));
        $pathSegmentsCount = \count($pathSegments);
        $routeVars = $allowedMethods = [];

        foreach (self::getMatchCandidates($this->rootNode, $pathSegments, $pathSegmentsCount, 0, $hostSegments, $routeVars) as $candidate) {
            foreach ($candidate->route->constraints as $constraint) {
                // If any constraints fail, collect the allowed methods and go on to the next candidate
                if (!$constraint->passes($candidate, $httpMethod, $host, $path, $headers)) {
                    if ($constraint instanceof HttpMethodRouteConstraint) {
                        $allowedMethods = [...$allowedMethods, ...$constraint->getAllowedMethods()];
                    }

                    continue 2;
                }
            }

            return new RouteMatchingResult($candidate->route, $candidate->routeVariables, []);
        }

        return new RouteMatchingResult(null, [], \array_unique($allowedMethods));
    }

    /**
     * Gets the list of matching route candidates for a particular node
     *
     * This method uses generators that, given the order of the code, will return literal segments before variables
     * ones. Callers of this method will only attempt to descend the trie either on the first time or if previous
     * match candidates didn't meet constraints, hence saving us unnecessary descents down the trie.
     *
     * @param TrieNode $node The current node
     * @param array $segments The list of URI segments to match
     * @param int $segmentCount The length of the URI segments
     * @param int $segmentIter The current index of segments
     * @param array $hostSegments The list of URI host segments, which will be traversed if there's a host trie
     * @param array $routeVars The mapping of route variable names to values
     * @return Generator<int, MatchedRouteCandidate> The list of matched route candidates
     */
    private static function getMatchCandidates(
        TrieNode $node,
        array $segments,
        int $segmentCount,
        int $segmentIter,
        array $hostSegments,
        array $routeVars
    ): Generator {
        // Base case.  We iterate to 1 past the past segments because there are n + 1 levels of nodes due to the root node.
        if ($segmentIter === $segmentCount) {
            // If we're only matching paths
            if ($node->hostTrie === null) {
                foreach ($node->routes as $route) {
                    yield new MatchedRouteCandidate($route, $routeVars);
                }
            } else {
                // We have to traverse the host trie now
                $routeVarsCopy = $routeVars;
                yield from self::getMatchCandidates($node->hostTrie, $hostSegments, \count($hostSegments), 0, $hostSegments, $routeVarsCopy);
            }

            return;
        }

        $segment = $segments[$segmentIter];

        // Check for a literal segment match, and recursively check its descendants
        if (($childNode = ($node->literalChildrenByValue[\strtolower($segment)] ?? null)) !== null) {
            $routeVarsCopy = $routeVars;
            yield from self::getMatchCandidates($childNode, $segments, $segmentCount, $segmentIter + 1, $hostSegments, $routeVarsCopy);
        }

        // If a variable child is a match, check its descendants
        foreach ($node->variableChildren as $childNode) {
            $routeVarsCopy = $routeVars;

            if ($childNode->isMatch($segment, $routeVarsCopy)) {
                yield from self::getMatchCandidates(
                    $childNode,
                    $segments,
                    $segmentCount,
                    $segmentIter + 1,
                    $hostSegments,
                    $routeVarsCopy
                );
            }
        }
    }
}
