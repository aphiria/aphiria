<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

/**
 * Defines the constraint factory registrant that registers all the built-in constraint' factories
 */
final class ConstraintFactoryRegistrant
{
    /**
     * Registers the built-in constraint factories
     *
     * @param IRouteVariableConstraintFactory The constraint factory to register new factories to
     * @return IRouteVariableConstraintFactory The constraint factory with all the registered factories (for chaining)
     */
    public function registerConstraintFactories(
        IRouteVariableConstraintFactory $constraintFactory
    ): IRouteVariableConstraintFactory {
        $constraintFactory->registerConstraintFactory(
            AlphaConstraint::getSlug(),
            fn () => new AlphaConstraint()
        );
        $constraintFactory->registerConstraintFactory(
            AlphanumericConstraint::getSlug(), fn () => new AlphanumericConstraint()
        );
        $constraintFactory->registerConstraintFactory(
            BetweenConstraint::getSlug(),
            fn ($min, $max, bool $isInclusive = true) => new BetweenConstraint($min, $max, $isInclusive)
        );
        $constraintFactory->registerConstraintFactory(
            DateConstraint::getSlug(),
            fn ($formats) => new DateConstraint($formats)
        );
        $constraintFactory->registerConstraintFactory(
            InConstraint::getSlug(),
            fn (array $acceptableValues) => new InConstraint($acceptableValues)
        );
        $constraintFactory->registerConstraintFactory(
            IntegerConstraint::getSlug(),
            fn () => new IntegerConstraint()
        );
        $constraintFactory->registerConstraintFactory(
            NotInConstraint::getSlug(),
            fn (array $unacceptableValues) => new NotInConstraint($unacceptableValues)
        );
        $constraintFactory->registerConstraintFactory(
            NumericConstraint::getSlug(),
            fn () => new NumericConstraint()
        );
        $constraintFactory->registerConstraintFactory(
            RegexConstraint::getSlug(),
            fn (string $regex) => new RegexConstraint($regex)
        );
        $constraintFactory->registerConstraintFactory(
            UuidV4Constraint::getSlug(),
            fn () => new UuidV4Constraint()
        );

        return $constraintFactory;
    }
}
