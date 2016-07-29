<?php

namespace Algolia\Test;

use Algolia\DOMParser;

class DOMParserTest extends \PHPUnit_Framework_TestCase
{
    private $content = <<<EOT
<h1>My h1 heading</h1>
    <h3>     </h3>
    <article>
        <h2>My h2 heading</h2>
                    <p>My first paragraph</p>
                    
            <h3>My h3 heading</h3>
    </article>
                <div>
                <h4>My h4 heading</h4>
                    <p>Awesome content</p>
                    <p>
                        Other content
                        <pre>
                            Some code that should not be present.
                        </pre>
                        
                    </p>
                    <pre>
                        <p>Some html inside pre</p>
                        Some code that should not be present.
                    </pre>
                    <ul>
                        <li>Line 1</li>
                        <li>Line 2</li>
                    </ul>
                    <table>
                        <tr>
                            <td>Table</td>
                            <td>Content</td>
                        </tr>
                    </table>
                    <p></p>
                </div>
        <h2>Second h2</h2>
        <script>
            alert('hello');
            <p>Some weird nested tag.</p>
        </script>
<h1>Another h1</h1>
EOT;

    public function testParsingLogic()
    {
        $expected = array(
            array(
                'title1'  => 'My h1 heading',
                'title2'  => 'My h2 heading',
                'title3'  => '',
                'title4'  => '',
                'title5'  => '',
                'title6'  => '',
                'content' => 'My first paragraph',
            ),
            array(
                'title1'  => 'My h1 heading',
                'title2'  => 'My h2 heading',
                'title3'  => 'My h3 heading',
                'title4'  => 'My h4 heading',
                'title5'  => '',
                'title6'  => '',
                'content' => 'Awesome content',
            ),
            array(
                'title1'  => 'My h1 heading',
                'title2'  => 'My h2 heading',
                'title3'  => 'My h3 heading',
                'title4'  => 'My h4 heading',
                'title5'  => '',
                'title6'  => '',
                'content' => 'Other content',
            ),
            array(
                'title1'  => 'My h1 heading',
                'title2'  => 'My h2 heading',
                'title3'  => 'My h3 heading',
                'title4'  => 'My h4 heading',
                'title5'  => '',
                'title6'  => '',
                'content' => 'Line 1 Line 2',
            ),
            array(
                'title1'  => 'My h1 heading',
                'title2'  => 'My h2 heading',
                'title3'  => 'My h3 heading',
                'title4'  => 'My h4 heading',
                'title5'  => '',
                'title6'  => '',
                'content' => 'Table Content',
            ),
            array(
                'title1'  => 'My h1 heading',
                'title2'  => 'Second h2',
                'title3'  => '',
                'title4'  => '',
                'title5'  => '',
                'title6'  => '',
                'content' => '',
            ),
            array(
                'title1'  => 'Another h1',
                'title2'  => '',
                'title3'  => '',
                'title4'  => '',
                'title5'  => '',
                'title6'  => '',
                'content' => '',
            ),
        );

        $parser = new DOMParser();
        $parser->setExcludeSelectors(array('pre'));
        $objects = $parser->parse($this->content);
        $this->assertEquals($expected, $objects);
    }

    public function testParsingFromRootSelector()
    {
        $expected = array(
            array(
                'title1'  => '',
                'title2'  => 'My h2 heading',
                'title3'  => '',
                'title4'  => '',
                'title5'  => '',
                'title6'  => '',
                'content' => 'My first paragraph',
            ),
            array(
                'title1'  => '',
                'title2'  => 'My h2 heading',
                'title3'  => 'My h3 heading',
                'title4'  => '',
                'title5'  => '',
                'title6'  => '',
                'content' => '',
            ),
        );

        $parser = new DOMParser();
        $parser->setRootSelector('article');
        $objects = $parser->parse($this->content);
        $this->assertEquals($expected, $objects);
    }

    public function testSharedAttributes()
    {
        $expected = array(
            array(
                'url'     => 'http://www.example.com',
                'visits'  => 1933,
                'title1'  => '',
                'title2'  => 'My h2 heading',
                'title3'  => '',
                'title4'  => '',
                'title5'  => '',
                'title6'  => '',
                'content' => 'My first paragraph',
            ),
            array(
                'url'     => 'http://www.example.com',
                'visits'  => 1933,
                'title1'  => '',
                'title2'  => 'My h2 heading',
                'title3'  => 'My h3 heading',
                'title4'  => '',
                'title5'  => '',
                'title6'  => '',
                'content' => '',
            ),
        );

        $parser = new DOMParser();
        $parser->setRootSelector('article');
        $parser->setSharedAttributes(array(
            'url'    => 'http://www.example.com',
            'visits' => 1933,
        ));
        $objects = $parser->parse($this->content);
        $this->assertEquals($expected, $objects);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseURLChecksURLValidity()
    {
        $parser = new DOMParser();
        $parser->parseURL('not_a_url');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testParseURLRaisesAnExceptionIfUnreachableURL()
    {
        $parser = new DOMParser();
        $parser->parseURL('https://nothing.algolia.biz');
    }

    public function testHandlesEmptyStringGracefully()
    {
        $parser = new DOMParser();
        $records = $parser->parse('');
        $this->assertEquals(array(), $records);
    }

    public function testReturnsAtLeast1RecordIfSharedAttributesAreGiven()
    {
        $parser = new DOMParser();

        $parser->setSharedAttributes(array(
            'url'  => 'http://example.com',
            'name' => 'The Name',
        ));

        $expected = array(
            array(
                'url'     => 'http://example.com',
                'name'    => 'The Name',
                'title1'  => '',
                'title2'  => '',
                'title3'  => '',
                'title4'  => '',
                'title5'  => '',
                'title6'  => '',
                'content' => '',
            ),
        );
        $records = $parser->parse('');
        $this->assertEquals($expected, $records);

        $dom = <<<EOT
<div>
    <div>Some content</div>
</div>
EOT;
        $records = $parser->parse($dom);
        $this->assertEquals($expected, $records);
    }
}
