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

/**
 * Defines a base constraint
 */
abstract class ValidationConstraint implements IValidationConstraint
{
    /** @var string The ID of the error message associated with this constraint */
    protected string $errorMessageId;

    /**
     * @param string $errorMessageId The ID of the error message associated with this constraint
     */
    public function __construct(string $errorMessageId)
    {
        $this->errorMessageId = $errorMessageId;
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessageId(): string
    {
        return $this->errorMessageId;
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessagePlaceholders(): array
    {
        // Let overriding implementations overconstraint this if there is something to return
        return [];
    }
}
