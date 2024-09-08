<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

use Exception;
use Throwable;

/**
 * Defines an exception that's thrown when a dependency could not be resolved
 */
final class ResolutionException extends Exception
{
    /**
     * @inheritdoc
     * @param class-string $interface The name of the interface that could not be resolved
     * @param Context $context The context that the exception was thrown in
     */
    public function __construct(
        public readonly string $interface,
        public readonly Context $context,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
