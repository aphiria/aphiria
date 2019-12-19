<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Mocks;

use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\ValidationContext;

/**
 * Defines a mock constraint for use in tests
 */
final class MockConstraint implements IConstraint
{
    /**
     * @inheritdoc
     */
    public function getErrorMessageId(): string
    {
        return 'error';
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessagePlaceholders(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        return true;
    }
}
