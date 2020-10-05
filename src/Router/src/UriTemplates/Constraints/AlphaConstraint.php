<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

/**
 * Defines the alpha constraint
 */
final class AlphaConstraint implements IRouteVariableConstraint
{
    /**
     * Gets the slug that will be used to actually add a constraint in a URI template
     *
     * @return string The slug used in the URI template
     */
    public static function getSlug(): string
    {
        return 'alpha';
    }

    /**
     * @inheritdoc
     */
    public function passes(mixed $value): bool
    {
        return \ctype_alpha($value) && !\str_contains($value, ' ');
    }
}
