<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Таблица дел</title>
	<link rel="stylesheet" href="/assets/css/style.css">
</head>
<style>
    /* Зачеркивание названия для статуса "Готово" */
    .task.status-done .task-name {
        text-decoration: line-through;
        color: #888; /* Светло-серый цвет текста */
    }

    /* Прозрачность для статусов "Обработать" и "Заморозка" */
    .task.status-on-hold,
    .task.status-frozen {
        opacity: 0.8; /* 80% прозрачности */
    }

    /* Цветовые индикаторы для статусов */
    .task.status-for-monkey { border-left: 5px solid #FFD700; } /* Золотой */
    .task.status-in-progress { border-left: 5px solid #4CAF50; } /* Зеленый */
    .task.status-on-hold { border-left: 5px solid #FF9800; } /* Оранжевый */
    .task.status-frozen { border-left: 5px solid #2196F3; } /* Синий */
    .task.status-done { border-left: 5px solid #9E9E9E; } /* Серый */
</style>
<body>
<h1>Таблица дел</h1>
<table border="1">
	<thead>
	<tr>
		<th>ID</th>
		<th>Название</th>
		<th>Проект</th>
		<th>Контексты</th>
		<th>Целевое количество подходов</th>
		<th>Время на подход</th>
		<th>Общее время</th>
		<th>Статус</th>
		<th>Цвет</th>
		<th>Сферы (ID | Value)</th>
		<th>Внешняя ссылка</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($tasks as $task): ?>
		<tr data-id="<?= htmlspecialchars($task['id']) ?>" class=" task <?= htmlspecialchars($task['status_class']) ?>" data-status="<?= htmlspecialchars($task['status_value']) ?>">
			<td><?= htmlspecialchars($task['id']) ?></td>
			<td class="task-name" contenteditable="true" class="editable" data-field="name"><?= htmlspecialchars($task['name']) ?></td>
			<td><?= htmlspecialchars($task['project_name']) ?? '' ?></td>
			<td><?php if (!empty($task['contexts'])): ?>
					<?= htmlspecialchars(implode(', ', $task['contexts'])) ?>
				<?php else: ?>

				<?php endif; ?></td>
			<td contenteditable="true" class="editable" data-field="target_attempts"><?= htmlspecialchars($task['target_attempts']) ?></td>
			<td contenteditable="true" class="editable" data-field="time_per_attempt"><?= htmlspecialchars($task['time_per_attempt']) ?></td>
			<td><?= htmlspecialchars($task['total_time']) ?></td>
			<td class="task-status"><?= htmlspecialchars($task['status_value']) ?? '' ?></td>
			<td><?= htmlspecialchars($task['color_value']) ?? ''?></td>
			<td>
				<?php foreach ($task['domains'] as $domain): ?>
					<?= htmlspecialchars($domain['id']) ?> | <?= htmlspecialchars($domain['value']) ?><br>
				<?php endforeach; ?>
			</td>
			<td contenteditable="true" class="editable" data-field="name"><?= htmlspecialchars($task['external_link']) ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<!-- Собственные скрипты -->
<script src="/assets/js/tables.js"></script>
</body>
</html>