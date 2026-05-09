document.addEventListener('DOMContentLoaded', () => {

    // Bouton -
    document.querySelectorAll('.btn-decrease').forEach(btn => {
        btn.addEventListener('click', async () => {
            const url = btn.dataset.url;
            const id = btn.dataset.id;
            const response = await fetch(url, { method: 'POST' });
            const data = await response.json();

            // Met à jour la quantité
            const quantityEl = document.getElementById(`quantity-${id}`);
            if (data.quantity === 0) {
                document.getElementById(`cart-item-${id}`).remove();
            } else {
                quantityEl.textContent = data.quantity;
            }

            // Met à jour le compteur dans la nav
            const cartCount = document.getElementById('cart-count');
            if (cartCount) cartCount.textContent = data.totalQuantity;
        });
    });

    // Bouton +
    document.querySelectorAll('.btn-increase').forEach(btn => {
        btn.addEventListener('click', async () => {
            const url = btn.dataset.url;
            const id = btn.dataset.id;
            const response = await fetch(url, {
                method: 'POST',
                headers : { 'X-Requested-With': 'XMLHttpRequest'}
             });
            const data = await response.json();

            // Met à jour la quantité
            document.getElementById(`quantity-${id}`).textContent = data.quantity;

            // Met à jour le compteur dans la nav
            const cartCount = document.getElementById('cart-count');
            if (cartCount) cartCount.textContent = data.totalQuantity;
        });
    });

    // Bouton vider le panier
    const btnClear = document.querySelector('.btn-clear');
    if (btnClear) {
        btnClear.addEventListener('click', async () => {
            const url = btnClear.dataset.url;
            await fetch(url, { method: 'POST' });
            window.location.reload();
        });
    }
});