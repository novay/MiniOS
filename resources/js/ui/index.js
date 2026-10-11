/**
 * MiniOS UI Engine
 * Modular Web Components & Alpine.js Bindings
 */

// Polyfills
import './polyfills/popover.js';

// Core Engine
import './core/utils.js';
import './core/element.js';
import './core/mixins.js';

// Components
import './components/modal.js';
import './components/select.js';
import './components/calendar.js';
import './components/time-picker.js';
import './components/disclosure.js';
import './components/resizable.js';
import './components/checkbox.js';
import './components/dropdown.js';
import './components/file-upload.js';
import './components/composer.js';
import './components/progress.js';
import './components/context.js';
import './components/menu.js';
import './components/toolbar.js';
import './components/tooltip.js';
import './components/sidebar.js';
import './components/slider.js';
import './components/carousel.js';
import './components/color-picker.js';
import './components/switch.js';
import './components/field.js';
import './components/chart.js';
import './components/button.js';
import './components/toast.js';
import './components/radio.js';
import './components/table.js';
import './components/tabs.js';
import './components/otp.js';

// Alpine.js Directives & Component Handlers
import './alpine/index.js';

// Polyfill auto-apply check
if (typeof isSupported2 === 'function' && typeof isPolyfilled === 'function') {
  if (!isSupported2() && !isPolyfilled()) {
    apply2();
  }
}
