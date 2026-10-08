import minios from './minios';

if (typeof window !== 'undefined' && window.Alpine) {
    window.Alpine.data('minios', minios);
}

export { minios };
export default minios;
