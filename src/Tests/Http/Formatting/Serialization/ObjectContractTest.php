<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use DateTime;
use Opulence\Net\Http\Formatting\Serialization\ContractRegistry;
use Opulence\Net\Http\Formatting\Serialization\DefaultContractRegistrant;
use Opulence\Net\Http\Formatting\Serialization\IEncodingInterceptor;
use Opulence\Net\Http\Formatting\Serialization\Property;
use Opulence\Net\Tests\Http\Formatting\Serialization\Mocks\Post;
use Opulence\Net\Tests\Http\Formatting\Serialization\Mocks\User;

/**
 * Tests the object contract
 */
class ObjectContractTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContractRegistry The contract registry to use in tests */
    private $contracts;

    public function setUp(): void
    {
        $this->contracts = new ContractRegistry();
        (new DefaultContractRegistrant)->registerContracts($this->contracts);
        $this->contracts->registerObjectContract(
            User::class,
            function ($hash) {
                return new User($hash['id'], $hash['email']);
            },
            new Property('id', 'int', function (User $user) {
                return $user->getId();
            }),
            new Property('email', 'string', function (User $user) {
                return $user->getEmail();
            })
        );
        $this->contracts->registerObjectContract(
            Post::class,
            function ($hash) {
                return new Post($hash['id'], $hash['author'], $hash['publicationDate']);
            },
            new Property('id', 'int', function (Post $post) {
                return $post->getId();
            }),
            new Property('author', User::class, function (Post $post) {
                return $post->getAuthor();
            }),
            new Property('publicationDate', DateTime::class, function (Post $post) {
                return $post->getPublicationDate();
            })
        );
    }

    public function testDecodingAlsoDecodesPropertyValues(): void
    {
        // Purposely set the ID to a string to verify that it gets converted to an integer
        $encodedValue = ['id' => '123', 'email' => 'foo@bar.com'];
        /** @var User $actualUser */
        $actualUser = $this->contracts->getContractForType(User::class)->decode($encodedValue);
        $this->assertTrue(\is_int($actualUser->getId()));
    }

    public function testDecodingPropertyNamesUsesFuzzyMatchingWhenNoExactMatchIsFound(): void
    {
        $expectedPublicationDate = $this->createIso8601DateTime();
        $expectedPost = new Post(123, new User(456, 'foo@bar.com'), $expectedPublicationDate);
        $encodedPost = [
            'id' => 123,
            'author' => [
                'id' => 456,
                'email' => 'foo@bar.com'
            ],
            // Property name here is snake-case, which should fuzzy match the proper camelCase name
            'publication_date' => $expectedPublicationDate->format(DateTime::ISO8601)
        ];
        /** @var Post $actualPost */
        $actualPost = $this->contracts->getContractForType(Post::class)->decode($encodedPost);
        $this->assertInstanceOf(Post::class, $actualPost);
        $this->assertEquals($expectedPost, $actualPost);
    }

    public function testDecodingWithInterceptorsCallsInterceptorOnEachPropertyValue(): void
    {
        $expectedPost = new Post(123, new User(456, 'foo@bar.com'), $this->createIso8601DateTime());
        $expectedFormattedPublicationDate = $expectedPost->getPublicationDate()->format(DateTime::ISO8601);
        $encodedPost = [
            'id' => 123,
            'author' => [
                'id' => 456,
                'email' => 'foo@bar.com'
            ],
            'publicationDate' => $expectedFormattedPublicationDate
        ];
        // This is the fully decoded post hash just before an instance of Post is created
        $expectedDecodedPostHash = [
            'id' => 123,
            'author' => $expectedPost->getAuthor(),
            'publicationDate' => $expectedPost->getPublicationDate()
        ];
        /** @var IEncodingInterceptor $interceptor */
        $interceptor = $this->createMock(IEncodingInterceptor::class);
        $interceptor->expects($this->at(0))
            ->method('onDecoding')
            ->with(123, 'int')
            ->willReturn(123);
        $interceptor->expects($this->at(1))
            ->method('onDecoding')
            ->with(456, 'int')
            ->willReturn(456);
        $interceptor->expects($this->at(2))
            ->method('onDecoding')
            ->with('foo@bar.com', 'string')
            ->willReturn('foo@bar.com');
        $interceptor->expects($this->at(3))
            ->method('onDecoding')
            ->with($encodedPost['author'], User::class)
            ->willReturn($encodedPost['author']);
        $interceptor->expects($this->at(4))
            ->method('onDecoding')
            ->with($expectedFormattedPublicationDate, DateTime::class)
            ->willReturn($expectedFormattedPublicationDate);
        $interceptor->expects($this->at(5))
            ->method('onDecoding')
            ->with($expectedDecodedPostHash, Post::class)
            ->willReturn($expectedDecodedPostHash);
        $actualPost = $this->contracts->getContractForType(Post::class)->decode($encodedPost, [$interceptor]);
        $this->assertInstanceOf(Post::class, $actualPost);
        $this->assertEquals($expectedPost, $actualPost);
    }

    public function testDecodingUndefinedPropertyIgnoresThatProperty(): void
    {
        $encodedUser = [
            'id' => 123,
            'email' => 'foo@bar.com',
            'doesNotExist' => 'ahhhh'
        ];
        $actualUser = $this->contracts->getContractForType(User::class)->decode($encodedUser);
        $this->assertEquals(new User(123, 'foo@bar.com'), $actualUser);
    }

    public function testGettingTypeReturnsTypeSetInContract(): void
    {
        $this->assertEquals(User::class, $this->contracts->getContractForType(User::class)->getType());
    }

    public function testEncodingAlsoEncodesPropertyValues(): void
    {
        $post = new Post(123, new User(456, 'foo@bar.com'), $this->createIso8601DateTime());
        $encodedPost = $this->contracts->getContractForType(Post::class)->encode($post);
        $this->assertEquals(
            [
                'id' => 123,
                'author' => [
                    'id' => 456,
                    'email' => 'foo@bar.com'
                ],
                'publicationDate' => $post->getPublicationDate()->format(DateTime::ISO8601)
            ],
            $encodedPost
        );
    }

    public function testEnccodingWithInterceptorsCallsInterceptorOnEachPropertyValue(): void
    {
        $post = new Post(123, new User(456, 'foo@bar.com'), $this->createIso8601DateTime());
        $expectedEncodedAuthorHash = ['id' => 456, 'email' => 'foo@bar.com'];
        $expectedEncodedPostHash = [
            'id' => 123,
            'author' => $expectedEncodedAuthorHash,
            'publicationDate' => $post->getPublicationDate()->format(DateTime::ISO8601)
        ];
        /** @var IEncodingInterceptor $interceptor */
        $interceptor = $this->createMock(IEncodingInterceptor::class);
        $interceptor->expects($this->at(0))
            ->method('onEncoding')
            ->with(123, 'int')
            ->willReturn(123);
        $interceptor->expects($this->at(1))
            ->method('onEncoding')
            ->with(456, 'int')
            ->willReturn(456);
        $interceptor->expects($this->at(2))
            ->method('onEncoding')
            ->with('foo@bar.com', 'string')
            ->willReturn('foo@bar.com');
        $interceptor->expects($this->at(3))
            ->method('onEncoding')
            ->with($expectedEncodedAuthorHash, User::class)
            ->willReturn($expectedEncodedAuthorHash);
        $interceptor->expects($this->at(4))
            ->method('onEncoding')
            ->with($expectedEncodedPostHash['publicationDate'], DateTime::class)
            ->willReturn($expectedEncodedPostHash['publicationDate']);
        $interceptor->expects($this->at(5))
            ->method('onEncoding')
            ->with($expectedEncodedPostHash, Post::class)
            ->willReturn($expectedEncodedPostHash);
        $encodedPost = $this->contracts->getContractForType(Post::class)->encode($post, [$interceptor]);
        $this->assertEquals(
            [
                'id' => 123,
                'author' => [
                    'id' => 456,
                    'email' => 'foo@bar.com'
                ],
                'publicationDate' => $post->getPublicationDate()->format(DateTime::ISO8601)
            ],
            $encodedPost
        );
    }


    /**
     * Creates an ISO-8601 DateTime
     * This is useful when doing DateTime comparisons and you don't want fractions of a second included so you can get equaltiy
     *
     * @return DateTime The DateTime
     */
    private function createIso8601DateTime(): DateTime
    {
        return DateTime::createFromFormat(
            DateTime::ISO8601,
            (new DateTime)->format(DateTime::ISO8601)
        );
    }
}
