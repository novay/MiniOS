  // js/disclosure.js
  var UIDisclosure = class extends UIElement {
    boot() {
      let button = this.button();
      let details = this.details();
      if (!button) {
        return console.warn("ui-disclosure: no trigger element found", this);
      }
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          if (!button.hasAttribute("disabled")) {
            setAttribute2(button, "disabled", "");
          }
        } else {
          if (button.hasAttribute("disabled")) {
            removeAttribute(button, "disabled");
          }
        }
      });
      if (!details) return;
      this._controllable = new Controllable(this, { disabled: this.disabled });
      details._disclosable = new Disclosable(details);
      this._controllable.initial((initial) => initial && details._disclosable.setState(true));
      this._controllable.getter(() => details._disclosable.getState());
      this._controllable.setter((value3) => details._disclosable.setState(value3));
      details._disclosable.onChange(() => {
        this.dispatchEvent(new CustomEvent("lofi-disclosable-change", { bubbles: true }));
        this._controllable.dispatch();
      });
      let transitions = details.getAttributeNames().some((name) => {
        return name === "x-collapse" || name.startsWith("x-collapse.");
      });
      let refresh = (initial = false) => {
        if (details._disclosable.getState()) {
          setAttribute2(this, "data-open", "");
          setAttribute2(button, "data-open", "");
          setAttribute2(details, "data-open", "");
          removeAttribute(details, "hidden");
        } else {
          removeAttribute(this, "data-open");
          removeAttribute(button, "data-open");
          removeAttribute(details, "data-open");
          if (!transitions || initial) setAttribute2(details, "hidden", "until-found");
        }
      };
      details._disclosable.onChange(() => refresh());
      refresh(true);
      if (transitions) {
        on(details, "transitionend", (e) => {
          if (e.target !== details || e.propertyName !== "height") return;
          if (details._disclosable.getState()) return;
          setAttribute2(details, "hidden", "until-found");
        });
      }
      on(details, "beforematch", () => {
        details._disclosable.setState(true);
      });
      on(button, "click", (e) => {
        if (!this.disabled) {
          details._disclosable.setState(!details._disclosable.getState());
        }
      });
      let id = assignId(details, "disclosure");
      setAttribute2(button, "aria-controls", id);
      setAttribute2(button, "aria-expanded", "false");
      details._disclosable.onChange(() => {
        details._disclosable.getState() ? setAttribute2(button, "aria-expanded", "true") : setAttribute2(button, "aria-expanded", "false");
      });
      if (this.hasAttribute("open")) {
        details._disclosable.setState(true);
      }
    }
    button() {
      return this.querySelector("button,ui-button");
    }
    details() {
      let details = this.lastElementChild;
      return details === this.button() ? null : details;
    }
  };
  var UIDisclosureGroup = class _UIDisclosureGroup extends UIElement {
    boot() {
      this.exclusive = this.hasAttribute("exclusive");
      if (this.exclusive) {
        on(this, "lofi-disclosable-change", (e) => {
          e.stopPropagation();
          if (e.target.localName === "ui-disclosure" && e.target.value) {
            this.disclosureWalker().each((el) => {
              if (el === e.target) return;
              el.value = false;
            });
          }
        });
      }
    }
    disclosureWalker() {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof _UIDisclosureGroup && el !== this) return reject();
        if (el.localName !== "ui-disclosure") return reject();
      });
    }
  };
  element("disclosure", UIDisclosure);
  element("disclosure-group", UIDisclosureGroup);

