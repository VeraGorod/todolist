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
	if (!$task) {
		http_response_code(404);
		echo json_encode(['error' => 'Задача не найдена']);
		exit;
	}
	// Преобразуем JSON-поля обратно в массивы
	$task['domains'] = json_decode($task['domains'], true);
	echo json_encode($task);
	exit;
}

// Обработка POST-запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = json_decode(file_get_contents('php://input'), true);

	if (empty($data['name'])) {
		http_response_code(400);
		echo json_encode(['error' => 'Название дела не может быть пустым']);
		exit;
	}

	// Сохранение дела в базу
	$taskId = $repository->save([
		'name' => $data['name'],
		'projectId' => 1, // По умолчанию (можно добавить выбор проекта)
		'contexts' => [],
		'targetAttempts' => 0,
		'timePerAttempt' => 0,
		'totalTime' => 0,
		'status' => 'new',
		'color' => 'default',
		'domains' => [],
		'externalLink' => '',
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
		'status' => $data['status'],
		'projectId' => $data['projectId'] !== null ? (int) $data['projectId'] : null,
		'domains' => json_encode($data['domains'] ?? []),
		'color' => $data['color'] ?? 'default',
		'targetAttempts' => $data['targetAttempts'] ?? 1,
		'timePerAttempt' => $data['timePerAttempt'] ?? 0,
	]);

	if ($updated) {
		echo json_encode($repository->findById($taskId));
	} else {
		http_response_code(500);
		echo json_encode(['error' => 'Ошибка при обновлении задачи']);
	}
	exit;
}

http_response_code(405);
echo json_encode(['error' => 'Метод не поддерживается']);