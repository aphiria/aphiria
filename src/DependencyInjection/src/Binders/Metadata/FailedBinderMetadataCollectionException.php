<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
    /** @var BinderMetadata The incomplete binder metadata */
    private BinderMetadata $incompleteBinderMetadata;
    /** @var string The name of the interface that failed to be resolved */
    private string $failedInterface;

    /**
     * @inheritdoc
     * @param BinderMetadata $incompleteBinderMetadata The incomplete binder metadata
     * @param string $failedInterface The name of the interface that failed to be resolved
     */
    public function __construct(
        BinderMetadata $incompleteBinderMetadata,
        string $failedInterface,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            'Failed to collect metadata for ' . \get_class($incompleteBinderMetadata->getBinder()),
            $code,
            $previous
        );

        $this->incompleteBinderMetadata = $incompleteBinderMetadata;
        $this->failedInterface = $failedInterface;
    }

    /**
     * Gets the name of the interface that failed to be resolved
     *
     * @return string The name of the interface
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
