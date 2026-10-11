  // js/field.js
  var UIField = class _UIField extends UIElement {
    mount() {
      this.control = this.fieldWalker().find((el) => this.isControl(el));
      if (!this.control) return;
    }
    associateLabelWithControl(control) {
      if (!control) return;
      if (!this.label) return;
      this.control = control;
      if (this.control.hasAttribute("aria-labelledby")) return;
      setAttribute2(this.elOrButton(this.control), "aria-labelledby", this.label.id);
      if (this.control && !(this.control instanceof UIControl) && this.hasAttribute("disabled")) {
        this.control.setAttribute("disabled", "");
      }
    }
    associateDescriptionWithControl(control) {
      if (!control) return;
      if (!this.description) return;
      this.control = control;
      if (this.control.hasAttribute("aria-describedby")) return;
      setAttribute2(this.elOrButton(this.control), "aria-describedby", this.description.id);
    }
    associateLabel(label) {
      this.label = label;
      on(label, "click", (e) => {
        if (["a", "button", "ui-button"].includes(e.target.localName)) return;
        this.focusOrTogggle(this.control);
      });
      this.control && this.associateLabelWithControl(this.control);
    }
    associateDescription(description) {
      this.description = description;
      this.control && this.associateDescriptionWithControl(this.control);
    }
    fieldWalker() {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof _UIField && el !== this) return reject();
        if (el.parentElement.localName === "ui-editor" && el !== this) return reject();
      });
    }
    isControl(el) {
      if (el instanceof UIControl) return true;
      if (el.matches("input, textarea, select")) return true;
      return false;
    }
    focusOrTogggle(control) {
      if (!control) return;
      if (control.disabled || control.hasAttribute("disabled")) return;
      let isCheckable = control.localName === "input" && ["checkbox", "radio"].includes(control.type) || ["ui-switch", "ui-radio", "ui-checkbox"].includes(control.localName);
      if (isCheckable) {
        control.click();
        control.focus();
      } else if (control.localName === "input" && ["file"].includes(control.type)) {
        control.click();
      } else if (["ui-select", "ui-date-picker", "ui-time-picker", "ui-otp", "ui-color-picker"].includes(control.localName)) {
        control.trigger()?.focus();
      } else if (["ui-editor"].includes(control.localName)) {
        control.focus();
      } else if (["ui-composer"].includes(control.localName)) {
        control.focusInput();
      } else {
        control.focus();
      }
    }
    elOrButton(el) {
      if (el instanceof UIElement && el.firstElementChild instanceof HTMLButtonElement) return el.firstElementChild;
      return el;
    }
  };
  var UILabel = class extends UIElement {
    mount() {
      assignId(this, "label");
      setAttribute2(this, "aria-hidden", "true");
      this.closest("ui-field")?.associateLabel(this);
    }
  };
  var UIDescription = class extends UIElement {
    mount() {
      assignId(this, "description");
      setAttribute2(this, "aria-hidden", "true");
      this.closest("ui-field")?.associateDescription(this);
    }
  };
  element("field", UIField);
  element("label", UILabel);
  element("description", UIDescription);

  // js/legend.js
  var UILegend = class extends UIElement {
    mount() {
      let id = assignId(this, "legend");
      let fieldset = this.closest("fieldset");
      if (fieldset) {
        setAttribute2(fieldset, "aria-labelledby", id);
      }
    }
  };
  element("legend", UILegend);

