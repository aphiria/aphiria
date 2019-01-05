<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Rules;

/**
 * Defines the in-array rule
 */
class InRule implements IRule
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
        return \in_array($value, $this->acceptableValues, true);
    }
}
