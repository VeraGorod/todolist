document.addEventListener('DOMContentLoaded', () => {
    // Добавление дела
    const addTaskForm = document.getElementById('add-task-form');
    addTaskForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const taskName = document.getElementById('task-name').value;
        const response = await fetch('/api/tasks', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: taskName }),
        });
        if (response.ok) {
            const tasksList = document.getElementById('tasks-list');
            const newTask = await response.json();

            // Создаем новый элемент с актуальной версткой
            const taskElement = document.createElement('div');
            taskElement.className = 'list-group-item d-flex justify-content-between align-items-center position-relative';
            taskElement.dataset.id = newTask.id;

            taskElement.innerHTML = `
                <!-- Кнопка добавления подхода -->
                <button class="btn btn-success btn-sm add-attempt-btn me-2" data-id="${newTask.id}">
                    <i class="bi bi-plus"></i>
                </button>

                <!-- Название дела -->
                <strong class="task-name">${newTask.name}</strong>

                <!-- Прогресс -->
                <div class="progress-container position-absolute top-0 start-0 bottom-0">
                    <div class="progress-bar" style="width: 0%;"></div>
                </div>

                <!-- Количество подходов / цель -->
                <span class="text-muted attempts-count">0/1</span>

                <!-- Кнопка редактирования -->
                <button class="btn btn-outline-secondary btn-sm edit-task-btn" data-bs-toggle="modal" data-bs-target="#edit-task-modal" data-id="${newTask.id}">
                    <i class="bi bi-pencil"></i>
                </button>
            `;

            tasksList.appendChild(taskElement);

            // Очистка формы
            addTaskForm.reset();

            // Добавляем обработчики событий для новых кнопок
            attachEventListeners();
        } else {
            alert('Ошибка при добавлении дела');
        }
    });


    // Добавление проекта
    const addProjectForm = document.getElementById('add-project-form');
    addProjectForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const projectName = document.getElementById('project-name').value;
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: projectName }),
        });
        if (response.ok) {
            const projectsList = document.getElementById('projects-list');
            const newProject = await response.json();

            // Создаем новый элемент с актуальной версткой
            const projectElement = document.createElement('div');
            projectElement.className = 'list-group-item d-flex justify-content-between align-items-center';
            projectElement.dataset.id = newProject.id;

            projectElement.innerHTML = `
                <div>
                    <strong class="project-name">${newProject.name}</strong>
                    <small class="project-level text-muted">beginner</small>
                </div>
                <button class="btn btn-outline-secondary btn-sm edit-project-btn" data-bs-toggle="modal" data-bs-target="#edit-project-modal" data-id="${newProject.id}">
                    <i class="bi bi-pencil"></i>
                </button>
            `;

            projectsList.appendChild(projectElement);

            // Очистка формы
            addProjectForm.reset();

            // Добавляем обработчики событий для новых кнопок
            attachEventListeners();
        } else {
            alert('Ошибка при добавлении проекта');
        }
    });

    // Функция для добавления обработчиков событий к новым элементам
    function attachEventListeners() {
        // Добавление обработчиков для кнопок "Добавить подход"
        document.querySelectorAll('.add-attempt-btn').forEach(button => {
            button.addEventListener('click', async () => {
                const taskId = button.dataset.id;
                const response = await fetch(`/api/attempts`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ taskId }),
                });
                if (response.ok) {
                    const updatedTask = await response.json();
                    const taskElement = document.querySelector(`.list-group-item[data-id="${updatedTask.id}"]`);

                    // Обновляем счетчик подходов
                    taskElement.querySelector('.attempts-count').textContent = `${updatedTask.attempts_count}/1`;

                    // Обновляем ширину прогресса
                    taskElement.querySelector('.progress-bar').style.width = `${updatedTask.progress_percent}%`;
                } else {
                    alert('Ошибка при добавлении подхода');
                }
            });
        });

        // Добавление обработчиков для кнопок "Редактировать"
        document.querySelectorAll('.edit-task-btn').forEach(button => {
            button.addEventListener('click', async () => {
                const taskId = button.closest('.list-group-item').dataset.id;
                const response = await fetch(`/api/tasks/${taskId}`);
                if (response.ok) {
                    const task = await response.json();
                    document.getElementById('task-id').value = task.id;
                    document.getElementById('edit-task-name').value = task.name;
                    document.getElementById('edit-task-status').value = task.status;
                    document.getElementById('edit-task-target-attempts').value = task.target_attempts || 1;
                    document.getElementById('edit-task-time-per-attempt').value = task.time_per_attempt || 0;

                    // Заполняем множественный выбор сфер жизни
                    const domainsSelect = document.getElementById('edit-task-domains');
                    domainsSelect.value = task.domains ? JSON.parse(task.domains) : [];

                    // Заполняем цвет
                    document.getElementById('edit-task-color').value = task.color || 'default';
                }
            });
        });
    }

    // Инициализация обработчиков событий
    attachEventListeners();


    // Редактирование дела
    document.querySelectorAll('.edit-task-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const taskId = button.closest('.list-group-item').dataset.id;
            const response = await fetch(`/api/tasks/${taskId}`);
            if (response.ok) {
                const task = await response.json();
                document.getElementById('task-id').value = task.id;
                document.getElementById('edit-task-name').value = task.name;
                document.getElementById('edit-task-status').value = task.status;

                // Заполняем выпадающий список проектов
                const projectSelect = document.getElementById('edit-task-project');
                projectSelect.value = task.project_id || ''; // Если проект не указан, выбирается пустое значение

                document.getElementById('edit-task-target-attempts').value = task.target_attempts || 1;
                document.getElementById('edit-task-time-per-attempt').value = task.time_per_attempt || 0;
            }
        });
    });

