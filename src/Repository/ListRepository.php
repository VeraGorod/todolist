<?php

namespace App\Repository;

use PDO;

class ListRepository
{
	private $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	/**
	 * Получить все элементы списка по типу.
	 *
	 * @param string $type
	 * @return array
	 */
	public function findAllByType(string $type): array
	{
		$stmt = $this->pdo->prepare("SELECT * FROM lists WHERE type = :type ORDER BY name");
		$stmt->execute([':type' => $type]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Добавить или обновить элемент списка.
	 *
	 * @param string $name
	 * @param string $type
	 * @param string $value
	 * @return bool
	 */
	public function save(string $name, string $type, string $value): bool
	{
		$stmt = $this->pdo->prepare("INSERT OR REPLACE INTO lists (name, type, value) VALUES (:name, :type, :value)");
		return $stmt->execute([
			':name' => $name,
			':type' => $type,
			':value' => $value,
		]);
	}

	/**
	 * Удалить элемент списка.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		$stmt = $this->pdo->prepare("DELETE FROM lists WHERE id = :id");
		return $stmt->execute([':id' => $id]);
	}

	/**
	 * Получить все элементы списков.
	 *
	 * @return array
	 */
	public function findAll(): array
	{
		$stmt = $this->pdo->query("SELECT * FROM lists ORDER BY type, name");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getDomains(): array
	{
		$stmt = $this->pdo->prepare("SELECT id, value FROM lists WHERE type = 'domains'");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}