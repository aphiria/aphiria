<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests;

use Aphiria\Validation\ValidatorFactory;
use Aphiria\Validation\Rules\RulesFactory;
use Aphiria\Validation\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validator factory
 */
class ValidatorFactoryTest extends TestCase
{
    private ValidatorFactory $validatorFactory;
    /** @var RulesFactory|MockObject The rules factory */
    private RulesFactory $rulesFactory;

    protected function setUp(): void
    {
        $this->rulesFactory = $this->getMockBuilder(RulesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorFactory = new ValidatorFactory($this->rulesFactory);
    }

    public function testValidatorIsSetUpCorrectly(): void
    {
        $this->assertEquals(
            new Validator($this->rulesFactory),
            $this->validatorFactory->createValidator()
        );
    }
}
