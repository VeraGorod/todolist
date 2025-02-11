<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <!-- Подключение Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Подключение CSS Choices.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

    <!-- Собственные стили -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="container mt-4">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['level']) && $_GET['level'] === 'Первая' ? 'active' : '' ?>" href="?level=Первая">Первый</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['level']) && $_GET['level'] === 'Вторая' ? 'active' : '' ?>" href="?level=Вторая">Второй</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['level']) && $_GET['level'] === 'Третья' ? 'active' : '' ?>" href="?level=Третья">Годовой</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= !isset($_GET['level']) || $_GET['level'] === 'all' ? 'active' : '' ?>" href="?level=all">Все</a>
                </li>
            </ul>
        </div>
    </nav>
    <h5 class="mt-4">Статистика</h5>
    <div class="mb-3">
        <strong>Всего:</strong> <?= htmlspecialchars($totalProjectStats['spent_hours']) ?>/<?= htmlspecialchars($totalProjectStats['planned_hours']) ?> ч. (<?= htmlspecialchars($totalProjectStats['progress_percent']) ?>%)
    </div>
    <h5>Общая статистика</h5>
    <div class="d-flex flex-wrap total-progress">
		<?php for ($i = 1; $i <= 100; $i++): ?>
            <div class="square" style="background-color: <?= $i <= $totalProgressPercent ? 'green' : '#e0e0e0'; ?>;"></div>
		<?php endfor; ?>
    </div>
    <p class="total-progress-text">
        Выполнено: <?= htmlspecialchars($totalProgressPercent) ?>%<br>
        Осталось: <?= htmlspecialchars(100 - $totalProgressPercent) ?>%
    </p>
    <!--<div class="mb-3">
		<?php /*foreach ($domainStats as $domain => $stats): */?>
            <div>
                <strong><?php /*= ucfirst($domain) */?>:</strong> <?php /*= htmlspecialchars($stats['spent_hours']) */?>/<?php /*= htmlspecialchars($stats['planned_hours']) */?> ч. (<?php /*= htmlspecialchars($stats['progress_percent']) */?>%)
            </div>
		<?php /*endforeach; */?>
    </div>-->
    <div class="mt-4">
        <h5>Статистика по сферам</h5>
        <div class="domain-stats">
			<?php foreach ($domainStats as $domain => $stats):
				if($stats['planned_hours'] == 0) continue;?>
                <div class="domain-stat">
                    <div class=""><strong><?= ucfirst($domain) ?>:</strong><span>&nbsp;&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($stats['progress_percent']) ?>%</span></div>
                    <p>
                        <?= htmlspecialchars($stats['spent_hours']) ?> / <?= htmlspecialchars($stats['planned_hours']) ?> ч.<br>
                        <!--Доля в общем: --><?php /*= htmlspecialchars($stats['domain_percentage']) */?>
                    </p>
                    <div class="squares-container d-flex flex-wrap">
							<?php for ($i = 0; $i < $stats['full_squares']; $i++): ?>
                                <div class="square full" style="width: <?= htmlspecialchars($stats['square_size']) ?>px; height: <?= htmlspecialchars($stats['square_size']) ?>px;"></div>
							<?php endfor; ?>
							<?php if ($stats['remainder'] > 0): ?>
                                <div class="square partial" style="width: <?= htmlspecialchars($stats['square_size']) ?>px; height: <?= htmlspecialchars($stats['square_size']) ?>px; background-size: <?= htmlspecialchars($stats['remainder']) ?>% 100%;"></div>
							<?php endif; ?>
							<?php
							$totalSquares = ceil($stats['domain_percentage']); // Общее количество квадратиков
							$emptySquares = $totalSquares - $stats['full_squares'] - ($stats['remainder'] > 0 ? 1 : 0);
							for ($i = 0; $i < $emptySquares; $i++): ?>
                                <div class="square empty" style="width: <?= htmlspecialchars($stats['square_size']) ?>px; height: <?= htmlspecialchars($stats['square_size']) ?>px;"></div>
							<?php endfor; ?>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
    </div>
    <div class="mb-3 today-stats">
        <strong>Сегодня:</strong> <?= htmlspecialchars($todayStats['time_today']) ?> ч. (<?= htmlspecialchars($todayStats['progress_percent']) ?>%)
    </div>
    <div class="mb-3 today-domain-stats">
		<?php foreach ($todayDomainStats as $domain => $stats):
            if($stats['progress_percent'] == 0) continue;?>
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
        <a href="/">Главная</a>
        <a href="/settings">Настройки</a>
        <a href="/tasks-table">Задачи</a>
        <a href="/projects-table">Проекты</a>
    </div>
</footer>
<!-- Подключение Bootstrap JS и Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Подключение JS Choices.js -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<!-- Собственные скрипты -->
<script src="/assets/js/app.js"></script>
</body>
</html>