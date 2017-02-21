<?php
namespace Opulence\Router\UriTemplates\Parsers;

use InvalidArgumentException;
use Opulence\Router\UriTemplates\IUriTemplate;
use Opulence\Router\UriTemplates\RegexUriTemplate;
use Opulence\Router\UriTemplates\Rules\IRuleFactory;

/**
 * Defines the regex URI template parser
 */
class RegexUriTemplateParser implements IUriTemplateParser
{
    /** @var The maximum length of a variable name */
    private const VARIABLE_MAXIMUM_LENGTH = 32;
    /** @var string The variable matching regex */
    private const VARIABLE_REGEX = '#:([a-zA-Z_][a-zA-Z0-9_]*)(?:=([^:\[\]/]+))?#';
    /** @var IRuleFactory The factory for rules found in the routes */
    private $ruleFactory = null;

    /**
     * @param IRuleFactory $ruleFactory The factory for rules found in the routes
     */
    public function __construct(IRuleFactory $ruleFactory)
    {
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @inheritdoc
     */
    public function parse(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false) : IUriTemplate
    {
        $variableNames = [];
        $defaultVariableValues = [];
        $rules = [];
        $hostRegex = $hostTemplate === null ?
            null :
            $this->convertRawStringToRegex($hostTemplate, $variableNames, $defaultVariableValues, $rules);
        $pathRegex = $this->convertRawStringToRegex($pathTemplate, $variableNames, $defaultVariableValues, $rules);

        return new RegexUriTemplate(
            $this->createUriRegex($hostRegex, $pathRegex, $isHttpsOnly),
            $defaultVariableValues,
            $rules
        );
    }

    /**
     * Converts a raw string with variables to a regex
     *
     * @param string $rawString The raw string to convert
     * @param array $variableNames The list of variable names used by the route
     * @param array $defaultVariableValues The mapping of default variable names to their values
     * @param array $rules The mapping of variable names to their rules
     * @return string The regex
     * @throws RouteException Thrown if the route variables are not correctly defined
     */
    private function convertRawStringToRegex(
        string $rawString,
        array &$variableNames,
        array &$defaultVariableValues,
        array &$rules
    ) : string {
        if (empty($rawString)) {
            return '#^.*$#';
        }

        $bracketDepth = 0;
        $cursor = 0;
        $rawStringLength = mb_strlen($rawString);
        $regex = '';

        while ($cursor < $rawStringLength) {
            $char = $rawString[$cursor];

            switch ($char) {
                case ':':
                    $regex .= $this->getVarRegex(
                        $rawString,
                        $cursor,
                        $variableNames,
                        $defaultVariableValues,
                        $rules
                    );
                    break;
                case '[':
                    $regex .= '(?:';
                    $bracketDepth++;
                    $cursor++;
                    break;
                case ']':
                    $regex .= ')?';
                    $bracketDepth--;
                    $cursor++;
                    break;
                default:
                    $regex .= preg_quote($char, '#');
                    $cursor++;
            }
        }

        if ($bracketDepth != 0) {
            throw new InvalidArgumentException(
                sprintf('URI template has %s brackets', $bracketDepth > 0 ? 'unclosed' : 'unopened')
            );
        }

        return $regex;
    }

    /**
     * Creates a URI regex
     *
     * @param string $hostRegex The host regex
     * @param string $pathRegex The path regex
     * @param bool $isHttpsOnly Whether or not the URI is HTTPS-only
     * @return string The URI regex
     */
    private function createUriRegex(?string $hostRegex, string $pathRegex, bool $isHttpsOnly) : string
    {
        return '#^http' . ($isHttpsOnly ? 's' : '(?:s)?') . '://' . ($hostRegex ?? '[^/]+') . $pathRegex . '$#';
    }

    /**
     * Parses a variable and returns the regex
     *
     * @param string $rawString The raw string we're parsing
     * @param int $cursor The current cursor
     * @param array $variableNames The list of variable names used by the route
     * @param array $defaultVariableValues The mapping of default variable names to their values
     * @param array $rules The mapping of variable names to their lists of rules
     * @return string The variable regex
     * @throws RouteException Thrown if the variable definition is invalid
     */
    private function getVarRegex(
        string $rawString,
        int &$cursor,
        array &$variableNames,
        array &$defaultVariableValues,
        array &$rules
    ) : string {
        $segment = mb_substr($rawString, $cursor);

        if (preg_match(self::VARIABLE_REGEX, $segment, $matches) !== 1) {
            throw new InvalidArgumentException("Variable name can't be empty");
        }

        $variableName = $matches[1];
        $defaultValue = $matches[2] ?? null;

        if (strlen($variableName) > self::VARIABLE_MAXIMUM_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'Variable name "%s" cannot be longer than %d characters. Please use a shorter name.',
                $variableName,
                self::VARIABLE_MAXIMUM_LENGTH)
            );
        }

        if (in_array($variableName, $variableNames)) {
            throw new RouteException("URI template uses multiple references to \"$variableName\"");
        }

        $variableNames[] = $variableName;

        if ($defaultValue !== null) {
            $defaultVariableValues[$variableName] = $defaultValue;
        }

        $cursor += mb_strlen($matches[0]);
        $this->parseRules($variableName, $rawString, $cursor, $rules);

        return sprintf('(?P<%s>%s)', $variableName, '[^\/:]+');
    }

    /**
     * Parses rules for a route variable
     *
     * @param string $variableName The name of the variable whose rules we're parsing
     * @param string $rawString The raw string we're parsing
     * @param int $cursor The current cursor
     * @param array $rules The mapping of variable names to their rules
     */
    private function parseRules(string $variableName, string $rawString, int &$cursor, array &$rules) : void
    {
        if ($cursor > mb_strlen($rawString) - 1 || $rawString[$cursor] !== '(') {
            return;
        }

        // We're starting at the first character after the opening parenthesis
        $cursor++;
        $parenthesesDepth = 1;
        $rawStringLength = mb_strlen($rawString);
        $bufferedRuleSlug = '';
        $bufferedParameters = '';
        $rawRules = [];
        $rules[$variableName] = [];

        while ($cursor < $rawStringLength) {
            $char = $rawString[$cursor];

            switch ($char) {
                case '(':
                    $parenthesesDepth++;
                    break;
                case ')':
                    $parenthesesDepth--;

                    // We're either done with all rules or one specific rule
                    if ($parenthesesDepth <= 1) {
                        if ($bufferedRuleSlug !== '') {
                            $rawRules[$bufferedRuleSlug] = $bufferedParameters;
                        }

                        if ($parenthesesDepth === 0) {
                            break;
                        }
                    } else {
                        $bufferedParameters .= $char;
                    }

                    break;
                case ',':
                    // If we're at the top-most level of parameters
                    if ($parenthesesDepth === 1) {
                        // This is a comma that's separating rules
                        $rawRules[$bufferedRuleSlug] = $bufferedParameters;
                        $bufferedRuleSlug = '';
                        $bufferedParameters = '';
                    } else {
                        $bufferedParameters .= $char;
                    }

                    break;
                default:
                    // If we're at the top-most level of parameters
                    if ($parenthesesDepth === 1) {
                        $bufferedRuleSlug .= $char;
                    } else {
                        $bufferedParameters .= $char;
                    }
            }

            $cursor++;
        }

        foreach ($rawRules as $ruleSlug => $parameterString) {
            $rules[$variableName][] = $this->ruleFactory->createRule($ruleSlug, str_getcsv($parameterString));
        }
    }
}
