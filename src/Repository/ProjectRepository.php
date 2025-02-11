<?php
namespace App\Repository;

use App\Entity\Project;
use PDO;

class ProjectRepository
{
	public function __construct(private PDO $pdo) {}

	public function findAll(): array
	{
		$stmt = $this->pdo->query("SELECT p.*, 
               s.value AS size_value,
               l.value AS level_value,
               c.value AS color_value,
               e.value AS status_value
        FROM projects p
        LEFT JOIN lists s ON p.size_id = s.id
        LEFT JOIN lists l ON p.level_id = l.id
        LEFT JOIN lists c ON p.color_id = c.id
        LEFT JOIN lists e ON p.status_id = e.id");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function save(array $data): int
	{
		$sql = "INSERT INTO projects (name, external_link, size_id, level_id, hours, status_id, color_id, domains, color, size, level)
            VALUES (:name, :externalLink, :sizeId, :levelId, :hours, :statusId, :colorId, '', '', '', '')";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':name' => $data['name'],
			':externalLink' => $data['external_link'] ?? '',
			':sizeId' => $data['size_id'] ?? null,
			':levelId' => $data['level_id'] ?? null,
			':hours' => $data['hours'] ?? 0,
			':statusId' => $data['status_id'] ?? null,
			':colorId' => $data['color_id'] ?? null,
		]);
		$projectId = (int) $this->pdo->lastInsertId();

		// Сохранение связей со списками
		if (!empty($data['domain_ids'])) {
			foreach ($data['domain_ids'] as $listId) {
				$this->saveProjectList($projectId, $listId);
			}
		}

		return $projectId;
	}

	public function saveProjectList(int $projectId, int $listId): bool
	{
		$stmt = $this->pdo->prepare("INSERT INTO project_lists (project_id, list_id) VALUES (:projectId, :listId)");
		return $stmt->execute([':projectId' => $projectId, ':listId' => $listId]);
	}

	public function findProjectLists(int $projectId): array
	{
		$stmt = $this->pdo->prepare("SELECT l.* FROM project_lists pl JOIN lists l ON pl.list_id = l.id WHERE pl.project_id = :projectId");
		$stmt->execute([':projectId' => $projectId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function update(int $id, array $data): bool {
		$sql = "UPDATE projects SET 
                name = :name,
                size_id = :sizeId,
                level_id = :levelId,
                hours = :hours,
                status_id = :statusId,
                color_id = :colorId,
                external_link = :externalLink
            WHERE id = :id";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([
			':id' => $id,
			':name' => $data['name'],
			':sizeId' => $data['size_id'] ?? null,
			':levelId' => $data['level_id'] ?? null,
			':hours' => $data['hours'] ?? 0,
			':statusId' => $data['status_id'] ?? null,
			':colorId' => $data['color_id'] ?? null,
			':externalLink' => $data['external_link'] ?? '',
		]);
	}

	public function findById(int $id): ?array {
		$stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = :id");
		$stmt->execute([':id' => $id]);
		return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
	}

	public function saveProjectDomains(int $projectId, array $domainIds): void
	{
		// Удаление старых связей
		$this->pdo->prepare("DELETE FROM project_lists WHERE project_id = :projectId")
			->execute([':projectId' => $projectId]);

		// Добавление новых связей
		foreach ($domainIds as $listId) {
			$this->pdo->prepare("INSERT INTO project_lists (project_id, list_id) VALUES (:projectId, :listId)")
				->execute([':projectId' => $projectId, ':listId' => $listId]);
		}
	}

	public function findProjectDomains(int $projectId): array
	{
		$stmt = $this->pdo->prepare("
        SELECT l.id, l.value
        FROM project_lists pl
        JOIN lists l ON pl.list_id = l.id
        WHERE pl.project_id = :projectId
    ");
		$stmt->execute([':projectId' => $projectId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function findProjectsByDomain(int $domainId): array
	{
		$stmt = $this->pdo->prepare("
        SELECT p.*
        FROM projects p
        JOIN project_lists pl ON p.id = pl.project_id
        WHERE pl.list_id = :domainId
    ");
		$stmt->execute([':domainId' => $domainId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function findProjectsByDomainAndLevel(int $domainId, string|null $level): array
	{
		$stmt = $this->pdo->prepare("
        SELECT p.*
        FROM projects p
        JOIN project_lists pl ON p.id = pl.project_id
        JOIN lists l ON p.level_id = l.id
        WHERE pl.list_id = :domainId AND l.value = :level
    ");
		$stmt->execute([':domainId' => $domainId, ':level' => $level]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function findProjectDomainsWithValues(int $projectId): array
	{
		$stmt = $this->pdo->prepare("
        SELECT l.id, l.value
        FROM project_lists pl
        JOIN lists l ON pl.list_id = l.id
        WHERE pl.project_id = :projectId
    ");
		$stmt->execute([':projectId' => $projectId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}