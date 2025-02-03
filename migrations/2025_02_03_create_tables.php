<?php
use App\Database;

require __DIR__ . '/../vendor/autoload.php';

$database = new Database(__DIR__ . '/../database.sqlite');
$pdo = $database->getPdo();

// Создание таблицы проектов
$pdo->exec("
    CREATE TABLE IF NOT EXISTS projects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        external_link TEXT NOT NULL,
        domains TEXT NOT NULL,
        color TEXT NOT NULL,
        size TEXT NOT NULL,
        hours INTEGER NOT NULL,
        level TEXT NOT NULL
    )
");

// Создание таблицы дел
$pdo->exec("
    CREATE TABLE IF NOT EXISTS tasks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        project_id INTEGER NOT NULL,
        contexts TEXT NOT NULL,
        target_attempts INTEGER NOT NULL,
        time_per_attempt INTEGER NOT NULL,
        total_time INTEGER NOT NULL,
        status TEXT NOT NULL,
        color TEXT NOT NULL,
        domains TEXT NOT NULL,
        external_link TEXT NOT NULL
    )
");

// Создание таблицы подходов
$pdo->exec("
    CREATE TABLE IF NOT EXISTS attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        date TEXT NOT NULL,
        task_id INTEGER NOT NULL
    )
");