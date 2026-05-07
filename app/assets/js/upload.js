let imageCount = 1;

document.addEventListener('DOMContentLoaded', () => {
    const addBtn = document.getElementById('add-image-btn');
    const container = document.getElementById('upload-container');
    const template = document.getElementById('upload-template');

    if (!addBtn || !container || !template) return;

    addBtn.addEventListener('click', () => {
        const newField = template.contentEditable.cloneNode(true);

        newField.querySelector('.btn-remove-upload').addEventListener('click', (e) => {
            e.target.closest('.upload-field').remove();
        });

        container.appendChild(newField)
    });
});
