<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use App\Repository\ProjectRepository;
use App\Database;
use App\Repository\TaskRepository;

header('Content-Type: application/json');

// Инициализация базы данных и репозитория
$config = require __DIR__ . '/../../config/config.php';
$database = new Database($config['database']['path']);
$repository = new ProjectRepository($database->getPdo());

// Обработка GET-запроса для поиска проектов
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['query'])) {
	$query = trim($_GET['query']);
	$repository = new \App\Repository\ProjectRepository((new \App\Database($config['database']['path']))->getPdo());

	// Поиск проектов по имени
	$stmt = $database->getPdo()->prepare("SELECT id, name FROM projects WHERE name LIKE :query LIMIT 50");
	$stmt->execute([':query' => "%$query%"]);
	$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Возврат данных в формате JSON
	echo json_encode($projects);
	exit;
}

else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	$projectId = $_GET['id'] ?? null;
	if (!$projectId) {
		http_response_code(400);
		echo json_encode(['error' => 'ID задачи не указан']);
		exit;
	}
	$project = $repository->findById((int)$projectId);
	if (!$project) {
		http_response_code(404);
		echo json_encode(['error' => 'Задача не найдена']);
		exit;
	}
	$project['domains'] = array_column($repository->findProjectLists((int)$projectId), 'id');
	echo json_encode($project);
	exit;
}

// Обработка POST-запроса на обновление проекта
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
	$projectId = (int) $_GET['id'];
	$data = json_decode(file_get_contents('php://input'), true);

	if (empty($data['field']) || !isset($data['value'])) {
		http_response_code(400);
		echo json_encode(['error' => 'Необходимо указать поле и значение']);
		exit;
	}

	$field = $data['field'];
	$value = $data['value'];

	// Проверяем допустимые поля
	$allowedFields = ['name', 'hours', 'external_link'];
	if (!in_array($field, $allowedFields)) {
		http_response_code(400);
		echo json_encode(['error' => 'Недопустимое поле']);
		exit;
	}

	// Обновляем проект в базе
	$repository = new \App\Repository\ProjectRepository((new \App\Database($config['database']['path']))->getPdo());
	$stmt = $database->getPdo()->prepare("UPDATE projects SET $field = :value WHERE id = :id");
	$stmt->execute([':value' => $value, ':id' => $projectId]);

	echo json_encode(['success' => true]);
	exit;
}

// Обработка POST-запроса
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = json_decode(file_get_contents('php://input'), true);

	if (empty($data['name'])) {
		http_response_code(400);
		echo json_encode(['error' => 'Название проекта не может быть пустым']);
		exit;
	}

	// Сохранение проекта в базу
	$projectId = $repository->save([
		'name' => $data['name'],
		'externalLink' => $data['external_link'] ?? '',
		'sizeId' => $data['size_id'] ?? null,
		'levelId' => $data['level_id'] ?? null,
		'hours' => $data['hours'] ?? 0,
		'statusId' => $data['status_id'] ?? null,
		'colorId' => $data['color_id'] ?? null,
		'domain_ids' => $data['domain_ids'] ?? [],
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
		'externalLink' => $data['external_link'] ?? '',
		'size_id' => $data['size_id'] ?? null,
		'level_id' => $data['level_id'] ?? null,
		'hours' => $data['hours'] ?? 0,
		'status_id' => $data['status_id'] ?? null,
		'color_id' => $data['color_id'] ?? null,
		'domain_ids' => $data['domain_ids'] ?? [],
	]);

	if ($updated) {
		// Сохранение доменов
		if (!empty($data['domains'])) {
			$repository->saveProjectDomains($projectId, $data['domains']);
		}

		$projectService = new \App\Service\ProjectService($repository, new TaskRepository($database->getPdo()), new \App\Repository\AttemptRepository($database->getPdo()),  new \App\Repository\ListRepository($database->getPdo()));
		$project = $repository->findById($projectId);
		$project['progress_percent'] = $projectService->calculateProjectProgress($project);
		// Вычисляем фактическое время
		$project['total_time_spent'] = $projectService->calculateTotalTimeSpent($projectId);

		// Вычисляем прогресс
		$project['progress_percent'] = $projectService->calculateProjectProgress($project);
		$project['domains'] = array_column($repository->findProjectDomains((int)$projectId), 'id');;

		$totalTimeSpent = $projectService->calculateTotalTimeSpent($projectId);

		$stats = $projectService->recalculateAllStats();
		echo json_encode([
			'project' => $project,
			'stats' => $stats,
			'total_time_spent' => $totalTimeSpent,
			'hours' => $project['hours'],
		]);
	} else {
		http_response_code(500);
		echo json_encode(['error' => 'Ошибка при обновлении проекта']);
	}
	exit;
}

http_response_code(405);
echo json_encode(['error' => 'Метод не поддерживается']);