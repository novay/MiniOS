  // js/radio.js
  var UIRadioGroup = class _UIRadioGroup extends UIControl {
    boot() {
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        this.walker().each((el) => {
          el.disabled = disabled;
        });
      });
      this._selectable = new SelectableGroup(this);
      this._controllable = new Controllable(this, { disabled: this._disabled, bubbles: true });
      this._focusable = new FocusableGroup(this, { wrap: true });
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this._selectable.getState(),
        includeWhenEmpty: false
      });
      this._controllable.initial((initial) => initial && this._selectable.setState(initial));
      this._controllable.getter(() => this._selectable.getState());
      this._detangled = detangle();
      this._controllable.setter(this._detangled((value3) => {
        this._selectable.setState(value3);
      }));
      this._selectable.onChange(this._detangled(() => {
        this._controllable.dispatch();
      }));
      this._selectable.onInitAndChange(() => {
        this._submittable.update(this._selectable.getState());
      });
      on(this, "keydown", (e) => {
        if (["ArrowDown", "ArrowRight"].includes(e.key)) {
          this._focusable.focusNext();
          e.preventDefault();
          e.stopPropagation();
        } else if (["ArrowUp", "ArrowLeft"].includes(e.key)) {
          this._focusable.focusPrev();
          e.preventDefault();
          e.stopPropagation();
        }
      });
      setAttribute2(this, "role", "radiogroup");
      queueMicrotask(() => {
        this._submittable.update(this._selectable.getState());
      });
    }
    walker() {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof _UIRadioGroup) return reject();
        if (!(el.localName === "ui-radio")) return skip();
      });
    }
  };
  var UIRadio = class extends UIControl {
    boot() {
      let button = this;
      this._disableable = new Disableable(this);
      this._selectable = new Selectable(button, {
        value: this.hasAttribute("value") ? this.getAttribute("value") : Math.random().toString(36).substring(2, 10),
        label: this.hasAttribute("label") ? this.getAttribute("label") : null,
        selectedInitially: this.hasAttribute("checked"),
        dataAttr: "data-checked",
        ariaAttr: "aria-checked"
      });
      this._selectable.onChange(() => {
        if (this._selectable.isSelected()) this._focusable.focus(false);
      });
      this._disableable.onChange((disabled) => {
        if (disabled) {
          this._focusable.untabbable();
        } else {
          this._selectable.isSelected() && this._focusable.tabbable();
        }
      });
      setAttribute2(button, "role", "radio");
      this._focusable = new Focusable(button, { disableable: this._disableable, tabbableAttr: "data-active" });
      on(button, "click", this._disableable.disabled((e) => {
        e.preventDefault();
        e.stopPropagation();
      }), { capture: true });
      on(button, "click", this._disableable.enabled((e) => {
        this._selectable.press();
      }));
      on(button, "keydown", this._disableable.enabled((e) => {
        if (e.key === "Enter") {
          this.closest("form")?.requestSubmit();
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
      respondToLabelClick3(button);
      on(button, "focus", (e) => {
        isUsingKeyboard() && this._selectable.select();
      });
    }
    get value() {
      return this._selectable?.getValue();
    }
    set value(val) {
      if (!this._selectable) return;
      this._selectable.value = val + "";
    }
    get checked() {
      return this._selectable.isSelected();
    }
    set checked(value3) {
      let detangled = this.closest("ui-radio-group")?._detangled || (() => {
      });
      detangled(() => {
        value3 && this._selectable.select();
      })();
    }
  };
  function respondToLabelClick3(el) {
    el.closest("label")?.addEventListener("click", (e) => {
      if (!el.contains(e.target)) {
        el.click();
      }
    });
  }
  element("radio-group", UIRadioGroup);
  element("radio", UIRadio);

