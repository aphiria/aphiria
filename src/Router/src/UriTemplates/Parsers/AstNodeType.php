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
enum AstNodeType
{
    /** The host node type */
    case Host;
    /** The optional route part node type */
    case OptionalRoutePart;
    /** The path node type */
    case Path;
    /** The root node type */
    case Root;
    /** The segment delimiter node type */
    case SegmentDelimiter;
    /** The text node type */
    case Text;
    /** The variable node type */
    case Variable;
    /** The variable constraint node type */
    case VariableConstraint;
    /** The variable constraint parameters node type */
    case VariableConstraintParameters;
}
