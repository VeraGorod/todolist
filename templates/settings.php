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
    <h5>Редактирование списков</h5>

    <div class="row">
		<?php foreach ($listsByType as $type => $items): ?>
            <div class="col-md-6 mb-4">
                <h6><?= ucfirst(str_replace('_', ' ', $type)) ?></h6>
                <!-- Список элементов -->
                <div id="list-items-<?= htmlspecialchars($type) ?>">
					<?php foreach ($items as $item): ?>
                        <div class="list-item mb-2" data-id="<?= htmlspecialchars($item['id']) ?>">
                            <span><?= htmlspecialchars($item['value']) ?></span>
                            <button class="btn btn-danger btn-sm delete-list-item" data-type="<?= htmlspecialchars($type) ?>">Удалить</button>
                        </div>
					<?php endforeach; ?>
                </div>

                <!-- Форма добавления нового элемента -->
                <form class="add-list-item-form mt-2" data-type="<?= htmlspecialchars($type) ?>">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text" name="value" class="form-control" placeholder="Новое значение" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Добавить</button>
                        </div>
                    </div>
                </form>
            </div>
		<?php endforeach; ?>
    </div>
</div>
<!-- Подключение Bootstrap JS и Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Собственные скрипты -->
<script src="/assets/js/app.js"></script>
</body>
</html>