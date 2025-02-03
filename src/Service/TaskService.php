<?php
namespace App\Service;

use App\Entity\Task;
use App\Repository\AttemptRepository;
use App\Repository\TaskRepository;

class TaskService
{
	public function __construct(TaskRepository $taskRepository, AttemptRepository $attemptRepository)
	{
		$this->taskRepository = $taskRepository;
		$this->attemptRepository = $attemptRepository;
	}

	/**
	 * Получить все задачи.
	 *
	 * @return array
	 */
	public function getAllTasks(): array
	{
		return $this->taskRepository->findAll();
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

		return $this->repository->save($task);
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
		$tasks = $this->taskRepository->findAll();
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
}