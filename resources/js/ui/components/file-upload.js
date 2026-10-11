  // js/file-upload.js
  var UIFileUpload = class extends UIElement {
    boot() {
      this.type = "file";
    }
    mount() {
      this._disableable = new Disableable(this);
      this._files = [];
      this.inputEl = this.querySelector('input[data-slot="receiver"]');
      if (this.inputEl) {
        this.forwardAcceptAttribute();
        this.configureSRInput();
      }
      this.triggerEl = this.findTriggerElement();
      this.rootEl = this.closest("ui-file-upload");
      let hasMultiple = this.hasAttribute("multiple") || this.rootEl && this.rootEl.hasAttribute("multiple");
      if (hasMultiple) {
        setAttribute2(this.inputEl, "multiple", "");
      }
      Object.defineProperty(this, "multiple", {
        get() {
          return hasMultiple;
        }
      });
      Object.defineProperty(this, "files", {
        get() {
          return this._files;
        },
        set(newValue) {
          if (newValue === null || newValue === "") {
            this._files = [];
          } else if (Array.isArray(newValue)) {
            this._files = newValue;
          } else if (newValue instanceof FileList) {
            this._files = Array.from(newValue);
          } else {
            this._files = [newValue];
          }
          this.dispatchEvent(new Event("change", { bubbles: true }));
        }
      });
      this.observer = new MutationObserver(() => {
        this.ensureInputsExist();
      });
      this.observer.observe(this, { childList: true });
      this.style.setProperty("--os-file-upload-progress", "0%");
      this.style.setProperty("--os-file-upload-progress-as-string", "'0%'");
      on(this, "livewire-upload-start", () => {
        setAttribute2(this, "data-loading", "");
        this.disabled = true;
        this.style.setProperty("--os-file-upload-progress", "0");
        this.style.setProperty("--os-file-upload-progress-as-string", "'0%'");
      });
      on(this, "livewire-upload-finish", () => {
        removeAttribute(this, "data-loading");
        this.disabled = false;
        this.style.setProperty("--os-file-upload-progress", "100%");
        this.style.setProperty("--os-file-upload-progress-as-string", "'100%'");
      });
      on(this, "livewire-upload-cancel", () => {
        removeAttribute(this, "data-loading");
        this.disabled = false;
        this.style.setProperty("--os-file-upload-progress", "0%");
        this.style.setProperty("--os-file-upload-progress-as-string", "'0%'");
      });
      on(this, "livewire-upload-error", () => {
        removeAttribute(this, "data-loading");
        this.disabled = false;
        this.style.setProperty("--os-file-upload-progress", "0%");
        this.style.setProperty("--os-file-upload-progress-as-string", "'0%'");
      });
      on(this, "livewire-upload-progress", (e) => {
        let percentage = e.detail.progress;
        this.style.setProperty("--os-file-upload-progress", `${percentage}%`);
        this.style.setProperty("--os-file-upload-progress-as-string", `'${percentage}%'`);
      });
      this._disableable.onInitAndChange((disabled) => {
        this.inputEl.disabled = disabled;
        if (this.triggerEl) {
          if (disabled) {
            removeAttribute(this.triggerEl, "tabindex");
          } else {
            setAttribute2(this.triggerEl, "tabindex", "0");
          }
        }
      });
      on(this.inputEl, "click", (e) => {
        e.stopPropagation();
        this.clear();
      });
      this.initDragListeners();
      this.initClickListeners();
    }
    addFiles(newFiles) {
      let files = Array.from(newFiles);
      let hasMultiple = this.multiple;
      if (hasMultiple) {
        this._files = [...this._files, ...files];
      } else {
        this._files = files.slice(0, 1);
      }
      this.dispatchEvent(new Event("change", { bubbles: true }));
    }
    removeFile(index) {
      this._files = this._files.filter((_, i) => i !== index);
      this.dispatchEvent(new Event("change", { bubbles: true }));
    }
    clear() {
      this.inputEl.value = null;
      this._files = [];
    }
    forwardAcceptAttribute() {
      if (this.hasAttribute("accept")) {
        this.inputEl.setAttribute("accept", this.getAttribute("accept"));
      }
    }
    configureSRInput() {
      this.inputEl.tabIndex = -1;
      Object.assign(this.inputEl.style, {
        position: "absolute",
        width: "1px",
        height: "1px",
        padding: "0",
        margin: "-1px",
        overflow: "hidden",
        clip: "rect(0, 0, 0, 0)",
        whiteSpace: "nowrap",
        border: "0"
      });
    }
    findTriggerElement() {
      let children = Array.from(this.children);
      return children.find((child) => !child.hasAttribute("data-slot"));
    }
    ensureInputsExist() {
      if (!this.inputEl || !this.querySelector('input[data-slot="receiver"]')) {
        this.inputEl = this.querySelector('input[data-slot="receiver"]');
        if (this.inputEl) {
          this.configureSRInput();
          let hasMultiple = this.hasAttribute("multiple") || this.rootEl && this.rootEl.hasAttribute("multiple");
          if (hasMultiple) {
            setAttribute2(this.inputEl, "multiple", "");
          }
          this.inputEl.disabled = this.disabled;
        }
      }
      if (!this.triggerEl || !this.contains(this.triggerEl)) {
        this.triggerEl = this.findTriggerElement();
        if (this.triggerEl && !this.disabled) {
          setAttribute2(this.triggerEl, "tabindex", "0");
        } else if (this.triggerEl && this.disabled) {
          removeAttribute(this.triggerEl, "tabindex");
        }
      }
    }
    initDragListeners() {
      if (!this.triggerEl) return;
      on(this.triggerEl, "dragenter", this._disableable.enabled((e) => {
        e.preventDefault();
        setAttribute2(this, "data-dragging", "");
        this.clear();
      }));
      on(this.triggerEl, "dragover", this._disableable.enabled((e) => {
        e.preventDefault();
        setAttribute2(this, "data-dragging", "");
      }));
      on(this.triggerEl, "drop", this._disableable.enabled((e) => {
        e.preventDefault();
        removeAttribute(this, "data-dragging");
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
          this.addFiles(e.dataTransfer.files);
        }
      }));
      on(this.triggerEl, "dragleave", (e) => {
        removeAttribute(this, "data-dragging");
      });
    }
    initClickListeners() {
      if (!this.triggerEl) return;
      on(this.triggerEl, "click", this._disableable.disabled((e) => {
        e.preventDefault();
        e.stopPropagation();
      }), { capture: true });
      on(this.triggerEl, "click", this._disableable.enabled((e) => {
        e.preventDefault();
        this.inputEl.click();
      }));
      on(this.inputEl, "change", (e) => {
        e.stopPropagation();
        if (this.inputEl.files && this.inputEl.files.length > 0 && !this.disabled) {
          this.addFiles(this.inputEl.files);
        }
      });
      on(this.triggerEl, "keydown", this._disableable.enabled((e) => {
        if (["Enter", " "].includes(e.key)) {
          e.preventDefault();
        }
        if (["Enter"].includes(e.key)) {
          this.inputEl.click();
        }
      }));
      on(this.triggerEl, "keyup", this._disableable.enabled((e) => {
        if (e.key === " ") {
          e.preventDefault();
          this.inputEl.click();
        }
      }));
      on(this.triggerEl, "focus", this._disableable.enabled(() => {
        setAttribute2(this, "data-focus", "");
      }));
      on(this.triggerEl, "blur", () => {
        removeAttribute(this, "data-focus");
      });
    }
  };
  element("file-upload", UIFileUpload);

