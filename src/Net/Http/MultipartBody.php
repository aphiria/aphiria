<?php
namespace Opulence\Net\Http;

/**
 * Defines the multipart HTTP body
 */
class MultipartBody implements IHttpBody
{
    protected $subType = "mixed";
    protected $boundary = "";
    protected $bodies = [];
    
    public function __construct(string $subType = "mixed", ?string $boundary = null)
    {
        $this->subType = $subType;
        // Todo: Generate new UUID if $boundary is null
        $this->boundary = $boundary;
    }
    
    public function add(IHttpBody $body) : void
    {
        $this->bodies[] = $body;
    }
    
    // Todo
}