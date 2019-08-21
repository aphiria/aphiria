<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Parsers;

/**
 * Defines the list of abstract syntax tree node types
 */
final class AstNodeTypes
{
    /** @const The host node type */
    public const HOST = 'HOST';
    /** @const The optional route part node type */
    public const OPTIONAL_ROUTE_PART = 'OPTIONAL_ROUTE_PART';
    /** @const The path node type */
    public const PATH = 'PATH';
    /** @const The root node type */
    public const ROOT = 'ROOT';
    /** @const The segment delimiter node type */
    public const SEGMENT_DELIMITER = 'SEGMENT_DELIMITER';
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
