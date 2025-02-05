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

// Инициализация базы данных
$database = new Database($config['database']['path']);
$pdo = $database->getPdo();


$listRepository = new \App\Repository\ListRepository($pdo);
$listService = new \App\Service\ListService($listRepository);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = json_decode(file_get_contents('php://input'), true);

	if ($data['action'] === 'add') {
		$success = $listService->save($data['type'], $data['type'], $data['value']);
		echo json_encode(['success' => $success]);
		exit;
	}

	if ($data['action'] === 'delete') {
		$success = $listService->delete((int) $data['id']);
		echo json_encode(['success' => $success]);
		exit;
	}
}

// Заранее определенные типы списков
$knownTypes = [
	'project_sizes' => [],
	'project_levels' => [],
	'task_contexts' => [],
	'domains' => [],
	'colors' => [],
	'statuses' => [],
	'project_statuses' => [],
];

// Получение всех списков из базы данных
$listsByType = $listService->getAll();

// Если база пустая, используем заранее определенные типы списков
foreach ($knownTypes as $type => $defaultValues) {
	if (!isset($listsByType[$type])) {
		$listsByType[$type] = array_map(function ($value) use ($type) {
			return [
				'id' => null,
				'name' => $type,
				'type' => $type,
				'value' => $value,
			];
		}, $defaultValues);
	}
}

include __DIR__ . '/../templates/settings.php';