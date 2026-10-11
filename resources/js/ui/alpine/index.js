// js/store.js - MiniOS UI Store & Alpine Integration
export function setupAlpineOS(AlpineInstance) {
  let Alpine = AlpineInstance || window.Alpine;
  if (!Alpine || Alpine._os_registered) return;
  Alpine._os_registered = true;

  let applyAppearance = window.OS?.applyAppearance;
  if (!applyAppearance) {
    applyAppearance = (appearance) => {
      let applyDark = () => document.documentElement.classList.add('dark');
      let applyLight = () => document.documentElement.classList.remove('dark');

      if (appearance === 'system') {
        let media = window.matchMedia('(prefers-color-scheme: dark)');
        window.localStorage.removeItem('os.appearance');
        media.matches ? applyDark() : applyLight();
      } else if (appearance === 'dark') {
        window.localStorage.setItem('os.appearance', 'dark');
        applyDark();
      } else if (appearance === 'light') {
        window.localStorage.setItem('os.appearance', 'light');
        applyLight();
      }
    };
  }

  let os = Alpine.reactive({
    toast(...params) {
      let detail = { slots: {}, dataset: {} };
      if (typeof params[0] === "string") {
        detail.slots.text = params.shift();
      }
      if (typeof params[0] === "string") {
        detail.slots.heading = detail.slots.text;
        detail.slots.text = params.shift();
      }
      let options = params.shift() || {};
      if (options.text) detail.slots.text = options.text;
      if (options.heading) detail.slots.heading = options.heading;
      if (options.variant) detail.dataset.variant = options.variant;
      if (options.position) detail.dataset.position = options.position;
      if (options.link) detail.link = options.link;
      if (options.action) detail.action = options.action;
      if (options.duration !== void 0) detail.duration = options.duration;
      document.dispatchEvent(new CustomEvent("toast-show", { detail }));
    },
    modal(name) {
      return {
        show() {
          document.dispatchEvent(new CustomEvent("modal-show", { detail: { name } }));
        },
        close() {
          document.dispatchEvent(new CustomEvent("modal-close", { detail: { name } }));
        }
      };
    },
    modals() {
      return {
        close() {
          document.dispatchEvent(new CustomEvent("modal-close", { detail: {} }));
        }
      };
    },
    appearance: window.localStorage.getItem("os.appearance") || "system",
    systemAppearanceChanged: 1,
    get dark() {
      JSON.stringify(os.systemAppearanceChanged);
      if (os.appearance === "system") {
        let media2 = window.matchMedia("(prefers-color-scheme: dark)");
        return media2.matches;
      } else {
        return os.appearance === "dark";
      }
    },
    set dark(value3) {
      let current = this.dark;
      if (value3 === current) return;
      if (value3) {
        os.appearance = "dark";
      } else {
        os.appearance = "light";
      }
    },
    applyAppearance(appearance) {
      applyAppearance(appearance);
    }
  });

  window.OS = os;

  Alpine.magic("os", () => os);

  Alpine.effect(() => {
    applyAppearance(os.appearance);
  });

  document.addEventListener("livewire:navigating", (e) => {
    e.detail.onSwap(() => {
      applyAppearance(os.appearance);
    });
  });

  let media = window.matchMedia("(prefers-color-scheme: dark)");
  media.addEventListener("change", () => {
    os.systemAppearanceChanged++;
    applyAppearance(os.appearance);
  });

  Alpine.data("osInputClearable", () => ({
    clear() {
      let input = this.$el.closest("[data-os-input]")?.querySelector("input");
      if (!input) return;
      input.value = "";
      input.dispatchEvent(new Event("input", { bubbles: false }));
      input.dispatchEvent(new Event("change", { bubbles: false }));
      input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
      input.focus();
    }
  }));

  Alpine.data("osInputViewable", () => ({
    open: false,
    toggle() {
      this.open = !this.open;
      let input = this.$el.closest("[data-os-input]")?.querySelector("input");
      if (!input) return;
      input.setAttribute("type", this.open ? "text" : "password");
    },
    init() {
      let input = this.$el.closest("[data-os-input]")?.querySelector("input");
      if (!input) return;
      let observer = new MutationObserver(() => {
        let type = this.open ? "text" : "password";
        if (input.getAttribute("type") === type) return;
        input.setAttribute("type", type);
      });
      observer.observe(input, { attributes: true, attributeFilter: ["type"] });
    }
  }));

  Alpine.data("osInputCopyable", () => ({
    copied: false,
    copy() {
      this.copied = !this.copied;
      let input = this.$el.closest("[data-os-input]")?.querySelector("input");
      if (!input) return;
      navigator.clipboard && navigator.clipboard.writeText(input.value);
      input.dispatchEvent(new CustomEvent("copy", { bubbles: false }));
      setTimeout(() => this.copied = false, 2e3);
    }
  }));

  Alpine.data("osInputFile", (translations) => ({
    updateLabel(event) {
      this.$refs.name.textContent = event.target.files[1] ? event.target.files.length + " " + translations.files : event.target.files[0]?.name || translations.noFile;
    },
    init() {
      Object.defineProperty(this.$refs.input, "value", {
        ...Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, "value"),
        set(value3) {
          Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, "value").set.call(this, value3);
          if (!value3) this.dispatchEvent(new Event("change", { bubbles: true }));
        }
      });
    }
  }));

  Alpine.data("osModal", (name, scope) => ({
    handleShow(event) {
      if (event.detail.name === name) {
        if (scope && event.detail.scope === scope) {
          this.$el.showModal();
        } else if (!event.detail.scope) {
          this.$el.showModal();
        }
      }
    },
    handleClose(event) {
      if (!event.detail.name) {
        this.$el.close();
      } else if (event.detail.name === name) {
        if (scope && event.detail.scope === scope) {
          this.$el.close();
        } else if (!event.detail.scope) {
          this.$el.close();
        }
      }
    }
  }));

  Alpine.data("osCommandInputClearable", () => ({
    clear() {
      let input = this.$el.closest("[data-os-command-input]")?.querySelector("input");
      if (!input) return;
      input.value = "";
      input.dispatchEvent(new Event("input", { bubbles: false }));
      input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
      input.focus();
    }
  }));

  Alpine.data("osSelectSearchClearable", () => ({
    clear() {
      let input = this.$el.closest("[data-os-select-search]")?.querySelector("input");
      if (!input) return;
      input.value = "";
      input.dispatchEvent(new Event("input", { bubbles: false }));
      input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
      input.focus();
    }
  }));

  Alpine.data("osPillboxSearchClearable", () => ({
    clear() {
      let input = this.$el.closest("[data-os-pillbox-search]")?.querySelector("input");
      if (!input) return;
      input.value = "";
      input.dispatchEvent(new Event("input", { bubbles: false }));
      input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
      input.focus();
    }
  }));
}

if (typeof window !== "undefined") {
  if (window.Alpine) {
    setupAlpineOS(window.Alpine);
  } else {
    document.addEventListener("alpine:init", () => setupAlpineOS(window.Alpine));
  }
}

