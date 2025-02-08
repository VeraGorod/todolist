<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Таблица дел</title>
	<link rel="stylesheet" href="/assets/css/style.css">
</head>
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
		<tr data-id="<?= htmlspecialchars($task['id']) ?>">
			<td><?= htmlspecialchars($task['id']) ?></td>
			<td contenteditable="true" class="editable" data-field="name"><?= htmlspecialchars($task['name']) ?></td>
			<td><?= htmlspecialchars($task['project_name']) ?? '' ?></td>
			<td><?php if (!empty($task['contexts'])): ?>
					<?= htmlspecialchars(implode(', ', $task['contexts'])) ?>
				<?php else: ?>

				<?php endif; ?></td>
			<td contenteditable="true" class="editable" data-field="target_attempts"><?= htmlspecialchars($task['target_attempts']) ?></td>
			<td contenteditable="true" class="editable" data-field="time_per_attempt"><?= htmlspecialchars($task['time_per_attempt']) ?></td>
			<td><?= htmlspecialchars($task['total_time']) ?></td>
			<td><?= htmlspecialchars($task['status_value']) ?? '' ?></td>
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