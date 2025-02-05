<?php
namespace App\Service;

use App\Repository\AttemptRepository;
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
		AttemptRepository $attemptRepository
	) {
		$this->projectRepository = $projectRepository;
		$this->taskRepository = $taskRepository;
		$this->attemptRepository = $attemptRepository;
		$this->taskService = new TaskService($taskRepository, $attemptRepository);
	}

	public function getAllProjects(): array
	{
		return $this->projectRepository->findAll();
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
			$project['progress_percent'] = $this->calculateProjectProgress($project);
			$project['total_time_spent'] = $this->calculateTotalTimeSpent($project['id']);
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
	 * @return array
	 */
	public function getTotalProjectStats(): array
	{
		$projects = $this->projectRepository->findAll();
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
		$domains = ['work', 'health', 'family', 'personal_growth'];
		$stats = [];

		foreach ($domains as $domain) {
			// Проекты в сфере
			$projectsInDomain = array_filter($this->projectRepository->findAll(), function ($project) use ($domain) {
				$domains = trim($project['domains'], '"'); // Удаляем внешние кавычки
				$domains = stripslashes($domains); // Удаляем лишние слэши
				$decodedDomains = json_decode($domains, true);
				// Проверяем, что результат декодирования — это массив
				if (!is_array($decodedDomains) || empty($decodedDomains)) {
					return false;
				}
				return in_array($domain, $decodedDomains);
			});
			$plannedHours = 0;
			$spentHours = 0;

			foreach ($projectsInDomain as $project) {
				$plannedHours += $project['hours'];
				$spentHours += $this->calculateTotalTimeSpent($project['id']);
			}

			// Дела без проекта в сфере
			$tasksWithoutProject = array_filter($this->taskRepository->findAll(), function ($task) use ($domain) {
				return $task['project_id'] === null && in_array($domain, json_decode($task['domains'], true));
			});

			foreach ($tasksWithoutProject as $task) {
				$attemptsCount = $this->attemptRepository->countByTaskId($task['id']);
				$timePerAttempt = $task['time_per_attempt'];
				$spentHours += ($attemptsCount * $timePerAttempt) / 60; // В часах
			}

			$progressPercent = $plannedHours > 0
				? round(($spentHours / $plannedHours) * 100, 2)
				: 0;

			$stats[$domain] = [
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
	 * @return array
	 */
	public function getDomainStatsWithPercentages(): array
	{
		$domains = ['work', 'health', 'family', 'personal_growth'];
		$stats = [];
		$totalPlannedHours = 0;

		// Считаем общее количество запланированных часов
		foreach ($this->projectRepository->findAll() as $project) {
			$totalPlannedHours += $project['hours'];
		}

		foreach ($domains as $domain) {
			// Проекты в сфере
			$projectsInDomain = array_filter($this->projectRepository->findAll(), function ($project) use ($domain) {
				$domains = trim($project['domains'], '"');
				$domains = stripslashes($domains);
				$decodedDomains = json_decode($domains, true);
				return is_array($decodedDomains) && in_array($domain, $decodedDomains);
			});


			$plannedHours = 0;
			$spentHours = 0;

			foreach ($projectsInDomain as $project) {
				$plannedHours += $project['hours'];
				$spentHours += $this->calculateTotalTimeSpent($project['id']);
			}

			// Дела без проекта в сфере
			$tasksWithoutProject = array_filter($this->taskRepository->findAll(), function ($task) use ($domain) {
				$domains = trim($task['domains'], '"');
				$domains = stripslashes($domains);
				$decodedDomains = json_decode($domains, true);
				return is_array($decodedDomains) && in_array($domain, $decodedDomains) && $task['project_id'] === null;
			});

			foreach ($tasksWithoutProject as $task) {
				$attemptsCount = $this->attemptRepository->countByTaskId($task['id']);
				$timePerAttempt = $task['time_per_attempt'];
				$spentHours += ($attemptsCount * $timePerAttempt) / 60; // В часах
			}

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

			$stats[$domain] = [
				'planned_hours' => $plannedHours,
				'spent_hours' => round($spentHours, 1),
				'progress_percent' => $progressPercent,
				'domain_percentage' => $domainPercentage,
				'square_size' => $squareSize, // Размер квадратика
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


}