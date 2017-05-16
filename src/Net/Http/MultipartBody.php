<?php
namespace Opulence\Net\Http;

/**
 * Defines the multipart HTTP body
 */
class MultipartBody implements IHttpBody
{
    /** @var string The subtype of the body */
    protected $subType = 'mixed';
    /** @var string The boundary between the bodies */
    protected $boundary = '';
    /** @var IHttpBody[] The list of bodies in this multipart body */
    protected $bodies = [];

    public function __construct(string $subType = 'mixed', ?string $boundary = null)
    {
        $this->subType = $subType;
        // Todo: Generate new UUID if $boundary is null
        $this->boundary = $boundary;
    }

    public function add(IHttpBody $body) : void
    {
        $this->bodies[] = $body;
    }

    /**
     * @inheritdoc
     */
    public function readAsStream() : IStream
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function readAsString() : string
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function writeToStream(IStream $stream) : void
    {
        // Todo
    }
}
