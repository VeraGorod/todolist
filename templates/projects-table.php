<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Таблица проектов</title>
	<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<h1>Таблица проектов</h1>
<table border="1">
	<thead>
	<tr>
		<th>ID</th>
		<th>Название</th>
		<th>Внешняя ссылка</th>
		<th>Сферы (ID | Value)</th>
		<th>Цвет</th>
		<th>Размер</th>
		<th>Часы</th>
		<th>Уровень</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($projects as $project): ?>
		<tr>
			<td data-id="<?= htmlspecialchars($project['id']) ?>"><?= htmlspecialchars($project['id']) ?></td>
			<td contenteditable="true" class="editable" data-field="name"><?= htmlspecialchars($project['name']) ?></td>
			<td contenteditable="true" class="editable" data-field="external_link"><?= htmlspecialchars($project['external_link']) ?></td>
			<td>
				<?php foreach ($project['domains'] as $domain): ?>
					<?= htmlspecialchars($domain['id']) ?> | <?= htmlspecialchars($domain['value']) ?><br>
				<?php endforeach; ?>
			</td>
			<td><?= htmlspecialchars($project['color_value']) ?? '' ?></td>
			<td><?= htmlspecialchars($project['size_value']) ?? '' ?></td>
			<td contenteditable="true" class="editable" data-field="hours"><?= htmlspecialchars($project['hours']) ?></td>
			<td><?= htmlspecialchars($project['level_value']) ?? '' ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<!-- Собственные скрипты -->
<script src="/assets/js/tables.js"></script>
</body>
</html>