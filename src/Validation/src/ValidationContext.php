<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

/**
 * Defines the context that validation occurs in
 */
final class ValidationContext
{
    /** @var object The object being validated */
    private object $object;

    /**
     * @param object $object The object being validated
     */
    public function __construct(object $object)
    {
        $this->object = $object;
    }

    /**
     * Gets the object being validated
     *
     * @return object The object being validated
     */
    public function getObject(): object
    {
        return $this->object;
    }
}
