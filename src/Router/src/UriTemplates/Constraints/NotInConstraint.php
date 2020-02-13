<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

/**
 * Defines the not-in-array constraint
 */
final class NotInConstraint implements IRouteVariableConstraint
{
    /** @var array The list of unacceptable values */
    private array $unacceptableValues;

    /**
     * @param array $unacceptableValues The list of unacceptable values
     */
    public function __construct(...$unacceptableValues)
    {
        $this->unacceptableValues = $unacceptableValues;
    }

    /**
     * Gets the slug that will be used to actually add a constraint in a URI template
     *
     * @return string The slug used in the URI template
     */
    public static function getSlug(): string
    {
        return 'notIn';
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return !\in_array($value, $this->unacceptableValues, true);
    }
}
