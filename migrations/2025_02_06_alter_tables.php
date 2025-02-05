<?php

use App\Database;

require_once __DIR__ . '/../src/Database.php';

$config = require __DIR__ . '/../config/config.php';
$database = new Database($config['database']['path']);
$pdo = $database->getPdo();

// Создание таблицы для связи проектов и списков
$pdo->exec("
    CREATE TABLE IF NOT EXISTS project_lists (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        project_id INTEGER NOT NULL,
        list_id INTEGER NOT NULL,
        FOREIGN KEY (project_id) REFERENCES projects(id),
        FOREIGN KEY (list_id) REFERENCES lists(id)
    )
");

// Создание таблицы для связи дел и списков
$pdo->exec("
    CREATE TABLE IF NOT EXISTS task_lists (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        task_id INTEGER NOT NULL,
        list_id INTEGER NOT NULL,
        FOREIGN KEY (task_id) REFERENCES tasks(id),
        FOREIGN KEY (list_id) REFERENCES lists(id)
    )
");

// Добавление столбцов для хранения ID элементов списков
$pdo->exec("
    ALTER TABLE projects ADD COLUMN size_id INTEGER REFERENCES lists(id)
");
$pdo->exec("
    ALTER TABLE projects ADD COLUMN level_id INTEGER REFERENCES lists(id)
");
$pdo->exec("
    ALTER TABLE tasks ADD COLUMN context_id INTEGER REFERENCES lists(id)
");

// Добавление столбцов для статуса и цвета
$pdo->exec("
    ALTER TABLE projects ADD COLUMN status_id INTEGER REFERENCES lists(id)
");
$pdo->exec("
    ALTER TABLE projects ADD COLUMN color_id INTEGER REFERENCES lists(id)
");
$pdo->exec("
    ALTER TABLE tasks ADD COLUMN status_id INTEGER REFERENCES lists(id)
");
$pdo->exec("
    ALTER TABLE tasks ADD COLUMN color_id INTEGER REFERENCES lists(id)
");

echo "Миграция завершена успешно.";