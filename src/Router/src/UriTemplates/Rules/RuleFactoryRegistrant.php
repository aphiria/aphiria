<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Rules;

/**
 * Defines the rule factory registrant that registers all the built-in rules' factories
 */
final class RuleFactoryRegistrant
{
    /**
     * Registers the built-in rule factories
     *
     * @param IRuleFactory The rule factory to register new factories to
     * @return IRuleFactory The rule factory with all the registered factories (for chaining)
     */
    public function registerRuleFactories(IRuleFactory $ruleFactory): IRuleFactory
    {
        $ruleFactory->registerRuleFactory(AlphaRule::getSlug(), fn () => new AlphaRule());
        $ruleFactory->registerRuleFactory(AlphanumericRule::getSlug(), fn () => new AlphanumericRule());
        $ruleFactory->registerRuleFactory(BetweenRule::getSlug(), fn ($min, $max, bool $isInclusive = true) => new BetweenRule($min, $max, $isInclusive));
        $ruleFactory->registerRuleFactory(DateRule::getSlug(), fn ($formats) => new DateRule($formats));
        $ruleFactory->registerRuleFactory(InRule::getSlug(), fn (array $acceptableValues) => new InRule($acceptableValues));
        $ruleFactory->registerRuleFactory(IntegerRule::getSlug(), fn () => new IntegerRule());
        $ruleFactory->registerRuleFactory(NotInRule::getSlug(), fn (array $unacceptableValues) => new NotInRule($unacceptableValues));
        $ruleFactory->registerRuleFactory(NumericRule::getSlug(), fn () => new NumericRule());
        $ruleFactory->registerRuleFactory(RegexRule::getSlug(), fn (string $regex) => new RegexRule($regex));
        $ruleFactory->registerRuleFactory(UuidV4Rule::getSlug(), fn () => new UuidV4Rule());

        return $ruleFactory;
    }
}
