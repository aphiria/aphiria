<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Validation\Binders;

use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\Configuration\MissingConfigurationValueException;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\Caching\FileObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\Caching\IObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\ErrorMessages\DefaultErrorMessageTemplateRegistry;
use Aphiria\Validation\ErrorMessages\IcuFormatErrorMessageInterpolator;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageInterpolator;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\Validator;
use Doctrine\Common\Annotations\AnnotationException;
use InvalidArgumentException;

/**
 * Defines the validation binder
 */
final class ValidationBinder extends Binder
{
    /**
     * @inheritdoc
     * @throws MissingConfigurationValueException Thrown if the config is missing values
     * @throws InvalidArgumentException Thrown if the error message interpolator configuration is invalid
     * @throws AnnotationException Thrown if PHP is not configured to handle scanning for annotations
     */
    public function bind(IContainer $container): void
    {
        $objectConstraints = new ObjectConstraintsRegistry();
        $container->bindInstance(ObjectConstraintsRegistry::class, $objectConstraints);
        $validator = new Validator($objectConstraints);
        $container->bindInstance([IValidator::class, Validator::class], $validator);
        $constraintCache = new FileObjectConstraintsRegistryCache(GlobalConfiguration::getString('aphiria.validation.constraintsCachePath'));
        $container->bindInstance(IObjectConstraintsRegistryCache::class, $constraintCache);

        if (getenv('APP_ENV') === 'production') {
            $constraintsRegistrants = new ObjectConstraintsRegistrantCollection($constraintCache);
        } else {
            $constraintsRegistrants = new ObjectConstraintsRegistrantCollection(null);
        }

        $container->bindInstance(ObjectConstraintsRegistrantCollection::class, $constraintsRegistrants);
        $errorMessageTemplateConfiguration = null;

        if (GlobalConfiguration::tryGetArray('aphiria.validation.errorMessageTemplates', $errorMessageTemplateConfiguration)) {
            switch ($errorMessageTemplateConfiguration['type']) {
                case DefaultErrorMessageTemplateRegistry::class:
                    $errorMessageTemplates = new DefaultErrorMessageTemplateRegistry();
                    break;
                default:
                    $errorMessageTemplates = $container->resolve($errorMessageTemplateConfiguration['type']);
                    break;
            }
        } else {
            $errorMessageTemplates = null;
        }

        $errorMessageInterpolatorConfiguration = GlobalConfiguration::getArray('aphiria.validation.errorMessageInterpolator');

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
                throw new InvalidArgumentException("Unsupported error message interpolator type {$errorMessageInterpolatorConfiguration['type']}");
        }

        $container->bindInstance(IErrorMessageInterpolator::class, $errorMessageInterpolator);

        // Register some constraint annotation dependencies
        $constraintAnnotationRegistrant = new AnnotationObjectConstraintsRegistrant(
            GlobalConfiguration::getArray('aphiria.validation.annotationPaths')
        );
        $container->bindInstance(AnnotationObjectConstraintsRegistrant::class, $constraintAnnotationRegistrant);
    }
}
