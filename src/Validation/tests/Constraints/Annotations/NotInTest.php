<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Annotations;

use Aphiria\Validation\Constraints\Annotations\NotIn;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the not-in constraint annotation
 */
class NotInTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new NotIn(['value' => ['val']]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new NotIn(['value' => ['val']]);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testNotSettingArrayValuesThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be set to an array');
        new NotIn(['value' => 'foo']);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new NotIn(['value' => ['val'], 'errorMessageId' => 'foo']);
        $this->assertEquals('foo', $annotation->errorMessageId);
    }

    public function testValuesCanBeSetViaValue(): void
    {
        $annotation = new NotIn(['value' => ['foo']]);
        $this->assertEquals(['foo'], $annotation->values);
    }
}
