import minios from './minios.js';

// Auto-register with Alpine if present in global scope
if (typeof window !== 'undefined') {
    window.minios = minios;

    if (window.Alpine) {
        window.Alpine.data('minios', minios);
    } else {
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('minios', minios);
        });
    }
}

export default minios;
