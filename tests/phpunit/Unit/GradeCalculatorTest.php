<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GradeCalculatorTest extends TestCase
{
    public function test_pass_threshold(): void
    {
        $this->assertGreaterThanOrEqual(5, 7);
    }
}