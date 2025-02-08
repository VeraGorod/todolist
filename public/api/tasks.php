<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use App\Repository\TaskRepository;
use App\Database;

header('Content-Type: application/json');

// Инициализация базы данных и репозитория
$config = require __DIR__ . '/../../config/config.php';
$database = new Database($config['database']['path']);
$repository = new TaskRepository($database->getPdo());

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	$taskId = $_GET['id'] ?? null;
	if (!$taskId) {
		http_response_code(400);
		echo json_encode(['error' => 'ID задачи не указан']);
		exit;
	}
	$task = $repository->findById((int)$taskId);
	$task['domains'] = array_column($repository->findTaskDomains((int)$taskId), 'id');
	if (!$task) {
		http_response_code(404);
		echo json_encode(['error' => 'Задача не найдена']);
		exit;
	}
	// Преобразуем JSON-поля обратно в массивы
	//$task['domains'] = json_decode($task['domains'], true);
	echo json_encode($task);
	exit;
}

// Обработка POST-запроса на обновление задачи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
	$taskId = (int) $_GET['id'];
	$data = json_decode(file_get_contents('php://input'), true);

	if (empty($data['field']) || !isset($data['value'])) {
		http_response_code(400);
		echo json_encode(['error' => 'Необходимо указать поле и значение']);
		exit;
	}

	$field = $data['field'];
	$value = $data['value'];

	// Проверяем допустимые поля
	$allowedFields = ['name', 'target_attempts', 'time_per_attempt', 'external_link'];
	if (!in_array($field, $allowedFields)) {
		http_response_code(400);
		echo json_encode(['error' => 'Недопустимое поле']);
		exit;
	}

	// Обновляем задачу в базе
	$repository = new \App\Repository\TaskRepository((new \App\Database($config['database']['path']))->getPdo());
	$stmt = $database->getPdo()->prepare("UPDATE tasks SET $field = :value WHERE id = :id");
	$stmt->execute([':value' => $value, ':id' => $taskId]);

	echo json_encode(['success' => true]);
	exit;
}

// Обработка POST-запроса
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = json_decode(file_get_contents('php://input'), true);

	if (empty($data['name'])) {
		http_response_code(400);
		echo json_encode(['error' => 'Название дела не может быть пустым']);
		exit;
	}

	// Сохранение дела в базу
	$taskId = $repository->save([
		'name' => $data['name'],
		'project_id' => $data['project_id'] ?? null,
		'context_id' => $data['context_id'] ?? null,
		'targetAttempts' => $data['target_attempts'] ?? 0,
		'timePerAttempt' => $data['time_per_attempt'] ?? 0,
		'total_time' => $data['total_time'] ?? 0,
		'status_id' => $data['status_id'] ?? null,
		'color_id' => $data['color_id'] ?? null,
		'external_link' => $data['external_link'] ?? '',
		'domain_ids' => $data['domain_ids'] ?? [],
	]);

	// Возврат данных о новом деле
	echo json_encode([
		'id' => $taskId,
		'name' => $data['name'],
	]);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
	$data = json_decode(file_get_contents('php://input'), true);
	$taskId = intval($data['id']) ?? null;

	if (!$taskId) {
		http_response_code(400);
		echo json_encode(['error' => 'ID задачи не указан']);
		exit;
	}

	$updated = $repository->update($taskId, [
		'name' => $data['name'],
		'project_id' => $data['project_id'] ?? null,
		'context_id' => $data['context_id'] ?? null,
		'target_attempts' => $data['targetAttempts'] ?? 0,
		'time_per_attempt' => $data['timePerAttempt'] ?? 0,
		'total_time' => $data['total_time'] ?? 0,
		'status_id' => $data['status_id'] ?? null,
		'color_id' => $data['color_id'] ?? null,
		'external_link' => $data['external_link'] ?? '',
		'domain_ids' => $data['domain_ids'] ?? [],
	]);

	if ($updated) {
		$task = $repository->findById($taskId);
		$taskService = new \App\Service\TaskService($repository, new \App\Repository\AttemptRepository($database->getPdo()), new \App\Repository\ListRepository($database->getPdo()));

		// Сохранение доменов
		if (!empty($data['domains'])) {
			$repository->saveTaskDomains($taskId, $data['domains']);
		}

		$task['progress_percent'] = $taskService->calculateTaskProgress($task);
		$task['domains'] = $repository->findTaskLists(intval($task['id'])) ?? [];
		$projectService = new \App\Service\ProjectService(new \App\Repository\ProjectRepository($database->getPdo()), new TaskRepository($database->getPdo()), new \App\Repository\AttemptRepository($database->getPdo()), new \App\Repository\ListRepository($database->getPdo()));
		$stats = $projectService->recalculateAllStats();
		echo json_encode([
			'task' => $task,
			'stats' => $stats,
		]);
	} else {
		http_response_code(500);
		echo json_encode(['error' => 'Ошибка при обновлении задачи']);
	}
	exit;
}


http_response_code(405);
echo json_encode(['error' => 'Метод не поддерживается']);