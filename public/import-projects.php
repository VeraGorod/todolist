<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Repository\AttemptRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Service\ProjectService;
use App\Service\TaskService;

// Загрузка конфигурации
$config = require __DIR__ . '/../config/config.php';

$database = new \App\Database($config['database']['path']);
$pdo = $database->getPdo();


// Функция для поиска или добавления значения в таблицу lists
function findOrCreateListValue(PDO $pdo, string $type, string $value): int
{
	// Проверяем, существует ли значение
	$stmt = $pdo->prepare("SELECT id FROM lists WHERE type = :type AND value = :value");
	$stmt->execute([':type' => $type, ':value' => $value]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($result) {
		return (int) $result['id'];
	}

	// Если значение не найдено, добавляем его
	$stmt = $pdo->prepare("INSERT INTO lists (type, value, name) VALUES (:type, :value, :name)");
	$stmt->execute([
		':type' => $type,
		':value' => $value,
		':name' => $value, // Используем value как name
	]);

	return (int) $pdo->lastInsertId();
}

// Открываем CSV-файл
$csvFile = fopen('projects.csv', 'r');
if (!$csvFile) {
	die('Ошибка при открытии CSV-файла.');
}

// Пропускаем заголовок
fgetcsv($csvFile);

while (($row = fgetcsv($csvFile)) !== false) {
	// Разбираем данные строки
	[
		$statusText,
		$name,
		$domainText,
		$colorText,
		$sizeText,
		$hours,
		$levelText
	] = $row;

	// Находим или создаем ID для статуса, сферы, цвета, размера и уровня
	$statusId = findOrCreateListValue($pdo, 'project_statuses', $statusText);
	//$domainId = findOrCreateListValue($pdo, 'domains', $domainText);
	$colorId = findOrCreateListValue($pdo, 'colors', $colorText);
	$sizeId = findOrCreateListValue($pdo, 'project_sizes', $sizeText);
	$levelId = findOrCreateListValue($pdo, 'project_levels', $levelText);

	// Добавляем проект в таблицу projects
	$stmt = $pdo->prepare("
        INSERT INTO projects (name, status_id, color_id, size_id, hours, level_id, external_link, domains, color, size, level)
        VALUES (:name, :statusId, :colorId, :sizeId, :hours, :levelId, :external_link, :domains, :color, :size, :level)
    ");
	$stmt->execute([
		':name' => $name,
		':statusId' => $statusId,
		':colorId' => $colorId,
		':sizeId' => $sizeId,
		':hours' => (int) $hours,
		':levelId' => $levelId,
		':external_link' => '',
		':domains' => '',
		':color' => '',
		':size' => '',
		':level' => '',
	]);

	$projectId = (int) $pdo->lastInsertId();

	// Обрабатываем несколько сфер, разделенных запятой и пробелом
	$domains = array_map('trim', explode(',', $domainText)); // Разделяем сферы
	foreach ($domains as $domain) {
		if (!empty($domain)) {
			$domainId = findOrCreateListValue($pdo, 'domains', $domain); // Находим или создаем ID для сферы

			// Добавляем связь между проектом и сферой
			$stmt = $pdo->prepare("
                INSERT INTO project_lists (project_id, list_id)
                VALUES (:projectId, :listId)
            ");
			$stmt->execute([
				':projectId' => $projectId,
				':listId' => $domainId,
			]);
		}
	}

	// Добавляем связь между проектом и сферой
	$stmt = $pdo->prepare("
        INSERT INTO project_lists (project_id, list_id)
        VALUES (:projectId, :listId)
    ");
	$stmt->execute([
		':projectId' => $projectId,
		':listId' => $domainId,
	]);
}

fclose($csvFile);

echo "Импорт завершен успешно.";