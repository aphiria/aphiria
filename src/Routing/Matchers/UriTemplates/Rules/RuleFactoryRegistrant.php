<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\UriTemplates\Rules;

/**
 * Defines the rule factory registrant that registers all the built-in rules' factories
 */
class RuleFactoryRegistrant
{
    /**
     * Registers the built-in rule factories
     *
     * @param IRuleFactory The rule factory to register new factories to
     * @return IRuleFactory The rule factory with all the registered factories (for chaining)
     */
    public function registerRuleFactories(IRuleFactory $ruleFactory) : IRuleFactory
    {
        $ruleFactory->registerRuleFactory(AlphaRule::getSlug(), function () {
            return new AlphaRule();
        });
        $ruleFactory->registerRuleFactory(AlphanumericRule::getSlug(), function () {
            return new AlphanumericRule();
        });
        $ruleFactory->registerRuleFactory(BetweenRule::getSlug(), function ($min, $max, bool $isInclusive = true) {
            return new BetweenRule($min, $max, $isInclusive);
        });
        $ruleFactory->registerRuleFactory(DateRule::getSlug(), function ($formats) {
            return new DateRule($formats);
        });
        $ruleFactory->registerRuleFactory(InRule::getSlug(), function (array $acceptableValues) {
            return new InRule($acceptableValues);
        });
        $ruleFactory->registerRuleFactory(IntegerRule::getSlug(), function () {
            return new IntegerRule();
        });
        $ruleFactory->registerRuleFactory(NotInRule::getSlug(), function (array $unacceptableValues) {
            return new NotInRule($unacceptableValues);
        });
        $ruleFactory->registerRuleFactory(NumericRule::getSlug(), function () {
            return new NumericRule();
        });
        $ruleFactory->registerRuleFactory(RegexRule::getSlug(), function (string $regex) {
            return new RegexRule($regex);
        });
        $ruleFactory->registerRuleFactory(UuidV4Rule::getSlug(), function () {
            return new UuidV4Rule();
        });

        return $ruleFactory;
    }
}
