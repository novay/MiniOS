  // js/composer.js
  var UIComposer = class extends UIControl {
    boot() {
      setAttribute2(this, "role", "group");
      this.config = {
        submit: this.getAttribute("submit") || "cmd-enter",
        rows: Number(this.getAttribute("rows")) || 2,
        maxRows: Number(this.getAttribute("max-rows")) || 10
      };
      let textarea = this.querySelector("textarea");
      let editor = this.querySelector("ui-editor");
      if (textarea) {
        this._inputEl = textarea;
        this._initTextarea();
      } else {
        this._inputEl = editor;
        this._initEditor();
      }
      this.state = {
        onChanges: [],
        getValue: () => {
          return this._inputEl.value;
        },
        setValue: (value3) => {
          this._inputEl.value = value3;
          this.state.onChanges.forEach((i) => i(this.state.getValue()));
        },
        onChange: (callback) => {
          this.state.onChanges.push(callback);
        }
      };
      this._controllable = new Controllable(this);
      this._disableable = new Disableable(this);
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.state.getValue()
      });
      this.state.onChange((value3) => {
        this._submittable.update(value3);
      });
      this._controllable.initial((initial) => initial && this.state.setValue(initial));
      this._controllable.getter(() => this.state.getValue());
      this._controllable.setter((v) => this.state.setValue(v));
      let undoDisableActions = () => {
      };
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          undoDisableActions = this._disableActions();
          setAttribute2(this._inputEl, "disabled", "");
        } else {
          undoDisableActions();
          undoDisableActions = () => {
          };
          removeAttribute(this._inputEl, "disabled");
        }
      });
      on(this._inputEl, "input", (e) => {
        this._controllable.dispatch();
        e.stopPropagation();
      });
    }
    _resizeTextarea() {
      setAttribute2(this._inputEl, "style", "height: auto");
      let style = window.getComputedStyle(this._inputEl);
      let height = this._inputEl.scrollHeight;
      let lineHeight = parseFloat(style.lineHeight);
      if (isNaN(lineHeight)) {
        lineHeight = parseFloat(style.fontSize) * 1.2;
      }
      let borderTop = parseFloat(style.borderTopWidth);
      let borderBottom = parseFloat(style.borderBottomWidth);
      let paddingTop = parseFloat(style.paddingTop);
      let paddingBottom = parseFloat(style.paddingBottom);
      let minHeight = this.config.rows * lineHeight;
      let maxHeight = this.config.maxRows * lineHeight;
      if (style.boxSizing === "border-box") {
        height += borderTop + borderBottom;
        minHeight += paddingTop + paddingBottom + borderTop + borderBottom;
        maxHeight += paddingTop + paddingBottom + borderTop + borderBottom;
      } else {
        height -= paddingTop + paddingBottom;
      }
      if (height < minHeight) height = minHeight;
      let overflow = "hidden";
      if (height > maxHeight) {
        height = maxHeight;
        overflow = "auto";
      }
      setAttribute2(this._inputEl, "style", `height: ${height}px; overflow-y: ${overflow}`);
    }
    focusInput() {
      this._inputEl.focus();
    }
    _resizeEditor() {
      let contentEl = this._inputEl.querySelector('[data-slot="content"]');
      if (!contentEl) return;
      let style = window.getComputedStyle(contentEl);
      let lineHeight = parseFloat(style.lineHeight);
      if (isNaN(lineHeight)) {
        lineHeight = parseFloat(style.fontSize) * 1.2;
      }
      let borderTop = parseFloat(style.borderTopWidth);
      let borderBottom = parseFloat(style.borderBottomWidth);
      let paddingTop = parseFloat(style.paddingTop);
      let paddingBottom = parseFloat(style.paddingBottom);
      let minHeight = this.config.rows * lineHeight;
      let maxHeight = this.config.maxRows * lineHeight;
      if (style.boxSizing === "border-box") {
        minHeight += paddingTop + paddingBottom + borderTop + borderBottom;
        maxHeight += paddingTop + paddingBottom + borderTop + borderBottom;
      }
      setAttribute2(contentEl, "style", `min-height: ${minHeight}px; max-height: ${maxHeight}px; height: auto; overflow-y: auto;`);
    }
    _initTextarea() {
      if (!this._inputEl.hasAttribute("rows")) {
        setAttribute2(this._inputEl, "rows", this.config.rows);
      }
      if (this.hasAttribute("placeholder")) {
        setAttribute2(this._inputEl, "placeholder", this.getAttribute("placeholder"));
      }
      this._inputEl.addEventListener("input", () => this._resizeTextarea());
      this._resizeTextarea();
      on(this._inputEl, "keydown", (e) => this._handleSubmitKeydown(e));
    }
    _initEditor() {
      if (this.hasAttribute("placeholder")) {
        setAttribute2(this._inputEl, "placeholder", this.getAttribute("placeholder"));
      }
      this.addEventListener("os:editor:ready", () => {
        this._resizeEditor();
      });
      on(this._inputEl, "keydown", (e) => this._handleSubmitKeydown(e), { capture: true });
    }
    _handleSubmitKeydown(e) {
      if (["cmd-enter", "super-enter", "ctrl-enter"].includes(this.config.submit) && (e.metaKey || e.ctrlKey) && e.key === "Enter") {
        e.preventDefault();
        e.stopPropagation();
        this._submittable.submitEnclosingForm();
      } else if (this.config.submit === "enter" && e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        e.stopPropagation();
        this._submittable.submitEnclosingForm();
      } else if (this.config.submit === "shift-enter" && e.key === "Enter" && e.shiftKey) {
        e.preventDefault();
        e.stopPropagation();
        this._submittable.submitEnclosingForm();
      }
    }
    _disableActions() {
      let undos = [];
      let actionEls = this.querySelectorAll("button");
      actionEls.forEach((el) => {
        if (!el.hasAttribute("disabled")) {
          setAttribute2(el, "disabled", "");
          undos.push(() => {
            removeAndReleaseAttribute(el, "disabled");
          });
        }
      });
      return () => {
        undos.forEach((undo) => undo());
        undos = [];
      };
    }
  };
  element("composer", UIComposer);

