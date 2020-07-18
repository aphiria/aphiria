<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\Constraint;
use PHPUnit\Framework\TestCase;

class ConstraintTest extends TestCase
{
    private Constraint $constraint;

    protected function setUp(): void
    {
        $this->constraint = new class('foo') extends Constraint {
            public function __construct(string $errorMessageId)
            {
                parent::__construct($errorMessageId);
            }

            public function passes($value): bool
            {
                return true;
            }
        };
    }

    public function testGettingErrorMessagePlaceholderForNonScalarNonObjectUsesHardCodedStringAsValue(): void
    {
        $this->assertEquals(['value' => 'value'], $this->constraint->getErrorMessagePlaceholders(fopen('php://temp', 'rb')));
    }

    public function testGettingErrorMessagePlaceholderForScalarValuesUsesThatValue(): void
    {
        $this->assertEquals(['value' => 'foo'], $this->constraint->getErrorMessagePlaceholders('foo'));
        $this->assertEquals(['value' => 1], $this->constraint->getErrorMessagePlaceholders(1));
        $this->assertEquals(['value' => 1.0], $this->constraint->getErrorMessagePlaceholders(1.0));
        $this->assertEquals(['value' => true], $this->constraint->getErrorMessagePlaceholders(true));
    }

    public function testGettingErrorMessagePlaceholderForSerializableObjectsUsesSerializedValue(): void
    {
        $value = new class() {
            public function __toString()
            {
                return 'foo';
            }
        };
        $this->assertEquals(['value' => 'foo'], $this->constraint->getErrorMessagePlaceholders($value));
    }

    public function testGettingErrorMessagePlaceholderForUnserializableObjectsUsesClassName(): void
    {
        $value = new class() {
        };
        $this->assertEquals(
            ['value' => \get_class($value) . ' object'],
            $this->constraint->getErrorMessagePlaceholders($value)
        );
    }
}
