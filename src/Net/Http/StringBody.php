<?php
namespace Opulence\Net\Http;

/**
 * Defines the string HTTP body
 */
class StringBody implements IHttpBody
{
    protected $content = "";
    
    public function __construct(string $content)
    {
        $this->content = $content;
    }
    
    // Todo
}
