  // js/switch.js
  var UISwitch = class extends UIControl {
    boot() {
      let button = this;
      this._disableable = new Disableable(this);
      this._selectable = new Selectable(button, {
        toggleable: true,
        dataAttr: "data-checked",
        ariaAttr: "aria-checked",
        value: this.hasAttribute("value") ? this.getAttribute("value") : null,
        label: this.hasAttribute("label") ? this.getAttribute("label") : null,
        selectedInitially: this.hasAttribute("checked")
      });
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.getAttribute("value") ?? "on",
        // Default value for checkboxes...
        includeWhenEmpty: false,
        shouldUpdateValue: false
      });
      this.value = this._selectable.getValue();
      this._detangled = detangle();
      this._selectable.onChange(this._detangled(() => {
        this.dispatchEvent(new Event("input", { bubbles: false, cancelable: true }));
        this.dispatchEvent(new Event("change", { bubbles: false, cancelable: true }));
      }));
      setAttribute2(button, "role", "switch");
      this._selectable.onInitAndChange(() => {
        this._submittable.update(this._selectable.getState());
      });
      this._disableable.onInitAndChange((disabled) => {
        disabled ? removeAttribute(button, "tabindex", "0") : setAttribute2(button, "tabindex", "0");
      });
      on(button, "click", this._disableable.disabled((e) => {
        e.preventDefault();
        e.stopPropagation();
      }), { capture: true });
      on(button, "click", this._disableable.enabled((e) => {
        this._selectable.press();
      }));
      on(button, "keydown", this._disableable.enabled((e) => {
        if (e.key === "Enter") {
          this._selectable.press();
          e.preventDefault();
          e.stopPropagation();
        }
      }));
      on(button, "keydown", this._disableable.enabled((e) => {
        if (e.key === " ") {
          e.preventDefault();
          e.stopPropagation();
        }
      }));
      on(button, "keyup", this._disableable.enabled((e) => {
        if (e.key === " ") {
          this._selectable.press();
          e.preventDefault();
          e.stopPropagation();
        }
      }));
      respondToLabelClick2(button);
    }
    get checked() {
      return this._selectable.isSelected();
    }
    set checked(value3) {
      this._detangled(() => {
        value3 ? this._selectable.select() : this._selectable.deselect();
      })();
    }
  };
  function respondToLabelClick2(el) {
    el.closest("label")?.addEventListener("click", (e) => {
      if (!el.contains(e.target)) {
        el.click();
      }
    });
  }
  element("switch", UISwitch);

