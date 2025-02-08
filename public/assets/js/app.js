document.addEventListener('DOMContentLoaded', () => {

    const taskSelect = document.getElementById('project-select');
    let choicesInstance = null;

    // Инициализация Choices.js
    function initializeChoices() {
        if (choicesInstance) {
            choicesInstance.destroy(); // Уничтожаем предыдущий экземпляр
        }
        choicesInstance = new Choices(taskSelect, {
            searchEnabled: true,
            placeholder: true,
            placeholderValue: 'Выберите проект...',
            noResultsText: 'Нет результатов',
        });
    }

    initializeChoices();

    // Обновление прогресса после сохранения
    const updateProgressBar = (element, progressPercent) => {
        const progressBar = element.querySelector('.progress-bar');
        progressBar.style.width = `${progressPercent}%`;
    };

    // Добавление дела
    const addTaskForm = document.getElementById('add-task-form');
    if (addTaskForm){
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
    }



    // Добавление проекта
    const addProjectForm = document.getElementById('add-project-form');
    if (addProjectForm){
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
                projectElement.className = 'project-item d-flex justify-content-between align-items-center';
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
    }


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
                    if(task.project_id) choicesInstance.setChoiceByValue(task.project_id);;
                    if(task.status_id) document.getElementById('edit-task-status').value = task.status_id;
                    if(task.color_id) document.getElementById('edit-task-color').value = task.color_id;
                    document.getElementById('edit-task-target-attempts').value = task.target_attempts || 1;
                    document.getElementById('edit-task-time-per-attempt').value = task.time_per_attempt || 0;

                    // Заполняем множественный выбор сфер жизни
                    const domainsSelect = document.getElementById('edit-task-domains');
                    Array.from(domainsSelect.options).forEach(option => {
                        if ((task.domains ? task.domains : []).includes(option.value)) {
                            option.selected = true;
                        }
                    });
                    // Заполняем цвет
                }
            });
        });
    }

    // Инициализация обработчиков событий
    attachEventListeners();

// Сохранение изменений дела
    const editTaskForm = document.getElementById('edit-task-form');
    if(editTaskForm){
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
                    status_id: formData.get('status'),
                    color_id: formData.get('color'),
                    domains: Array.from(formData.getAll('domains[]')),
                    project_id: formData.get('projectId') || null, // Если проект не выбран, отправляем null
                    targetAttempts: parseInt(formData.get('targetAttempts'), 10),
                    timePerAttempt: parseInt(formData.get('timePerAttempt'), 10),
                }),
            });
            if (response.ok) {
                const updatedTask = await response.json();
                const taskElement = document.querySelector(`.list-group-item[data-id="${updatedTask.task.id}"]`);
                taskElement.querySelector('.task-name').textContent = updatedTask.task.name;
                updateProgressBar(taskElement, updatedTask.task.progress_percent);
                bootstrap.Modal.getInstance(document.getElementById('edit-task-modal')).hide();
            } else {
                alert('Ошибка при обновлении дела');
            }
        });
    }


// Редактирование проекта
    document.querySelectorAll('.edit-project-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const projectId = button.closest('.project-item').dataset.id;
            const response = await fetch(`/api/projects/${projectId}`);
            if (response.ok) {
                const project = await response.json();
                document.getElementById('project-id').value = project.id;
                document.getElementById('edit-project-name').value = project.name;
                if(project.level_id) document.getElementById('edit-project-level').value = project.level_id;

                // Заполняем множественный выбор сфер жизни
                const domainsSelect = document.getElementById('edit-project-domains');
                Array.from(domainsSelect.options).forEach(option => {
                    if ((project.domains ? project.domains : []).includes(option.value)) {
                        option.selected = true;
                    }
                });

                // Заполняем размер
                if(project.size_id) document.getElementById('edit-project-size').value = project.size_id;// Заполняем размер

                if(project.status_id) document.getElementById('edit-project-status').value = project.status_id;
                if(project.color_id) document.getElementById('edit-project-color').value = project.color_id;

                // Заполняем часы
                document.getElementById('edit-project-hours').value = project.hours || 0;
            }
        });
    });

