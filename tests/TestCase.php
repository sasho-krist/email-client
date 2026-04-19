<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTruncation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(
            'sqlite',
            config('database.default'),
            'Тестовете трябва да ползват само SQLite (виж phpunit.xml); не се пипа production MySQL.'
        );
    }
}
