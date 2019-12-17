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

use Aphiria\ValidationAnnotations\Annotations\AlphanumericTemp;
use PHPUnit\Framework\TestCase;

/**
 * Tests the alphanumeric constraint annotation
 */
class AlphanumericTempTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new AlphanumericTemp([]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingWithEmptyArrayCreatesInstance(): void
    {
        new AlphanumericTemp([]);

        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new AlphanumericTemp(['errorMessageId' => 'foo']);
        $this->assertEquals('foo', $annotation->errorMessageId);
    }
}
