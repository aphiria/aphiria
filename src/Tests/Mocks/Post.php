<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Mocks;

use DateTime;

/**
 * Defines a fake "post" object
 */
class Post
{
    /** @var int The post's ID */
    private $id;
    /** @var User The post's author */
    private $author;
    /** @var string The post's publication date */
    private $publicationDate;

    /**
     * @param int $id The post's ID
     * @param User $author The post's author
     * @param DateTime $publicationDate The post's publication date
     */
    public function __construct(int $id, User $author, DateTime $publicationDate)
    {
        $this->id = $id;
        $this->author = $author;
        $this->publicationDate = $publicationDate;
    }

    /**
     * Gets the posts's author
     *
     * @return User The post's author
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * Gets the user's ID
     *
     * @return int The user's ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the posts's publication date
     *
     * @return DateTime The post's publication date
     */
    public function getPublicationDate(): DateTime
    {
        return $this->publicationDate;
    }
}
