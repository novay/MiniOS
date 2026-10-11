  // js/tabs.js
  var UITabGroup = class _UITabGroup extends UIElement {
    boot() {
      this._disabled = this.hasAttribute("disabled");
    }
    mount() {
      this.walkPanels((el) => initializePanel(el));
      new MutationObserver((mutations) => {
        this.walkPanels((el) => initializePanel(el));
      }).observe(this, { childList: true });
    }
    showPanel(name) {
      this.walkPanels((el) => {
        if (el.getAttribute("name") === name) el.show();
        else el.hide();
      });
    }
    selectPanel(name) {
      for (let tabs of this.querySelectorAll("ui-tabs")) {
        if (tabs.closest("ui-tab-group") !== this) continue;
        tabs._selectableGroup?.setState(name);
      }
    }
    getPanel(name) {
      return this.walkPanels((el, bail) => {
        if (el.getAttribute("name") === name) {
          bail(el);
        }
      });
    }
    walkPanels(callback) {
      let bailed = false;
      let bailedReturn;
      for (let child of this.children) {
        if (child instanceof _UITabGroup) continue;
        if (child instanceof UITabs) continue;
        callback(child, (forward) => {
          bailed = true;
          bailedReturn = forward;
        });
        if (bailed) break;
      }
      return bailedReturn;
    }
  };
  var UITabsScrollArea = class extends UIElement {
    boot() {
      on(this, "scroll", (e) => {
        this.updateScrollVariable();
      }, { passive: true });
      new ResizeObserver(() => {
        this.updateScrollVariable();
      }).observe(this);
    }
    updateScrollVariable() {
      let percentage = Math.abs(this.scrollLeft) / (this.scrollWidth - this.clientWidth) * 100;
      this.style.setProperty("--os-scroll-percentage", percentage + "%");
    }
  };
  var UITabs = class _UITabs extends UIControl {
    boot() {
      this._focusable = new FocusableGroup(this, { wrap: true });
      this._selectableGroup = new SelectableGroup(this);
      this._controllable = new Controllable(this, { disabled: this._disabled });
      this._controllable.initial((initial) => initial && this._selectableGroup.setState(initial));
      this._controllable.getter(() => this._selectableGroup.getState());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this._selectableGroup.setState(value3);
      }));
      this._selectableGroup.onChange(detangled(() => {
        this._controllable.dispatch();
      }));
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
      setAttribute2(this, "role", "tablist");
    }
    mount() {
      this.initializeTabs();
      this._focusable.ensureTabbable();
      if (!this._selectableGroup.getState()) {
        this._selectableGroup.selectFirst();
      }
      new MutationObserver((mutations) => {
        this.initializeTabs();
        let selected = this._selectableGroup.selected();
        selected.el.closest("ui-tab-group").showPanel(selected.value);
      }).observe(this, { childList: true });
    }
    initializeTabs() {
      this.walker().each((el) => {
        if (el._initialized) return;
        if (el._disableable) return;
        if (el.hasAttribute("action")) return;
        initializeTab(el);
        el._initialized = true;
      });
    }
    walker() {
      return walker(this, (el, { skip, reject }) => {
        if (el instanceof UITabGroup) return reject();
        if (el instanceof _UITabs) return reject();
        if (!["a", "button"].includes(el.localName)) return skip();
      });
    }
  };
  function initializeTab(el) {
    el._disableable = new Disableable(el);
    let link = el.matches("a") ? el : null;
    let target = link || el;
    el._disableable.onInitAndChange((disabled) => {
      if (disabled) {
        setAttribute2(target, "aria-disabled", "true");
      } else {
        removeAttribute(target, "aria-disabled");
      }
    });
    let id = assignId(target, "tab");
    setAttribute2(target, "role", "tab");
    target._focusable = new Focusable(target, { disableable: el._disableable, tabbableAttr: "data-active" });
    if (!link) {
      let panel = el.getAttribute("name");
      el._selectable = new Selectable(el, {
        value: panel || Math.random().toString(36).substring(2, 10),
        label: el.hasAttribute("label") ? el.getAttribute("label") : el.textContent.trim(),
        selectedInitially: el.hasAttribute("selected"),
        toggleable: false
      });
      on(el, "click", () => el._selectable.press());
      el._selectable.onInitAndChange(() => {
        let panelEl = el.closest("ui-tab-group")?.getPanel(panel);
        el._selectable.getState() ? panelEl?.show() : panelEl?.hide();
        if (el._selectable.getState()) {
          el._focusable.focus(false);
        }
      });
      let pointerdownTimeout = null;
      if (!el.closest("ui-tab-group")?.hasAttribute("manual")) {
        on(el, "pointerdown", () => {
          clearTimeout(pointerdownTimeout);
          pointerdownTimeout = setTimeout(() => pointerdownTimeout = null);
        });
        on(el, "focus", () => {
          if (!pointerdownTimeout) {
            el._selectable.select();
          }
        });
      }
      queueMicrotask(() => {
        let container = el.closest("ui-tab-group");
        if (!container) return;
        let panelEl = container.getPanel(panel);
        if (!panelEl) throw new Error("Could not find panel...");
        setAttribute2(el, "aria-controls", panelEl.id);
        setAttribute2(panelEl, "aria-labelledby", el.id);
      });
    }
  }
  function initializePanel(el) {
    if (el._initialized) return;
    assignId(el, "tab-panel");
    setAttribute2(el, "role", "tabpanel");
    el.hasAttribute("tabindex") || setAttribute2(el, "tabindex", "-1");
    el.show = () => {
      setAttribute2(el, "data-selected", "");
      setAttribute2(el, "tabindex", "0");
      removeAttribute(el, "hidden");
    };
    el.hide = () => {
      removeAttribute(el, "data-selected");
      setAttribute2(el, "tabindex", "-1");
      setAttribute2(el, "hidden", el.closest("ui-tab-group")?.hasAttribute("findable") ? "until-found" : "");
    };
    on(el, "beforematch", () => {
      let group = el.closest("ui-tab-group");
      if (group?.hasAttribute("findable")) group.selectPanel(el.getAttribute("name"));
    });
    el._initialized = true;
  }
  element("tab-group", UITabGroup);
  element("tabs-scroll-area", UITabsScrollArea);
  element("tabs", UITabs);