// Сохранение изменений проекта
    const editProjectForm = document.getElementById('edit-project-form');
    if(editProjectForm){
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
                    level_id: formData.get('level'),
                    domains: Array.from(formData.getAll('domains[]')),
                    size_id: formData.get('size'),
                    status_id: formData.get('status'),
                    color_id: formData.get('color'),
                    hours: parseInt(formData.get('hours'), 10),
                }),
            });
            if (response.ok) {
                const data = await response.json();
                const updatedProject = data.project;
                const projectElement = document.querySelector(`.project-item[data-id="${updatedProject.id}"]`);
                projectElement.querySelector('.project-name').textContent = updatedProject.name;
                updateProgressBar(projectElement, updatedProject.progress_percent);
                updateUI(data.stats);
                updateProjectUI(data.project); // Обновляем интерфейс проекта
                bootstrap.Modal.getInstance(document.getElementById('edit-project-modal')).hide();
            } else {
                alert('Ошибка при обновлении проекта');
            }
        });
    }


    // Функция для обновления интерфейса
    function updateUI(stats) {
        const { total_stats, domain_stats, today_stats, today_domain_stats } = stats;

        // Обновляем общую статистику
        const totalProgressContainer = document.querySelector('.total-progress');
        if (totalProgressContainer) {
            totalProgressContainer.innerHTML = ''; // Очищаем контейнер
            for (let i = 1; i <= 100; i++) {
                const square = document.createElement('div');
                square.classList.add('square');
                square.style.backgroundColor = i <= total_stats.progress_percent ? 'green' : '#e0e0e0';
                totalProgressContainer.appendChild(square);
            }
        }

        const totalProgressText = document.querySelector('.total-progress-text');
        if (totalProgressText) {
            totalProgressText.textContent = `Выполнено: ${total_stats.progress_percent}%`;
        }

        // Обновляем статистику по сферам
        const domainStatsContainer = document.querySelector('.domain-stats');
        if (domainStatsContainer) {
            domainStatsContainer.innerHTML = '';
            Object.entries(domain_stats).forEach(([domain, stats]) => {
                const domainStat = document.createElement('div');
                domainStat.classList.add('domain-stat');

                const title = document.createElement('strong');
                title.textContent = `${domain.charAt(0).toUpperCase() + domain.slice(1)}:`;
                domainStat.appendChild(title);

                const squaresContainer = document.createElement('div');
                squaresContainer.style.display = 'flex';
                squaresContainer.style.flexWrap = 'wrap';
                for (let i = 1; i <= stats.domain_percentage; i++) {
                    const square = document.createElement('div');
                    square.classList.add('square');
                    square.style.width = `${stats.square_size}px`;
                    square.style.height = `${stats.square_size}px`;
                    square.style.backgroundColor = i <= stats.progress_percent ? 'green' : '#e0e0e0';
                    squaresContainer.appendChild(square);
                }
                domainStat.appendChild(squaresContainer);

                const info = document.createElement('p');
                info.innerHTML = `
                Выполнено: ${stats.spent_hours}/${stats.planned_hours} ч. (${stats.progress_percent}%)<br>
                Доля в общем: ${stats.domain_percentage}%
            `;
                domainStat.appendChild(info);

                domainStatsContainer.appendChild(domainStat);
            });
        }

        // Обновляем статистику за сегодня
        const todayStatsContainer = document.querySelector('.today-stats');
        if (todayStatsContainer) {
            todayStatsContainer.innerHTML = `
            <strong>Сегодня:</strong> ${today_stats.time_today} ч. (${today_stats.progress_percent}%)
        `;
        }

        const todayDomainStatsContainer = document.querySelector('.today-domain-stats');
        if (todayDomainStatsContainer) {
            todayDomainStatsContainer.innerHTML = '';
            Object.entries(today_domain_stats).forEach(([domain, stats]) => {
                const domainStat = document.createElement('div');
                domainStat.innerHTML = `
                <strong>${domain.charAt(0).toUpperCase() + domain.slice(1)}:</strong> 
                ${stats.time_today} ч. (${stats.progress_percent}%)
            `;
                todayDomainStatsContainer.appendChild(domainStat);
            });
        }
    }

    // Функция для обновления интерфейса проекта
    function updateProjectUI(project) {
        const projectElement = document.querySelector(`.project-item[data-id="${project.id}"]`);
        if (projectElement) {
            // Обновляем название проекта
            const projectNameElement = projectElement.querySelector('.project-name');
            if (projectNameElement) {
                projectNameElement.textContent = project.name;
            }

            // Обновляем часы (факт / план)
            const hoursElement = projectElement.querySelector('.project-hours');
            if (hoursElement) {
                hoursElement.textContent = `${project.total_time_spent}/${project.hours} ч.`;
            }

            // Обновляем прогресс
            const progressBar = projectElement.querySelector('.progress-bar');
            if (progressBar) {
                const progressPercent = project.hours > 0
                    ? Math.round((project.total_time_spent / project.hours) * 100)
                    : 0;
                progressBar.style.width = `${progressPercent}%`;
            }
        }
    }

    //lists
// Добавление элемента списка
    document.querySelectorAll('.add-list-item-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const type = form.dataset.type;
            const value = formData.get('value');
            const response = await fetch('/settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'add',
                    type: type,
                    value: value,
                }),
            });
            if (response.ok) {
                location.reload(); // Перезагрузка страницы после успешного добавления
            } else {
                alert('Ошибка при добавлении элемента');
            }
        });
    });

    // Удаление элемента списка
    document.querySelectorAll('.delete-list-item').forEach(button => {
        button.addEventListener('click', async () => {
            const listItemId = button.closest('.list-item').dataset.id;
            const type = button.dataset.type;
            const response = await fetch('/settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete',
                    id: listItemId,
                }),
            });
            if (response.ok) {
                location.reload(); // Перезагрузка страницы после успешного удаления
            } else {
                alert('Ошибка при удалении элемента');
            }
        });
    });
    });