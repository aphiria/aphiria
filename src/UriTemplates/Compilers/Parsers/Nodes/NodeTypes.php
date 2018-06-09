<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\UriTemplates\Compilers\Parsers\Nodes;

/**
 * Defines the list of node types
 */
class NodeTypes
{
    /** @const The optional route part node type */
    public const OPTIONAL_ROUTE_PART = 'OPTIONAL_ROUTE_PART';
    /** @const The root node type */
    public const ROOT = 'ROOT';
    /** @const The text node type */
    public const TEXT = 'TEXT';
    /** @const The variable node type */
    public const VARIABLE = 'VARIABLE';
    /** @const The variable default value node type */
    public const VARIABLE_DEFAULT_VALUE = 'VARIABLE_DEFAULT_VALUE';
    /** @const The variable rule node type */
    public const VARIABLE_RULE = 'VARIABLE_RULE';
    /** @const The variable rule parameters node type */
    public const VARIABLE_RULE_PARAMETERS = 'VARIABLE_RULE_PARAMETERS';
}
