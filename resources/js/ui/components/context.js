  // js/context.js
  var UIContext = class extends UIElement {
    boot() {
      let trigger = this.trigger();
      let overlay = this.overlay();
      this._disabled = this.hasAttribute("disabled");
      this._popoverable = new Popoverable(overlay);
      let virtualReference = {
        getBoundingClientRect: () => ({ x: 0, y: 0, width: 0, height: 0, top: 0, left: 0, right: 0, bottom: 0 })
      };
      this._anchorable = new Anchorable(overlay, {
        reference: virtualReference,
        auto: false,
        position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
        gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
        offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
      });
      let { lock, unlock } = lockScroll(this._popoverable.el);
      this._popoverable.onChange(() => {
        this._popoverable.getState() ? lock() : unlock();
      });
      on(trigger, "contextmenu", (e) => {
        e.preventDefault();
        virtualReference.getBoundingClientRect = () => ({
          x: e.clientX,
          y: e.clientY,
          width: 0,
          height: 0,
          top: e.clientY,
          left: e.clientX,
          right: e.clientX,
          bottom: e.clientY
        });
        this._anchorable.reposition();
        this._popoverable.setState(true);
        if (this.hasAttribute("detail")) {
          setAttribute2(overlay, "data-detail", this.getAttribute("detail"));
        }
      });
      if (this.hasAttribute("detail")) {
        setAttribute2(overlay, "data-detail", "");
      }
    }
    unmount() {
      if (this.overlay()?._popoverable?.getState()) {
        let { unlock } = lockScroll();
        unlock();
      }
    }
    trigger() {
      return this.firstElementChild;
    }
    overlay() {
      if (this.hasAttribute("target")) {
        let target = this.getAttribute("target");
        return document.getElementById(target);
      } else {
        return this.lastElementChild.matches("[popover]") && this.lastElementChild;
      }
    }
  };
  element("context", UIContext);

