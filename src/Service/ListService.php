<?php

namespace App\Service;

use App\Repository\ListRepository;

class ListService
{
	private $repository;

	public function __construct(ListRepository $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * Получить все элементы списка по типу.
	 *
	 * @param string $type
	 * @return array
	 */
	public function getAllByType(string $type): array
	{
		return $this->repository->findAllByType($type);
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
		return $this->repository->save($name, $type, $value);
	}

	/**
	 * Удалить элемент списка.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		return $this->repository->delete($id);
	}

	/**
	 * Получить все элементы списков, сгруппированные по типам.
	 *
	 * @return array
	 */
	public function getAll(): array
	{
		$lists = $this->repository->findAll();
		$groupedLists = [];
		foreach ($lists as $list) {

			$groupedLists[$list['type']][] = $list;
		}
		return $groupedLists;
	}
}