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

http_response_code(405);
echo json_encode(['error' => 'Метод не поддерживается']);