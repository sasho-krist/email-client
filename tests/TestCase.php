<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTruncation;

    protected function setUp(): void
    {
        $conn = $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: '';
        $this->assertSame(
            'sqlite',
            $conn,
            'Тестовете изискват DB_CONNECTION=sqlite (виж tests/bootstrap.php). Не пускайте тестове срещу MySQL.'
        );

        parent::setUp();

        $this->assertSame(
            'sqlite',
            config('database.default'),
            'Тестовете трябва да ползват само SQLite; проверете кеша на конфигурацията.'
        );
    }
}
