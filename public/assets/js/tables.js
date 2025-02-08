document.addEventListener('DOMContentLoaded', () => {
    // Обработка редактирования ячеек
    document.querySelectorAll('.editable').forEach(cell => {
        cell.addEventListener('blur', async (e) => {
            const row = e.target.closest('tr');
            const id = row.dataset.id;
            const field = e.target.dataset.field;
            const value = e.target.textContent.trim();

            // Отправляем данные на сервер
            const response = await fetch(`/api/update/${row.tagName.toLowerCase() === 'tr' ? 'tasks' : 'projects'}/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ field, value }),
            });

            if (!response.ok) {
                alert('Ошибка при сохранении данных');
                e.target.textContent = value; // Восстанавливаем старое значение
            }
        });
    });
});