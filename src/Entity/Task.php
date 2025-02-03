<?php
namespace App\Entity;

class Task
{
	public function __construct(
		public ?int $id,
		public string $name,
		public int $projectId,
		public array $contexts,
		public int $targetAttempts,
		public int $timePerAttempt,
		public int $totalTime,
		public string $status,
		public string $color,
		public array $domains,
		public string $externalLink
	) {}
}