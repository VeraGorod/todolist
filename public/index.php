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

// Инициализация репозиториев
$projectRepository = new ProjectRepository($pdo);
$taskRepository = new TaskRepository($pdo);
$attemptRepository = new AttemptRepository($pdo);
$listRepository = new \App\Repository\ListRepository($pdo);

// Initialize services
$projectService = new \App\Service\ProjectService($projectRepository, $taskRepository, $attemptRepository,$listRepository);
$taskService = new TaskService($taskRepository, $attemptRepository, $listRepository); // Inject AttemptRepository here
$listService = new \App\Service\ListService($listRepository);


// Определение маршрута
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($requestUri === '/tasks-table') {
	// Получение данных о делах
	$tasks = $taskService->getAllTasks();
	ob_start();
	include __DIR__ . '/../templates/tasks-table.php';
	$content = ob_get_clean();
	echo $content;
	exit;
}

if ($requestUri === '/projects-table') {
	// Получение данных о проектах
	$projects = $projectService->getSortedProjects();
	ob_start();
	include __DIR__ . '/../templates/projects-table.php';
	$content = ob_get_clean();
	echo $content;
	exit;
}


// Получение данных
$projects = $projectService->getAllProjectsWithProgress();
$tasks = $taskService->getTasksWithAttempts();

// Добавляем прогресс к каждому проекту
foreach ($projects as &$project) {
	$project['progress_percent'] = $projectService->calculateProjectProgress($project);
}

// Группировка списков по типам
$listsByType = $listService->getAll();

// Общая статистика
$totalProjectStats = $projectService->getTotalProjectStats();
$todayStats = $taskService->getTodayStats();
$todayDomainStats = $taskService->getTodayDomainStats();

// Общая статистика
$totalProgressPercent = $totalProjectStats['progress_percent'];
// Статистика по сферам
$domainStats = $projectService->getDomainStatsWithPercentages();

// Подключение шаблонов
ob_start();
include __DIR__ . '/../templates/tasks.php';
include __DIR__ . '/../templates/projects.php';
$content = ob_get_clean();

// Вывод основного шаблона
include __DIR__ . '/../templates/layout.php';