<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Tests\UriTemplates\Rules;

use Closure;
use Opulence\Routing\Matchers\UriTemplates\Rules\RuleFactoryRegistrant;

/**
 * Tests the rule factory registrant
 */
class RuleFactoryRegistrantTest
{
    /** @var RuleFactoryRegistrant The registrant to use in tests */
    private $registrant = null;
    /** @var IRuleFactory|\PHPUnit_Framework_MockObject_MockObject The rule factory to register to */
    private $ruleFactory = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->registrant = new RuleFactoryRegistrant();
        $this->ruleFactory = $this->createMock(IRuleFactory::class);
    }

    /**
     * Tests that the alpha rule is registered
     */
    public function testAlphaRuleIsRegistered() : void
    {
        $this->ruleFactory->expects($this->once())
            ->method('registerRuleFactory')
            ->with(AlphaRule::getSlug(), $this->isInstanceOf(Closure::class));
        $actualRuleFactory = $this->registrant->registerRuleFactories($this->ruleFactory);
        $this->assertSame($actualRuleFactory, $this->ruleFactory);
    }

    /**
     * Tests that the alphanumeric rule is registered
     */
    public function testAlphanumericRuleIsRegistered() : void
    {
        $this->ruleFactory->expects($this->once())
            ->method('registerRuleFactory')
            ->with(AlphanumericRule::getSlug(), $this->isInstanceOf(Closure::class));
        $actualRuleFactory = $this->registrant->registerRuleFactories($this->ruleFactory);
        $this->assertSame($actualRuleFactory, $this->ruleFactory);
    }

    /**
     * Tests that the between rule is registered
     */
    public function testBetweenRuleIsRegistered() : void
    {
        $this->ruleFactory->expects($this->once())
            ->method('registerRuleFactory')
            ->with(BetweenRule::getSlug(), $this->isInstanceOf(Closure::class));
        $actualRuleFactory = $this->registrant->registerRuleFactories($this->ruleFactory);
        $this->assertSame($actualRuleFactory, $this->ruleFactory);
    }

    /**
     * Tests that the date rule is registered
     */
    public function testDateRuleIsRegistered() : void
    {
        $this->ruleFactory->expects($this->once())
            ->method('registerRuleFactory')
            ->with(DateRule::getSlug(), $this->isInstanceOf(Closure::class));
        $actualRuleFactory = $this->registrant->registerRuleFactories($this->ruleFactory);
        $this->assertSame($actualRuleFactory, $this->ruleFactory);
    }

    /**
     * Tests that the in-array rule is registered
     */
    public function testInArrayRuleIsRegistered() : void
    {
        $this->ruleFactory->expects($this->once())
            ->method('registerRuleFactory')
            ->with(InRule::getSlug(), $this->isInstanceOf(Closure::class));
        $actualRuleFactory = $this->registrant->registerRuleFactories($this->ruleFactory);
        $this->assertSame($actualRuleFactory, $this->ruleFactory);
    }

    /**
     * Tests that the integer rule is registered
     */
    public function testIntegerRuleIsRegistered() : void
    {
        $this->ruleFactory->expects($this->once())
            ->method('registerRuleFactory')
            ->with(Integer::getSlug(), $this->isInstanceOf(Closure::class));
        $actualRuleFactory = $this->registrant->registerRuleFactories($this->ruleFactory);
        $this->assertSame($actualRuleFactory, $this->ruleFactory);
    }

    /**
     * Tests that the not-in-array rule is registered
     */
    public function testNotInArrayRuleIsRegistered() : void
    {
        $this->ruleFactory->expects($this->once())
            ->method('registerRuleFactory')
            ->with(NotInRule::getSlug(), $this->isInstanceOf(Closure::class));
        $actualRuleFactory = $this->registrant->registerRuleFactories($this->ruleFactory);
        $this->assertSame($actualRuleFactory, $this->ruleFactory);
    }

    /**
     * Tests that the numeric rule is registered
     */
    public function testNumericRuleIsRegistered() : void
    {
        $this->ruleFactory->expects($this->once())
            ->method('registerRuleFactory')
            ->with(NumericRule::getSlug(), $this->isInstanceOf(Closure::class));
        $actualRuleFactory = $this->registrant->registerRuleFactories($this->ruleFactory);
        $this->assertSame($actualRuleFactory, $this->ruleFactory);
    }

    /**
     * Tests that the regex rule is registered
     */
    public function testRegexRuleIsRegistered() : void
    {
        $this->ruleFactory->expects($this->once())
            ->method('registerRuleFactory')
            ->with(RegexRule::getSlug(), $this->isInstanceOf(Closure::class));
        $actualRuleFactory = $this->registrant->registerRuleFactories($this->ruleFactory);
        $this->assertSame($actualRuleFactory, $this->ruleFactory);
    }

    /**
     * Tests that the UUIDV4 rule is registered
     */
    public function testUuidV4RuleIsRegistered() : void
    {
        $this->ruleFactory->expects($this->once())
            ->method('registerRuleFactory')
            ->with(UuidV4Rule::getSlug(), $this->isInstanceOf(Closure::class));
        $actualRuleFactory = $this->registrant->registerRuleFactories($this->ruleFactory);
        $this->assertSame($actualRuleFactory, $this->ruleFactory);
    }
}
