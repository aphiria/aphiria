<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

/**
 * Defines the integer constraint
 */
final class IntegerConstraint implements IRouteVariableConstraint
{
    /**
     * Gets the slug that will be used to actually add a constraint in a URI template
     *
     * @return string The slug used in the URI template
     */
    public static function getSlug(): string
    {
        return 'int';
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return \filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}
