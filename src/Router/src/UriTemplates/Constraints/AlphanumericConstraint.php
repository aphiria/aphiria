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
 * Defines the alphanumeric constraint
 */
final class AlphanumericConstraint implements IRouteVariableConstraint
{
    /**
     * @inheritdoc
     */
    public static function getSlug(): string
    {
        return 'alphanumeric';
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return \ctype_alnum($value) && \strpos($value, ' ') === false;
    }
}
