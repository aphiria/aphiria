<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Validation\Bootstrappers;

use Aphiria\Configuration\Configuration;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\Caching\FileObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolater;
use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageInterpolater;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\Validator;

/**
 * Defines the validation bootstrapper
 */
final class ValidationBootstrapper extends Bootstrapper
{
    /**
     * @inheritdoc
     */
    public function registerBindings(IContainer $container): void
    {
        $objectConstraints = new ObjectConstraintsRegistry();
        $container->bindInstance(ObjectConstraintsRegistry::class, $objectConstraints);
        $validator = new Validator($objectConstraints);
        $container->bindInstance([IValidator::class, Validator::class], $validator);

        if (getenv('APP_ENV') === 'production') {
            $constraintCache = new FileObjectConstraintsRegistryCache(Configuration::getString('validation.constraintsCache'));
        } else {
            $constraintCache = null;
        }

        $constraintsRegistrants = new ObjectConstraintsRegistrantCollection($constraintCache);
        $container->bindInstance(ObjectConstraintsRegistrantCollection::class, $constraintsRegistrants);
        $container->bindInstance(IErrorMessageInterpolater::class, new StringReplaceErrorMessageInterpolater());

        // Register some constraint annotation dependencies
        $constraintAnnotationRegistrant = new AnnotationObjectConstraintsRegistrant(
            Configuration::getArray('validation.annotationPaths')
        );
        $container->bindInstance(AnnotationObjectConstraintsRegistrant::class, $constraintAnnotationRegistrant);
    }
}
