<?php
namespace Opulence\Router\UriTemplates\Parsers\Nodes;

/**
 * Defines the list of node types
 */
class NodeTypes
{
    /** @var string The optional route part node type */
    public const OPTIONAL_ROUTE_PART = "OPTIONAL_ROUTE_PART";
    /** @var string The root node type */
    public const ROOT = "ROOT";
    /** @var string The text node type */
    public const TEXT = "TEXT";
    /** @var string The variable node type */
    public const VARIABLE = "VARIABLE";
    /** @var string The variable default value node type */
    public const VARIABLE_DEFAULT_VALUE = "VARIABLE_DEFAULT_VALUE";
    /** @var string The variable rule node type */
    public const VARIABLE_RULE = "VARIABLE_RULE";
    /** @var string The variable rule parameters node type */
    public const VARIABLE_RULE_PARAMETERS = "VARIABLE_RULE_PARAMETERS";
}
