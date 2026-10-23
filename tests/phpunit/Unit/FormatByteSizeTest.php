<?php

namespace Tests\Phpunit\Unit;

require_once 'include/main_lib.php';

use PHPUnit\Framework\TestCase;

class FormatByteSizeTest extends TestCase
{
    public function testZeroKbytes()
    {
        $this->assertSame('0.00&nbsp;Kb', format_bytesize(0));
    }

    public function testKilobytes()
    {
        $this->assertSame('512.00&nbsp;Kb', format_bytesize(512));
    }

    public function testMegabytes()
    {
        $this->assertSame('1024.00&nbsp;Kb', format_bytesize(1024));
    }

    public function testGigabytes()
    {
        $this->assertSame('1024.00&nbsp;Mb', format_bytesize(1048576));
    }

    public function testMegabyteRange()
    {
        $this->assertSame('1.50&nbsp;Mb', format_bytesize(1536));
    }

    public function testBoundaryJustBelowMb()
    {
        $this->assertSame('1023.00&nbsp;Kb', format_bytesize(1023));
    }

    public function testBoundaryJustAboveMb()
    {
        $this->assertSame('1.00&nbsp;Mb', format_bytesize(1025));
    }

    public function testGigabyteRange()
    {
        $this->assertSame('1.50&nbsp;Gb', format_bytesize(1572864));
    }

    public function testNegativeValue()
    {
        $this->assertSame('-512.00&nbsp;Kb', format_bytesize(-512));
    }

    public function testZeroDecimalPlaces()
    {
        $this->assertSame('2&nbsp;Gb', format_bytesize(2097152, 0));
    }
}
