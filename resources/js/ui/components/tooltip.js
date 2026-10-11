  // js/tooltip.js
  var UITooltip = class extends UIElement {
    boot() {
      let type = this.hasAttribute("label") ? "label" : "description";
      let button = this.button();
      let overlay = this.overlay();
      if (!button) {
        return console.warn("ui-tooltip: no trigger element found", this);
      } else if (!overlay) {
        return;
      }
      overlay._popoverable = new Popoverable(overlay, { scope: "tooltip" });
      overlay._anchorable = new Anchorable(overlay, {
        reference: button,
        position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
        gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
        offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
      });
      overlay._popoverable.onChange(() => {
        overlay._popoverable.getState() ? overlay._anchorable.reposition() : overlay._anchorable.cleanup();
      });
      this._disableable = new Disableable(this);
      let observer = new MutationObserver(() => {
        if (this.getAttribute("draggable") === "true") {
          overlay._popoverable.setState(false);
        }
      });
      observer.observe(this, { attributeFilter: ["draggable"] });
      let id = assignId(overlay, "tooltip");
      let interactive = this.hasAttribute("interactive");
      let wantsLabel = this.hasAttribute("label") || button.textContent.trim() === "";
      if (interactive) {
        setAttribute2(button, "aria-controls", id);
        setAttribute2(button, "aria-expanded", "false");
        overlay._popoverable.onChange(() => {
          overlay._popoverable.getState() ? setAttribute2(button, "aria-expanded", "true") : setAttribute2(button, "aria-expanded", "false");
        });
      } else {
        if (wantsLabel) setAttribute2(button, "aria-labelledby", id);
        else setAttribute2(button, "aria-describedby", id);
        setAttribute2(overlay, "aria-hidden", "true");
      }
      setAttribute2(overlay, "role", "tooltip");
    }
    // interest() registers a "focusin" listener on document. boot() runs on every
    // construction, including Livewire's cloneNode() and innerHTML parses that never
    // get connected, so registering there leaks a listener (and the whole subtree)
    // per clone. Only connected tooltips should touch document...
    mount() {
      if (!this._disableable) return;
      let overlay = this.overlay();
      let removeInterest;
      this._disableable.onInitAndChange((disabled) => {
        if (removeInterest) {
          removeInterest();
          removeInterest = null;
        }
        if (!disabled) {
          let result = interest(this, overlay, {
            gain() {
              overlay._popoverable.setState(true);
            },
            lose() {
              overlay._popoverable.setState(false);
            },
            focusable: true,
            useSafeArea: false
          });
          removeInterest = result.remove;
        }
      });
      this.onUnmount(() => {
        if (removeInterest) {
          removeInterest();
          removeInterest = null;
        }
      });
    }
    button() {
      return this.firstElementChild;
    }
    overlay() {
      return this.lastElementChild !== this.button() && this.lastElementChild.tagName !== "TEMPLATE" && this.lastElementChild;
    }
  };
  element("tooltip", UITooltip);

