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

// Initialize services
$projectService = new \App\Service\ProjectService($projectRepository, $taskRepository, $attemptRepository);
$taskService = new TaskService($taskRepository, $attemptRepository); // Inject AttemptRepository here

// Получение данных
$projects = $projectService->getAllProjectsWithProgress();
$tasks = $taskService->getTasksWithAttempts();

// Добавляем прогресс к каждому проекту
foreach ($projects as &$project) {
	$project['progress_percent'] = $projectService->calculateProjectProgress($project);
}

// Общая статистика
$totalProjectStats = $projectService->getTotalProjectStats();
$domainStats = $projectService->getDomainStats();
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