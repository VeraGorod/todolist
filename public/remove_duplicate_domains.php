<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Database;

// Загрузка конфигурации
$config = require __DIR__ . '/../config/config.php';
$database = new \App\Database($config['database']['path']);
$pdo = $database->getPdo();

// Шаг 1: Получаем все проекты
$stmt = $pdo->query("SELECT id FROM projects");
$projects = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($projects as $projectId) {
	// Шаг 2: Находим все связи проекта со сферами
	$stmt = $pdo->prepare("
        SELECT pl.id, pl.list_id
        FROM project_lists pl
        WHERE pl.project_id = :projectId
    ");
	$stmt->execute([':projectId' => $projectId]);
	$projectLists = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Шаг 3: Группируем записи по list_id и находим дубликаты
	$seenListIds = [];
	$duplicates = [];

	foreach ($projectLists as $record) {
		$listId = $record['list_id'];
		if (in_array($listId, $seenListIds)) {
			// Если сфера уже встречалась, добавляем ID записи в список дубликатов
			$duplicates[] = $record['id'];
		} else {
			// Иначе добавляем list_id в массив уникальных значений
			$seenListIds[] = $listId;
		}
	}

	// Шаг 4: Удаляем дубликаты
	if (!empty($duplicates)) {
		$placeholders = implode(',', array_fill(0, count($duplicates), '?'));
		$stmt = $pdo->prepare("
            DELETE FROM project_lists
            WHERE id IN ($placeholders)
        ");
		$stmt->execute($duplicates);

		echo "Удалено дубликатов для проекта ID $projectId: " . count($duplicates) . "\n";
	}
}

echo "Очистка дубликатов завершена.\n";