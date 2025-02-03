<?php
namespace App\Service;

use App\Entity\Attempt;
use App\Repository\AttemptRepository;

class AttemptService
{
	public function __construct(private AttemptRepository $repository) {}

	/**
	 * Получить все подходы.
	 *
	 * @return array
	 */
	public function getAllAttempts(): array
	{
		return $this->repository->findAll();
	}

	/**
	 * Получить подходы по задаче.
	 *
	 * @param int $taskId
	 * @return array
	 */
	public function getAttemptsByTask(int $taskId): array
	{
		return $this->repository->findByTaskId($taskId);
	}

	/**
	 * Добавить новый подход.
	 *
	 * @param array $data
	 * @return bool
	 */
	public function addAttempt(array $data): bool
	{
		$attempt = new Attempt(
			null,
			$data['date'],
			$data['taskId']
		);

		return $this->repository->save($attempt);
	}

	/**
	 * Получить статистику по подходам.
	 *
	 * @param int $taskId
	 * @return array
	 */
	public function getAttemptStats(int $taskId): array
	{
		$attempts = $this->repository->findByTaskId($taskId);
		$totalAttempts = count($attempts);
		$totalTime = $totalAttempts * $this->getAverageTimePerAttempt($attempts);

		return [
			'totalAttempts' => $totalAttempts,
			'totalTime' => $totalTime,
		];
	}

	/**
	 * Получить среднее время на подход.
	 *
	 * @param array $attempts
	 * @return int
	 */
	private function getAverageTimePerAttempt(array $attempts): int
	{
		if (empty($attempts)) {
			return 0;
		}

		$totalTime = array_reduce($attempts, function ($carry, $attempt) {
			return $carry + $attempt['timePerAttempt'];
		}, 0);

		return (int) ($totalTime / count($attempts));
	}
}