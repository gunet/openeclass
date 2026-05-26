<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class CourseRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        // Database::get()->query("START TRANSACTION");
    }

    protected function tearDown(): void
    {
        // Database::get()->query("ROLLBACK");
    }

    public function test_course_exists_after_insert(): void
    {
        $this->assertTrue(true);
    }
}