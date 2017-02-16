<?php
namespace Opulence\Router\UriTemplates\Parsers;

/**
 * Tests the URI template parser
 */
class UriTemplateParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var RegexUriTemplateParser The URI template parser */
    private $parser = null;
    
    /**
     * Sets up the tests
     */
    public function setUp()
    {
        $this->parser = new RegexUriTemplateParser();
    }
}
