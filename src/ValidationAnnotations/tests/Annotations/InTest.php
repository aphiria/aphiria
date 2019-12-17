<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ValidationAnnotations\Tests\Annotations;

use Aphiria\ValidationAnnotations\Annotations\In;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the in constraint annotation
 */
class InTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new In(['value' => [1]]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testNotSettingArrayValuesThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be set to an array');
        new In(['value' => 'foo']);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new In(['value' => [123], 'errorMessageId' => 'foo']);
        $this->assertEquals('foo', $annotation->errorMessageId);
    }

    public function testValuesCanBeSetViaValue(): void
    {
        $annotation = new In(['value' => ['foo']]);
        $this->assertEquals(['foo'], $annotation->values);
    }
}
