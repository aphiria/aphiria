<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

/**
 * Defines the in-array constraint
 */
final class InConstraint implements IRouteVariableConstraint
{
    /** @var list<mixed> The list of acceptable values */
    private readonly array $acceptableValues;

    /**
     * @param list<mixed> $acceptableValues The list of acceptable values
     */
    public function __construct(...$acceptableValues)
    {
        /** @var list<mixed> acceptableValues */
        $this->acceptableValues = $acceptableValues;
    }

    /**
     * Gets the slug that will be used to actually add a constraint in a URI template
     *
     * @return string The slug used in the URI template
     */
    public static function getSlug(): string
    {
        return 'in';
    }

    /**
     * @inheritdoc
     */
    public function passes(mixed $value): bool
    {
        return \in_array($value, $this->acceptableValues, true);
    }
}
