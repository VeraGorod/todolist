<?php
namespace App\Service;

use App\Repository\AttemptRepository;
use App\Repository\ListRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;

class ProjectService
{
	/**
	 * @param ProjectRepository $projectRepository
	 * @param TaskRepository $taskRepository
	 * @param AttemptRepository $attemptRepository
	 */
	public function __construct(
		ProjectRepository $projectRepository,
		TaskRepository $taskRepository,
		AttemptRepository $attemptRepository,
		ListRepository $listRepository
	) {
		$this->projectRepository = $projectRepository;
		$this->taskRepository = $taskRepository;
		$this->attemptRepository = $attemptRepository;
		$this->listRepository = $listRepository;
		$this->taskService = new TaskService($taskRepository, $attemptRepository, $listRepository);
	}

	public function getAllProjects(): array
	{
		$projects = $this->projectRepository->findAll();
		foreach ($projects as &$project) {
			$project['domains'] = $this->projectRepository->findProjectDomains($project['id']);
		}
		return $projects;
	}

	public function getSortedProjects(): array
	{
		$projects = $this->getAllProjects();

		// Маппинг статусов
		$statusMapping = [
			'Для обезьянки' => ['class' => 'status-for-monkey', 'priority' => 1],
			'Делаю' => ['class' => 'status-in-progress', 'priority' => 2],
			'Обработать' => ['class' => 'status-on-hold', 'priority' => 3],
			'Заморожено' => ['class' => 'status-frozen', 'priority' => 4],
			'Готово' => ['class' => 'status-done', 'priority' => 5],
		];

		// Сортируем задачи по приоритету статуса
		usort($projects, function ($a, $b) use ($statusMapping) {
			$priorityA = $statusMapping[$a['status_value']]['priority'] ?? 999; // По умолчанию низкий приоритет
			$priorityB = $statusMapping[$b['status_value']]['priority'] ?? 999;
			return $priorityA <=> $priorityB;
		});

		// Добавляем CSS-классы к задачам
		foreach ($projects as &$project) {
			$project['status_class'] = $statusMapping[$project['status_value']]['class'] ?? '';
		}

		return $projects;
	}

	/**
	 * Получить все проекты с прогрессом и фактическим временем.
	 *
	 * @return array
	 */
	public function getAllProjectsWithProgress(): array
	{
		$projects = $this->projectRepository->findAll();
		foreach ($projects as &$project) {
			$project['domains'] = $this->projectRepository->findProjectLists($project['id']);
			$project['progress_percent'] = $this->calculateProjectProgress($project);
			$project['total_time_spent'] = $this->calculateTotalTimeSpent($project['id']);
			$project['actual_tasks'] = count($this->taskRepository->findActualByProjectId($project['id']));
		}
		return $projects;
	}

	/**
	 * Вычислить процент выполнения проекта.
	 *
	 * @param array $project
	 * @return float
	 */
	public function calculateProjectProgress(array $project): float
	{
		$totalTimeSpent = $this->calculateTotalTimeSpent($project['id']);
		$targetHours = $project['hours'];
		return $targetHours > 0
			? round(($totalTimeSpent / $targetHours) * 100, 2)
			: 0;
	}



	/**
	 * Вычислить фактически затраченное время на проект.
	 *
	 * @param int $projectId
	 * @return float
	 */
	public function calculateTotalTimeSpent(int $projectId): float
	{
		$tasksInProject = $this->taskRepository->findByProjectId($projectId);
		$totalTimeSpent = 0;
		foreach ($tasksInProject as $task) {
			$attemptsCount = $this->attemptRepository->countByTaskId($task['id']);
			$timePerAttempt = $task['time_per_attempt'];
			$totalTimeSpent += $attemptsCount * $timePerAttempt; // В минутах
		}
		return round($totalTimeSpent / 60, 1); // Переводим в часы
	}

	/**
	 * Получить общую статистику по всем проектам.
	 *
	 * @param string|null $level
	 * @return array
	 */
	public function getTotalProjectStats(?string $level = null): array
	{
		if ($level && $level !== 'all') {
			$projects = $this->getProjectsByLevel($level);
		}
		else $projects = $this->projectRepository->findAll();
		$totalPlannedHours = 0;
		$totalSpentHours = 0;

		foreach ($projects as $project) {
			$totalPlannedHours += $project['hours'];
			$totalSpentHours += $this->calculateTotalTimeSpent($project['id']);
		}

		$progressPercent = $totalPlannedHours > 0
			? round(($totalSpentHours / $totalPlannedHours) * 100, 2)
			: 0;

		return [
			'planned_hours' => $totalPlannedHours,
			'spent_hours' => round($totalSpentHours, 1),
			'progress_percent' => $progressPercent,
		];
	}

