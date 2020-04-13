<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\IPAddressConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the IP address constraint
 */
class IPAddressConstraintTest extends TestCase
{
    public function testFailingValue(): void
    {
        $constraint = new IPAddressConstraint('foo');
        $this->assertFalse($constraint->passes(''));
        $this->assertFalse($constraint->passes('123'));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new IPAddressConstraint('foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new IPAddressConstraint())->getErrorMessagePlaceholders('val'));
    }

    public function testPassingValue(): void
    {
        $constraint = new IPAddressConstraint('foo');
        $this->assertTrue($constraint->passes('127.0.0.1'));
    }
}
