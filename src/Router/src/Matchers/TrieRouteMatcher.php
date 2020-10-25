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
use Aphiria\Routing\UriTemplates\Compilers\Tries\RouteVariable;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieNode;

/**
 * Defines the route matcher that uses a trie structure for matching
 */
final class TrieRouteMatcher implements IRouteMatcher
{
    /** @var string TODO: UPDATE */
    private const CODE_FILE_PATH = 'C:\PHP_80\tmp\routes.php';
    /** @var \Closure TODO UPDATE */
    private \Closure $matcher;
    /** @var bool TODO REMOVE */
    private bool $isBootstrapped = false;

    /**
     * @param TrieNode $rootNode The root node
     */
    public function __construct(private TrieNode $rootNode)
    {
    }

    // TODO: Remove this once I've gotten it set up so that only the fully-populated trie node is used to generate the code
    public function bootstrap(): void
    {
        if (!$this->isBootstrapped) {
            file_put_contents(self::CODE_FILE_PATH, $this->generateCode());
            $this->matcher = require_once self::CODE_FILE_PATH;
            $this->isBootstrapped = true;
        }
    }

    /**
     * @inheritdoc
     */
    public function matchRoute(string $httpMethod, string $host, string $path, array $headers = []): RouteMatchingResult
    {
        // TODO: Comment this out when running unit tests: $this->bootstrap();
        $hostSegments = $host === '' ? [] : \array_reverse(\explode('.', $host));
        $pathSegments =  \explode('/', \trim($path, '/'));
        $pathSegmentsCount = \count($pathSegments);
        $routeVars = $allowedMethods = [];

        foreach (($this->matcher)($this->rootNode, $pathSegments/* TODO: Do something with these params, $pathSegmentsCount, 0, $hostSegments, $routeVars*/) as $candidate) {
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
     * @return MatchedRouteCandidate[] The list of matched route candidates
     */
    private static function getMatchCandidates__OLD(
        TrieNode $node,
        array $segments,
        int $segmentCount,
        int $segmentIter,
        array $hostSegments,
        array $routeVars
    ): iterable {
        // TODO: Remove
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
                yield from self::getMatchCandidates__OLD($node->hostTrie, $hostSegments, \count($hostSegments), 0, $hostSegments, $routeVarsCopy);
            }

            return;
        }

        $segment = $segments[$segmentIter];

        // Check for a literal segment match, and recursively check its descendants
        if (($childNode = ($node->literalChildrenByValue[\strtolower($segment)] ?? null)) !== null) {
            $routeVarsCopy = $routeVars;
            yield from self::getMatchCandidates__OLD($childNode, $segments, $segmentCount, $segmentIter + 1, $hostSegments, $routeVarsCopy);
        }

        // If a variable child is a match, check its descendants
        foreach ($node->variableChildren as $childNode) {
            $routeVarsCopy = $routeVars;

            if ($childNode->isMatch($segment, $routeVarsCopy)) {
                yield from self::getMatchCandidates__OLD(
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

    /**
     * Generates code for traversing the trie and trying to find a matching route
     *
     * @return string The generated code
     */
    private function generateCode(): string
    {
        /**
         * Things to figure out:
         *
         * - How should the fall-through logic work?  For example, if we match a literal node, but its children don't match,
         *   we want to check the literal node's siblings for matches.
         * - How do we collect route vars without keeping matched vars from paths that fell through?  We need to keep
         *   track of everything we've matched up to any particular node, but scope that such that it doesn't interfere
         *   with other paths' route vars in case it falls through.
         *     - UPDATE 1: Can we just remove the "return" statement from the default cases?  If nothing is yield returned,
         *       it'll fall through to the parent switch statement.
         *     - UPDATE 2: I think that would work, but what about letting literal paths fall through to variable ones?
         *       Can you even fall through to a default path?
         *         - UPDATE 1: It turns out that you can fall back to a default case.  So, no problems.
         */
        $code = "<?php";
        $code .= "\n\ndeclare(strict_types=1);";
        $code .= "\n\n// This code was auto-generated on " . (new \DateTime())->format('Y-m-d H:i:s');
        $code .= "\nreturn static function (\Aphiria\Routing\UriTemplates\Compilers\Tries\TrieNode \$node, array \$segments): iterable {";
        $code .= "\n    \$numSegments = \count(\$segments);";
        // TODO: Remove this \|/
        $code .= "\n    \$routeVars = [];";
        // Check if there were no segments at all
        $code .= $this->generateCodeForEndOfSegmentsCheck(0, 1);
        $code .= $this->generateCodeForNode($this->rootNode, 0, 1);
        $code .= "\n};"; // Closes the function

        return $code;
    }

    /**
     * Generates code that checks if we're at the end of the segments
     *
     * @param int $depth The depth we've traversed down the trie
     * @param int $numTabs The number of tabs everything should start indenting at
     * @return string The generated code
     */
    private function generateCodeForEndOfSegmentsCheck(int $depth, int $numTabs): string
    {
        $code = "\n\n" . \str_repeat("    ", $numTabs) . "if (\$numSegments === $depth) {";
        $code .= "\n" . \str_repeat("    ", $numTabs + 1) . "foreach (\$node->routes as \$route) {";
        $code .= "\n" . \str_repeat("    ", $numTabs + 2) . "yield new \Aphiria\Routing\Matchers\MatchedRouteCandidate(\$route, \$routeVars);";
        $code .= "\n" . \str_repeat("    ", $numTabs + 1) . "}"; // Closes foreach loop
        $code .= "\n\n" . \str_repeat("    ", $numTabs + 1) . "return;"; // Makes sure we don't go any further
        $code .= "\n" . \str_repeat("    ", $numTabs) . "}"; // Close if statement

        return $code;
    }

    /**
     * Generates code to try and match literal children
     *
     * @param TrieNode $node The node to generate code for
     * @param int $depth The depth we've traversed down the trie
     * @param int $numTabs The number of tabs everything should start indenting at
     * @return string The generated code
     */
    private function generateCodeForLiteralChildren(TrieNode $node, int $depth, int $numTabs): string
    {
        $code = '';

        foreach ($node->literalChildrenByValue as $childNode) {
            $code .= "\n" . \str_repeat("    ", $numTabs) . "case '" . \addslashes($childNode->value) . "':";
            $code .= "\n" . \str_repeat("    ", $numTabs + 1) . "\$node = \$node->literalChildrenByValue['" . \addslashes($childNode->value) . "'];";
            $code .= $this->generateCodeForEndOfSegmentsCheck($depth + 1, $numTabs + 1);
            $code .= $this->generateCodeForNode($childNode, $depth + 1, $numTabs + 1);
        }

        return $code;
    }

    /**
     * Generates code for a trie node to try and match routes from
     *
     * @param TrieNode $node The node to generate code from
     * @param int $depth The depth we've traversed down the trie
     * @param int $numTabs The number of tabs everything should start indenting at
     * @return string The generated code
     */
    private function generateCodeForNode(TrieNode $node, int $depth, int $numTabs): string
    {
        // If this is a leaf node, we don't generate any code for it
        if (empty($node->literalChildrenByValue) && empty($node->variableChildren)) {
            return '';
        }

        // Use two new lines to give proper separate for switch statement
        $code = "\n\n" . str_repeat("    ", $numTabs) . "switch (\strtolower(\$segments[$depth])) {";
        $code .= $this->generateCodeForLiteralChildren($node, $depth, $numTabs + 1);
        $code .= $this->generateCodeForVariableChildren($node, $depth, $numTabs + 1);
        $code .= "\n" . str_repeat("    ", $numTabs) . "}"; // Closes switch

        return $code;
    }

    /**
     * Generates code to try and match variable children
     *
     * @param TrieNode $node The node to generate code from
     * @param int $depth The depth we've traversed down the trie
     * @param int $numTabs The number of tabs everything should start indenting at
     * @return string The generated code
     */
    private function generateCodeForVariableChildren(TrieNode $node, int $depth, int $numTabs): string
    {
        if (empty($node->variableChildren)) {
            return '';
        }

        $code = "\n" . \str_repeat("    ", $numTabs) . "default:";

        foreach ($node->variableChildren as $iter => $childNode) {
            $variableName = null;

            foreach ($childNode->parts as $part) {
                if ($part instanceof RouteVariable) {
                    $variableName = $part->name;
                    break;
                }
            }

            $code .= "\n" . \str_repeat("    ", $numTabs + 1) . "// Auto-generated code for variable" . ($variableName === null ? '' : " :$variableName");
            $code .= "\n" . \str_repeat("    ", $numTabs + 1) . "if (\$node->variableChildren[$iter]->isMatch(\$segments[$depth], \$routeVars)) {";
            $code .= "\n" . \str_repeat("    ", $numTabs + 2) . "\$node = \$node->variableChildren[$iter];";
            $code .= $this->generateCodeForEndOfSegmentsCheck($depth + 1, $numTabs + 2);
            $code .= $this->generateCodeForNode($childNode, $depth + 1, $numTabs + 2);
            $code .= "\n" . \str_repeat("    ", $numTabs + 1) . "}"; // Closes if statement
        }

        $code .= "\n\n" . \str_repeat("    ", $numTabs + 1) . "break;"; // Closes default

        return $code;
    }
}
