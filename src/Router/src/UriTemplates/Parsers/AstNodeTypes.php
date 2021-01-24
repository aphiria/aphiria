<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
    /** @const The variable constraint node type */
    public const VARIABLE_CONSTRAINT = 'VARIABLE_CONSTRAINT';
    /** @const The variable constraint parameters node type */
    public const VARIABLE_CONSTRAINT_PARAMETERS = 'VARIABLE_CONSTRAINT_PARAMETERS';
}
