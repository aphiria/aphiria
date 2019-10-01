<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Tests\Ids;

use Aphiria\Sessions\Ids\UuidV4IdGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Tests the UUID V4 ID generator
 */
class UuidV4IdGeneratorTest extends TestCase
{
    private UuidV4IdGenerator $idGenerator;

    protected function setUp(): void
    {
        $this->idGenerator = new UuidV4IdGenerator();
    }

    public function testGenerateCreatesValidUuidV4String(): void
    {
        $id = $this->idGenerator->generate();
        $this->assertNotEmpty($id);
        $this->idGenerator->idIsValid($id);
    }

    public function testIsIdValidReturnsFalseIfIdIsNotString(): void
    {
        $this->assertFalse($this->idGenerator->idIsValid(123));
    }

    public function testIsIdValidReturnsTrueIfIdIsValidUuidV4String(): void
    {
        $this->assertFalse($this->idGenerator->idIsValid('123e4567-e89b-12d3-a456-426655440000'));
    }
}