	/**
	 * Получить статистику по сферам.
	 *
	 * @return array
	 */
	public function getDomainStats(): array
	{
		$domains = $this->listRepository->getDomains(); // Получаем все сферы из базы
		$stats = [];

		foreach ($domains as $domain) {
			// Проекты в сфере
			$projectsInDomain = $this->projectRepository->findProjectsByDomain($domain['id']);
			$plannedHours = 0;
			$spentHours = 0;

			foreach ($projectsInDomain as $project) {
				$plannedHours += $project['hours'];
				$spentHours += $this->calculateTotalTimeSpent($project['id']);
			}

			// Дела без проекта в сфере
			$tasksWithoutProject = $this->taskRepository->findTasksByDomainAndNoProject($domain['id']);
			foreach ($tasksWithoutProject as $task) {
				$attemptsCount = $this->attemptRepository->countByTaskId($task['id']);
				$timePerAttempt = $task['time_per_attempt'];
				$targetAttempts = $task['target_attempts'];
				$spentHours += ($attemptsCount * $timePerAttempt) / 60; // В часах
				$plannedHours += ($targetAttempts * $timePerAttempt) / 60;;
			}


			// Расчет процента прогресса
			$progressPercent = $plannedHours > 0
				? round(($spentHours / $plannedHours) * 100, 2)
				: 0;



			$stats[$domain['value']] = [
				'planned_hours' => $plannedHours,
				'spent_hours' => round($spentHours, 1),
				'progress_percent' => $progressPercent,
			];
		}
		return $stats;
	}

	/**
	 * Получить статистику по сферам с учетом их доли в общем количестве часов.
	 *
	 * @param string|null $level
	 * @return array
	 */
	public function getDomainStatsWithPercentages(?string $level = null): array
	{
		$domains = $this->listRepository->getDomains(); // Получаем все сферы из базы
		$stats = [];
		$totalPlannedHours = 0;

		if ($level && $level !== 'all') {
			$projects = $this->getProjectsByLevel($level);
		}
		else $projects = $this->projectRepository->findAll();

		// Считаем общее количество запланированных часов
		foreach ($projects as $project) {
			$totalPlannedHours += $project['hours'];
		}

		foreach ($domains as $domain) {
			// Проекты в сфере
			if ($level && $level !== 'all') {
				$projectsInDomain = $this->projectRepository->findProjectsByDomainAndLevel($domain['id'], $level);
			}
			else $projectsInDomain = $this->projectRepository->findProjectsByDomain($domain['id']);
			$plannedHours = 0;
			$spentHours = 0;

			foreach ($projectsInDomain as $project) {
				$plannedHours += $project['hours'];
				$spentHours += $this->calculateTotalTimeSpent($project['id']);
			}

			// Дела без проекта в сфере
			$tasksWithoutProject = $this->taskRepository->findTasksByDomainAndNoProject($domain['id']);
			foreach ($tasksWithoutProject as $task) {
				$attemptsCount = $this->attemptRepository->countByTaskId($task['id']);
				$timePerAttempt = $task['time_per_attempt'];
				$targetAttempts = $task['target_attempts'];
				$spentHours += ($attemptsCount * $timePerAttempt) / 60; // В часах
				$plannedHours += ($targetAttempts * $timePerAttempt) / 60;;
			}

			// Расчет процентов
			$progressPercent = $plannedHours > 0
				? round(($spentHours / $plannedHours) * 100, 2)
				: 0;

			$domainPercentage = $totalPlannedHours > 0
				? round(($plannedHours / $totalPlannedHours) * 100, 2)
				: 0;

			// Расчет размера квадратиков
			$squareSize = $domainPercentage > 0
				? round((100 / $domainPercentage) * 10)
				: 10;


			if($squareSize > 50) $squareSize = 50;

			if ($domainPercentage > 0){
				// Расчет количества квадратиков
				$squarePercentage = 100 / $domainPercentage; // Процент, который занимает один квадратик
				$fullSquares = floor($progressPercent / $squarePercentage); // Количество полностью заполненных квадратиков
				if($squarePercentage > 0){
					$remainder = $progressPercent % ceil($squarePercentage); // Остаток для последнего квадратика
					$remainder = round($remainder / ceil($squarePercentage) * 100, 2);
				}
				else $remainder = 0;
			}


			$stats[$domain['value']] = [
				'planned_hours' => $plannedHours,
				'spent_hours' => round($spentHours, 1),
				'progress_percent' => $progressPercent,
				'domain_percentage' => $domainPercentage,
				'full_squares' => $fullSquares ?? 0, // Количество полностью заполненных квадратиков
				'square_size' => $squareSize, // Размер квадратика
				'remainder' => $remainder ?? 0, // Процент заполнения последнего квадратика
			];
		}

		return $stats;
	}

	/**
	 * Пересчитать всю статистику (общую и по сферам).
	 *
	 * @return array
	 */
	public function recalculateAllStats(): array
	{
		// Общая статистика
		$totalProjectStats = $this->getTotalProjectStats();

		// Статистика по сферам
		$domainStats = $this->getDomainStatsWithPercentages();

		// Статистика за сегодня
		$todayStats = $this->taskService->getTodayStats();
		$todayDomainStats = $this->taskService->getTodayDomainStats();

		return [
			'total_stats' => $totalProjectStats,
			'domain_stats' => $domainStats,
			'today_stats' => $todayStats,
			'today_domain_stats' => $todayDomainStats,
		];
	}

	/**
	 * @param mixed $level
	 * @return array
	 */
	public function getProjectsByLevel(mixed $level) : array
	{
		$projects = $this->getAllProjectsWithProgress();
		foreach ($projects as $key => $project){
			if($project['level_value'] !== $level) unset($projects[$key]);
		}
		return $projects;
	}


}