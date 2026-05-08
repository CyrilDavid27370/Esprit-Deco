import '@hotwired/turbo';

import './stimulus_bootstrap.js';
import './js/bootstrap.js';
import './js/app.general.js';
import './js/header.js';
import './js/nav.js';
import './js/footer.js';

// Désactive Turbo Drive globalement
document.addEventListener('turbo:load', () => {});
window.Turbo?.session && (window.Turbo.session.drive = false);

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
