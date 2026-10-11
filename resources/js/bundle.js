import minios from './minios.js';
import { setupAlpineOS } from './ui/alpine/index.js';

// Auto-register with Alpine if present in global scope
if (typeof window !== 'undefined') {
    window.minios = minios;

    if (window.Alpine) {
        window.Alpine.data('minios', minios);
        setupAlpineOS(window.Alpine);
    } else {
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('minios', minios);
            setupAlpineOS(window.Alpine);
        });
    }
}

export { minios, setupAlpineOS };
export default minios;

