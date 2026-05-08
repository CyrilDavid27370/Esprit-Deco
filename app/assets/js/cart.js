document.addEventListener('DOMContentLoaded', () => {

     // Bouton -
    document.querySelectorAll('.btn-decrease').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            await fetch(`/cart/decrease/${id}`, { method: 'POST' });
            window.location.reload();
        });
    });

    // Bouton +
    document.querySelectorAll('.btn-increase').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            await fetch(`/cart/add/${id}`, { method: 'POST' });
            window.location.reload();
        });
    });

    // Bouton supprimer un produit
    document.querySelectorAll('.btn-remove').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            await fetch(`/cart/remove/${id}`, { method: 'POST' });
            window.location.reload();
        });
    });

    // Bouton vider le panier
    const btnClear = document.querySelector('.btn-clear');
    if (btnClear) {
        btnClear.addEventListener('click', async () => {
            await fetch('/cart/clear', { method: 'POST' });
            window.location.reload();
        });
    }
});
