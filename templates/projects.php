<div>
    <h2 class="mb-3">Проекты</h2>
	<?php foreach ($projects as $project): ?>
        <div class="project">
            <span class="project-name"><?= htmlspecialchars($project['name']) ?></span>
            <span class="project-level"><?= htmlspecialchars($project['level']) ?></span>
        </div>
	<?php endforeach; ?>
</div>