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

use Aphiria\Validation\Constraints\RegexConstraint;
use PHPUnit\Framework\TestCase;

class RegexConstraintTest extends TestCase
{
    public function testGettingErrorMessageId(): void
    {
        $constraint = new RegexConstraint('/foo/', 'foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new RegexConstraint('regex'))->getErrorMessagePlaceholders('val'));
    }

    public function testMatchingValuesPass(): void
    {
        $constraint = new RegexConstraint('/^[a-z]{3}$/', 'foo');
        $this->assertTrue($constraint->passes('foo'));
    }

    /**
     * Tests that non-matching values fail
     */
    public function testNonMatchingValuesFail(): void
    {
        $constraint = new RegexConstraint('/^[a-z]{3}$/', 'foo');
        $this->assertFalse($constraint->passes('a'));
    }
}
