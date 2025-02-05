<?php
use App\Database;
require __DIR__ . '/../vendor/autoload.php';

$database = new Database(__DIR__ . '/../database.sqlite');
$pdo = $database->getPdo();

// Создание таблицы списков
$pdo->exec("
CREATE TABLE IF NOT EXISTS lists (
id INTEGER PRIMARY KEY AUTOINCREMENT,
name TEXT NOT NULL,
type TEXT NOT NULL,
value TEXT NOT NULL
)
");