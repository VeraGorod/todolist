<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Database;
use App\Repository\AttemptRepository;
use App\Repository\TaskRepository;

$config = require __DIR__ . '/../../config/config.php';
$database = new Database($config['database']['path']);
$repository = new AttemptRepository($database->getPdo());
$taskRepository = new \App\Repository\TaskRepository($database->getPdo());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = json_decode(file_get_contents('php://input'), true);
	$taskId = $data['taskId'] ?? null;

	if (!$taskId) {
		http_response_code(400);
		echo json_encode(['error' => 'ID задачи не указан']);
		exit;
	}

	// Создаем новый подход
	$attemptId = $repository->save([
		'date' => date('Y-m-d H:i:s'),
		'taskId' => $taskId,
	]);

	// Получаем обновленное количество подходов
	$attemptsCount = $repository->countByTaskId($taskId);

	// Получаем цель из базы данных
	$task = $taskRepository->findById($taskId);
	$targetAttempts = $task['target_attempts'];

	// Вычисляем процент выполнения
	$progressPercent = $targetAttempts > 0
		? round(($attemptsCount / $targetAttempts) * 100, 2)
		: 0;

	// Возвращаем данные о задаче
	$projectService = new \App\Service\ProjectService(new \App\Repository\ProjectRepository($database->getPdo()), new TaskRepository($database->getPdo()), new \App\Repository\AttemptRepository($database->getPdo()), new \App\Repository\ListRepository($database->getPdo()));
	$stats = $projectService->recalculateAllStats();
	echo json_encode([
		'attempt' => $repository->findById($attemptId),
		'attempts_count' => $attemptsCount,
		'target_attempts' => $targetAttempts,
		'progress_percent' => $progressPercent,
		'stats' => $stats,
	]);
	/*echo json_encode([
		'id' => $taskId,
		'attempts_count' => $attemptsCount,
		'target_attempts' => $targetAttempts,
		'progress_percent' => $progressPercent,
	]);*/
	exit;
}

http_response_code(405);
echo json_encode(['error' => 'Метод не поддерживается']);