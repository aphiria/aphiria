<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Rules;

use Aphiria\Validation\Rules\Errors\Compilers\ICompiler;
use Aphiria\Validation\Rules\Errors\ErrorTemplateRegistry;
use Aphiria\Validation\Rules\RulesFactory;
use Aphiria\Validation\Rules\RuleExtensionRegistry;
use Aphiria\Validation\Rules\Rules;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the rules factory
 */
class RulesFactoryTest extends TestCase
{
    public function testRulesCreated(): void
    {
        /** @var RuleExtensionRegistry|MockObject $ruleExtensionRegistry */
        $ruleExtensionRegistry = $this->createMock(RuleExtensionRegistry::class);
        /** @var ErrorTemplateRegistry|MockObject $errorTemplateRegistry */
        $errorTemplateRegistry = $this->createMock(ErrorTemplateRegistry::class);
        /** @var ICompiler|MockObject $errorTemplateCompiler */
        $errorTemplateCompiler = $this->createMock(ICompiler::class);
        $factory = new RulesFactory($ruleExtensionRegistry, $errorTemplateRegistry, $errorTemplateCompiler);
        $this->assertInstanceOf(Rules::class, $factory->createRules());
    }
}
