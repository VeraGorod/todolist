<?php
namespace App\Repository;

use App\Entity\Task;
use PDO;

class TaskRepository
{
	public function __construct(private PDO $pdo) {}

	public function findAll(): array
	{
		$stmt = $this->pdo->query("SELECT * FROM tasks");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	public function save(array $data): int
	{
		$sql = "INSERT INTO tasks (name, project_id, contexts, target_attempts, time_per_attempt, total_time, status, color, domains, external_link)
            VALUES (:name, :projectId, :contexts, :targetAttempts, :timePerAttempt, :totalTime, :status, :color, :domains, :externalLink)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':name' => $data['name'],
			':projectId' => $data['projectId'],
			':contexts' => json_encode($data['contexts']),
			':targetAttempts' => $data['targetAttempts'],
			':timePerAttempt' => $data['timePerAttempt'],
			':totalTime' => $data['totalTime'],
			':status' => $data['status'],
			':color' => $data['color'],
			':domains' => json_encode($data['domains']),
			':externalLink' => $data['externalLink'],
		]);
		return (int) $this->pdo->lastInsertId();
	}

	public function update(int $id, array $data): bool {
		$sql = "UPDATE tasks SET 
        name = :name,
        status = :status,
        project_id = :projectId,
        domains = :domains,
        color = :color,
        target_attempts = :targetAttempts,
        time_per_attempt = :timePerAttempt
        WHERE id = :id";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([
			':id' => $id,
			':name' => $data['name'],
			':status' => $data['status'],
			':projectId' => $data['projectId'] !== null ? $data['projectId'] : null,
			':domains' => json_encode($data['domains']),
			':color' => $data['color'],
			':targetAttempts' => $data['targetAttempts'],
			':timePerAttempt' => $data['timePerAttempt'],
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
}