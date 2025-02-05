<?php
namespace App\Repository;

use App\Entity\Attempt;
use PDO;

class AttemptRepository
{
	public function __construct(private PDO $pdo) {}

	/**
	 * Получить все подходы.
	 *
	 * @return array
	 */
	public function findAll(): array
	{
		$stmt = $this->pdo->query("SELECT * FROM attempts");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Получить подходы по ID задачи.
	 *
	 * @param int $taskId
	 * @return array
	 */
	public function findByTaskId(int $taskId): array
	{
		$stmt = $this->pdo->prepare("SELECT * FROM attempts WHERE task_id = :taskId");
		$stmt->execute([':taskId' => $taskId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Найти подход по ID.
	 *
	 * @param int $id
	 * @return array|null
	 */
	public function findById(int $id): ?array
	{
		$stmt = $this->pdo->prepare("SELECT * FROM attempts WHERE id = :id");
		$stmt->execute([':id' => $id]);
		return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
	}

	/**
	 * Сохранить подход (добавить или обновить).
	 *
	 * @param Attempt $attempt
	 * @return bool
	 */
	public function save(array $data): int|bool
	{
		// Проверяем обязательные поля
		if (empty($data['date']) || empty($data['taskId'])) {
			throw new InvalidArgumentException('Необходимые поля "date" и "taskId" отсутствуют');
		}

		if (!empty($data['id'])) {
			// Обновление существующего подхода
			$sql = "UPDATE attempts SET date = :date, task_id = :taskId WHERE id = :id";
			$params = [
				':id' => $data['id'],
				':date' => $data['date'],
				':taskId' => $data['taskId'],
			];
		} else {
			// Добавление нового подхода
			$sql = "INSERT INTO attempts (date, task_id) VALUES (:date, :taskId)";
			$params = [
				':date' => $data['date'],
				':taskId' => $data['taskId'],
			];
		}

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		return (int) $this->pdo->lastInsertId(); // Возвращаем ID новой записи
	}

	/**
	 * Удалить подход по ID.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		$sql = "DELETE FROM attempts WHERE id = :id";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([':id' => $id]);
	}

	/**
	 * Получить общее количество подходов для задачи.
	 *
	 * @param int $taskId
	 * @return int
	 */
	public function countByTaskId(int $taskId): int
	{
		$stmt = $this->pdo->prepare("SELECT COUNT(*) FROM attempts WHERE task_id = :taskId");
		$stmt->execute([':taskId' => $taskId]);
		return (int) $stmt->fetchColumn();
	}

	/**
	 * Получить общее время, затраченное на задачу.
	 *
	 * @param int $taskId
	 * @return int
	 */
	public function getTotalTimeByTaskId(int $taskId): int
	{
		$stmt = $this->pdo->prepare("
            SELECT SUM(t.time_per_attempt) as total_time
            FROM attempts a
            JOIN tasks t ON a.task_id = t.id
            WHERE a.task_id = :taskId
        ");
		$stmt->execute([':taskId' => $taskId]);
		return (int) $stmt->fetchColumn();
	}

	/**
	 * Получить подходы по дате.
	 *
	 * @param string $date
	 * @return array
	 */
	public function findByDate(string $date): array
	{
		$stmt = $this->pdo->prepare("SELECT * FROM attempts WHERE date LIKE :date");
		$stmt->execute([':date' => "$date%"]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

}