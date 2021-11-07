<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata;

use Exception;
use Throwable;

/**
 * Defines the exception that's thrown when a binder's metadata couldn't be collected (eg an exception was thrown while resolving some dependencies)
 */
final class FailedBinderMetadataCollectionException extends Exception
{
    /**
     * @inheritdoc
     * @param BinderMetadata $incompleteBinderMetadata The incomplete binder metadata
     * @param class-string $failedInterface The name of the interface that failed to be resolved
     */
    public function __construct(
        public readonly BinderMetadata $incompleteBinderMetadata,
        public readonly string $failedInterface,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            'Failed to collect metadata for ' . $this->incompleteBinderMetadata->binder::class,
            $code,
            $previous
        );
    }
}
