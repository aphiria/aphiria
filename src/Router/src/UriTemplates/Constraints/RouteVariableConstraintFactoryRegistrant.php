<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

/**
 * Defines the constraint factory registrant that registers all the built-in constraint' factories
 */
final class RouteVariableConstraintFactoryRegistrant
{
    /**
     * Registers the built-in constraint factories
     *
     * @param RouteVariableConstraintFactory The constraint factory to register new factories to
     * @return RouteVariableConstraintFactory The constraint factory with all the registered factories (for chaining)
     */
    public function registerConstraintFactories(
        RouteVariableConstraintFactory $constraintFactory
    ): RouteVariableConstraintFactory {
        $constraintFactory->registerConstraintFactory(
            AlphaConstraint::getSlug(),
            fn (): AlphaConstraint => new AlphaConstraint()
        );
        $constraintFactory->registerConstraintFactory(
            AlphanumericConstraint::getSlug(),
            fn (): AlphanumericConstraint => new AlphanumericConstraint()
        );
        $constraintFactory->registerConstraintFactory(
            BetweenConstraint::getSlug(),
            fn (int|float $min, int|float $max, bool $isInclusive = true): BetweenConstraint => new BetweenConstraint($min, $max, $isInclusive)
        );
        $constraintFactory->registerConstraintFactory(
            DateConstraint::getSlug(),
            fn (string|array $formats): DateConstraint => new DateConstraint($formats)
        );
        $constraintFactory->registerConstraintFactory(
            InConstraint::getSlug(),
            fn (array $acceptableValues): InConstraint => new InConstraint($acceptableValues)
        );
        $constraintFactory->registerConstraintFactory(
            IntegerConstraint::getSlug(),
            fn (): IntegerConstraint => new IntegerConstraint()
        );
        $constraintFactory->registerConstraintFactory(
            NotInConstraint::getSlug(),
            fn (array $unacceptableValues): NotInConstraint => new NotInConstraint($unacceptableValues)
        );
        $constraintFactory->registerConstraintFactory(
            NumericConstraint::getSlug(),
            fn (): NumericConstraint => new NumericConstraint()
        );
        $constraintFactory->registerConstraintFactory(
            RegexConstraint::getSlug(),
            fn (string $regex): RegexConstraint => new RegexConstraint($regex)
        );
        $constraintFactory->registerConstraintFactory(
            UuidV4Constraint::getSlug(),
            fn (): UuidV4Constraint => new UuidV4Constraint()
        );

        return $constraintFactory;
    }
}
