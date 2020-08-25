<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Annotations;

use Aphiria\Validation\Constraints\Annotations\Integer;
use PHPUnit\Framework\TestCase;

class IntegerTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new Integer([]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new Integer([]);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testCreatingConstraintUsesErrorMessageIdIfSpecified(): void
    {
        $annotation = new Integer(['errorMessageId' => 'foo']);
        $this->assertSame('foo', $annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testCreatingWithEmptyArrayCreatesInstance(): void
    {
        new Integer([]);

        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new Integer(['errorMessageId' => 'foo']);
        $this->assertSame('foo', $annotation->errorMessageId);
    }
}
