<div>
    <h2 class="mb-3">Дела</h2>
	<?php foreach ($tasks as $task): ?>
        <div class="task">
            <span class="task-name"><?= htmlspecialchars($task['name']) ?></span>
            <span class="task-status"><?= htmlspecialchars($task['status']) ?></span>
        </div>
	<?php endforeach; ?>
</div>