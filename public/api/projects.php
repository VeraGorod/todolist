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

http_response_code(405);
echo json_encode(['error' => 'Метод не поддерживается']);