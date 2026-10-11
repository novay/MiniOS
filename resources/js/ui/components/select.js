  // js/select.js
  var UISelect = class extends UIControl {
    boot() {
      let list = this.list();
      this._controllable = new Controllable(this, { bubbles: true });
      this._selectable = new SelectableGroup(list, {
        multiple: this.hasAttribute("multiple")
      });
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this._selectable.getState()
      });
      this._controllable.initial((initial) => initial && this._selectable.setState(initial));
      this._controllable.getter(() => this._selectable.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this._selectable.setState(value3);
      }));
      this._selectable.onChange(detangled(() => {
        this._controllable.dispatch();
        this.dispatchEvent(new CustomEvent("select", { bubbles: false }));
      }));
      this._selectable.onInitAndChange(() => {
        this._submittable.update(this._selectable.getState());
      });
    }
    mount() {
      this._disableable = new Disableable(this);
      queueMicrotask(() => {
        this._submittable.update(this._selectable.getState());
      });
      let input = this.input();
      let button = this.button();
      let list = this.list();
      let multiple = this.hasAttribute("multiple");
      let autocomplete = this.hasAttribute("autocomplete");
      let strictAutocomplete = this.hasAttribute("autocomplete") && this.getAttribute("autocomplete").trim().split(" ").includes("strict");
      let listbox = this.querySelector("ui-options") || this;
      let listId = initListbox(listbox, "options", multiple);
      this._activatable = new ActivatableGroup(listbox, { filter: "data-hidden" });
      if (!input && !button) {
        this._disableable.onInitAndChange((disabled) => {
          disabled ? this.removeAttribute("tabindex") : this.setAttribute("tabindex", "0");
        });
      }
      if (this.hasAttribute("filter") && this.getAttribute("filter") !== "manual") {
        this._filterable = new FilterableGroup(list);
        this._filterable.onChange(() => {
          this._activatable.clearActive();
          if (this._filterable.hasResults()) {
            this._activatable.activateFirst();
          }
        });
        this.addEventListener("open", () => {
          if (this._filterable) {
            this._filterable.filter("");
          }
        });
        this.addEventListener("close", () => {
          if (this._filterable) {
            requestAnimationFrame(() => {
              this._filterable.filter("");
            });
          }
        });
      }
      let popoverEl = this.querySelector("[popover]:not(ui-tooltip > [popover])");
      let popoverInputEl = popoverEl?.querySelector('input:not([type="hidden"])');
      let inputEl = this.querySelector('input:not([type="hidden"])');
      inputEl = popoverEl?.contains(inputEl) ? null : inputEl;
      let buttonEl = this.querySelector("button,ui-button");
      buttonEl = popoverEl?.contains(buttonEl) ? null : buttonEl;
      if (!(popoverEl || inputEl)) {
        handleKeyboardNavigation(this, this._activatable);
        handleKeyboardSelection(this, this, this._activatable);
        handleActivationOnFocus(this, this._activatable, this._selectable);
      } else if (!popoverEl && inputEl) {
        let input2 = inputEl;
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            input2 && setAttribute2(input2, "disabled", "");
          } else {
            input2 && removeAttribute(input2, "disabled");
          }
        });
        handleInputClearing(this, input2, this._selectable, this._popoverable);
        handleActivationOnFocus(input2, this._activatable, this._selectable);
        handleAutocomplete(autocomplete, strictAutocomplete, this, input2, this._selectable, this._popoverable, this._filterable);
        preventInputEventsFromBubblingToSelectRoot(input2);
        highlightInputContentsWhenFocused(input2);
        this._filterable && filterResultsByInput(list, input2, this._filterable);
        trackActiveDescendant(input2, this._activatable, this._selectable);
        handleKeyboardNavigation(input2, this._activatable);
        handleKeyboardSelection(this, input2, this._activatable);
        handleMouseSelection(this, this._activatable);
      } else if (popoverEl && inputEl) {
        let input2 = inputEl;
        setAttribute2(input2, "role", "combobox");
        setAttribute2(input2, "aria-controls", listId);
        let popover = popoverEl;
        this._popoverable = new Popoverable(popover);
        this._anchorable = new Anchorable(popover, {
          reference: input2,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
        });
        handleAutocomplete(autocomplete, strictAutocomplete, this, input2, this._selectable, this._popoverable, this._filterable);
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            input2 && setAttribute2(input2, "disabled", "");
          } else {
            input2 && removeAttribute(input2, "disabled");
          }
        });
        this.querySelectorAll("button,ui-button").forEach((button2) => {
          if (popover.contains(button2)) return;
          setAttribute2(button2, "tabindex", "-1");
          setAttribute2(button2, "aria-controls", listId);
          setAttribute2(button2, "aria-haspopup", "listbox");
          linkExpandedStateToPopover(button2, this._popoverable);
          on(button2, "click", () => {
            this._popoverable.toggle();
            if (!isUsingTouch()) input2.focus();
          });
        });
        handleInputClearing(this, input2, this._selectable, this._popoverable);
        initPopover(this, input2, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable, [input2]);
        linkExpandedStateToPopover(input2, this._popoverable);
        preventInputEventsFromBubblingToSelectRoot(input2);
        highlightInputContentsWhenFocused(input2);
        this._filterable && filterResultsByInput(list, input2, this._filterable);
        trackActiveDescendant(input2, this._activatable, this._selectable);
        controlPopoverWithInput(input2, this._popoverable);
        controlPopoverWithKeyboard(input2, this._popoverable, this._activatable, this._selectable);
        openPopoverWithMouse(input2, this._popoverable);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(input2, this._activatable);
        handleKeyboardSelection(this, input2, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
      } else if (popoverEl && popoverInputEl) {
        let button2 = buttonEl;
        let input2 = popoverInputEl;
        let popover = popoverEl;
        setAttribute2(button2, "role", "combobox");
        setAttribute2(input2, "role", "combobox");
        setAttribute2(button2, "aria-controls", listId);
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            button2 && setAttribute2(button2, "disabled", "");
            input2 && setAttribute2(input2, "disabled", "");
          } else {
            button2 && removeAttribute(button2, "disabled");
            input2 && removeAttribute(input2, "disabled");
          }
        });
        this._popoverable = new Popoverable(popover);
        this._anchorable = new Anchorable(popover, {
          reference: button2,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0,
          scrollY: false
        });
        preventInputEventsFromBubblingToSelectRoot(input2);
        highlightInputContentsWhenFocused(input2);
        this._filterable && filterResultsByInput(list, input2, this._filterable);
        trackActiveDescendant(input2, this._activatable, this._selectable);
        focusInputWhenPopoverOpens(input2, this._popoverable);
        initPopover(this, button2, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable, [input2]);
        linkExpandedStateToPopover(button2, this._popoverable);
        handleInputClearing(this, input2, this._selectable, this._popoverable);
        controlPopoverWithKeyboard(button2, this._popoverable, this._activatable, this._selectable);
        togglePopoverWithMouse(button2, this._popoverable);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(input2, this._activatable);
        handleKeyboardSearchNavigation(button2, this._activatable, this._popoverable);
        handleKeyboardSelection(this, input2, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
      } else if (popoverEl) {
        let button2 = buttonEl;
        let popover = popoverEl;
        setAttribute2(button2, "role", "combobox");
        setAttribute2(button2, "aria-controls", listId);
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            button2 && setAttribute2(button2, "disabled", "");
            input && setAttribute2(input, "disabled", "");
          } else {
            button2 && removeAttribute(button2, "disabled");
            input && removeAttribute(input, "disabled");
          }
        });
        this._popoverable = new Popoverable(popover);
        this._anchorable = new Anchorable(popover, {
          reference: button2,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
        });
        initPopover(this, button2, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable);
        linkExpandedStateToPopover(button2, this._popoverable);
        controlPopoverWithKeyboard(button2, this._popoverable, this._activatable, this._selectable);
        togglePopoverWithMouse(button2, this._popoverable);
        trackActiveDescendant(button2, this._activatable, this._selectable);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(button2, this._activatable);
        handleKeyboardSearchNavigation(button2, this._activatable, this._popoverable);
        handleKeyboardSelection(this, button2, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
      }
      let observer = new MutationObserver(() => {
        requestAnimationFrame(() => {
          if (!this._popoverable || this._popoverable.getState()) {
            let firstSelectedOption = this._selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
            this._activatable.activateSelectedOrFirst(firstSelectedOption);
          } else {
            this._activatable.clearActive();
          }
        });
      });
      observer.observe(list, { childList: true });
    }
    unmount() {
      if (this._popoverable?.getState()) {
        let { unlock } = lockScroll();
        unlock();
      }
    }
    trigger() {
      return this.button() || this.input();
    }
    button() {
      return Array.from(this.querySelectorAll("button,ui-button")).find(
        (button) => button.nextElementSibling?.matches("[popover]")
      ) || null;
    }
    input() {
      return this.querySelector('input:not([type="hidden"])');
    }
    list() {
      return this.querySelector("ui-options") || this;
    }
    clear() {
      this._selectable.setState(null);
      this.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
    }
    open() {
      this._popoverable.setState(true);
    }
    close() {
      this._popoverable.setState(false);
    }
    deselectLast() {
      if (!this.hasAttribute("multiple") && this.value !== null) {
        this.value = null;
        this.dispatchEvent(new Event("input", { bubbles: false }));
        this.dispatchEvent(new Event("change", { bubbles: false }));
      }
      if (this.hasAttribute("multiple") && this.value.length !== 0) {
        this.value = this.value.slice(0, -1);
        this.dispatchEvent(new Event("input", { bubbles: false }));
        this.dispatchEvent(new Event("change", { bubbles: false }));
      }
    }
  };
  element("selected-remove", UISelectedRemove);
  element("selected", UISelected);
  element("select", UISelect);
  function handleKeyboardNavigation(el, activatable) {
    on(el, "keydown", (e) => {
      if (!["ArrowDown", "ArrowUp"].includes(e.key)) return;
      if (e.key === "ArrowDown") {
        activatable.activateNext();
        e.preventDefault();
        e.stopPropagation();
      } else if (e.key === "ArrowUp") {
        activatable.activatePrev();
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }
  function handleKeyboardSearchNavigation(el, activatable, popoverable) {
    search(el, (query) => {
      activatable.activateBySearch(query);
      if (!popoverable.getState()) {
        activatable.getActive()?.click();
      }
    });
  }
  function handleKeyboardSelection(root, el, activatable) {
    on(el, "keydown", (e) => {
      if (e.key === "Enter") {
        let activeEl = activatable.getActive();
        e.preventDefault();
        e.stopPropagation();
        if (!activeEl) return;
        if (activeEl._disableable?.isDisabled()) return;
        activeEl.click();
        root.dispatchEvent(new CustomEvent("interaction", {
          bubbles: false,
          cancelable: false,
          detail: { optionEl: activeEl }
        }));
        if (activeEl.hasAttribute("action")) {
          root.dispatchEvent(new CustomEvent("action", {
            bubbles: false,
            cancelable: false,
            detail: { optionEl: activeEl }
          }));
        }
      }
    });
  }
  function handleMouseSelection(root, activatable, pointerdown = false) {
    on(root, pointerdown ? "pointerdown" : "click", (e) => {
      if (e.target.closest("ui-option, ui-option-create")) {
        let option = e.target.closest("ui-option, ui-option-create");
        if (option._disableable.isDisabled()) return;
        option._selectable?.trigger();
        root.dispatchEvent(new CustomEvent("interaction", {
          bubbles: false,
          cancelable: false,
          detail: { optionEl: option }
        }));
        if (option.hasAttribute("action")) {
          root.dispatchEvent(new CustomEvent("action", {
            bubbles: false,
            cancelable: false,
            detail: { optionEl: option }
          }));
        }
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }
  function handleActivationOnFocus(el, activatable, selectable) {
    on(el, "focus", () => {
      let firstSelectedOption = selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
      activatable.activateSelectedOrFirst(firstSelectedOption);
    });
    on(el, "blur", () => {
      activatable.clearActive();
    });
  }
  function initListbox(el, multiple) {
    let listId = assignId(el, "options");
    setAttribute2(el, "role", "listbox");
    setAttribute2(el, "aria-multiselectable", multiple ? "true" : "false");
    return listId;
  }
  function linkExpandedStateToPopover(el, popoverable) {
    setAttribute2(el, "aria-haspopup", "listbox");
    let refreshPopover = () => {
      setAttribute2(el, "aria-expanded", popoverable.getState() ? "true" : "false");
      popoverable.getState() ? setAttribute2(el, "data-open", "") : removeAttribute(el, "data-open", "");
    };
    popoverable.onChange(() => {
      refreshPopover();
    });
    refreshPopover();
  }
  function initPopover(root, trigger, popover, popoverable, anchorable) {
    let refreshPopover = () => {
      Array.from([root, popover]).forEach((i) => {
        popoverable.getState() ? setAttribute2(i, "data-open", "") : removeAttribute(i, "data-open", "");
      });
      popoverable.getState() ? anchorable.reposition() : anchorable.cleanup();
    };
    popoverable.onChange(() => refreshPopover());
    refreshPopover();
    popoverable.onChange(() => {
      if (popoverable.getState()) {
        root.dispatchEvent(new Event("open", {
          bubbles: false,
          cancelable: false
        }));
      } else {
        root.dispatchEvent(new Event("close", {
          bubbles: false,
          cancelable: false
        }));
      }
    });
  }
  function controlActivationWithPopover(popoverable, activatable, selectable) {
    popoverable.onChange(() => {
      if (popoverable.getState()) {
        let firstSelectedOption = selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
        setTimeout(() => {
          activatable.activateSelectedOrFirst(firstSelectedOption);
        });
      } else {
        activatable.clearActive();
      }
    });
  }
  function controlPopoverWithEscapeKey(root, popoverable) {
    on(root, "keydown", (e) => {
      if (e.key === "Escape" && popoverable.getState()) {
        popoverable.setState(false);
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    });
  }
  function controlPopoverWithKeyboard(button, popoverable) {
    on(button, "keydown", (e) => {
      if (!["ArrowDown", "ArrowUp"].includes(e.key)) return;
      if (e.key === "ArrowDown") {
        if (!popoverable.getState()) {
          popoverable.setState(true);
          e.preventDefault();
          e.stopImmediatePropagation();
        }
      } else if (e.key === "ArrowUp") {
        if (!popoverable.getState()) {
          popoverable.setState(true);
          e.preventDefault();
          e.stopImmediatePropagation();
        }
      }
    });
  }
  function openPopoverWithMouse(el, popoverable) {
    on(el, "click", () => {
      if (!popoverable.getState()) {
        popoverable.setState(true);
        el.focus();
      }
    });
  }
  function togglePopoverWithMouse(button, popoverable) {
    on(button, "click", () => {
      popoverable.setState(!popoverable.getState());
      button.focus();
    });
  }
  function focusInputWhenPopoverOpens(input, popoverable) {
    popoverable.onChange(() => {
      if (popoverable.getState() && !isUsingTouch()) {
        setTimeout(() => input.focus());
      }
    });
  }
  function filterResultsByInput(list, input, filterable) {
    on(input, "input", (e) => {
      filterable.filter(e.target.value);
    });
    let original = Object.getOwnPropertyDescriptor(Object.getPrototypeOf(input), "value");
    Object.defineProperty(input, "value", {
      get() {
        return original.get.call(this);
      },
      set(value3) {
        original.set.call(this, value3);
        filterable.filter(value3);
      }
    });
    let observer = new MutationObserver(() => {
      requestAnimationFrame(() => {
        filterable.filter(filterable.lastSearch);
      });
    });
    observer.observe(list, { childList: true });
  }
  function highlightInputContentsWhenFocused(input) {
    on(input, "focus", () => input.select());
  }
  function preventInputEventsFromBubblingToSelectRoot(input) {
    on(input, "change", (e) => e.stopPropagation());
    on(input, "input", (e) => e.stopPropagation());
  }
  function controlPopoverWithInput(input, popoverable) {
    on(input, "keydown", (e) => {
      if (/^[a-zA-Z0-9]$/.test(e.key) || e.key === "Backspace") {
        popoverable.getState() || popoverable.setState(true);
      }
    });
  }
  function handleInputClearing(root, input, selectable, popoverable) {
    let shouldClear = root.hasAttribute("clear");
    if (!shouldClear) return;
    let setInputValue = (value3) => {
      if (input.value === value3) return;
      input.value = value3;
      input.dispatchEvent(new Event("input", { bubbles: false }));
    };
    let clear = root.getAttribute("clear");
    let clearOnAction = clear === "" || clear.split(" ").includes("action");
    let clearOnSelect = clear === "" || clear.split(" ").includes("select");
    let clearOnClose = clear === "" || clear.split(" ").includes("close");
    let clearOnEsc = clear === "" || clear.split(" ").includes("esc");
    if (clear === "none") clearOnAction = clearOnSelect = clearOnClose = clearOnEsc = false;
    if (clearOnAction) {
      root.addEventListener("action", (e) => {
        setInputValue("");
      });
    } else if (clearOnSelect) {
      selectable.onChange(() => {
        queueMicrotask(() => setInputValue(""));
      });
    }
    if (clearOnClose) {
      popoverable.onChange(() => {
        if (!popoverable.getState()) {
          setInputValue("");
        }
      });
    }
    if (clearOnEsc) {
      on(input, "keydown", (e) => {
        if (e.key === "Escape") {
          setInputValue("");
        }
      });
    }
  }
  function handlePopoverClosing(root, selectable, popoverable, multiple) {
    let closeOnInteraction = !multiple;
    let closeOnAction = !multiple;
    let closeOnSelect = !multiple;
    if (root.hasAttribute("close")) {
      let close = root.getAttribute("close");
      closeOnInteraction = close === "";
      closeOnAction = close.split(" ").includes("action");
      closeOnSelect = close.split(" ").includes("select");
      if (close === "none") return;
    }
    if (closeOnInteraction || closeOnAction) {
      root.addEventListener("interaction", (e) => {
        let optionEl = e.detail.optionEl;
        if (!optionEl.hasAttribute("action")) {
          popoverable.setState(false);
          return;
        }
        setTimeout(() => {
          if (!optionEl.hasAttribute("data-os-loading") && !optionEl.hasAttribute("data-loading")) {
            popoverable.setState(false);
            return;
          }
          let observer = new MutationObserver(() => {
            requestAnimationFrame(() => {
              if (!root.querySelector("input[data-invalid]")) {
                popoverable.setState(false);
              }
            });
            observer.disconnect();
          });
          observer.observe(optionEl, { attributes: true, attributeFilter: ["data-loading", "data-os-loading"] });
        }, 10);
      });
    } else if (closeOnSelect) {
      selectable.onChange(() => {
        popoverable.setState(false);
      });
    }
  }
  function trackActiveDescendant(input, activatable, selectable) {
    activatable.onChange(() => {
      let activeEl = activatable.getActive();
      activeEl ? setAttribute2(input, "aria-activedescendant", activeEl.id) : removeAttribute(input, "aria-activedescendant");
    });
    selectable.onChange((selectableItem) => {
      if (!selectableItem) return;
      let activeEl = selectableItem.el;
      activeEl && setAttribute2(input, "aria-activedescendant", activeEl.id);
    });
  }
  function handleAutocomplete(autocomplete, isStrict, root, input, selectable, popoverable, filterable) {
    if (!autocomplete) {
      setAttribute2(input, "autocomplete", "off");
      setAttribute2(input, "aria-autocomplete", "none");
      return;
    }
    let setInputValue = (value3) => {
      if (input.value === value3) return;
      input.value = value3;
      input.dispatchEvent(new Event("input", { bubbles: false }));
    };
    setAttribute2(input, "autocomplete", "off");
    setAttribute2(input, "aria-autocomplete", "list");
    if (input.value !== "" && selectable.isEmpty()) {
      selectable.setState(input.value);
    }
    let sync = () => {
      if (selectable.isNotEmpty()) {
        requestAnimationFrame(() => {
          setInputValue(selectable.selectedTextValue());
          if (isStrict) setAttribute2(input, "data-selected", selectable.selectedTextValue());
        });
      } else {
        setInputValue("");
        removeAttribute(input, "data-selected");
      }
    };
    queueMicrotask(() => {
      if (selectable.isEmpty() && input.value !== "") {
        selectable.setState(input.value);
      }
      selectable.onInitAndChange(() => sync());
    });
    root.addEventListener("interaction", (e) => {
      if (e.detail.optionEl._selectable?.isSelected()) sync();
    });
    if (isStrict) {
      popoverable.onChange(() => {
        if (!popoverable.getState()) {
          requestAnimationFrame(() => {
            setInputValue(input.getAttribute("data-selected") || "");
          });
        }
      });
    }
  }
  function preventScrollWhenPopoverIsOpen(root, popoverable, except = []) {
    let { lock, unlock } = lockScroll(popoverable.el, false, except);
    popoverable.onChange(() => {
      popoverable.getState() ? lock() : unlock();
    });
  }

  // js/pillbox.js
  var UIPillbox = class extends UISelect {
    mount() {
      this._disableable = new Disableable(this);
      queueMicrotask(() => {
        this._submittable.update(this._selectable.getState());
      });
      let input = this.input();
      let list = this.list();
      let multiple = this.hasAttribute("multiple");
      let triggerEl = this.querySelector("ui-pillbox-trigger");
      let listbox = this.querySelector("ui-options") || this;
      let listId = initListbox(listbox, "options", multiple);
      this._activatable = new ActivatableGroup(listbox, { filter: "data-hidden" });
      if (this.hasAttribute("filter") && this.getAttribute("filter") !== "manual") {
        this._filterable = new FilterableGroup(list);
        this._filterable.onChange(() => {
          this._activatable.clearActive();
          if (this._filterable.hasResults()) {
            this._activatable.activateFirst();
          }
        });
        this.addEventListener("close", () => {
          if (this._filterable) {
            this._filterable.filter("");
          }
        });
      }
      let popoverEl = this.querySelector("[popover]:not(ui-tooltip > [popover])");
      let popoverInputEl = popoverEl?.querySelector('input:not([type="hidden"])');
      let inlineInputEl = this.querySelector("ui-selected input");
      if (!popoverEl) throw new Error("Popover element not found");
      if (popoverInputEl) {
        let trigger = triggerEl;
        let input2 = popoverInputEl;
        let popover = popoverEl;
        setAttribute2(trigger, "role", "button");
        setAttribute2(input2, "aria-autocomplete", "list");
        setAttribute2(input2, "aria-controls", listId);
        setAttribute2(input2, "role", "combobox");
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            trigger && setAttribute2(trigger, "disabled", "");
            input2 && setAttribute2(input2, "disabled", "");
          } else {
            trigger && removeAttribute(trigger, "disabled");
            input2 && removeAttribute(input2, "disabled");
          }
        });
        this._popoverable = new Popoverable(popover);
        this._anchorable = new Anchorable(popover, {
          reference: trigger,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0,
          scrollY: false
        });
        trackActiveDescendant(input2, this._activatable, this._selectable);
        preventInputEventsFromBubblingToSelectRoot(input2);
        highlightInputContentsWhenFocused(input2);
        this._filterable && filterResultsByInput(list, input2, this._filterable);
        focusInputWhenPopoverOpens(input2, this._popoverable);
        initPopover(this, trigger, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable, [input2]);
        linkExpandedStateToPopover(trigger, this._popoverable);
        handleInputClearing(this, input2, this._selectable, this._popoverable);
        controlPopoverWithKeyboard(trigger, this._popoverable, this._activatable, this._selectable);
        togglePopoverWithMouse(trigger, this._popoverable);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(input2, this._activatable);
        handleKeyboardSearchNavigation(trigger, this._activatable, this._popoverable);
        handleKeyboardSelection(this, input2, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
        openPopoverWithSpacebar(trigger);
      } else if (inlineInputEl) {
        let trigger = triggerEl;
        let input2 = inlineInputEl;
        let popover = popoverEl;
        setAttribute2(trigger, "role", "button");
        setAttribute2(input2, "aria-autocomplete", "list");
        setAttribute2(input2, "aria-controls", listId);
        setAttribute2(input2, "role", "combobox");
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            trigger && setAttribute2(trigger, "disabled", "");
            input2 && setAttribute2(input2, "disabled", "");
          } else {
            trigger && removeAttribute(trigger, "disabled");
            input2 && removeAttribute(input2, "disabled");
          }
        });
        this._popoverable = new Popoverable(popover, {
          triggers: [input2, trigger]
          // Make sure that focusing the input doesn't close the popover...
        });
        this._anchorable = new Anchorable(popover, {
          reference: trigger,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
        });
        trackActiveDescendant(input2, this._activatable, this._selectable);
        preventInputEventsFromBubblingToSelectRoot(input2);
        highlightInputContentsWhenFocused(input2);
        this._filterable && filterResultsByInput(list, input2, this._filterable);
        focusInputWhenPopoverOpens(input2, this._popoverable);
        initPopover(this, trigger, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable, [trigger]);
        linkExpandedStateToPopover(trigger, this._popoverable);
        handleInputClearing(this, input2, this._selectable, this._popoverable);
        controlPopoverWithInput(input2, this._popoverable);
        controlPopoverWithKeyboard(input2, this._popoverable, this._activatable, this._selectable);
        openPopoverWithMouse(trigger, this._popoverable, input2);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(input2, this._activatable);
        handleKeyboardSearchNavigation(trigger, this._activatable, this._popoverable);
        handleKeyboardSelection(this, input2, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
        focusInputOnSelection(this, this._selectable, input2);
        hideInputPlaceholderWhenNotEmpty(this._selectable, input2);
        deselectLastItemWithBackspace(this._selectable, input2);
        forwardClicksToInputWhenPopoverIsOpen(this._popoverable, trigger, input2);
      } else {
        let trigger = triggerEl;
        let popover = popoverEl;
        setAttribute2(trigger, "role", "combobox");
        setAttribute2(trigger, "aria-controls", listId);
        setAttribute2(trigger, "aria-autocomplete", "none");
        this._disableable.onInitAndChange((disabled) => {
          if (disabled) {
            trigger && setAttribute2(trigger, "disabled", "");
            input && setAttribute2(input, "disabled", "");
          } else {
            trigger && removeAttribute(trigger, "disabled");
            input && removeAttribute(input, "disabled");
          }
        });
        this._popoverable = new Popoverable(popover);
        this._anchorable = new Anchorable(popover, {
          reference: trigger,
          matchWidth: true,
          position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
          gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
          offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
        });
        initPopover(this, trigger, popover, this._popoverable, this._anchorable);
        preventScrollWhenPopoverIsOpen(this, this._popoverable);
        linkExpandedStateToPopover(trigger, this._popoverable);
        controlPopoverWithKeyboard(trigger, this._popoverable, this._activatable, this._selectable);
        togglePopoverWithMouse(trigger, this._popoverable);
        trackActiveDescendant(trigger, this._activatable, this._selectable);
        controlPopoverWithEscapeKey(this, this._popoverable);
        handleKeyboardNavigation(trigger, this._activatable);
        handleKeyboardSearchNavigation(trigger, this._activatable, this._popoverable);
        handleKeyboardSelection(this, trigger, this._activatable);
        handleMouseSelection(this, this._activatable);
        controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
        handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
        openPopoverWithSpacebar(trigger);
      }
      let observer = new MutationObserver(() => {
        requestAnimationFrame(() => {
          if (!this._popoverable || this._popoverable.getState()) {
            let firstSelectedOption = this._selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
            this._activatable.activateSelectedOrFirst(firstSelectedOption);
          } else {
            this._activatable.clearActive();
          }
        });
      });
      observer.observe(list, { childList: true });
    }
    list() {
      return this.querySelector("ui-options") || this;
    }
  };
  var UIPillboxTrigger = class extends UIElement {
    boot() {
      if (this.querySelector("input")) {
        setAttribute2(this, "tabindex", "-1");
      } else {
        setAttribute2(this, "tabindex", "0");
      }
      this._disableable = new Disableable(this);
      let disableClick = (e) => {
        e.preventDefault();
        e.stopImmediatePropagation();
      };
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          this.addEventListener("click", disableClick, true);
        } else {
          this.removeEventListener("click", disableClick, true);
        }
      });
    }
    mount() {
    }
  };
  element("pillbox-trigger", UIPillboxTrigger);
  element("pillbox", UIPillbox);
  function openPopoverWithSpacebar(el) {
    on(el, "keydown", (e) => {
      if (e.key === " ") {
        e.preventDefault();
        e.stopImmediatePropagation();
        el.click();
      }
    });
  }
  function focusInputOnSelection(root, selectable, input) {
    selectable.onChange(() => {
      if (document.activeElement !== input && document.activeElement.closest("ui-pillbox") === root) {
        input.focus();
      }
    });
  }
  function hideInputPlaceholderWhenNotEmpty(selectable, input) {
    selectable.onInitAndChange(() => {
      if (selectable.noneAreSelected()) {
        setAttribute2(input, "placeholder", input.getAttribute("data-placeholder"));
      } else {
        setAttribute2(input, "placeholder", "");
      }
    });
  }
  function deselectLastItemWithBackspace(selectable, input) {
    on(input, "keydown", (e) => {
      if (input.value == "" && e.key === "Backspace") {
        let last = selectable.findByValue(
          selectable.getState().at(-1)
        );
        last?.deselect();
      }
    });
  }
  function forwardClicksToInputWhenPopoverIsOpen(popoverable, trigger, input) {
    on(trigger, "click", (e) => {
      if (popoverable.getState()) {
        input.focus();
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    });
  }

  // js/options.js
  var UIOptions = class extends UIElement {
    boot() {
      setAttribute2(this, "tabindex", "-1");
      if (this.hasAttribute("popover")) {
        this.addEventListener("lofi-close-popovers", () => {
          this.hidePopover();
        });
      }
      setAttribute2(this, "role", "listbox");
    }
    mount() {
      let picker = this.closest("ui-autocomplete, ui-combobox, ui-select, ui-pillbox");
      let input = picker?.input();
      if (input) displayEmptyAndCreateOptions(this, input, picker._filterable);
    }
  };
  var UIOption = class extends UIElement {
    mount() {
      let target = this;
      this._disableable = new Disableable(target, {
        disableWithParent: false
      });
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(target, "aria-disabled", "true");
        } else {
          removeAttribute(target, "aria-disabled");
        }
      });
      let id = assignId(target, "option");
      setAttribute2(target, "role", "option");
      this._filterable = new Filterable(target, {
        mirror: this,
        keep: !!this.closest("ui-option-empty, ui-empty") || this.getAttribute("filter") === "manual"
      });
      this._activatable = new Activatable(target);
      if (!this.hasAttribute("action")) {
        this._selectable = new Selectable(target, {
          value: this._pendingValue ?? this.getValue(),
          label: this.getLabel(),
          selectedLabel: this.getSelectedLabel(),
          selectedInitially: this.hasAttribute("selected")
        });
      }
      let observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.attributeName === "selected") {
            if (this.hasAttribute("selected")) this._selectable.setState(true);
            else this._selectable.setState(false);
          }
          if (mutation.attributeName === "value") {
            if (this._selectable) {
              this._selectable.value = this.getValue();
            }
          }
        });
      });
      observer.observe(this, { attributeFilter: ["selected", "value"] });
    }
    get value() {
      return this._selectable?.value ?? this._pendingValue ?? this.getValue();
    }
    set value(val) {
      if (!this._selectable) {
        this._pendingValue = val + "";
        return;
      }
      this._selectable.value = val + "";
    }
    get selected() {
      if (!this?._selectable) return false;
      return this._selectable.isSelected();
    }
    set selected(value3) {
      if (!this?._selectable) return false;
      this._selectable.setState(value3);
    }
    getSelectedLabel() {
      return this.getAttribute("selected-label");
    }
    getLabel() {
      return this.hasAttribute("label") ? this.getAttribute("label") : this.textContent.trim();
    }
    getValue() {
      return this.hasAttribute("value") ? this.getAttribute("value") : this.textContent.trim();
    }
  };
  var UIOptionCreate = class extends UIElement {
    mount() {
      this._activatable = new Activatable(this);
      this._filterable = new Filterable(this, { keep: true });
      this._disableable = new Disableable(this, {
        disableWithParent: false
      });
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(this, "aria-disabled", "true");
        } else {
          removeAttribute(this, "aria-disabled");
        }
      });
    }
  };
  var UIOptionEmpty = class extends UIElement {
  };
  var UIEmpty = class extends UIOptionEmpty {
  };
  element("option-create", UIOptionCreate);
  element("options", UIOptions);
  element("option", UIOption);
  element("option-empty", UIOptionEmpty);
  element("empty", UIEmpty);
  function displayEmptyAndCreateOptions(list, input, filterable) {
    let empty = Array.from(list.children).find((element2) => element2.matches("ui-option-empty, ui-empty"));
    let create = Array.from(list.children).find((element2) => element2.matches("ui-option-create"));
    let minLength = create?.hasAttribute("min-length") ? parseInt(create.getAttribute("min-length")) : void 0;
    setAttribute2(empty, "data-hidden", "");
    if (create) setAttribute2(create, "data-hidden", "");
    let isHidden = (el) => el.hasAttribute("data-hidden");
    let isUnique = (el) => el.textContent.toLowerCase().trim() !== input.value.toLowerCase().trim();
    let getItems = () => Array.from(list.querySelectorAll("ui-option")).filter((option) => option.closest("ui-options") === list);
    let showLoading = () => {
      if (empty.hasAttribute("when-loading")) {
        let emptyContent = empty.textContent;
        empty.textContent = empty.getAttribute("when-loading");
        empty.__hideLoading = () => {
          empty.textContent = emptyContent;
          empty.__hideLoading = void 0;
        };
      }
    };
    let revertDisable;
    let debounceEnable;
    let refresh = () => {
      clearTimeout(revertDisable);
      clearTimeout(debounceEnable);
      if (input.hasAttribute("data-os-loading") || input.hasAttribute("data-loading")) return;
      let items = getItems();
      let isEmpty = items.every((i) => isHidden(i));
      if (create) {
        if (minLength && input.value.trim().length < minLength) {
          setAttribute2(create, "data-hidden", "");
        } else {
          if (items.every((i) => isUnique(i))) {
            removeAttribute(create, "data-hidden");
          } else {
            setAttribute2(create, "data-hidden", "");
          }
        }
        if (isEmpty) create._activatable.activate(true);
        let enable = () => {
          removeAttribute(create, "disabled");
        };
        if (filterable) {
          enable();
        } else {
          debounceEnable = setTimeout(enable, 150);
        }
      }
      if (isEmpty && (!create || isHidden(create))) {
        removeAttribute(empty, "data-hidden");
      } else {
        setAttribute2(empty, "data-hidden", "");
      }
      empty.__hideLoading?.();
    };
    if (filterable) {
      filterable.onChange(() => refresh());
      if (create) {
        on(input, "input", () => {
          if (minLength && input.value.trim().length < minLength) {
            setAttribute2(create, "data-hidden", "");
          }
        });
      }
    } else {
      let observer = new MutationObserver(() => {
        requestAnimationFrame(() => refresh());
      });
      observer.observe(list, { childList: true });
      observer.observe(input, { attributes: true, attributeFilter: ["data-loading", "data-os-loading"] });
      if (create) {
        on(input, "input", (e) => {
          clearTimeout(revertDisable);
          clearTimeout(debounceEnable);
          let isEmpty = getItems().every((i) => isHidden(i));
          if (isEmpty && input.value.trim() === "" && !isHidden(create)) {
            showLoading();
            removeAttribute(empty, "data-hidden");
            setAttribute2(create, "data-hidden", "");
          } else if (input.value.trim() === "" && !isHidden(create)) {
            setAttribute2(create, "data-hidden", "");
          }
          if (minLength && input.value.trim().length >= minLength) {
            removeAttribute(create, "data-hidden");
          }
          setAttribute2(create, "disabled", "");
          revertDisable = setTimeout(() => {
            removeAttribute(create, "disabled");
            if (isEmpty) create._activatable.activate();
          }, 200);
        });
      }
    }
    queueMicrotask(() => refresh());
  }

