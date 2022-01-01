<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use Closure;

/**
 * Defines the callback constraint
 */
class CallbackConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field must pass callback';

    /**
     * @inheritdoc
     * @param Closure(mixed): bool $callback The callback to execute
     */
    public function __construct(private readonly Closure $callback, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return ($this->callback)($value);
    }
}
