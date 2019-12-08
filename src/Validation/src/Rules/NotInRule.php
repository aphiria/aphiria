<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Rules;

use Aphiria\Validation\ValidationContext;

/**
 * Defines the not-in-array rule
 */
class NotInRule extends Rule
{
    /** @var array The value to compare against */
    private array $array;

    /**
     * @inheritdoc
     * @param array $array The value to compare against
     */
    public function __construct(array $array, string $errorMessageId)
    {
        parent::__construct($errorMessageId);

        $this->array = $array;
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        return !in_array($value, $this->array);
    }
}
