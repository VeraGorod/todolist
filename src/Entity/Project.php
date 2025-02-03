<?php
namespace App\Entity;

class Project
{
	public function __construct(
		public ?int $id,
		public string $name,
		public string $externalLink,
		public array $domains,
		public string $color,
		public string $size,
		public int $hours,
		public string $level
	) {}
}