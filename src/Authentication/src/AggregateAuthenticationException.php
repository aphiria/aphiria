<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication;

use Exception;

/**
 * Defines an exception that aggregates multiple authentication exceptions
 */
final class AggregateAuthenticationException extends Exception
{
    /** @var list<Exception> The list of exceptions that created this exception */
    public readonly array $innerExceptions;

    /**
     * @param string $message The message to set
     * @param Exception|list<Exception> $exceptions The exception or list of exceptions to aggregate
     */
    public function __construct(string $message, Exception|array $exceptions)
    {
        parent::__construct($message);

        $this->innerExceptions = $exceptions instanceof Exception ? [$exceptions] : $exceptions;
    }
}
