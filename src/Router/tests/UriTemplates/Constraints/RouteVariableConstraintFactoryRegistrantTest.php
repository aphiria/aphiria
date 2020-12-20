<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Constraints;

use Aphiria\Routing\UriTemplates\Constraints\AlphaConstraint;
use Aphiria\Routing\UriTemplates\Constraints\AlphanumericConstraint;
use Aphiria\Routing\UriTemplates\Constraints\BetweenConstraint;
use Aphiria\Routing\UriTemplates\Constraints\DateConstraint;
use Aphiria\Routing\UriTemplates\Constraints\InConstraint;
use Aphiria\Routing\UriTemplates\Constraints\IntegerConstraint;
use Aphiria\Routing\UriTemplates\Constraints\NotInConstraint;
use Aphiria\Routing\UriTemplates\Constraints\NumericConstraint;
use Aphiria\Routing\UriTemplates\Constraints\RegexConstraint;
use Aphiria\Routing\UriTemplates\Constraints\RouteVariableConstraintFactory;
use Aphiria\Routing\UriTemplates\Constraints\RouteVariableConstraintFactoryRegistrant;
use Aphiria\Routing\UriTemplates\Constraints\UuidV4Constraint;
use PHPUnit\Framework\TestCase;

class RouteVariableConstraintFactoryRegistrantTest extends TestCase
{
    private RouteVariableConstraintFactoryRegistrant $registrant;

    protected function setUp(): void
    {
        $this->registrant = new RouteVariableConstraintFactoryRegistrant();
    }

    public function testDefaultConstraintFactoriesAreRegistered(): void
    {
        $factory = new RouteVariableConstraintFactory();
        $this->registrant->registerConstraintFactories($factory);
        $this->assertInstanceOf(
            AlphaConstraint::class,
            $factory->createConstraint(AlphaConstraint::getSlug())
        );
        $this->assertInstanceOf(
            AlphanumericConstraint::class,
            $factory->createConstraint(AlphanumericConstraint::getSlug())
        );
        $this->assertInstanceOf(
            BetweenConstraint::class,
            $factory->createConstraint(BetweenConstraint::getSlug(), [1, 2])
        );
        $this->assertInstanceOf(
            DateConstraint::class,
            $factory->createConstraint(DateConstraint::getSlug(), [['Ymd']])
        );
        $this->assertInstanceOf(
            InConstraint::class,
            $factory->createConstraint(InConstraint::getSlug(), [['foo']])
        );
        $this->assertInstanceOf(
            IntegerConstraint::class,
            $factory->createConstraint(IntegerConstraint::getSlug())
        );
        $this->assertInstanceOf(
            NotInConstraint::class,
            $factory->createConstraint(NotInConstraint::getSlug(), [['foo']])
        );
        $this->assertInstanceOf(
            NumericConstraint::class,
            $factory->createConstraint(NumericConstraint::getSlug())
        );
        $this->assertInstanceOf(
            RegexConstraint::class,
            $factory->createConstraint(RegexConstraint::getSlug(), ['/foo/'])
        );
        $this->assertInstanceOf(
            UuidV4Constraint::class,
            $factory->createConstraint(UuidV4Constraint::getSlug())
        );
    }
}
