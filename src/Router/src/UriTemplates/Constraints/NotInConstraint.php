<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

/**
 * Defines the not-in-array constraint
 */
final class NotInConstraint implements IRouteVariableConstraint
{
    /** @var list<mixed> The list of unacceptable values */
    private array $unacceptableValues;

    /**
     * @param list<mixed> $unacceptableValues The list of unacceptable values
     */
    public function __construct(...$unacceptableValues)
    {
        /** @var list<mixed> $unacceptableValues */
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
    public function passes(mixed $value): bool
    {
        return !\in_array($value, $this->unacceptableValues, true);
    }
}
