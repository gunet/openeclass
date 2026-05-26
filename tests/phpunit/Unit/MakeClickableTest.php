<?php

namespace Tests\Phpunit\Unit;

require_once 'include/main_lib.php';

use PHPUnit\Framework\TestCase;

class MakeClickableTest extends TestCase
{
    public function testPlainTextIsUnchanged()
    {
        $this->assertSame('hello world', make_clickable('hello world'));
    }

    public function testHttpUrlIsLinked()
    {
        $input = 'visit https://example.com/page';
        $expected = "visit <a href='https://example.com/page'>https://example.com/page</a>";
        $this->assertSame($expected, make_clickable($input));
    }

    public function testHttpsUrlIsLinked()
    {
        $input = 'check https://example.com';
        $expected = "check <a href='https://example.com'>https://example.com</a>";
        $this->assertSame($expected, make_clickable($input));
    }

    public function testFtpUrlIsLinked()
    {
        $input = 'ftp://files.example.com';
        $expected = "<a href='ftp://files.example.com'>ftp://files.example.com</a>";
        $this->assertSame($expected, make_clickable($input));
    }

    public function testWwwUrlIsLinkedWithHttpPrefix()
    {
        $input = 'site www.example.com';
        $expected = "site <a href='http://www.example.com'>www.example.com</a>";
        $this->assertSame($expected, make_clickable($input));
    }

    public function testEmailIsLinked()
    {
        $input = 'email user@example.com';
        $expected = "email <a href='mailto:user@example.com'>user@example.com</a>";
        $this->assertSame($expected, make_clickable($input));
    }

    public function testEmailWithPlusIsLinked()
    {
        $input = 'user+tag@example.com';
        $expected = "<a href='mailto:user+tag@example.com'>user+tag@example.com</a>";
        $this->assertSame($expected, make_clickable($input));
    }

    public function testExistingHtmlLinksAreLeftAlone()
    {
        $input = "click <a href='https://example.com'>here</a>";
        $this->assertSame($input, make_clickable($input));
    }

    public function testMultipleUrls()
    {
        $input = 'a https://example.com and http://test.org';
        $expected = "a <a href='https://example.com'>https://example.com</a> and <a href='http://test.org'>http://test.org</a>";
        $this->assertSame($expected, make_clickable($input));
    }

    public function testUrlWithPathAndQuery()
    {
        $input = 'https://example.com/path?q=1&x=2';
        $expected = "<a href='https://example.com/path?q=1&x=2'>https://example.com/path?q=1&x=2</a>";
        $this->assertSame($expected, make_clickable($input));
    }

    public function testUrlWithTrailingPunctuation()
    {
        $input = 'visit https://example.com.';
        $expected = "visit <a href='https://example.com.'>https://example.com.</a>";
        $this->assertSame($expected, make_clickable($input));
    }
}
