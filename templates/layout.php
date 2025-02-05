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
    <h5>Статистика</h5>
    <div class="mb-3">
        <strong>Всего:</strong> <?= htmlspecialchars($totalProjectStats['spent_hours']) ?>/<?= htmlspecialchars($totalProjectStats['planned_hours']) ?> ч. (<?= htmlspecialchars($totalProjectStats['progress_percent']) ?>%)
    </div>
    <h5>Общая статистика</h5>
    <div class="d-flex flex-wrap total-progress">
        <!-- Здесь будут квадратики общей статистики -->
    </div>
    <p class="total-progress-text">
        Выполнено: <?= htmlspecialchars($totalProgressPercent) ?>%<br>
        Осталось: <?= htmlspecialchars(100 - $totalProgressPercent) ?>%
    </p>
    <div class="mb-3">
		<?php foreach ($domainStats as $domain => $stats): ?>
            <div>
                <strong><?= ucfirst($domain) ?>:</strong> <?= htmlspecialchars($stats['spent_hours']) ?>/<?= htmlspecialchars($stats['planned_hours']) ?> ч. (<?= htmlspecialchars($stats['progress_percent']) ?>%)
            </div>
		<?php endforeach; ?>
    </div>
    <div class="mt-4">
        <h5>Статистика по сферам</h5>
        <div class="domain-stats">
			<?php foreach ($domainStats as $domain => $stats): ?>
                <div class="domain-stat">
                    <strong><?= ucfirst($domain) ?>:</strong>
                    <div class="d-flex flex-wrap">
						<?php for ($i = 1; $i <= $stats['domain_percentage']; $i++): ?>
                            <div class="square" style="width: <?= htmlspecialchars($stats['square_size']) ?>px; height: <?= htmlspecialchars($stats['square_size']) ?>px; background-color: <?= $i <= $stats['progress_percent'] ? 'green' : '#e0e0e0'; ?>;"></div>
						<?php endfor; ?>
                    </div>
                    <p>
                        Выполнено: <?= htmlspecialchars($stats['spent_hours']) ?>/<?= htmlspecialchars($stats['planned_hours']) ?> ч. (<?= htmlspecialchars($stats['progress_percent']) ?>%)<br>
                        Доля в общем: <?= htmlspecialchars($stats['domain_percentage']) ?>%
                    </p>
                </div>
			<?php endforeach; ?>
        </div>
    </div>
    <div class="mb-3 today-stats">
        <strong>Сегодня:</strong> <?= htmlspecialchars($todayStats['time_today']) ?> ч. (<?= htmlspecialchars($todayStats['progress_percent']) ?>%)
    </div>
    <div class="mb-3 today-domain-stats">
		<?php foreach ($todayDomainStats as $domain => $stats): ?>
            <div>
                <strong><?= ucfirst($domain) ?>:</strong> <?= htmlspecialchars($stats['time_today']) ?> ч. (<?= htmlspecialchars($stats['progress_percent']) ?>%)
            </div>
		<?php endforeach; ?>
    </div>
</div>
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
<footer class="footer mt-auto py-3 bg-light">
    <div class="container text-center">
        <a href="/settings">Настройки</a>
    </div>
</footer>
<!-- Подключение Bootstrap JS и Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Собственные скрипты -->
<script src="/assets/js/app.js"></script>
</body>
</html>