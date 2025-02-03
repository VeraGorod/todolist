document.addEventListener('DOMContentLoaded', () => {
    // Добавление дела
    const addTaskForm = document.getElementById('add-task-form');
    addTaskForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const taskName = document.getElementById('task-name').value;

        const response = await fetch('/api/tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ name: taskName }),
        });

        if (response.ok) {
            const tasksList = document.getElementById('tasks-list');
            const newTask = await response.json();
            tasksList.innerHTML += `
                <div class="task">
                    <span class="task-name">${newTask.name}</span>
                </div>
            `;
            addTaskForm.reset(); // Очистка формы
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
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ name: projectName }),
        });

        if (response.ok) {
            const projectsList = document.getElementById('projects-list');
            const newProject = await response.json();
            projectsList.innerHTML += `
                <div class="project">
                    <span class="project-name">${newProject.name}</span>
                </div>
            `;
            addProjectForm.reset(); // Очистка формы
        } else {
            alert('Ошибка при добавлении проекта');
        }
    });
});