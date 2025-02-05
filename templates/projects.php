<div class="card">
    <div class="card-header">
        <h3 class="card-title">Проекты</h3>
    </div>
    <div class="card-body">
        <!-- Форма добавления проекта -->
        <form id="add-project-form" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" id="project-name" name="name" placeholder="Введите название проекта" required>
                <button type="submit" class="btn btn-primary">Добавить</button>
            </div>
        </form>

        <!-- Список проектов -->
        <div id="projects-list">
			<?php foreach ($projects as $project): ?>
                <div class="list-group-item project-item d-flex justify-content-between align-items-center" data-id="<?= htmlspecialchars($project['id']) ?>">
                    <!-- Прогресс -->
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= htmlspecialchars($project['progress_percent']) ?>%;"></div>
                    </div>
                    <div>
                        <strong class="project-name"><?= htmlspecialchars($project['name']) ?></strong>
                        <!-- Часы (факт / план) -->
                        <small class="project-hours text-muted">
							<?= htmlspecialchars($project['total_time_spent']) ?>/<?= htmlspecialchars($project['hours']) ?> ч
                        </small>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm edit-project-btn" data-bs-toggle="modal" data-bs-target="#edit-project-modal" data-id="<?= htmlspecialchars($project['id']) ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
			<?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования проекта -->
<div class="modal fade" id="edit-project-modal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProjectModalLabel">Редактирование проекта</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-project-form">
                    <input type="hidden" id="project-id" name="id">
                    <div class="mb-3">
                        <label for="edit-project-name" class="form-label">Название</label>
                        <input type="text" class="form-control" id="edit-project-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-project-level" class="form-label">Уровень сложности</label>
                        <select class="form-select" id="edit-project-level" name="level">
                            <option value="beginner">Начинающий</option>
                            <option value="intermediate">Средний</option>
                            <option value="advanced">Продвинутый</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-project-domains" class="form-label">Сферы жизни</label>
                        <select class="form-select" id="edit-project-domains" name="domains[]" multiple>
                            <option value="work">Работа</option>
                            <option value="health">Здоровье</option>
                            <option value="family">Семья</option>
                            <option value="personal_growth">Личностный рост</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-project-size" class="form-label">Размер</label>
                        <select class="form-select" id="edit-project-size" name="size">
                            <option value="small">Маленький</option>
                            <option value="medium">Средний</option>
                            <option value="large">Большой</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-project-hours" class="form-label">Часы (затраченное время)</label>
                        <input type="number" class="form-control" id="edit-project-hours" name="hours" value="0" min="0" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </form>
            </div>
        </div>
    </div>
</div>