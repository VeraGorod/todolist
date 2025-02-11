<?php
namespace App\Service;

use App\Entity\Task;
use App\Repository\AttemptRepository;
use App\Repository\ListRepository;
use App\Repository\TaskRepository;

class TaskService
{
	public function __construct(TaskRepository $taskRepository, AttemptRepository $attemptRepository, ListRepository $listRepository)
	{
		$this->taskRepository = $taskRepository;
		$this->attemptRepository = $attemptRepository;
		$this->listRepository = $listRepository;
	}

	/**
	 * Получить все задачи.
	 *
	 * @return array
	 */
	public function getAllTasks(): array
	{
		$tasks = $this->getSortedTasks();
		foreach ($tasks as &$task) {
			$task['domains'] = $this->taskRepository->findTaskLists($task['id']);
			$task['contexts'] = $this->taskRepository->findTaskContexts($task['id']);
		}
		return $tasks;
	}

	/**
	 * Получить задачи по фильтрам.
	 *
	 * @param array $filters
	 * @return array
	 */
	public function getFilteredTasks(array $filters): array
	{
		return $this->taskRepository->findByFilters($filters);
	}

	/**
	 * Добавить новую задачу.
	 *
	 * @param array $data
	 * @return bool
	 */
	public function addTask(array $data): bool
	{
		$task = new Task(
			null,
			$data['name'],
			$data['projectId'],
			$data['contexts'],
			$data['targetAttempts'],
			$data['timePerAttempt'],
			$data['totalTime'],
			$data['status'],
			$data['color'],
			$data['domains'],
			$data['externalLink']
		);

		return $this->taskRepository->save($task);
	}

	/**
	 * Обновить задачу.
	 *
	 * @param int $id
	 * @param array $data
	 * @return bool
	 */
	public function updateTask(int $id, array $data): bool
	{
		$task = $this->taskRepository->findById($id);
		if (!$task) {
			return false;
		}

		// Обновляем поля задачи
		$task->name = $data['name'] ?? $task->name;
		$task->projectId = $data['projectId'] ?? $task->projectId;
		$task->contexts = $data['contexts'] ?? $task->contexts;
		$task->targetAttempts = $data['targetAttempts'] ?? $task->targetAttempts;
		$task->timePerAttempt = $data['timePerAttempt'] ?? $task->timePerAttempt;
		$task->totalTime = $data['totalTime'] ?? $task->totalTime;
		$task->status = $data['status'] ?? $task->status;
		$task->color = $data['color'] ?? $task->color;
		$task->domains = $data['domains'] ?? $task->domains;
		$task->externalLink = $data['externalLink'] ?? $task->externalLink;

		return $this->taskRepository->save($task);
	}

	/**
	 * Удалить задачу.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function deleteTask(int $id): bool
	{
		return $this->taskRepository->delete($id);
	}

	public function getTasksWithAttempts(): array
	{
		$tasks = $this->getSortedTasks();
		foreach ($tasks as &$task) {
			// Получаем количество сделанных подходов
			$task['attempts_count'] = $this->attemptRepository->countByTaskId($task['id']);

			// Вычисляем процент выполнения
			$targetAttempts = $task['target_attempts'];
			$task['progress_percent'] = $targetAttempts > 0
				? round(($task['attempts_count'] / $targetAttempts) * 100, 2)
				: 0;
		}
		return $tasks;
	}

	/**
	 * Получить процент выполнения задачи.
	 *
	 * @param array $task
	 * @return float
	 */
	public function calculateTaskProgress(array $task): float
	{
		$attemptsCount = $this->attemptRepository->countByTaskId($task['id']);
		$targetAttempts = $task['target_attempts'];

		return $targetAttempts > 0
			? round(($attemptsCount / $targetAttempts) * 100, 2)
			: 0;
	}

	/**
	 * Получить статистику за сегодня.
	 *
	 * @return array
	 */
	public function getTodayStats(): array
	{
		$today = date('Y-m-d');
		$attemptsToday = $this->attemptRepository->findByDate($today);
		$totalTimeToday = 0;

		foreach ($attemptsToday as $attempt) {
			$task = $this->taskRepository->findById($attempt['task_id']);
			$totalTimeToday += $task['time_per_attempt'];
		}

		$totalTimeTodayInHours = round($totalTimeToday / 60, 1);

		return [
			'time_today' => $totalTimeTodayInHours,
			'progress_percent' => $totalTimeTodayInHours > 0 ? 100 : 0,
		];
	}

	/**
	 * Получить статистику по сферам за сегодня.
	 *
	 * @return array
	 */
	public function getTodayDomainStats(): array
	{
		$today = date('Y-m-d'); // Текущая дата
		$domains = $this->listRepository->getDomains(); // Получаем все сферы из базы
		$stats = [];

		foreach ($domains as $domain) {
			// Выборка задач, связанных с текущей сферой
			$tasksInDomain = $this->taskRepository->findTasksByDomain($domain['id']);

			$totalTimeToday = 0;

			foreach ($tasksInDomain as $task) {
				// Выборка подходов для задачи за сегодня
				$attemptsToday = array_filter(
					$this->attemptRepository->findByTaskId($task['id']),
					function ($attempt) use ($today) {
						return substr($attempt['date'], 0, 10) === $today;
					}
				);

				// Суммируем время за сегодня
				$totalTimeToday += count($attemptsToday) * $task['time_per_attempt'];
			}

			// Переводим время в часы
			$totalTimeTodayInHours = round($totalTimeToday / 60, 1);

			// Формируем статистику для текущей сферы
			$stats[$domain['value']] = [
				'time_today' => $totalTimeTodayInHours,
				'progress_percent' => $totalTimeTodayInHours > 0 ? 100 : 0,
			];
		}

		return $stats;
	}

	/**
	 * Получить все задачи, отсортированные по статусу.
	 *
	 * @return array
	 */
	public function getSortedTasks(): array
	{
		$tasks = $this->taskRepository->findAll();

		// Маппинг статусов
		$statusMapping = [
			'Для обезьянки' => ['class' => 'status-for-monkey', 'priority' => 1],
			'Делается' => ['class' => 'status-in-progress', 'priority' => 2],
			'Обработать' => ['class' => 'status-on-hold', 'priority' => 3],
			'Заморозка' => ['class' => 'status-frozen', 'priority' => 4],
			'Готово' => ['class' => 'status-done', 'priority' => 5],
		];

		// Сортируем задачи по приоритету статуса
		usort($tasks, function ($a, $b) use ($statusMapping) {
			$priorityA = $statusMapping[$a['status_value']]['priority'] ?? 999; // По умолчанию низкий приоритет
			$priorityB = $statusMapping[$b['status_value']]['priority'] ?? 999;
			return $priorityA <=> $priorityB;
		});

		// Добавляем CSS-классы к задачам
		foreach ($tasks as &$task) {
			$task['status_class'] = $statusMapping[$task['status_value']]['class'] ?? '';
		}

		return $tasks;
	}

	/**
	 * @param array $filteredProjectIds
	 * @return array
	 */
	public function getTasksWithAttemptsByProjectIds(array $filteredProjectIds) : array
	{
		$tasks = $this->getTasksWithAttempts();
		foreach ($tasks as $key => $task){
			if(!in_array($task['project_id'], $filteredProjectIds) && !empty($task['project_id'])) unset($tasks[$key]);
		}
		return $tasks;
	}
}