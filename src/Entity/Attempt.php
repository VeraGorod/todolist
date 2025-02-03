<?php
namespace App\Entity;

class Attempt
{
	public function __construct(
		public ?int $id,
		public string $date,
		public int $taskId
	) {}
}