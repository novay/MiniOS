  // js/modal.js
  var UIModal = class extends UIElement {
    boot() {
      this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
      this._controllable = new Controllable(this, { disabled: this.hasAttribute("disabled") });
      let button = this.button();
      let dialog = this.dialog();
      if (!dialog) return;
      dialog._dialogable = new Dialogable(dialog, {
        clickOutside: !this.hasAttribute("disable-click-outside"),
        escape: !this.hasAttribute("disable-escape")
      });
      dialog._closeable = new Closeable(dialog);
      dialog._closeable.onClose(() => dialog._dialogable.hide());
      this._controllable.initial((initial) => initial && dialog._dialogable.show());
      this._controllable.getter(() => dialog._dialogable.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        dialog._dialogable.setState(value3);
      }));
      dialog._dialogable.onChange(detangled(() => {
        this._controllable.dispatch();
      }));
      let refresh = () => {
        if (dialog._dialogable.getState()) {
          setAttribute2(this, "data-open", "");
          button?.setAttribute("data-open", "");
          setAttribute2(dialog, "data-open", "");
        } else {
          removeAttribute(this, "data-open");
          button?.removeAttribute("data-open");
          removeAttribute(dialog, "data-open");
        }
      };
      dialog._dialogable.onChange(() => refresh());
      refresh();
      let { lock, unlock } = lockScroll();
      dialog._dialogable.onChange(() => {
        dialog._dialogable.getState() ? lock() : unlock();
      });
      button && on(button, "click", (e) => {
        dialog._dialogable.show();
      });
    }
    unmount() {
      if (this.dialog()?._dialogable?.getState()) {
        let { unlock } = lockScroll();
        unlock();
      }
    }
    button() {
      let button = this.querySelector("button,ui-button");
      let dialog = this.dialog();
      if (dialog?.contains(button)) return;
      return button;
    }
    dialog() {
      return this.querySelector("dialog");
    }
    showModal() {
      let dialog = this.dialog();
      if (!dialog) return;
      dialog.showModal();
    }
  };
  inject(({ css }) => css`dialog, ::backdrop { margin: auto; }`);
  element("modal", UIModal);

