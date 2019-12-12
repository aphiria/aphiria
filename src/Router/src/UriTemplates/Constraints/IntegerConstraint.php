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
     * @inheritdoc
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
