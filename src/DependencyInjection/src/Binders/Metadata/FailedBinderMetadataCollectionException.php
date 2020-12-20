<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
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
        private BinderMetadata $incompleteBinderMetadata,
        private string $failedInterface,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            'Failed to collect metadata for ' . $this->incompleteBinderMetadata->getBinder()::class,
            $code,
            $previous
        );
    }

    /**
     * Gets the name of the interface that failed to be resolved
     *
     * @return class-string The name of the interface
     */
    public function getFailedInterface(): string
    {
        return $this->failedInterface;
    }

    /**
     * Gets the incomplete binder metadata
     *
     * @return BinderMetadata The incomplete binder metadata
     */
    public function getIncompleteBinderMetadata(): BinderMetadata
    {
        return $this->incompleteBinderMetadata;
    }
}
