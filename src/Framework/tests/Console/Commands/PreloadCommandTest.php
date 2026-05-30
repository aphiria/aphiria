<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Commands;

use Aphiria\Framework\Console\Commands\PreloadCommand;
use PHPUnit\Framework\TestCase;

class PreloadCommandTest extends TestCase
{
    private PreloadCommand $command;

    protected function setUp(): void
    {
        $this->command = new PreloadCommand();
    }

    public function testCommandNameIsCorrect(): void
    {
        $this->assertSame('app:preload', $this->command->name);
    }

    public function testDescriptionIsCorrect(): void
    {
        $this->assertSame('Generates a PHP preload script from OPcache', $this->command->description);
    }

    public function testDryRunOptionIsFlag(): void
    {
        $dryRunOption = $this->findOption('dry-run');
        $this->assertNotNull($dryRunOption);
        $this->assertFalse($dryRunOption->valueIsPermitted);
    }

    public function testExcludeOptionIsArray(): void
    {
        $excludeOption = $this->findOption('exclude');
        $this->assertNotNull($excludeOption);
        $this->assertSame('e', $excludeOption->shortName);
        $this->assertTrue($excludeOption->valueIsArray);
    }

    public function testMinHitsOptionHasCorrectConfiguration(): void
    {
        $minHitsOption = $this->findOption('min-hits');
        $this->assertNotNull($minHitsOption);
        $this->assertNull($minHitsOption->shortName);
        $this->assertTrue($minHitsOption->valueIsRequired);
        $this->assertSame('1', $minHitsOption->defaultValue);
    }

    public function testOutputOptionHasCorrectConfiguration(): void
    {
        $outputOption = $this->findOption('output');
        $this->assertNotNull($outputOption);
        $this->assertSame('o', $outputOption->shortName);
        $this->assertTrue($outputOption->valueIsRequired);
        $this->assertSame('preload.php', $outputOption->defaultValue);
    }

    public function testUrlsArgumentIsOptionalArray(): void
    {
        $this->assertCount(1, $this->command->arguments);
        $urlsArgument = $this->command->arguments[0];
        $this->assertSame('urls', $urlsArgument->name);
        $this->assertTrue($urlsArgument->isOptional);
        $this->assertTrue($urlsArgument->isArray);
    }

    /**
     * Finds an option by name
     */
    private function findOption(string $name): ?\Aphiria\Console\Input\Option
    {
        foreach ($this->command->options as $option) {
            if ($option->name === $name) {
                return $option;
            }
        }

        return null;
    }
}
