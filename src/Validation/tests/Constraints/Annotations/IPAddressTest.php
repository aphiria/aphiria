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

use Aphiria\Validation\Constraints\Annotations\IPAddress;
use PHPUnit\Framework\TestCase;

class IPAddressTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new IPAddress([]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new IPAddress([]);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testCreatingConstraintUsesErrorMessageIdIfSpecified(): void
    {
        $annotation = new IPAddress(['errorMessageId' => 'foo']);
        $this->assertSame('foo', $annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testCreatingWithEmptyArrayCreatesInstance(): void
    {
        new IPAddress([]);

        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new IPAddress(['errorMessageId' => 'foo']);
        $this->assertSame('foo', $annotation->errorMessageId);
    }
}
