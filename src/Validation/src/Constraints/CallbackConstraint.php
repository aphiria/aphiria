<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use Aphiria\Validation\ValidationContext;

/**
 * Defines the callback constraint
 */
class CallbackConstraint extends ValidationConstraint
{
    /** @var callable The callback to run */
    private $callback;

    /**
     * @inheritdoc
     * @param callable $callback The callback to execute
     */
    public function __construct(callable $callback, string $errorMessageId)
    {
        parent::__construct($errorMessageId);

        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        return ($this->callback)($value, $validationContext);
    }
}
