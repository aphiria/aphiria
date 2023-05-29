<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests;

use Aphiria\Authorization\AuthorizationPolicy;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    public static function getAuthenticationSchemes(): array
    {
        return [
            ['foo', ['foo']],
            [['foo', 'bar'], ['foo', 'bar']],
            [null, null]
        ];
    }

    public static function getRequirements(): array
    {
        $object = new class () {
        };

        return [
            [$object, [$object]],
            [[$object, $object], [$object, $object]]
        ];
    }

    /**
     * @param string|list<string>|null $authenticationSchemeNames The authentication scheme name or names to test
     * @param list<string>|null $expectedAuthenticationSchemeNames The expected nullable array of authentication scheme names
     */
    #[TestWith(['foo', ['foo']])]
    #[TestWith([['foo', 'bar'], ['foo', 'bar']])]
    #[TestWith([null, null])]
    public function testAuthenticationSchemeNamesAreConvertedToArray(string|array|null $authenticationSchemeNames, ?array $expectedAuthenticationSchemeNames): void
    {
        $policy = new AuthorizationPolicy('foo', [$this], $authenticationSchemeNames);
        $this->assertSame($expectedAuthenticationSchemeNames, $policy->authenticationSchemeNames);
    }

    public function testEmptyRequirementsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requirements cannot be empty');
        /** @psalm-suppress ArgumentTypeCoercion We are purposely testing an empty array */
        new AuthorizationPolicy('foo', [], []);
    }

    public function testNameIsSetInConstructor(): void
    {
        $policy = new AuthorizationPolicy('foo', $this, []);
        $this->assertSame('foo', $policy->name);
    }

    /**
     * @param object|list<object> $requirements The requirement or list of requirements
     * @param list<object> $expectedRequirements The expected array of requirements
     */
    #[DataProvider('getRequirements')]
    public function testRequirementsAreConvertedToArray(object|array $requirements, array $expectedRequirements): void
    {
        $policy = new AuthorizationPolicy('foo', $requirements, []);
        $this->assertSame($expectedRequirements, $policy->requirements);
    }
}
