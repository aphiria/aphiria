<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata;

use Aphiria\DependencyInjection\Binders\Binder;

/**
 * Defines the interface for all binder metadata collectors to implement
 */
interface IBinderMetadataCollector
{
    /**
     * Collects metadata about a binder and its bindings
     *
     * @param Binder $binder The binder whose metadata we want to collect
     * @return BinderMetadata The collected binder metadata
     * @throws FailedBinderMetadataCollectionException Thrown if the binder metadata could not be fully collected
     */
    public function collect(Binder $binder): BinderMetadata;
}
