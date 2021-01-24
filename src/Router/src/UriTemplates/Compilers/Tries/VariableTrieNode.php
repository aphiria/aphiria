<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Compilers\Tries;

use Aphiria\Routing\Route;
use InvalidArgumentException;

/**
 * Defines a trie node that contains a variable value
 */
final class VariableTrieNode extends TrieNode
{
    /** @var string[]|RouteVariable[] The parts that make up this node */
    public array $parts;
    /** @var bool Whether or not this node contains just a single variable part (for performance reasons) */
    private bool $onlyContainsVariable;
    /** @var string The regex to use for matching */
    private string $regex = '';

    /**
     * @param string[]|RouteVariable[]|string|RouteVariable $parts The parts that make up this segment
     * @param TrieNode[] $children The list of children
     * @param Route[]|Route $routes The list of routes contained by this segment
     * @param TrieNode|null $hostTrie The host trie, if there is one
     * @throws InvalidArgumentException Thrown if the parts are empty
     */
    public function __construct(string|RouteVariable|array $parts, array $children, Route|array $routes = [], TrieNode $hostTrie = null)
    {
        parent::__construct($children, $routes, $hostTrie);

        $this->parts = \is_array($parts) ? $parts : [$parts];

        if (\count($this->parts) === 0) {
            throw new InvalidArgumentException('Must have at least one variable part');
        }

        // If this segment is composed of only a single variable (which is the norm in REST APIs), optimize for it
        if (\count($this->parts) === 1 && $this->parts[0] instanceof RouteVariable) {
            $this->onlyContainsVariable = true;
        } else {
            // This must contain at least two parts (variable or literal parts)
            $this->onlyContainsVariable = false;
            $this->regex = '';

            foreach ($this->parts as $part) {
                if ($part instanceof RouteVariable) {
                    $this->regex .= '(?<' . $part->name . '>.*)';
                } else {
                    $this->regex .= \preg_quote($part, '#');
                }
            }

            $this->regex = '#^' . $this->regex . '$#i';
        }
    }

    /**
     * Gets whether or not a segment matches this node
     *
     * @param string $segmentValue The segment value to match against
     * @param array<string, mixed> $routeVariables The route variables found on a successful match
     * @return bool True if the input segment value matches this node, otherwise false
     * @psalm-suppress PossiblyInvalidPropertyFetch Constraints is always an array - bug
     */
    public function isMatch(string $segmentValue, array &$routeVariables): bool
    {
        if ($this->onlyContainsVariable) {
            foreach ($this->parts[0]->constraints as $constraint) {
                if (!$constraint->passes($segmentValue)) {
                    return false;
                }
            }

            $routeVariables[$this->parts[0]->name] = $segmentValue;

            return true;
        }

        $matches = [];

        if (\preg_match($this->regex, $segmentValue, $matches, PREG_UNMATCHED_AS_NULL) !== 1) {
            return false;
        }

        // Don't change the actual array until we're sure all the constraints pass
        $routeVariablesCopy = $routeVariables;

        foreach ($this->parts as $part) {
            if ($part instanceof RouteVariable) {
                foreach ($part->constraints as $constraint) {
                    if (!$constraint->passes($matches[$part->name])) {
                        return false;
                    }
                }

                $routeVariablesCopy[$part->name] = (string)$matches[$part->name];
            }
        }

        $routeVariables = $routeVariablesCopy;

        return true;
    }
}
