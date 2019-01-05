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
 * Defines the not-in-array rule
 */
class NotInRule implements IRule
{
    /** @var array The list of unacceptable values */
    private $unacceptableValues;

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
