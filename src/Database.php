<?php
namespace App;

use PDO;

class Database
{
	private PDO $pdo;

	public function __construct(string $path)
	{
		$this->pdo = new PDO("sqlite:$path");
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function getPdo(): PDO
	{
		return $this->pdo;
	}
}