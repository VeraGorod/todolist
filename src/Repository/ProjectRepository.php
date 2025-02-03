<?php
namespace App\Repository;

use App\Entity\Project;
use PDO;

class ProjectRepository
{
	public function __construct(private PDO $pdo) {}

	public function findAll(): array
	{
		$stmt = $this->pdo->query("SELECT * FROM projects");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function save(array $data): int
	{
		$sql = "INSERT INTO projects (name, external_link, domains, color, size, hours, level)
            VALUES (:name, :externalLink, :domains, :color, :size, :hours, :level)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':name' => $data['name'],
			':externalLink' => $data['externalLink'],
			':domains' => json_encode($data['domains']),
			':color' => $data['color'],
			':size' => $data['size'],
			':hours' => $data['hours'],
			':level' => $data['level'],
		]);
		return (int) $this->pdo->lastInsertId();
	}
}