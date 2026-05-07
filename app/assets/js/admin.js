import '../styles/admin.css';

document.addEventListener('DOMContentLoaded', () => {

    // Supprimer une image
    document.querySelectorAll('.btn-delete-image').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Supprimer cette image ?')) return;

            const imageId = btn.dataset.imageId;
            const csrf = btn.dataset.csrf;

            const response = await fetch(`/admin/image/${imageId}/delete`, {
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

            const response = await fetch(`/admin/image/${imageId}/principal`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrf }
            });

            if (response.ok) {
                // Retire les styles principal de toutes les images
                document.querySelectorAll('.image-card').forEach(card => {
                    card.classList.remove('image-principal');
                    const badge = card.querySelector('.badge');
                    if (badge) badge.remove();
                    // Remet le bouton étoile si pas déjà là
                    const actions = card.querySelector('.image-actions');
                    if (!actions.querySelector('.btn-set-principal')) {
                        const newId = card.id.replace('image-', '');
                        // on recharge la page pour simplifier
                    }
                });

                // Recharge la page pour mettre à jour l'affichage
                window.location.reload();
            } else {
                alert('Erreur lors du changement d\'image principale.');
            }
        });
    });

});
