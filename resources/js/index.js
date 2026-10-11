import minios from './minios';
import { setupAlpineOS } from './ui/alpine/index.js';

if (typeof window !== 'undefined' && window.Alpine) {
    window.Alpine.data('minios', minios);
    setupAlpineOS(window.Alpine);
}

export { minios, setupAlpineOS };
export default minios;

