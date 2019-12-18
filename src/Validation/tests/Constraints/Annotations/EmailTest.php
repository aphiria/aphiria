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

use Aphiria\Validation\Constraints\Annotations\Email;
use PHPUnit\Framework\TestCase;

/**
 * Tests the email constraint annotation
 */
class EmailTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new Email([]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new Email([]);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testCreatingWithEmptyArrayCreatesInstance(): void
    {
        new Email([]);

        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new Email(['errorMessageId' => 'foo']);
        $this->assertEquals('foo', $annotation->errorMessageId);
    }
}
