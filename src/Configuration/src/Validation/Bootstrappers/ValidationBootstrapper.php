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
use Aphiria\Configuration\ConfigurationException;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\Caching\FileObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\ErrorMessages\IcuFormatErrorMessageInterpolater;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolater;
use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageInterpolater;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\Validator;
use Doctrine\Annotations\AnnotationException;

/**
 * Defines the validation bootstrapper
 */
final class ValidationBootstrapper extends Bootstrapper
{
    /**
     * @inheritdoc
     * @throws ConfigurationException Thrown if the config is missing values
     * @throws AnnotationException Thrown if PHP is not configured to handle scanning for annotations
     */
    public function registerBindings(IContainer $container): void
    {
        $objectConstraints = new ObjectConstraintsRegistry();
        $container->bindInstance(ObjectConstraintsRegistry::class, $objectConstraints);
        $validator = new Validator($objectConstraints);
        $container->bindInstance([IValidator::class, Validator::class], $validator);

        if (getenv('APP_ENV') === 'production') {
            $constraintCache = new FileObjectConstraintsRegistryCache(Configuration::getString('validation.constraintsCachePath'));
        } else {
            $constraintCache = null;
        }

        $constraintsRegistrants = new ObjectConstraintsRegistrantCollection($constraintCache);
        $container->bindInstance(ObjectConstraintsRegistrantCollection::class, $constraintsRegistrants);

        $errorMessageInterpolaterConfiguration = Configuration::getArray('validation.errorMessageInterpolater');

        switch ($errorMessageInterpolaterConfiguration['type']) {
            case StringReplaceErrorMessageInterpolater::class:
                $errorMessageInterpolater = new StringReplaceErrorMessageInterpolater();
                break;
            case IcuFormatErrorMessageInterpolater::class:
                $errorMessageInterpolater = new IcuFormatErrorMessageInterpolater(
                    $errorMessageInterpolaterConfiguration['defaultLocale'] ?? 'en'
                );
                break;
            default:
                throw new ConfigurationException("Unsupported error message interpolater type {$errorMessageInterpolaterConfiguration['type']}");
        }

        $container->bindInstance(IErrorMessageInterpolater::class, $errorMessageInterpolater);

        // Register some constraint annotation dependencies
        $constraintAnnotationRegistrant = new AnnotationObjectConstraintsRegistrant(
            Configuration::getArray('validation.annotationPaths')
        );
        $container->bindInstance(AnnotationObjectConstraintsRegistrant::class, $constraintAnnotationRegistrant);
    }
}
