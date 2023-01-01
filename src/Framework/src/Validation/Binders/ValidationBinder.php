<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Validation\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\MissingConfigurationValueException;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Validation\Constraints\Attributes\AttributeObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\Caching\FileObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\Caching\IObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\ErrorMessages\DefaultErrorMessageTemplateRegistry;
use Aphiria\Validation\ErrorMessages\IcuFormatErrorMessageInterpolator;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\ErrorMessages\IErrorMessageTemplateRegistry;
use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageInterpolator;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\Validator;
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
     */
    public function bind(IContainer $container): void
    {
        $objectConstraints = new ObjectConstraintsRegistry();
        $container->bindInstance(ObjectConstraintsRegistry::class, $objectConstraints);
        $validator = new Validator($objectConstraints);
        $container->bindInstance([IValidator::class, Validator::class], $validator);
        $constraintCache = new FileObjectConstraintsRegistryCache(GlobalConfiguration::getString('aphiria.validation.constraintsCachePath'));
        $container->bindInstance(IObjectConstraintsRegistryCache::class, $constraintCache);

        if (\getenv('APP_ENV') === 'production') {
            $constraintsRegistrants = new ObjectConstraintsRegistrantCollection($constraintCache);
        } else {
            $constraintsRegistrants = new ObjectConstraintsRegistrantCollection(null);
        }

        $container->bindInstance(ObjectConstraintsRegistrantCollection::class, $constraintsRegistrants);
        $errorMessageTemplateConfiguration = null;

        if (GlobalConfiguration::tryGetArray('aphiria.validation.errorMessageTemplates', $errorMessageTemplateConfiguration)) {
            /** @var array{type: class-string<IErrorMessageTemplateRegistry>} $errorMessageTemplateConfiguration */
            if (!isset($errorMessageTemplateConfiguration['type'])) {
                throw new InvalidArgumentException('Missing key "type" from error message template config');
            }

            $errorMessageTemplates = match ($errorMessageTemplateConfiguration['type']) {
                DefaultErrorMessageTemplateRegistry::class => new DefaultErrorMessageTemplateRegistry(),
                default => $container->resolve($errorMessageTemplateConfiguration['type']),
            };
        } else {
            $errorMessageTemplates = new DefaultErrorMessageTemplateRegistry();
        }

        if (!$errorMessageTemplates instanceof IErrorMessageTemplateRegistry) {
            throw new InvalidArgumentException('Error message template must be instance of ' . IErrorMessageTemplateRegistry::class);
        }

        $errorMessageInterpolatorConfiguration = GlobalConfiguration::getArray('aphiria.validation.errorMessageInterpolator');

        switch ($errorMessageInterpolatorConfiguration['type']) {
            case StringReplaceErrorMessageInterpolator::class:
                $errorMessageInterpolator = new StringReplaceErrorMessageInterpolator($errorMessageTemplates);
                break;
            case IcuFormatErrorMessageInterpolator::class:
                $errorMessageInterpolator = new IcuFormatErrorMessageInterpolator(
                    $errorMessageTemplates,
                    (string)($errorMessageInterpolatorConfiguration['defaultLocale'] ?? 'en')
                );
                break;
            default:
                throw new InvalidArgumentException("Unsupported error message interpolator type {$errorMessageInterpolatorConfiguration['type']}");
        }

        $container->bindInstance(IErrorMessageInterpolator::class, $errorMessageInterpolator);

        // Register some constraint attribute dependencies
        /** @psalm-suppress ArgumentTypeCoercion We will assume this contains an array of strings */
        $constraintAttributeRegistrant = new AttributeObjectConstraintsRegistrant(
            GlobalConfiguration::getArray('aphiria.validation.attributePaths')
        );
        $container->bindInstance(AttributeObjectConstraintsRegistrant::class, $constraintAttributeRegistrant);
    }
}
