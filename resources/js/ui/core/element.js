  // js/element.js
  var UIElement = class extends HTMLElement {
    wasDisconnected = false;
    onUnmounts = [];
    constructor() {
      super();
      this.boot?.();
    }
    connectedCallback() {
      if (this.wasDisconnected) {
        this.wasDisconnected = false;
        return;
      }
      queueMicrotask(() => {
        this.mount?.();
      });
    }
    disconnectedCallback() {
      this.wasDisconnected = true;
      queueMicrotask(() => {
        if (this.wasDisconnected) {
          this.unmount?.();
          this.onUnmounts.forEach((i) => i());
          this.onUnmounts = [];
        }
        this.wasDisconnected = false;
      });
    }
    onUnmount(callback) {
      this.onUnmounts.push(callback);
    }
    mixin(func, options = {}) {
      return new func(this, options);
    }
    // @todo: this is redundant now...
    appendMixin(func, options = {}) {
      return new func(this, options);
    }
    use(func) {
      let found;
      this.mixins.forEach((mixin) => {
        if (mixin instanceof func) found = mixin;
      });
      return found;
    }
    uses(func) {
      let found;
      this.mixins.forEach((mixin) => {
        if (mixin instanceof func) found = true;
      });
      return !!found;
    }
    on(event, handler) {
      return on(this, event, handler);
    }
    root(name, attributes = {}) {
      if (name === void 0) return this.__root;
      let el = document.createElement(name);
      for (let name2 in attributes) {
        setAttribute(el, name2, attributes[name2]);
      }
      let shadow = this.attachShadow({ mode: "open" });
      el.appendChild(document.createElement("slot"));
      shadow.appendChild(el);
      this.__root = el;
      return this.__root;
    }
  };
  var UIControl = class extends UIElement {
    //
  };

