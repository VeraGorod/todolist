<div class="card">
    <div class="card-header">
        <h3 class="card-title">Дела</h3>
    </div>
    <div class="card-body">
        <!-- Форма добавления дела -->
        <form id="add-task-form" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" id="task-name" name="name" placeholder="Введите название дела" required>
                <button type="submit" class="btn btn-primary">Добавить</button>
            </div>
        </form>

        <!-- Список дел -->
        <div id="tasks-list">
			<?php foreach ($tasks as $task): ?>
                <div class="list-group-item task-item d-flex justify-content-between align-items-center position-relative" data-id="<?= htmlspecialchars($task['id']) ?>">
                    <!-- Прогресс -->
                    <div class="progress-container position-absolute top-0 start-0 bottom-0">
                        <div class="progress-bar" style="width: <?= htmlspecialchars($task['progress_percent']) ?>%;"></div>
                    </div>
                    <!-- Кнопка добавления подхода -->
                    <button class="btn btn-success btn-sm add-attempt-btn me-2" data-id="<?= htmlspecialchars($task['id']) ?>">
                        <i class="bi bi-plus"></i>
                    </button>

                    <!-- Название дела -->
                    <strong class="task-name"><?= htmlspecialchars($task['name']) ?></strong>



                    <!-- Количество подходов / цель -->
                    <span class="text-muted attempts-count">
                <?= htmlspecialchars($task['attempts_count'] ?? 0) ?>/<?= htmlspecialchars($task['target_attempts']) ?>
            </span>

                    <!-- Кнопка редактирования -->
                    <button class="btn btn-outline-secondary btn-sm edit-task-btn" data-bs-toggle="modal" data-bs-target="#edit-task-modal" data-id="<?= htmlspecialchars($task['id']) ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
			<?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования дела -->
<div class="modal fade" id="edit-task-modal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTaskModalLabel">Редактирование дела</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-task-form">
                    <input type="hidden" id="task-id" name="id">
                    <div class="mb-3">
                        <label for="edit-task-name" class="form-label">Название</label>
                        <input type="text" class="form-control" id="edit-task-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-task-status" class="form-label">Статус</label>
                        <select class="form-select" id="edit-task-status" name="status">
                            <option value="new">Новое</option>
                            <option value="in_progress">В процессе</option>
                            <option value="completed">Завершено</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-task-project" class="form-label">Проект</label>
                        <select class="form-select" id="edit-task-project" name="projectId">
                            <option value="">Без проекта</option>
							<?php foreach ($projects as $project): ?>
                                <option value="<?= htmlspecialchars($project['id']) ?>">
									<?= htmlspecialchars($project['name']) ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-task-domains" class="form-label">Сферы жизни</label>
                        <select class="form-select" id="edit-task-domains" name="domains[]" multiple>
                            <option value="work">Работа</option>
                            <option value="health">Здоровье</option>
                            <option value="family">Семья</option>
                            <option value="personal_growth">Личностный рост</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-task-color" class="form-label">Цвет</label>
                        <select class="form-select" id="edit-task-color" name="color">
                            <option value="default">По умолчанию</option>
                            <option value="red">Красный</option>
                            <option value="blue">Синий</option>
                            <option value="green">Зеленый</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-task-target-attempts" class="form-label">Цель (количество подходов)</label>
                        <input type="number" class="form-control" id="edit-task-target-attempts" name="targetAttempts" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-task-time-per-attempt" class="form-label">Время на подход (минуты)</label>
                        <input type="number" class="form-control" id="edit-task-time-per-attempt" name="timePerAttempt" value="0" min="0" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </form>
            </div>
        </div>
    </div>
</div>