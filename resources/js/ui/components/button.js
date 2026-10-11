// js/button.js
var UIButton = class extends UIElement {
  boot() {
    setAttribute2(this, "role", "button");
    this._disableable = new Disableable(this);
    this._disableable.onInitAndChange((disabled) => {
      disabled ? removeAttribute(this, "tabindex", "0") : setAttribute2(this, "tabindex", "0");
    });
    this._buttonType = this.getAttribute("type") || "button";
  }
  mount() {
    on(this, "keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        this.disabled || this.click();
      }
      if (e.key === " ") {
        e.preventDefault();
      }
    });
    on(this, "keyup", (e) => {
      if (e.key === " ") {
        e.preventDefault();
        e.stopPropagation();
        this.disabled || this.click();
      }
    });
    on(this, "click", () => {
      if (this.disabled) return;
      if (this._buttonType === "submit") {
        this._handleSubmit();
      } else if (this._buttonType === "reset") {
        this._handleReset();
      }
    });
  }
  _handleSubmit() {
    let form = this.closest("form");
    if (form) {
      form.requestSubmit();
    }
  }
  _handleReset() {
    let form = this.closest("form");
    if (form) {
      form.reset();
    }
  }
};
element("button", UIButton);

// js/close.js
var UIClose = class extends UIElement {
  mount() {
    this.onUnmount(on(this, "click", (event) => {
      if (!event.target.closest("button,ui-button")) return;
      let closeable = closest(this, (el) => !!el._closeable)?._closeable;
      closeable?.close();
    }).off);
  }
};
element("close", UIClose);

