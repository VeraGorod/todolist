<?php


use App\Database;

require_once __DIR__ . '/../src/Database.php';

$config = require __DIR__ . '/../config/config.php';
$database = new Database($config['database']['path']);
$pdo = $database->getPdo();
//Создание временной таблицы
$pdo->exec("CREATE TABLE temp_tasks AS SELECT * FROM tasks;");

//2. Удаление старой таблицы
$pdo->exec("DROP TABLE tasks;");

//3. Создание новой таблицы
$pdo->exec("CREATE TABLE tasks (
id INTEGER PRIMARY KEY AUTOINCREMENT,
name TEXT NOT NULL,
project_id INTEGER,
context_id INTEGER REFERENCES lists(id),
target_attempts INTEGER NOT NULL,
time_per_attempt INTEGER NOT NULL,
total_time INTEGER NOT NULL,
status_id INTEGER REFERENCES lists(id),
color_id INTEGER REFERENCES lists(id),
external_link TEXT NOT NULL
);");

//4. Перенос данных из временной таблицы
$pdo->exec("INSERT INTO tasks (
id, name, project_id, context_id, target_attempts, time_per_attempt, total_time, status_id, color_id, external_link
)
SELECT
id, name, NULLIF(project_id, 0), NULL, target_attempts, time_per_attempt, total_time, NULL, NULL, external_link
FROM temp_tasks;");

//5. Удаление временной таблицы
$pdo->exec("DROP TABLE temp_tasks;");