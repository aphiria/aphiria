<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Framework\Validation\Bootstrappers;

use Aphiria\Configuration\Configuration;
use Aphiria\Configuration\ConfigurationException;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\Caching\FileObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\ErrorMessages\DefaultErrorMessageTemplateRegistry;
use Aphiria\Validation\ErrorMessages\IcuFormatErrorMessageInterpolator;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageInterpolator;
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
            $constraintCache = new FileObjectConstraintsRegistryCache(Configuration::getString('aphiria.validation.constraintsCachePath'));
        } else {
            $constraintCache = null;
        }

        $constraintsRegistrants = new ObjectConstraintsRegistrantCollection($constraintCache);
        $container->bindInstance(ObjectConstraintsRegistrantCollection::class, $constraintsRegistrants);

        $errorMessageTemplateConfiguration = null;

        if (Configuration::tryGetArray('aphiria.validation.errorMessageTemplates', $errorMessageTemplateConfiguration)) {
            switch ($errorMessageTemplateConfiguration['type']) {
                case DefaultErrorMessageTemplateRegistry::class;
                    $errorMessageTemplates = new DefaultErrorMessageTemplateRegistry();
                    break;
                default:
                    $errorMessageTemplates = $container->resolve($errorMessageTemplateConfiguration['type']);
                    break;
            }
        } else {
            $errorMessageTemplates = null;
        }

        $errorMessageInterpolatorConfiguration = Configuration::getArray('aphiria.validation.errorMessageInterpolator');

        switch ($errorMessageInterpolatorConfiguration['type']) {
            case StringReplaceErrorMessageInterpolator::class:
                $errorMessageInterpolator = new StringReplaceErrorMessageInterpolator($errorMessageTemplates);
                break;
            case IcuFormatErrorMessageInterpolator::class:
                $errorMessageInterpolator = new IcuFormatErrorMessageInterpolator(
                    $errorMessageTemplates,
                    $errorMessageInterpolatorConfiguration['defaultLocale'] ?? 'en'
                );
                break;
            default:
                throw new ConfigurationException("Unsupported error message interpolator type {$errorMessageInterpolatorConfiguration['type']}");
        }

        $container->bindInstance(IErrorMessageInterpolator::class, $errorMessageInterpolator);

        // Register some constraint annotation dependencies
        $constraintAnnotationRegistrant = new AnnotationObjectConstraintsRegistrant(
            Configuration::getArray('aphiria.validation.annotationPaths')
        );
        $container->bindInstance(AnnotationObjectConstraintsRegistrant::class, $constraintAnnotationRegistrant);
    }
}
