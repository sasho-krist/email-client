<?php

declare(strict_types=1);

/*
| Файлът се зарежда само от PHPUnit (виж phpunit.xml). При старт на тестове:
| махаме кеширания config (в него може да е записан mysql) и насилствено задаваме
| SQLite ПРЕДИ vendor/autoload и Laravel — така DatabaseTruncation не докосва MySQL.
*/
$root = dirname(__DIR__);

@unlink($root.'/bootstrap/cache/config.php');

$dbPath = $root.'/database/testing.sqlite';

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

putenv('DB_CONNECTION=sqlite');
$_ENV['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_CONNECTION'] = 'sqlite';

putenv('DB_DATABASE='.$dbPath);
$_ENV['DB_DATABASE'] = $dbPath;
$_SERVER['DB_DATABASE'] = $dbPath;

putenv('DB_URL=');
$_ENV['DB_URL'] = '';
$_SERVER['DB_URL'] = '';

require $root.'/vendor/autoload.php';
