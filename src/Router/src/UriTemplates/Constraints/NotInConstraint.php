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
     * @inheritdoc
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
