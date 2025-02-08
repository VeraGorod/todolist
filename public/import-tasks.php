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

$stmt = $pdo->prepare("DELETE FROM tasks")->execute();

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

// Функция для поиска проекта по названию
function findProjectByName(PDO $pdo, string $projectName): ?int
{
	$stmt = $pdo->prepare("SELECT id FROM projects WHERE name = :name");
	$stmt->execute([':name' => $projectName]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);

	return $result ? (int) $result['id'] : null;
}

// Функция для извлечения названия и ссылки из строки
function extractProjectNameAndLink(string $projectText): array
{
	// Регулярное выражение для извлечения названия и ссылки
	if (preg_match('/^(.*?)\s*\((https?:\/\/[^\)]+)\)$/', $projectText, $matches)) {
		return [
			'name' => trim($matches[1]), // Название проекта
			'link' => $matches[2],      // Ссылка
		];
	}
	// Если скобки отсутствуют, возвращаем только название
	return [
		'name' => trim($projectText),
		'link' => '', // Пустая ссылка
	];
}

// Открываем CSV-файл
$csvFile = fopen('tasks2.csv', 'r');
if (!$csvFile) {
	die('Ошибка при открытии CSV-файла.');
}

// Пропускаем заголовок
fgetcsv($csvFile);

while (($row = fgetcsv($csvFile)) !== false) {

	if (count($row) < 12) { // 12 — количество полей в CSV
		echo "Пропущена некорректная строка: " . implode(', ', $row) . "\n";
		continue;
	}

	// Разбираем данные строки
	[
		$taskName,
		$contextText,
		$_, // "Сделано" игнорируем
		$targetAttempts,
		$colorText,
		$statusText,
		$domainText,
		$projectText, // Название проекта с возможной ссылкой
		$timePerAttempt,
		$_, // Дата обновления игнорируем
		$_, // "Трекер" игнорируем
		$totalTime,
	] = $row;

	// Обработка названия проекта и ссылки
	$projectData = extractProjectNameAndLink($projectText);
	$projectName = $projectData['name'];
	$projectLink = $projectData['link'];

	// Находим или создаем ID для статуса, цвета и сфер
	$statusId = findOrCreateListValue($pdo, 'statuses', $statusText);
	$colorId = findOrCreateListValue($pdo, 'colors', $colorText);

	// Обработка нескольких сфер
	$domains = array_map('trim', explode(',', $domainText));
	$domainIds = [];
	foreach ($domains as $domain) {
		if (!empty($domain)) {
			$domainIds[] = findOrCreateListValue($pdo, 'domains', $domain);
		}
	}

	// Обработка нескольких контекстов
	$contexts = array_map('trim', explode(',', $contextText));
	$contextIds = [];
	foreach ($contexts as $context) {
		if (!empty($context)) {
			$contextIds[] = findOrCreateListValue($pdo, 'contexts', $context);
		}
	}

	// Находим проект по названию
	$projectId = findProjectByName($pdo, $projectName);

	// Если проект не найден, создаем новый
	if (!$projectId && $projectName !== '') {
		$stmt = $pdo->prepare("
            INSERT INTO projects (name, external_link)
            VALUES (:name, :externalLink)
        ");
		$stmt->execute([
			':name' => $projectName,
			':externalLink' => $projectLink,
		]);
		$projectId = (int)$pdo->lastInsertId();
	} else {
		// Если проект найден, обновляем ссылку, если она указана
		if (!empty($projectLink)) {
			$stmt = $pdo->prepare("
                UPDATE projects
                SET external_link = :externalLink
                WHERE id = :id
            ");
			$stmt->execute([
				':externalLink' => $projectLink,
				':id' => $projectId,
			]);
		}
	}

	// Добавляем задачу в таблицу tasks
	$stmt = $pdo->prepare("
        INSERT INTO tasks (
            name, project_id, target_attempts, time_per_attempt, total_time, status_id, color_id, external_link
        ) VALUES (
            :name, :projectId, :targetAttempts, :timePerAttempt, :totalTime, :statusId, :colorId, :external_link
        )
    ");
	$stmt->execute([
		':name' => $taskName,
		':projectId' => $projectId,
		':targetAttempts' => (int)$targetAttempts,
		':timePerAttempt' => (int)$timePerAttempt,
		':totalTime' => (int)$totalTime,
		':statusId' => $statusId,
		':colorId' => $colorId,
		':external_link' => ''
	]);
	$taskId = (int)$pdo->lastInsertId();

	// Добавляем связи между задачей и сферами
	foreach ($domainIds as $domainId) {
		$stmt = $pdo->prepare("
            INSERT INTO task_lists (task_id, list_id)
            VALUES (:taskId, :listId)
        ");
		$stmt->execute([
			':taskId' => $taskId,
			':listId' => $domainId,
		]);
	}

	// Добавляем связи между задачей и конт



}fclose($csvFile);
echo "Импорт завершен успешно.";