  // js/color-picker.js
  var UIColorPicker = class extends UIControl {
    // Lifecycle
    boot() {
      this.listeners = [];
      this.scrollLocked = false;
      this.pointerCleanup = null;
      this.previewEls = [];
      this.sliderEls = [];
      this.swatchContainers = [];
      this.swatchEls = [];
      this.dropperButtons = [];
      this.copyButtons = [];
      this.state = createEmptyState();
      this.committedState = createEmptyState();
      this._disableable = new Disableable(this);
      this._controllable = new Controllable(this, { bubbles: true });
      this._closeable = new Closeable(this);
      this._submittable = new Submittable(this, {
        name: this.resolveName(),
        value: null,
        includeWhenEmpty: false
      });
      let detangled = detangle();
      this.onStateChange(() => {
        this._submittable.update(this.getValue());
        this.syncUI();
      });
      this._controllable.initial((initial) => {
        if (initial !== void 0 && initial !== null && initial !== "") {
          this.setValue(initial, { dispatch: false });
        } else {
          this.setValue(this.getAttribute("value"), { dispatch: false });
        }
      });
      this._controllable.getter(() => this.getValue());
      this._controllable.setter(detangled((value3) => {
        this.setValue(value3, { dispatch: false });
        if (this.isDeferred()) {
          this.syncCommittedStateFromDraft();
        }
      }));
      this._disableable.onInitAndChange((disabled) => this.syncDisabledState(disabled));
      this.observeAttributes();
    }
    mount() {
      this.cacheDom();
      if (!this.popoverEl || !this.triggerEl) return;
      this.setupPopover();
      this.setupTrigger();
      this.setupArea();
      this.setupSliders();
      this.setupPopoverInput();
      this.setupSwatches();
      this.setupDropper();
      this.setupCopyButton();
      this.setupFieldIfExists();
      this.syncDisabledState(this._disableable.isDisabled());
      this.syncUI();
    }
    cacheDom() {
      this.popoverEl = this.querySelector("dialog");
      this.triggerEl = this.querySelector("[data-os-color-picker-trigger]") || this.querySelector("[data-os-color-picker-button]");
      this.inputEl = this.querySelector("[data-os-color-picker-input]");
      this.triggerControl = this.inputEl || this.triggerEl;
      this.popoverInputEl = this.querySelector("[data-os-color-picker-popover-input]");
      this.areaEl = this.querySelector("[data-os-color-picker-area]");
      this.areaThumb = this.querySelector("[data-os-color-picker-area-thumb]");
      this.areaHue = this.querySelector("[data-os-color-picker-area-hue]");
      this.buttonEl = this.querySelector("[data-os-color-picker-button]");
      this.swatchButton = this.querySelector("[data-os-color-picker-swatch-button]");
      this.previewEls = Array.from(this.querySelectorAll("[data-os-color-picker-preview]"));
      this.sliderEls = Array.from(this.querySelectorAll("[data-os-color-picker-slider]"));
      this.swatchContainers = Array.from(this.querySelectorAll("[data-os-color-picker-swatches]"));
      this.swatchEls = Array.from(this.querySelectorAll("[data-os-color-picker-swatch]"));
      this.dropperButtons = Array.from(this.querySelectorAll("[data-os-color-picker-dropper]"));
      this.copyButtons = Array.from(this.querySelectorAll("[data-os-color-picker-copy]"));
      this.mobileMediaQuery = window.matchMedia("(max-width: 639px)");
      this.initialFocusEl = this.querySelector("[data-os-color-picker-initial-focus]") || this.areaThumb || this.popoverInputEl;
    }
    unmount() {
      this.pointerCleanup?.();
      this.pointerCleanup = null;
    }
    observeAttributes() {
      this.attributeObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.attributeName === "disabled") {
            this.syncDisabledState(this._disableable.isDisabled());
            return;
          }
          if (mutation.attributeName === "value") {
            let attrValue = this.getAttribute("value");
            let stateValue = this.committedState.empty ? null : this.committedState.value;
            if ((attrValue ?? null) === stateValue) {
              this.syncUI();
              return;
            }
            this.setValue(attrValue, { dispatch: false });
            return;
          }
          if (mutation.attributeName === "format") {
            if (!this.state.empty) this.state.value = this.formatValue(this.state);
            if (!this.committedState.empty) this.committedState.value = this.formatValue(this.committedState);
            this.syncValueAttribute();
            this.notify();
          }
        });
      });
      this.attributeObserver.observe(this, { attributes: true, attributeFilter: ["disabled", "format", "value"] });
      this.onUnmount(() => this.attributeObserver.disconnect());
    }
    // State
    resolveName() {
      let name = this.getAttribute("name");
      if (name) return name;
      return Array.from(this.attributes).find((attribute) => attribute.name.startsWith("wire:model"))?.value;
    }
    onStateChange(callback) {
      this.listeners.push(callback);
      return () => {
        this.listeners = this.listeners.filter((listener) => listener !== callback);
      };
    }
    notify() {
      this.listeners.forEach((listener) => listener(this.state));
    }
    getFormat() {
      return this.getAttribute("format") || "hex";
    }
    supportsAlpha() {
      return ["hexa", "rgba", "hsla"].includes(this.getFormat());
    }
    getValue() {
      return this.committedState.empty ? null : this.committedState.value;
    }
    getDraftValue() {
      return this.state.empty ? null : this.state.value;
    }
    clear() {
      this.setValue(null);
      this.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
    }
    setValue(value3, options = {}) {
      let { dispatch = true } = options;
      if (value3 === void 0 || value3 === null || value3 === "") {
        this.writeState(createEmptyState(), { dispatch });
      } else {
        let parsed = parseColor(value3);
        if (!parsed) return false;
        this.writeState({
          hsv: rgbToHsv(parsed.r, parsed.g, parsed.b),
          alpha: parsed.a,
          empty: false
        }, { dispatch });
      }
      return true;
    }
    updateColor(partial, options = {}) {
      let { dispatch = true } = options;
      let nextState;
      if (this.state.empty) {
        nextState = {
          hsv: {
            h: clamp4(partial.h ?? 0, 0, 360),
            s: clamp4(partial.s ?? 0, 0, 100),
            v: clamp4(partial.v ?? 0, 0, 100)
          },
          alpha: clamp4(options.alpha ?? 1, 0, 1),
          empty: false
        };
      } else {
        nextState = {
          ...this.state,
          hsv: {
            h: clamp4(partial.h ?? this.state.hsv.h, 0, 360),
            s: clamp4(partial.s ?? this.state.hsv.s, 0, 100),
            v: clamp4(partial.v ?? this.state.hsv.v, 0, 100)
          },
          alpha: clamp4(options.alpha ?? this.state.alpha, 0, 1),
          empty: false
        };
      }
      this.writeState(nextState, { dispatch });
    }
    afterStateMutation({ dispatch = true } = {}) {
      if (this.isDeferred()) {
        this.notify();
        return;
      }
      this.syncCommittedState();
      this.notify();
      if (dispatch) this._controllable.dispatch();
    }
    writeState(state, { dispatch = true } = {}) {
      this.state = hydrateState(this, state);
      this.afterStateMutation({ dispatch });
    }
    syncCommittedState() {
      this.committedState = cloneState(this.state);
      this.syncValueAttribute();
    }
    syncCommittedStateFromDraft() {
      this.syncCommittedState();
    }
    commit() {
      this.syncCommittedState();
      this._submittable.update(this.getValue());
      this.notify();
      this._controllable.dispatch();
      this.close();
    }
    isDeferred() {
      let selectEl = this.querySelector("ui-color-picker-select");
      return !!selectEl && selectEl.getBoundingClientRect().width > 0;
    }
    setHue(value3, options = {}) {
      this.updateColor({ h: value3 }, options);
    }
    setAlpha(value3, options = {}) {
      this.updateColor({}, { ...options, alpha: value3 });
    }
    setSaturationValue(saturation, value3, options = {}) {
      this.updateColor({ s: saturation, v: value3 }, options);
    }
    getRgb() {
      if (this.state.empty) return null;
      return hsvToRgb(this.state.hsv.h, this.state.hsv.s, this.state.hsv.v);
    }
    getHsl() {
      let rgb = this.getRgb();
      if (!rgb) return null;
      return rgbToHsl(rgb.r, rgb.g, rgb.b);
    }
    formatValue(state = this.state) {
      if (state.empty) return null;
      let rgb = hsvToRgb(state.hsv.h, state.hsv.s, state.hsv.v);
      let hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
      let alpha = clamp4(state.alpha, 0, 1);
      switch (this.getFormat()) {
        case "hexa":
          return `${rgbToHex(rgb.r, rgb.g, rgb.b)}${alphaToHex(alpha)}`;
        case "rgb":
          return `rgb(${rgb.r} ${rgb.g} ${rgb.b})`;
        case "rgba":
          return `rgb(${rgb.r} ${rgb.g} ${rgb.b} / ${formatAlpha(alpha)})`;
        case "hsl":
          return `hsl(${Math.round(hsl.h)} ${Math.round(hsl.s)}% ${Math.round(hsl.l)}%)`;
        case "hsla":
          return `hsl(${Math.round(hsl.h)} ${Math.round(hsl.s)}% ${Math.round(hsl.l)}% / ${formatAlpha(alpha)})`;
        default:
          return rgbToHex(rgb.r, rgb.g, rgb.b);
      }
    }
    syncValueAttribute() {
      if (this.committedState.empty || !this.committedState.value) {
        removeAttribute(this, "value");
      } else {
        setAttribute2(this, "value", this.committedState.value);
      }
    }
    // Interaction
    toggle() {
      if (this._disableable.isDisabled()) return;
      this._dialogable?.toggle();
    }
    open() {
      if (this._disableable.isDisabled()) return;
      this._dialogable?.setState(true);
    }
    close() {
      this._dialogable?.setState(false);
    }
    trigger() {
      return this.triggerControl || this.triggerEl;
    }
    isMobileViewport() {
      return this.mobileMediaQuery?.matches ?? window.matchMedia("(max-width: 639px)").matches;
    }
    setupPopover() {
      this._dialogable = new Dialogable(this.popoverEl, {
        clickOutside: true,
        triggers: [this.triggerControl]
      });
      this._anchorable = new Anchorable(this.popoverEl, {
        reference: this.triggerEl,
        position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
        gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
        offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
      });
      this.linkPopoverAria();
      initPopover4(this, this.popoverEl, this._dialogable, this._anchorable, () => {
        queueMicrotask(() => this.initialFocusEl?.focus());
      });
      this._closeable.onClose(() => this._dialogable.setState(false));
      this._dialogable.onChange(() => {
        this.state = cloneState(this.committedState);
        this.notify();
      });
      this.getAriaTargets().forEach((el) => linkExpandedStateToPopover4(el, this._dialogable));
      preventScrollWhenPopoverIsOpen4(this, this._dialogable);
      controlPopoverWithKeyboard3(this.triggerControl, this._dialogable);
    }
    linkPopoverAria() {
      let popoverId = assignId(this.popoverEl, "color-picker-popover");
      this.getAriaTargets().forEach((el) => {
        setAttribute2(el, "aria-haspopup", "dialog");
        setAttribute2(el, "aria-controls", popoverId);
      });
    }
    setupTrigger() {
      if (this.inputEl) {
        on(this.triggerEl, "click", (event) => {
          if (event.target !== this.triggerEl && event.target !== this.inputEl) return;
          if (this.isMobileViewport()) {
            this.open();
            return;
          }
          this.inputEl.focus();
        });
        this.setupMobileTriggerInputMode(this.inputEl);
        this.setupInput(this.inputEl);
      }
      if (this.buttonEl) {
        on(this.buttonEl, "click", (event) => {
          if (event.target.closest('[aria-label="Clear color"]')) return;
          if (this._disableable.isDisabled()) return;
          this.toggle();
        });
      }
      if (this.swatchButton) {
        on(this.swatchButton, "click", (event) => {
          if (this._disableable.isDisabled()) return;
          event.stopPropagation();
          this.toggle();
        });
      }
    }
    setupInput(inputEl) {
      on(inputEl, "mousedown", (event) => {
        if (document.activeElement === inputEl) return;
        event.preventDefault();
        inputEl.focus();
      });
      on(inputEl, "focus", () => inputEl.select());
      on(inputEl, "change", (event) => {
        event.stopPropagation();
        let value3 = inputEl.value.trim();
        if (value3 === "") {
          this.setValue(null);
        } else if (!this.setValue(value3)) {
          inputEl.value = this.getValue() || "";
        }
      });
      on(inputEl, "input", (event) => event.stopPropagation());
      on(inputEl, "blur", () => {
        inputEl.value = this.getValue() || "";
      });
      on(inputEl, "keydown", (event) => {
        if (event.key === "Enter") {
          event.preventDefault();
          inputEl.blur();
        }
      });
    }
    setupArea() {
      if (!this.areaEl || !this.areaThumb) return;
      on(this.areaEl, "touchmove", (event) => {
        event.preventDefault();
      });
      on(this.areaEl, "pointerdown", (event) => {
        if (this._disableable.isDisabled()) return;
        if (event.button !== 0) return;
        event.preventDefault();
        this.setAreaFromPointer(event);
        this.areaEl.setPointerCapture(event.pointerId);
        this.pointerCleanup?.();
        let move = (moveEvent) => this.setAreaFromPointer(moveEvent);
        let up = (upEvent) => {
          this.pointerCleanup?.();
          this.pointerCleanup = null;
          document.removeEventListener("pointermove", move);
          this.areaEl.releasePointerCapture(upEvent.pointerId);
          this.dispatchEvent(new Event("change", { bubbles: false }));
        };
        document.addEventListener("pointermove", move);
        document.addEventListener("pointerup", up, { once: true });
        this.pointerCleanup = () => {
          document.removeEventListener("pointermove", move);
          document.removeEventListener("pointerup", up);
        };
      });
      on(this.areaEl, "keydown", (event) => {
        if (this._disableable.isDisabled()) return;
        let step = event.shiftKey ? 10 : 2;
        let horizontalStep = isRTL(this) ? -step : step;
        let { s, v } = this.state.hsv;
        switch (event.key) {
          case "ArrowRight":
            s += horizontalStep;
            break;
          case "ArrowLeft":
            s -= horizontalStep;
            break;
          case "ArrowUp":
            v += step;
            break;
          case "ArrowDown":
            v -= step;
            break;
          case "Home":
            s = 0;
            break;
          case "End":
            s = 100;
            break;
          case "PageUp":
            v = 100;
            break;
          case "PageDown":
            v = 0;
            break;
          default:
            return;
        }
        this.setSaturationValue(s, v);
        event.preventDefault();
      });
    }
    setAreaFromPointer(event) {
      let rect = this.areaEl.getBoundingClientRect();
      let horizontalRatio = isRTL(this) ? (rect.right - event.clientX) / rect.width : (event.clientX - rect.left) / rect.width;
      let saturation = clamp4(horizontalRatio * 100, 0, 100);
      let value3 = clamp4((1 - (event.clientY - rect.top) / rect.height) * 100, 0, 100);
      this.setSaturationValue(saturation, value3, { dispatch: false });
      this.dispatchEvent(new Event("input", { bubbles: false }));
    }
    setupSliders() {
      this.sliderEls.forEach((slider) => {
        let channel = slider.getAttribute("data-channel");
        on(slider, "input", (event) => {
          event.stopPropagation();
          this.updateSliderValue(channel, parseFloat(slider.value));
          this.dispatchEvent(new Event("input", { bubbles: false }));
        });
        on(slider, "change", (event) => {
          event.stopPropagation();
          this.dispatchEvent(new Event("change", { bubbles: false }));
        });
      });
    }
    updateSliderValue(channel, value3) {
      if (channel === "alpha") {
        this.setAlpha(value3 / 100, { dispatch: false });
      } else {
        this.setHue(value3, { dispatch: false });
      }
    }
    setupPopoverInput() {
      if (!this.popoverInputEl) return;
      this.setupInput(this.popoverInputEl);
    }
    setupMobileTriggerInputMode(inputEl) {
      let mq = this.mobileMediaQuery;
      let apply3 = () => {
        if (mq.matches) setAttribute2(inputEl, "inputmode", "none");
        else removeAttribute(inputEl, "inputmode");
      };
      apply3();
      mq.addEventListener("change", apply3);
      this.onUnmount(() => mq.removeEventListener("change", apply3));
      on(inputEl, "mousedown", (event) => {
        if (!mq.matches) return;
        event.preventDefault();
        event.stopImmediatePropagation();
      });
    }
    setupSwatches() {
      this.swatchContainers.forEach((container) => {
        on(container, "click", (event) => {
          let swatch = event.target.closest("[data-os-color-picker-swatch]");
          if (!swatch || this._disableable.isDisabled()) return;
          this.commitSwatch(swatch);
        });
        on(container, "keydown", (event) => {
          if (this._disableable.isDisabled()) return;
          let current = event.target.closest("[data-os-color-picker-swatch]");
          if (!current) return;
          let swatches = Array.from(container.querySelectorAll("[data-os-color-picker-swatch]"));
          let index = swatches.indexOf(current);
          let columns = deriveSwatchColumns(swatches);
          let target = null;
          switch (event.key) {
            case "ArrowRight":
              target = swatches[index + 1];
              break;
            case "ArrowLeft":
              target = swatches[index - 1];
              break;
            case "ArrowDown":
              target = swatches[index + columns];
              break;
            case "ArrowUp":
              target = swatches[index - columns];
              break;
            case "Home":
              target = swatches[0];
              break;
            case "End":
              target = swatches[swatches.length - 1];
              break;
            case "Enter":
            case " ":
              this.commitSwatch(current);
              event.preventDefault();
              return;
            default:
              return;
          }
          if (target) {
            this.focusSwatch(target);
            event.preventDefault();
          }
        });
      });
    }
    commitSwatch(swatch) {
      let value3 = swatch.getAttribute("data-value") || swatch.getAttribute("value");
      if (!value3) return;
      this.setValue(value3);
      this.announceSelection(this.getSwatchLabel(swatch));
      if (this.isDeferred() && this.isMobileViewport()) {
        this.commit();
      }
    }
    getSwatchLabel(swatch) {
      return swatch.getAttribute("aria-label") || swatch.getAttribute("title") || swatch.getAttribute("data-value") || swatch.getAttribute("value");
    }
    focusSwatch(swatch) {
      this.swatchEls.forEach((el) => {
        setAttribute2(el, "tabindex", el === swatch ? "0" : "-1");
      });
      swatch.focus();
    }
    announceSelection(label) {
      let region = this.querySelector("[data-os-color-picker-announce]");
      if (!region) return;
      region.textContent = "";
      setTimeout(() => {
        region.textContent = `${label} selected`;
      }, 150);
    }
    setupDropper() {
      this.dropperButtons.forEach((button) => {
        if (!window.EyeDropper) {
          setAttribute2(button, "disabled", "");
          setAttribute2(button, "hidden", "");
          return;
        }
        on(button, "click", async () => {
          try {
            let eyeDropper = new EyeDropper();
            let result = await eyeDropper.open();
            if (result.sRGBHex) {
              this.setValue(result.sRGBHex);
            }
          } catch {
          }
        });
      });
    }
    setupCopyButton() {
      this.copyButtons.forEach((button) => {
        on(button, "click", (event) => {
          event.stopPropagation();
          let value3 = this.getValue();
          if (value3 && navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(value3);
          }
        });
      });
    }
    // Rendering
    syncUI() {
      let committedValue = this.getValue();
      let draftValue = this.getDraftValue();
      this.previewEls.forEach((el) => {
        el.style.backgroundColor = committedValue ? this.getCheckerboardColor(this.committedState) : "transparent";
      });
      if (this.inputEl && document.activeElement !== this.inputEl) {
        this.inputEl.value = committedValue || "";
      }
      if (this.popoverInputEl && document.activeElement !== this.popoverInputEl) {
        this.popoverInputEl.value = draftValue || "";
      }
      this.syncTriggerValueText(committedValue);
      if (this.areaHue) {
        this.areaHue.style.backgroundColor = this.getAreaColor(this.state);
      }
      this.syncAreaThumb();
      this.syncSliders();
      this.syncSwatches(draftValue);
      this.syncSwatchButtonLabel(committedValue);
      this.syncSwatchTabIndex();
    }
    syncTriggerValueText(value3) {
      let valueSpan = this.querySelector("[data-os-color-picker-value]");
      if (valueSpan) valueSpan.textContent = value3 || "";
    }
    syncAreaThumb() {
      if (!this.areaThumb) return;
      let saturation = Math.round(this.state.hsv.s);
      let brightness = Math.round(this.state.hsv.v);
      this.areaThumb.style.insetInlineStart = `${this.state.hsv.s}%`;
      this.areaThumb.style.top = `${100 - this.state.hsv.v}%`;
      setAttribute2(this.areaThumb, "aria-valuenow", saturation);
      setAttribute2(this.areaThumb, "aria-valuetext", `Saturation ${saturation}%, brightness ${brightness}%`);
    }
    syncSliders() {
      this.sliderEls.forEach((slider) => {
        let channel = slider.getAttribute("data-channel");
        if (channel === "alpha") {
          slider.value = Math.round(this.state.alpha * 100);
          this.syncAlphaTrack(slider);
        } else {
          slider.value = Math.round(this.state.hsv.h);
        }
      });
    }
    syncAlphaTrack(slider) {
      let track = slider.querySelector("[data-os-color-picker-slider-track]");
      if (track) {
        track.style.background = `linear-gradient(to right, transparent, ${this.getSolidColor(this.state)})`;
      }
    }
    syncSwatches(draftValue) {
      this.swatchEls.forEach((swatch) => {
        let swatchValue = swatch.getAttribute("data-value") || swatch.getAttribute("value");
        let selected = draftValue && swatchValue && colorsMatch(swatchValue, draftValue);
        if (selected) setAttribute2(swatch, "data-selected", "");
        else removeAttribute(swatch, "data-selected");
        setAttribute2(swatch, "aria-selected", selected ? "true" : "false");
      });
    }
    syncSwatchButtonLabel(committedValue) {
      if (!this.swatchButton) return;
      if (this.swatchButton.dataset.baseLabel === void 0) {
        this.swatchButton.dataset.baseLabel = this.swatchButton.getAttribute("aria-label") || "";
      }
      let base = this.swatchButton.dataset.baseLabel;
      let committedLabel = committedValue ? this.getCommittedSwatchLabel(committedValue) : null;
      setAttribute2(this.swatchButton, "aria-label", committedLabel ? `${base}, ${committedLabel}` : base);
    }
    getCommittedSwatchLabel(committedValue) {
      let swatch = this.swatchEls.find((s) => {
        let value3 = s.getAttribute("data-value") || s.getAttribute("value");
        return value3 && colorsMatch(value3, committedValue);
      });
      return swatch ? this.getSwatchLabel(swatch) : committedValue;
    }
    syncSwatchTabIndex() {
      if (!this.swatchEls.length) return;
      let focused = this.querySelector("[data-os-color-picker-swatch]:focus");
      if (focused) return;
      let selected = this.swatchEls.find((s) => s.getAttribute("aria-selected") === "true");
      let target = selected || this.swatchEls[0];
      this.swatchEls.forEach((s) => setAttribute2(s, "tabindex", s === target ? "0" : "-1"));
    }
    syncDisabledState(disabled) {
      this.querySelectorAll("button, input").forEach((el) => {
        if (disabled) setAttribute2(el, "disabled", "");
        else removeAttribute(el, "disabled");
      });
      this.previewEls.forEach((el) => {
        el.style.opacity = disabled ? "0.65" : "";
      });
    }
    // Presentation helpers
    getAreaColor(state = this.state) {
      return `hsl(${state.hsv.h} 100% 50%)`;
    }
    getSolidColor(state = this.state) {
      if (state.empty) return "transparent";
      let rgb = hsvToRgb(state.hsv.h, state.hsv.s, state.hsv.v);
      return `rgb(${rgb.r} ${rgb.g} ${rgb.b})`;
    }
    getCheckerboardColor(state = this.state) {
      if (state.empty) return "transparent";
      let rgb = hsvToRgb(state.hsv.h, state.hsv.s, state.hsv.v);
      return `rgb(${rgb.r} ${rgb.g} ${rgb.b} / ${formatAlpha(state.alpha)})`;
    }
    setupFieldIfExists() {
      let field = this.closest("ui-field");
      if (!field) return;
      queueMicrotask(() => {
        if (field.label) {
          let clickListener = on(field.label, "click", () => this.triggerControl?.focus());
          this.onUnmount(() => clickListener.off());
        }
        if (field.label && this.triggerControl) setAttribute2(this.triggerControl, "aria-labelledby", field.label.id);
        if (field.description && this.triggerControl) setAttribute2(this.triggerControl, "aria-describedby", field.description.id);
      });
    }
    getAriaTargets() {
      return [this.triggerControl, this.swatchButton].filter(Boolean);
    }
  };
  function createEmptyState() {
    return {
      hsv: { h: 0, s: 0, v: 0 },
      alpha: 1,
      value: null,
      empty: true
    };
  }
  function cloneState(state) {
    return {
      hsv: { ...state.hsv },
      alpha: state.alpha,
      value: state.value,
      empty: state.empty
    };
  }
  function hydrateState(picker, state) {
    let nextState = cloneState(state);
    nextState.value = nextState.empty ? null : picker.formatValue(nextState);
    return nextState;
  }
  function clamp4(value3, min2, max2) {
    return Math.min(Math.max(value3, min2), max2);
  }
  function normalizeHue(value3) {
    let normalized = value3 % 360;
    return normalized < 0 ? normalized + 360 : normalized;
  }
  function formatAlpha(value3) {
    return Number(value3.toFixed(2)).toString();
  }
  function alphaToHex(alpha) {
    return Math.round(alpha * 255).toString(16).padStart(2, "0");
  }
  function rgbToHex(r, g, b) {
    return `#${[r, g, b].map((component) => component.toString(16).padStart(2, "0")).join("")}`;
  }
  function normalizeColorInput(input) {
    let value3 = input.trim();
    if (/^[0-9a-fA-F]{3}$|^[0-9a-fA-F]{6}$|^[0-9a-fA-F]{8}$/.test(value3)) {
      return `#${value3}`;
    }
    if (/^\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}$/.test(value3)) {
      return `rgb(${value3.replace(/,/g, " ").replace(/\s+/g, " ")})`;
    }
    return value3;
  }
  var _colorCtx;
  function parseColor(input) {
    if (!input) return null;
    let normalized = normalizeColorInput(input);
    if (!_colorCtx) _colorCtx = document.createElement("canvas").getContext("2d");
    let ctx = _colorCtx;
    ctx.fillStyle = "#000000";
    ctx.fillStyle = normalized;
    let parsed = ctx.fillStyle;
    if (parsed === "#000000" && normalized !== "#000000" && normalized !== "#000" && normalized !== "black") {
      ctx.fillStyle = "#ffffff";
      ctx.fillStyle = normalized;
      if (ctx.fillStyle === "#ffffff") return null;
      parsed = ctx.fillStyle;
    }
    if (parsed.startsWith("#")) {
      let hex = parsed.slice(1);
      return {
        r: parseInt(hex.slice(0, 2), 16),
        g: parseInt(hex.slice(2, 4), 16),
        b: parseInt(hex.slice(4, 6), 16),
        a: hex.length === 8 ? parseInt(hex.slice(6, 8), 16) / 255 : 1
      };
    }
    let match = parsed.match(/[\d.]+/g);
    if (!match) return null;
    return {
      r: parseFloat(match[0]),
      g: parseFloat(match[1]),
      b: parseFloat(match[2]),
      a: match[3] === void 0 ? 1 : parseFloat(match[3])
    };
  }
  function rgbToHsv(r, g, b) {
    r /= 255;
    g /= 255;
    b /= 255;
    let max2 = Math.max(r, g, b);
    let min2 = Math.min(r, g, b);
    let delta = max2 - min2;
    let h = 0;
    if (delta !== 0) {
      if (max2 === r) h = 60 * ((g - b) / delta % 6);
      else if (max2 === g) h = 60 * ((b - r) / delta + 2);
      else h = 60 * ((r - g) / delta + 4);
    }
    return {
      h: normalizeHue(h),
      s: max2 === 0 ? 0 : delta / max2 * 100,
      v: max2 * 100
    };
  }
  function hsvToRgb(h, s, v) {
    s /= 100;
    v /= 100;
    let c = v * s;
    let x = c * (1 - Math.abs(h / 60 % 2 - 1));
    let m = v - c;
    let r = 0;
    let g = 0;
    let b = 0;
    if (h >= 0 && h < 60) [r, g, b] = [c, x, 0];
    else if (h < 120) [r, g, b] = [x, c, 0];
    else if (h < 180) [r, g, b] = [0, c, x];
    else if (h < 240) [r, g, b] = [0, x, c];
    else if (h < 300) [r, g, b] = [x, 0, c];
    else [r, g, b] = [c, 0, x];
    return {
      r: Math.round((r + m) * 255),
      g: Math.round((g + m) * 255),
      b: Math.round((b + m) * 255)
    };
  }
  function rgbToHsl(r, g, b) {
    r /= 255;
    g /= 255;
    b /= 255;
    let max2 = Math.max(r, g, b);
    let min2 = Math.min(r, g, b);
    let h = 0;
    let s = 0;
    let l = (max2 + min2) / 2;
    if (max2 !== min2) {
      let d = max2 - min2;
      s = l > 0.5 ? d / (2 - max2 - min2) : d / (max2 + min2);
      switch (max2) {
        case r:
          h = (g - b) / d + (g < b ? 6 : 0);
          break;
        case g:
          h = (b - r) / d + 2;
          break;
        default:
          h = (r - g) / d + 4;
          break;
      }
      h /= 6;
    }
    return {
      h: h * 360,
      s: s * 100,
      l: l * 100
    };
  }
  function colorsMatch(a, b) {
    let parsedA = parseColor(a);
    let parsedB = parseColor(b);
    if (!parsedA || !parsedB) return false;
    return parsedA.r === parsedB.r && parsedA.g === parsedB.g && parsedA.b === parsedB.b;
  }
  function deriveSwatchColumns(swatches) {
    if (!swatches.length) return 1;
    let firstTop = swatches[0].offsetTop;
    let cols = 0;
    for (let swatch of swatches) {
      if (swatch.offsetTop !== firstTop) break;
      cols++;
    }
    return cols || 1;
  }
  var UIColorPickerSelect = class extends UIElement {
    mount() {
      this.picker = this.closest("ui-color-picker");
      if (!this.picker) return;
      initFauxButton(this, () => this.picker._disableable?.isDisabled?.() ?? false, () => {
        this.picker.commit();
      });
    }
  };
  element("color-picker", UIColorPicker);
  element("color-picker-select", UIColorPickerSelect);
  function linkExpandedStateToPopover4(el, dialogable) {
    let refresh = () => {
      setAttribute2(el, "aria-expanded", dialogable.getState() ? "true" : "false");
      dialogable.getState() ? setAttribute2(el, "data-open", "") : removeAttribute(el, "data-open");
    };
    dialogable.onChange(() => refresh());
    refresh();
  }
  function initPopover4(root, popover, dialogable, anchorable, onOpen = () => {
  }) {
    let refresh = () => {
      Array.from([root, popover]).forEach((el) => {
        dialogable.getState() ? setAttribute2(el, "data-open", "") : removeAttribute(el, "data-open");
      });
      if (dialogable.getState()) {
        anchorable.reposition();
        onOpen();
      } else {
        anchorable.cleanup();
      }
    };
    dialogable.onChange(() => refresh());
    refresh();
    dialogable.onChange(() => {
      if (dialogable.getState()) {
        root.dispatchEvent(new Event("open", { bubbles: false, cancelable: false }));
      } else {
        root.dispatchEvent(new Event("close", { bubbles: false, cancelable: false }));
      }
    });
  }
  function controlPopoverWithKeyboard3(el, dialogable) {
    on(el, "keydown", (event) => {
      if (event.key === "ArrowDown" && !dialogable.getState()) {
        dialogable.setState(true);
        event.preventDefault();
        event.stopImmediatePropagation();
      } else if (event.key === "Escape" && dialogable.getState()) {
        dialogable.setState(false);
        event.preventDefault();
        event.stopImmediatePropagation();
      }
    });
  }
  function preventScrollWhenPopoverIsOpen4(root, dialogable) {
    let { lock, unlock } = lockScroll(dialogable.el);
    let isMobile = () => !window.matchMedia("(min-width: 640px)").matches;
    dialogable.onChange(() => {
      let shouldLock = dialogable.getState() && isMobile();
      if (shouldLock && !root.scrollLocked) {
        lock();
        root.scrollLocked = true;
      } else if (!shouldLock && root.scrollLocked) {
        unlock();
        root.scrollLocked = false;
      }
    });
    root.onUnmount(() => {
      if (root.scrollLocked) {
        unlock();
        root.scrollLocked = false;
      }
    });
  }

