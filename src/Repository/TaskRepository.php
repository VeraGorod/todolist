<?php
namespace App\Repository;

use App\Entity\Task;
use PDO;

class TaskRepository
{
	public function __construct(private PDO $pdo) {}

	public function findAll(): array
	{
		$stmt = $this->pdo->query("SELECT t.*, 
               s.value AS status_value,
               c.value AS color_value,
               e.name AS project_name
        FROM tasks t
        LEFT JOIN lists s ON t.status_id = s.id
        LEFT JOIN lists c ON t.color_id = c.id
        LEFT JOIN projects e ON t.project_id = e.id");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function save(array $data): int
	{
		$sql = "INSERT INTO tasks (name, project_id, context_id, target_attempts, time_per_attempt, total_time, status_id, color_id, external_link)
            VALUES (:name, :projectId, :contextId, :targetAttempts, :timePerAttempt, :totalTime, :statusId, :colorId, :externalLink)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':name' => $data['name'],
			':projectId' => $data['project_id'] ?? null,
			':contextId' => $data['context_id'] ?? null,
			':targetAttempts' => $data['target_attempts'] ?? 0,
			':timePerAttempt' => $data['time_per_attempt'] ?? 0,
			':totalTime' => $data['total_time'] ?? 0,
			':statusId' => $data['status_id'] ?? null,
			':colorId' => $data['color_id'] ?? null,
			':externalLink' => $data['external_link'] ?? '',
		]);
		$taskId = (int) $this->pdo->lastInsertId();

		// Сохранение связей со списками
		if (!empty($data['domain_ids'])) {
			foreach ($data['domain_ids'] as $listId) {
				$this->saveTaskList($taskId, $listId);
			}
		}

		return $taskId;
	}

	public function saveTaskList(int $taskId, int $listId): bool
	{
		$stmt = $this->pdo->prepare("INSERT INTO task_lists (task_id, list_id) VALUES (:taskId, :listId)");
		return $stmt->execute([':taskId' => $taskId, ':listId' => $listId]);
	}

	public function findTaskLists(int $taskId): array
	{
		$stmt = $this->pdo->prepare("SELECT l.* FROM task_lists tl JOIN lists l ON tl.list_id = l.id WHERE tl.task_id = :taskId");
		$stmt->execute([':taskId' => $taskId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function update(int $id, array $data): bool {
		$sql = "UPDATE tasks SET 
                name = :name,
                project_id = :projectId,
                context_id = :contextId,
                target_attempts = :targetAttempts,
                time_per_attempt = :timePerAttempt,
                total_time = :totalTime,
                status_id = :statusId,
                color_id = :colorId,
                external_link = :externalLink
            WHERE id = :id";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([
			':id' => $id,
			':name' => $data['name'],
			':projectId' => $data['project_id'] ?? null,
			':contextId' => $data['context_id'] ?? null,
			':targetAttempts' => $data['target_attempts'] ?? 0,
			':timePerAttempt' => $data['time_per_attempt'] ?? 0,
			':totalTime' => $data['total_time'] ?? 0,
			':statusId' => $data['status_id'] ?? null,
			':colorId' => $data['color_id'] ?? null,
			':externalLink' => $data['external_link'] ?? '',
		]);
	}

	public function findById(int $id): ?array {
		$stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = :id");
		$stmt->execute([':id' => $id]);
		return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
	}

	/**
	 * Получить задачи по ID проекта.
	 *
	 * @param int $projectId
	 * @return array
	 */
	public function findByProjectId(int $projectId): array
	{
		$stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE project_id = :projectId");
		$stmt->execute([':projectId' => $projectId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function saveTaskDomains(int $taskId, array $domainIds): void
	{
		// Удаление старых связей
		$this->pdo->prepare("DELETE FROM task_lists WHERE task_id = :taskId")
			->execute([':taskId' => $taskId]);

		// Добавление новых связей
		foreach ($domainIds as $listId) {
			$this->pdo->prepare("INSERT INTO task_lists (task_id, list_id) VALUES (:taskId, :listId)")
				->execute([':taskId' => $taskId, ':listId' => $listId]);
		}
	}

	public function findTaskDomains(int $taskId): array
	{
		$stmt = $this->pdo->prepare("
        SELECT l.id, l.value
        FROM task_lists tl
        JOIN lists l ON tl.list_id = l.id
        WHERE tl.task_id = :taskId
    ");
		$stmt->execute([':taskId' => $taskId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function findTasksByDomain(int $domainId): array
	{
		$stmt = $this->pdo->prepare("
        SELECT t.*
        FROM tasks t
        JOIN task_lists tl ON t.id = tl.task_id
        WHERE tl.list_id = :domainId
    ");
		$stmt->execute([':domainId' => $domainId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function findTasksByDomainAndNoProject(int $domainId): array
	{
		$stmt = $this->pdo->prepare("
        SELECT t.*
        FROM tasks t
        JOIN task_lists tl ON t.id = tl.task_id
        WHERE tl.list_id = :domainId AND t.project_id IS NULL
    ");
		$stmt->execute([':domainId' => $domainId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function findTaskDomainsWithValues(int $taskId): array
	{
		$stmt = $this->pdo->prepare("
        SELECT l.id, l.value
        FROM task_lists tl
        JOIN lists l ON tl.list_id = l.id
        WHERE tl.task_id = :taskId
    ");
		$stmt->execute([':taskId' => $taskId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function findTaskContexts(int $taskId): array
	{
		$stmt = $this->pdo->prepare("
        SELECT l.value
        FROM task_lists tl
        JOIN lists l ON tl.list_id = l.id
        WHERE tl.task_id = :taskId AND l.type = 'context'
    ");
		$stmt->execute([':taskId' => $taskId]);
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}
}