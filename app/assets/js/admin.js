import '../styles/admin.css';

document.addEventListener('turbo:load', () => {

    // Supprimer une image
    document.querySelectorAll('.btn-delete-image').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Supprimer cette image ?')) return;

            const imageId = btn.dataset.imageId;
            const csrf = btn.dataset.csrf;

            const response = await fetch(`/admin/image/delete/${imageId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-Token': csrf }
            });

            if (response.ok) {
                document.getElementById(`image-${imageId}`).remove();
            } else {
                alert('Erreur lors de la suppression.');
            }
        });
    });

    // Passer en principale
    document.querySelectorAll('.btn-set-principal').forEach(btn => {
        btn.addEventListener('click', async () => {
            const imageId = btn.dataset.imageId;
            const csrf = btn.dataset.csrf;

            const response = await fetch(`/admin/image/principal/${imageId}`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrf }
            });

            if (response.ok) {
                window.location.reload();
            } else {
                alert('Erreur lors du changement d\'image principale.');
            }
        });
    });

});
