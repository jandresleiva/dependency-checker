<?php
declare(strict_types=1);

use Discovery\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @var Parser
     */
    protected Parser $parser;

    public function setUp(): void
    {
        $this->parser = new Parser();
    }

    public function testExtractName(): void
    {
        $exampleJsonContent = <<<Json
        {
            "name": "jandresleiva/repo1",
            "license": "proprietary",
            "repositories": [
                {
                  "type": "vcs",
                  "url": "https://github.com/jandresleiva/calendar.git"
                }
            ]
        }
        Json;

        $this->parser->parseFileContent($exampleJsonContent);
        $this->assertEquals("repo1", $this->parser->getRepositoryName());
    }

    public function testExtractDependencies(): void
    {
        $exampleJsonContent = <<<Json
        {
            "name": "jandresleiva/repo1",
            "license": "proprietary",
            "repositories": [
                {
                  "type": "vcs",
                  "url": "https://github.com/jandresleiva/calendar.git"
                },
                {
                  "type": "vcs",
                  "url": "https://github.com/jandresleiva/Repository2.git"
                }
            ]
        }
        Json;

        $this->parser->parseFileContent($exampleJsonContent);

        $expected = ['calendar', 'repository2'];

        $this->assertEquals($expected, $this->parser->getDependenciesFolderNames());
    }

    public function testParseFileContentFailsWithWrongJson(): void
    {
        $exampleJsonContent = <<<Json
        {
            "name": "jandresleiva/repo1"
            "license": "proprietary"
            "repositories": [
                {
                  "type": "vcs",
                  "url": "https://github.com/jandresleiva/calendar.git"
                }
            ]
        }
        Json;
        $this->expectException(Exception::class);

        $this->parser->parseFileContent($exampleJsonContent);
    }
}