// Сохранение изменений дела
    const editTaskForm = document.getElementById('edit-task-form');
    editTaskForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(editTaskForm);
        const taskId = formData.get('id');
        const response = await fetch(`/api/tasks/${taskId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: taskId,
                name: formData.get('name'),
                status: formData.get('status'),
                projectId: formData.get('projectId') || null, // Если проект не выбран, отправляем null
                targetAttempts: parseInt(formData.get('targetAttempts'), 10),
                timePerAttempt: parseInt(formData.get('timePerAttempt'), 10),
            }),
        });
        if (response.ok) {
            const updatedTask = await response.json();
            const taskElement = document.querySelector(`.list-group-item[data-id="${updatedTask.id}"]`);
            taskElement.querySelector('.task-name').textContent = updatedTask.name;
            bootstrap.Modal.getInstance(document.getElementById('edit-task-modal')).hide();
        } else {
            alert('Ошибка при обновлении дела');
        }
    });

// Редактирование проекта
    document.querySelectorAll('.edit-project-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const projectId = button.closest('.list-group-item').dataset.id;
            const response = await fetch(`/api/projects/${projectId}`);
            if (response.ok) {
                const project = await response.json();
                document.getElementById('project-id').value = project.id;
                document.getElementById('edit-project-name').value = project.name;
                document.getElementById('edit-project-level').value = project.level;

                // Заполняем множественный выбор сфер жизни
                const domainsSelect = document.getElementById('edit-project-domains');
                domainsSelect.value = project.domains ? JSON.parse(project.domains) : [];

                // Заполняем размер
                document.getElementById('edit-project-size').value = project.size || 'medium';

                // Заполняем часы
                document.getElementById('edit-project-hours').value = project.hours || 0;
            }
        });
    });

// Сохранение изменений проекта
    const editProjectForm = document.getElementById('edit-project-form');
    editProjectForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(editProjectForm);
        const projectId = formData.get('id');
        const response = await fetch(`/api/projects/${projectId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: projectId,
                name: formData.get('name'),
                level: formData.get('level'),
                domains: Array.from(formData.getAll('domains')),
                size: formData.get('size'),
                hours: parseInt(formData.get('hours'), 10),
            }),
        });
        if (response.ok) {
            const updatedProject = await response.json();
            const projectElement = document.querySelector(`.list-group-item[data-id="${updatedProject.id}"]`);
            projectElement.querySelector('.project-name').textContent = updatedProject.name;
            bootstrap.Modal.getInstance(document.getElementById('edit-project-modal')).hide();
        } else {
            alert('Ошибка при обновлении проекта');
        }
    });

    // Добавление нового подхода
    document.querySelectorAll('.add-attempt-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const taskId = button.dataset.id;
            const response = await fetch(`/api/attempts`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ taskId }),
            });
            if (response.ok) {
                const updatedTask = await response.json();
                const taskElement = document.querySelector(`.list-group-item[data-id="${updatedTask.id}"]`);

                // Обновляем счетчик подходов
                const attemptsCountElement = taskElement.querySelector('.attempts-count');
                attemptsCountElement.textContent = `${updatedTask.attempts_count}/${updatedTask.target_attempts}`;

                // Обновляем ширину прогресса
                const progressBar = taskElement.querySelector('.progress-bar');
                progressBar.style.width = `${updatedTask.progress_percent}%`;
            } else {
                alert('Ошибка при добавлении подхода');
            }
        });
    });
});