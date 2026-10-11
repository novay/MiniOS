  // js/checkbox.js
  var UICheckboxGroup = class _UICheckboxGroup extends UIControl {
    boot() {
      this._disableable = new Disableable(this);
      let undoDisableds = [];
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          this.walker().each((el) => {
            if (!el.hasAttribute("disabled")) {
              el.setAttribute("disabled", "");
              undoDisableds.push(() => el.removeAttribute("disabled"));
            }
          });
        } else {
          undoDisableds.forEach((fn) => fn());
          undoDisableds = [];
        }
      });
      this._selectable = new SelectableGroup(this, { multiple: true });
      this._controllable = new Controllable(this, { disabled: this._disabled, bubbles: true });
      this.walker().each((el) => {
        el.addEventListener("input", (e) => e.stopPropagation());
        el.addEventListener("change", (e) => e.stopPropagation());
      });
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.getAttribute("value"),
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
      setAttribute2(this, "role", "group");
      queueMicrotask(() => {
        this._submittable.update(this._selectable.getState());
      });
    }
    initCheckAll(checkAll) {
      let detangled = detangle();
      checkAll._selectable.onChange(detangled(() => {
        if (checkAll.indeterminate) {
          this.selectAll();
          checkAll.checked = true;
          checkAll.indeterminate = false;
        } else if (checkAll.checked) {
          this.selectAll();
          checkAll.checked = true;
          checkAll.indeterminate = false;
        } else {
          this.deselectAll();
          checkAll.checked = false;
          checkAll.indeterminate = false;
        }
      }));
      let setCheckAllIndeterminate = () => {
        if (this.allAreSelected()) {
          checkAll.indeterminate = false;
          checkAll._selectable.select();
        } else if (this.noneAreSelected()) {
          checkAll.indeterminate = false;
          checkAll._selectable.deselect();
        } else {
          checkAll.indeterminate = true;
        }
      };
      this._selectable.onChange(detangled(() => {
        setCheckAllIndeterminate();
      }));
      setCheckAllIndeterminate();
    }
    selectAll() {
      this.walker({ checkable: true }).filter((el) => !el.use(Selectable).isSelected()).map((el) => el.use(Selectable).select());
    }
    deselectAll() {
      this.walker({ checkable: true }).filter((el) => el.use(Selectable).isSelected()).map((el) => el.use(Selectable).deselect());
    }
    allAreSelected() {
      let selectableElements = this.walker({ checkable: true }).filter((el) => true);
      return selectableElements.length > 0 && this.walker({ checkable: true }).filter((el) => el.use(Selectable).isSelected()).length === selectableElements.length;
    }
    noneAreSelected() {
      return this.walker({ checkable: true }).filter((el) => el.use(Selectable).isSelected()).length === 0;
    }
    walker({ checkable } = {}) {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof _UICheckboxGroup) return reject();
        if (!(el.localName === "ui-checkbox")) return skip();
        if (checkable && (el.disabled || el.use(Selectable).ungrouped)) return skip();
      });
    }
  };
  var UICheckbox = class extends UIControl {
    boot() {
      let button = this;
      this.isIndeterminate = false;
      this._disableable = new Disableable(this);
      if (this.hasAttribute("all")) {
        this._selectable = new Selectable(button, {
          ungrouped: true,
          toggleable: true,
          value: this.hasAttribute("value") ? this.getAttribute("value") : Math.random().toString(36).substring(2, 10),
          label: this.hasAttribute("label") ? this.getAttribute("label") : null,
          selectedInitially: this.hasAttribute("checked"),
          dataAttr: "data-checked",
          ariaAttr: "aria-checked"
        });
        queueMicrotask(() => {
          this.closest("ui-checkbox-group")?.initCheckAll(this);
        });
      } else {
        this._selectable = new Selectable(button, {
          toggleable: true,
          dataAttr: "data-checked",
          ariaAttr: "aria-checked",
          value: this.hasAttribute("value") ? this.getAttribute("value") : Math.random().toString(36).substring(2, 10),
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
        this._selectable.onChange(() => {
          if (this.indeterminate) this.indeterminate = false;
        });
        this._selectable.onInitAndChange(() => {
          this._submittable.update(this._selectable.isSelected());
        });
        queueMicrotask(() => {
          this._submittable.update(this._selectable.isSelected());
        });
      }
      this._detangled = detangle();
      this._selectable.onChange(this._detangled(() => {
        this.dispatchEvent(new Event("input", { bubbles: true, cancelable: true }));
        this.dispatchEvent(new Event("change", { bubbles: true, cancelable: true }));
      }));
      setAttribute2(button, "role", "checkbox");
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
      respondToLabelClick(button);
    }
    get value() {
      return this._selectable?.getValue();
    }
    set value(val) {
      if (!this._selectable) return;
      this._selectable.value = val + "";
      if (this._submittable) {
        this._submittable.value = val + "";
      }
    }
    get checked() {
      return this._selectable.isSelected();
    }
    set checked(value3) {
      let groupDetangled = this.closest("ui-checkbox-group")?._detangled || ((i) => i);
      this._detangled(groupDetangled(() => {
        value3 ? this._selectable.select() : this._selectable.deselect();
      }))();
    }
    get indeterminate() {
      return this.isIndeterminate;
    }
    set indeterminate(value3) {
      this.isIndeterminate = !!value3;
      if (this.isIndeterminate) {
        setAttribute2(this, "data-indeterminate", "");
      } else {
        removeAttribute(this, "data-indeterminate");
      }
    }
  };
  element("checkbox-group", UICheckboxGroup);
  element("checkbox", UICheckbox);
  function respondToLabelClick(el) {
    el.closest("label")?.addEventListener("click", (e) => {
      if (!el.contains(e.target)) {
        el._selectable.press();
      }
    });
  }

