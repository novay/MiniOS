/**
 * Merge modular slices into target object while strictly preserving
 * property descriptors (getters, setters, and methods) for Alpine.js reactivity.
 *
 * @param {object} target
 * @param  {...object} slices
 * @returns {object}
 */
export function mergeSlices(target, ...slices) {
    for (const slice of slices) {
        if (slice) {
            Object.defineProperties(target, Object.getOwnPropertyDescriptors(slice));
        }
    }
    return target;
}
