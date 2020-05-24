<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Reflection\Binders;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Reflection\AggregateTypeReflector;
use Aphiria\Reflection\ITypeReflector;
use Aphiria\Reflection\PhpDocTypeReflector;
use Aphiria\Reflection\ReflectionTypeReflector;

/**
 * Defines the binder for type reflectors
 */
final class TypeReflectorBinder extends Binder
{
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        $phpDocTypeReflector = new PhpDocTypeReflector();
        $reflectionTypeReflector = new ReflectionTypeReflector();
        $aggregateTypeReflector = new AggregateTypeReflector([$phpDocTypeReflector, $reflectionTypeReflector]);
        $container->bindInstance(PhpDocTypeReflector::class, $phpDocTypeReflector);
        $container->bindInstance(ReflectionTypeReflector::class, $reflectionTypeReflector);
        $container->bindInstance([ITypeReflector::class, AggregateTypeReflector::class], $aggregateTypeReflector);
    }
}
