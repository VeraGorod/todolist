<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <!-- Подключение Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Собственные стили -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="container mt-4">
    <h1 class="text-center">Мой Todo List</h1>
    <div class="row">
        <div class="col-md-6">
			<?php include __DIR__ . '/tasks.php'; ?>
        </div>
        <div class="col-md-6">
			<?php include __DIR__ . '/projects.php'; ?>
        </div>
    </div>
</div>
<!-- Подключение Bootstrap JS и Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Собственные скрипты -->
<script src="/assets/js/app.js"></script>
</body>
</html>