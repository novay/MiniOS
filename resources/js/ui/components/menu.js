  // js/menu.js
  var UIMenu = class _UIMenu extends UIElement {
    boot() {
      this._focusable = new FocusableGroup(this, { wrap: false, ensureTabbable: false });
      on(this, "keydown", (e) => {
        if (["ArrowDown"].includes(e.key)) {
          e.target === this ? this._focusable.focusFirst() : this._focusable.focusNext();
          e.preventDefault();
          e.stopPropagation();
        } else if (["ArrowUp"].includes(e.key)) {
          e.target === this ? this._focusable.focusFirst() : this._focusable.focusPrev();
          e.preventDefault();
          e.stopPropagation();
        }
      });
      search(this, (query) => this._focusable.focusBySearch(query));
      if (this.hasAttribute("popover")) {
        this.addEventListener("lofi-close-popovers", (e) => {
          if (this.hasAttribute("keep-open")) return;
          if (e.detail?.immediate) {
            this.hidePopover();
          } else {
            setTimeout(() => this.hidePopover(), 50);
          }
        });
      }
      if (this.parentElement.localName === "ui-dropdown") {
        let dropdown = this.parentElement;
        on(dropdown.trigger(), "keydown", (e) => {
          if (e.key === "ArrowDown") {
            this.fromArrowDown = true;
            this.showPopover();
            e.preventDefault();
            e.stopPropagation();
          } else if (dropdown.hasAttribute("data-os-sidebar-group-dropdown") && ["Enter", " "].includes(e.key)) {
            this.fromCollapsedSidebarGroupTrigger = !this.matches(":popover-open");
          }
        });
      }
      setAttribute2(this, "role", "menu");
      setAttribute2(this, "tabindex", "-1");
    }
    mount() {
      this.initializeMenuItems();
      let observer = new MutationObserver((mutations) => {
        this.initializeMenuItems();
      });
      observer.observe(this, { childList: true, subtree: true });
    }
    onPopoverShow() {
      requestAnimationFrame(() => {
        let openedViaHover = this._openedViaHover;
        this._openedViaHover = false;
        if (openedViaHover) return;
        if (this.fromArrowDown || this.fromCollapsedSidebarGroupTrigger) {
          this._focusable.focusFirst();
          this.fromArrowDown = false;
          this.fromCollapsedSidebarGroupTrigger = false;
        } else {
          this.focus();
        }
      });
    }
    onPopoverHide() {
      this._focusable.wipeTabbables();
    }
    initializeMenuItems() {
      this.walker().each((el) => {
        if (el._disableable) return;
        initializeMenuItem(el);
      });
    }
    walker() {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof _UIMenu) return reject();
        if (el instanceof UISelect) return reject();
        if (!["a", "button"].includes(el.localName)) return skip();
      });
    }
  };
  var UISubmenu = class extends UIElement {
    boot() {
    }
  };
  var UIMenuCheckbox = class extends UIElement {
    boot() {
      let button = this;
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(button, "aria-disabled", "true");
        } else {
          removeAttribute(button, "aria-disabled");
        }
      });
      assignId(button, "menu-checkbox");
      setAttribute2(button, "role", "menuitemcheckbox");
      button._focusable = new Focusable(button, { disableable: this._disableable, hover: true, tabbableAttr: "data-active" });
      button._selectable = new Selectable(button, {
        toggleable: true,
        value: this.hasAttribute("value") ? this.getAttribute("value") : button.textContent.trim(),
        label: this.hasAttribute("label") ? this.getAttribute("label") : button.textContent.trim(),
        dataAttr: "data-checked",
        ariaAttr: "aria-checked",
        selectedInitially: this.hasAttribute("checked")
      });
      this._controllable = new Controllable(this);
      this._controllable.initial((initial) => initial && button._selectable.setState(initial));
      this._controllable.getter(() => button._selectable.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this._selectable.setState(value3);
      }));
      this._selectable.onChange(detangled(() => {
        this._controllable.dispatch();
      }));
      on(button, "click", this._disableable.enabled(() => {
        if (!this.hasAttribute("keep-open")) {
          this.dispatchEvent(new CustomEvent("lofi-close-popovers", { bubbles: true }));
        }
        button._selectable.press();
      }));
      respondToKeyboardClick(button);
    }
  };
  var UIMenuRadio = class extends UIElement {
    boot() {
      let button = this;
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(button, "aria-disabled", "true");
        } else {
          removeAttribute(button, "aria-disabled");
        }
      });
      assignId(button, "menu-radio");
      setAttribute2(button, "role", "menuitemradio");
      button._focusable = new Focusable(button, { disableable: this._disableable, hover: true, tabbableAttr: "data-active" });
      button._selectable = new Selectable(button, {
        toggleable: false,
        value: this.hasAttribute("value") ? this.getAttribute("value") : button.textContent.trim(),
        label: this.hasAttribute("label") ? this.getAttribute("label") : button.textContent.trim(),
        dataAttr: "data-checked",
        ariaAttr: "aria-checked",
        selectedInitially: this.hasAttribute("checked")
      });
      on(button, "click", this._disableable.enabled(() => {
        if (!this.hasAttribute("keep-open")) {
          this.dispatchEvent(new CustomEvent("lofi-close-popovers", { bubbles: true }));
        }
        button._selectable.press();
      }));
      respondToKeyboardClick(button);
    }
  };
  var UIMenuRadioGroup = class extends UIElement {
    boot() {
      this._selectable = new SelectableGroup(this);
      this._controllable = new Controllable(this);
      setAttribute2(this, "role", "group");
      this._controllable.initial((initial) => initial && this._selectable.setState(initial));
      this._controllable.getter(() => this._selectable.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this._selectable.setState(value3);
      }));
      this._selectable.onChange(detangled(() => {
        this._controllable.dispatch();
      }));
      on(this, "lofi-close-popovers", (e) => {
        if (this.hasAttribute("keep-open")) {
          e.preventDefault();
          e.stopPropagation();
        }
      });
    }
  };
  var UIMenuCheckboxGroup = class extends UIElement {
    boot() {
      this._selectable = new SelectableGroup(this, { multiple: true });
      this._controllable = new Controllable(this);
      setAttribute2(this, "role", "group");
      this._controllable.initial((initial) => initial && this._selectable.setState(initial));
      this._controllable.getter(() => this._selectable.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this._selectable.setState(value3);
      }));
      this._selectable.onChange(detangled(() => {
        this._controllable.dispatch();
      }));
    }
  };
  element("menu", UIMenu);
  element("submenu", UISubmenu);
  element("menu-checkbox", UIMenuCheckbox);
  element("menu-radio", UIMenuRadio);
  element("menu-radio-group", UIMenuRadioGroup);
  element("menu-checkbox-group", UIMenuCheckboxGroup);
  function respondToKeyboardClick(el) {
    on(el, "keydown", (e) => {
      if (e.key === "Enter") {
        el.click();
        e.preventDefault();
        e.stopPropagation();
      }
    });
    on(el, "keydown", (e) => {
      if (e.key === " ") {
        e.preventDefault();
        e.stopPropagation();
      }
    });
    on(el, "keyup", (e) => {
      if (e.key === " ") {
        el.click();
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }
  function initializeMenuItem(el) {
    let link = el.querySelector("a");
    let button = el;
    let submenu = el.parentElement.matches("ui-submenu") && el.parentElement.querySelector("ui-menu[popover]");
    let target = link || button;
    el._disableable = new Disableable(el);
    el._disableable.onInitAndChange((disabled) => {
      if (disabled) {
        setAttribute2(target, "aria-disabled", "true");
      } else {
        removeAttribute(target, "aria-disabled");
      }
    });
    assignId(target, "menu-item");
    setAttribute2(target, "role", "menuitem");
    target._focusable = new Focusable(target, { disableable: el._disableable, hover: true, tabbableAttr: "data-active" });
    if (!submenu) {
      let handler = el._disableable.enabled(() => {
        if (!el.hasAttribute("keep-open")) {
          el.dispatchEvent(new CustomEvent("lofi-close-popovers", { bubbles: true, detail: { immediate: true } }));
        }
      });
      on(el, "mouseup", handler);
      on(el, "click", (e) => !e.isTrusted && handler());
      respondToKeyboardClick(button);
    } else {
      submenu._popoverable = new Popoverable(submenu, { triggers: [button] });
      submenu._anchorable = new Anchorable(submenu, {
        reference: button,
        position: submenu.hasAttribute("position") ? submenu.getAttribute("position") : isRTL(submenu) ? "left start" : "right start",
        gap: submenu.hasAttribute("gap") ? submenu.getAttribute("gap") : "-5",
        crossAxis: true
      });
      button.addEventListener("click", el._disableable.enabled((e) => {
        submenu._popoverable.setState(true);
      }));
      let { clear } = interest(button, submenu, {
        gain() {
          submenu._popoverable.setState(true);
        },
        lose() {
          submenu._popoverable.setState(false);
        },
        focusable: false,
        useSafeArea: true
      });
      submenu._popoverable.onChange(() => {
        if (!submenu._popoverable.getState()) {
          clear();
          submenu._focusable.wipeTabbables();
        }
        submenu._popoverable.getState() ? submenu._anchorable.reposition() : submenu._anchorable.cleanup();
      });
      on(button, "keydown", (e) => {
        if (e.key === "Enter") {
          submenu._popoverable.setState(true);
          setTimeout(() => submenu._focusable.focusFirst());
        }
      });
      on(button, "keydown", (e) => {
        if (e.key === "ArrowRight") {
          submenu._popoverable.setState(true);
          setTimeout(() => submenu._focusable.focusFirst());
        }
      });
      on(submenu, "keydown", (e) => {
        if (e.key === "ArrowLeft") {
          submenu._popoverable.setState(false);
          button.focus();
          e.stopPropagation();
        }
      });
    }
  }

