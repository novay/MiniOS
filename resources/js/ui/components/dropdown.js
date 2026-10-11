  // js/dropdown.js
  var UIDropdown = class extends UIElement {
    boot() {
      let trigger = this.trigger();
      let overlay = this.overlay();
      let isTooltip = this.hasAttribute("data-os-tooltip") || this.hasAttribute("data-os-tooltip");
      if (!trigger) {
        return console.warn("ui-dropdown: no trigger element found", this);
      } else if (!overlay) {
        return console.warn("ui-dropdown: no [popover] overlay found", this);
      }
      this._disabled = this.hasAttribute("disabled");
      this._controllable = new Controllable(this);
      overlay._popoverable = new Popoverable(overlay);
      overlay._anchorable = new Anchorable(overlay, {
        reference: trigger,
        position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
        gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
        offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0,
        onReferenceHidden: isTooltip ? () => overlay._popoverable.hide() : null
      });
      overlay._popoverable.onChange(() => {
        overlay._popoverable.getState() ? overlay._anchorable.reposition() : overlay._anchorable.cleanup();
      });
      this._locksScroll = !this.hasAttribute("hover") && !isTooltip;
      if (this._locksScroll) {
        let { lock, unlock } = lockScroll(overlay._popoverable.el);
        overlay._popoverable.onChange(() => {
          overlay._popoverable.getState() ? lock() : unlock();
        });
      }
      this._controllable.initial((initial) => overlay._popoverable.setState(initial));
      this._controllable.getter(() => overlay._popoverable.getState());
      let detangled = detangle();
      this._controllable.setter((value3) => overlay._popoverable.setState(value3));
      overlay._popoverable.onChange(detangled(() => this._controllable.dispatch()));
      if (this.hasAttribute("hover")) {
        let off = () => {
        };
        interest(trigger, overlay, {
          gain() {
            overlay._openedViaHover = true;
            overlay._popoverable.setState(true);
            let listener = on(document, "scroll", () => {
              if (overlay._popoverable.getState()) {
                overlay._popoverable.setState(false);
                off();
              }
            });
            off = listener.off;
          },
          lose() {
            overlay._popoverable.setState(false);
            off();
          },
          focusable: false
        });
      }
      on(trigger, "click", () => overlay._popoverable.toggle());
      closeNavmenuOverlayBeforeNavigation(overlay);
      if (overlay._popoverable.getState()) {
        setAttribute2(this, "data-open", "");
        setAttribute2(trigger, "data-open", "");
        setAttribute2(overlay, "data-open", "");
      } else {
        removeAttribute(this, "data-open");
        removeAttribute(trigger, "data-open");
        removeAttribute(overlay, "data-open");
      }
      overlay._popoverable.onChange(() => {
        if (overlay._popoverable.getState()) {
          setAttribute2(this, "data-open", "");
          setAttribute2(trigger, "data-open", "");
          setAttribute2(overlay, "data-open", "");
        } else {
          removeAttribute(this, "data-open");
          removeAttribute(trigger, "data-open");
          removeAttribute(overlay, "data-open");
        }
      });
      let id = assignId(overlay, "dropdown");
      setAttribute2(trigger, "aria-haspopup", "true");
      setAttribute2(trigger, "aria-controls", id);
      setAttribute2(trigger, "aria-expanded", overlay._popoverable.getState() ? "true" : "false");
      overlay._popoverable.onChange(() => {
        setAttribute2(trigger, "aria-expanded", overlay._popoverable.getState() ? "true" : "false");
      });
      overlay._popoverable.onChange(() => {
        setTimeout(
          () => overlay._popoverable.getState() ? overlay.onPopoverShow?.() : overlay.onPopoverHide?.()
        );
      });
    }
    unmount() {
      if (this.overlay()?._popoverable?.getState() && this._locksScroll) {
        let { unlock } = lockScroll();
        unlock();
      }
    }
    trigger() {
      return this.querySelector("button,ui-button,a");
    }
    overlay() {
      return this.lastElementChild?.matches("[popover]") && this.lastElementChild;
    }
  };
  element("dropdown", UIDropdown);
  function closeNavmenuOverlayBeforeNavigation(overlay) {
    if (!overlay.matches("[data-os-navmenu]")) return;
    let close = (target) => {
      if (!target.closest?.("[data-os-navmenu-item]")) return;
      overlay._popoverable.hide();
    };
    on(overlay, "mouseup", (e) => close(e.target));
    on(overlay, "click", (e) => !e.isTrusted && close(e.target));
  }

