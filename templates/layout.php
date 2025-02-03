<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Todo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="container-md my-5" style="max-width: 800px;">
    <h1 class="text-center mb-4">Мой Todo List</h1>

    <!-- Форма для добавления дела -->
    <form id="add-task-form" class="mb-4">
        <div class="input-group">
            <input type="text" id="task-name" class="form-control" placeholder="Название дела" required>
            <button type="submit" class="btn btn-primary">Добавить дело</button>
        </div>
    </form>

    <!-- Список дел -->
    <div id="tasks-list" class="mb-5">
		<?php include __DIR__ . '/tasks.php'; ?>
    </div>

    <!-- Форма для добавления проекта -->
    <form id="add-project-form" class="mb-4">
        <div class="input-group">
            <input type="text" id="project-name" class="form-control" placeholder="Название проекта" required>
            <button type="submit" class="btn btn-primary">Добавить проект</button>
        </div>
    </form>

    <!-- Список проектов -->
    <div id="projects-list">
		<?php include __DIR__ . '/projects.php'; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>