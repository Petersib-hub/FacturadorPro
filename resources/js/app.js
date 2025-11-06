/*import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();*/

import 'bootstrap/scss/bootstrap.scss';   // <— añade esta línea
import '../scss/theme.scss';
import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './script';

// === Autosize para textareas con data-autosize ===
function autosize(el) {
    el.style.height = 'auto';
    el.style.overflowY = 'hidden';
    el.style.height = el.scrollHeight + 'px';
    // Si el contenido es muy grande, permitimos scroll
    if (el.scrollHeight > 240) {
        el.style.overflowY = 'auto';
        el.style.maxHeight = '320px';
    }
}

function initAutosize() {
    const areas = document.querySelectorAll('textarea[data-autosize]');
    areas.forEach((ta) => {
        ta.addEventListener('input', () => autosize(ta));
        // Primer ajuste
        autosize(ta);
    });
}

document.addEventListener('DOMContentLoaded', initAutosize);
