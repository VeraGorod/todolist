<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use App\Repository\ProjectRepository;
use App\Database;

header('Content-Type: application/json');

// Инициализация базы данных и репозитория
$config = require __DIR__ . '/../../config/config.php';
$database = new Database($config['database']['path']);
$repository = new ProjectRepository($database->getPdo());

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
	$task['domains'] = json_decode($task['domains'], true);
	echo json_encode($task);
	exit;
}



// Обработка POST-запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = json_decode(file_get_contents('php://input'), true);

	if (empty($data['name'])) {
		http_response_code(400);
		echo json_encode(['error' => 'Название проекта не может быть пустым']);
		exit;
	}

	// Сохранение проекта в базу
	$projectId = $repository->save([
		'name' => $data['name'],
		'externalLink' => '',
		'domains' => [],
		'color' => 'default',
		'size' => 'medium',
		'hours' => 0,
		'level' => 'beginner',
	]);

	// Возврат данных о новом проекте
	echo json_encode([
		'id' => $projectId,
		'name' => $data['name'],
	]);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
	$data = json_decode(file_get_contents('php://input'), true);
	$projectId = (int) $data['id'] ?? null;

	if (!$projectId) {
		http_response_code(400);
		echo json_encode(['error' => 'ID проекта не указан']);
		exit;
	}

	$updated = $repository->update($projectId, [
		'name' => $data['name'],
		'level' => $data['level'],
		'domains' => json_encode($data['domains'] ?? []),
		'size' => $data['size'] ?? 'medium',
		'hours' => $data['hours'] ?? 0,
	]);

	if ($updated) {
		echo json_encode($repository->findById($projectId));
	} else {
		http_response_code(500);
		echo json_encode(['error' => 'Ошибка при обновлении проекта']);
	}
	exit;
}

http_response_code(405);
echo json_encode(['error' => 'Метод не поддерживается']);