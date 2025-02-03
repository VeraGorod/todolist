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
}