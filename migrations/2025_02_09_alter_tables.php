<?php

use App\Database;

require_once __DIR__ . '/../src/Database.php';

$config = require __DIR__ . '/../config/config.php';
$database = new Database($config['database']['path']);
$pdo = $database->getPdo();

$pdo->exec("
    ALTER TABLE tasks
		ADD COLUMN content TEXT;
");

$pdo->exec("
    ALTER TABLE projects
		ADD COLUMN content TEXT;
");

echo "Миграция завершена успешно.";