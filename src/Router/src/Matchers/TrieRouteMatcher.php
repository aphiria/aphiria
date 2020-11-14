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
    private const CODE_FILE_PATH = 'C:\PHP_80\tmp\RouteMatcher.php';
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
            require_once self::CODE_FILE_PATH;
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
        $routeVars = $allowedMethods = [];

        foreach (\Aphiria\Routing\Matchers\CodeGeneration\RouteMatcher::matchRoute($this->rootNode, $pathSegments) as $candidate) {
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
        $copyrightYear = date('Y');
        $generatedDateString = (new \DateTime())->format('Y-m-d H:i:s');
        $generatedCode = $this->generateCodeForEndOfSegmentsCheck(0, 2) . $this->generateCodeForNode($this->rootNode, 0, 2);

        return <<<CODE
<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) {$copyrightYear} David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers\CodeGeneration;

use Aphiria\Routing\Matchers\MatchedRouteCandidate;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieNode;

/**
 * Defines the trie route matcher
 * Note: This code was auto-generated on {$generatedDateString}
 */
final class RouteMatcher
{
    /**
     * Gets route matches for URI segments
     *
     * @param TrieNode \$node The trie to match against
     * @param string[] \$segments The list of URI segments to match
     * @return MatchedRouteCandidate[] The list of matched routes
     */
    public static function matchRoute(TrieNode \$node, array \$segments): iterable
    {
        \$numSegments = \count(\$segments);
        \$routeVars = []; // TODO: Remove
        {$generatedCode}
    }
}
CODE;
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
        $code .= "\n" . \str_repeat("    ", $numTabs + 2) . "yield new MatchedRouteCandidate(\$route, \$routeVars);";
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
        if (empty($node->literalChildrenByValue)) {
            return '';
        }

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
