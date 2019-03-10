<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers\Rules;

use function in_array;

/**
 * Defines the in-array rule
 */
final class InRule implements IRule
{
    /** @var array The list of acceptable values */
    private $acceptableValues;

    /**
     * @param array $acceptableValues The list of acceptable values
     */
    public function __construct(...$acceptableValues)
    {
        $this->acceptableValues = $acceptableValues;
    }

    /**
     * @inheritdoc
     */
    public static function getSlug(): string
    {
        return 'in';
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return in_array($value, $this->acceptableValues, true);
    }
}
