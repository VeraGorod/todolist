<?php
namespace App\Service;

use App\Repository\ProjectRepository;

class ProjectService
{
	public function __construct(private ProjectRepository $repository) {}

	public function getAllProjects(): array
	{
		return $this->repository->findAll();
	}
}