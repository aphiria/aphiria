<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Annotations;

use Aphiria\Validation\Constraints\Annotations\Alpha;
use PHPUnit\Framework\TestCase;

/**
 * Tests the alpha constraint annotation
 */
class AlphaTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new Alpha([]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new Alpha([]);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testCreatingWithEmptyArrayCreatesInstance(): void
    {
        new Alpha([]);

        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new Alpha(['errorMessageId' => 'foo']);
        $this->assertEquals('foo', $annotation->errorMessageId);
    }
}
