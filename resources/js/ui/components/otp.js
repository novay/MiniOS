  // js/otp.js
  var UIOTP = class extends UIControl {
    mount() {
      this.inputEls = this.querySelectorAll("[data-os-otp-input]");
      this.length = this.inputEls.length;
      this.config = {
        mode: this.getAttribute("mode") || "numeric",
        autocomplete: this.getAttribute("autocomplete") || "one-time-code",
        autoSubmit: this.getAttribute("submit") === "auto",
        inputAriaLabelTemplate: this.getAttribute("data-os-input-aria-label") || "Character {current} of {total}"
      };
      this.state = {
        length: 0,
        onChanges: [],
        getValue: () => {
          return Array.from(this.inputEls).map((input) => input.value).join("");
        },
        setValue: (value3) => {
          let cleaned = (value3 ?? "").replace(/\s/g, "").replace(this.invalidCharacters(), "").toUpperCase();
          for (let i = 0; i < this.length; i++) {
            this.inputEls[i].value = cleaned[i] || "";
          }
          this.state.length = cleaned.length;
          this.state.onChanges.forEach((i) => i(cleaned));
        },
        notify: () => {
          let value3 = this.state.getValue();
          this.state.length = value3.length;
          this.state.onChanges.forEach((i) => i(value3));
        },
        reapply: () => {
          let value3 = this.state.getValue();
          this.state.setValue(value3);
        },
        onChange: (callback) => {
          this.state.onChanges.push(callback);
        }
      };
      if (this.hasAttribute("value")) {
        this.state.setValue(this.getAttribute("value"));
      }
      this._controllable = new Controllable(this);
      this._controllable.initial((initial) => initial && this.state.setValue(initial));
      this._controllable.getter(() => this.state.getValue());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => this.state.setValue(value3)));
      this.state.onChange(detangled(() => this._controllable.dispatch()));
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.state.getValue()
      });
      this.state.onChange((value3) => {
        this._submittable.update(value3);
        if (value3.length === this.length && this.config.autoSubmit) {
          this.closest("form")?.requestSubmit();
        }
        let activeIndex = Array.from(this.inputEls).findIndex((input) => input === document.activeElement);
        if (activeIndex > this.nextIndex()) {
          this.focusIndex(this.nextIndex());
        }
      });
      this._disableable = new Disableable(this);
      this._disableable.onInitAndChange((disabled) => {
        for (let inputEl of this.inputEls) {
          if (disabled) {
            setAttribute2(inputEl, "disabled", disabled);
          } else {
            removeAttribute(inputEl, "disabled");
          }
        }
      });
      this.updateTabIndexes(this.nextIndex());
      for (let i = 0; i < this.length; i++) {
        setAttribute2(this.inputEls[i], "autocomplete", i === 0 ? this.config.autocomplete : "off");
        setAttribute2(this.inputEls[i], "aria-label", this.config.inputAriaLabelTemplate.replace("{current}", i + 1).replace("{total}", this.length));
        let prevValue;
        let prevInsertedChar;
        on(this.inputEls[i], "beforeinput", (e) => {
          prevValue = e.target.value;
          prevInsertedChar = e.inputType === "insertText" && e.data?.length === 1 ? e.data : null;
        });
        on(this.inputEls[i], "input", (e) => {
          e.stopPropagation();
          let value3 = e.target.value;
          if (value3.length > 1 && prevInsertedChar) {
            value3 = e.target.value = prevInsertedChar;
          }
          if (value3.length > 1) {
            this.state.setValue(value3);
            this.focusIndex(this.nextIndex());
            e.preventDefault();
            return;
          }
          if (value3 == "") {
            this.state.reapply();
            if (i < this.nextIndex()) {
              this.focusIndex(i);
            } else {
              this.focusPrev(i);
            }
            return;
          }
          if (this.invalidCharacters().test(value3)) {
            e.target.value = prevValue;
            this.focusIndex(i);
            return;
          }
          if (this.config.mode !== "numeric") {
            e.target.value = value3.toUpperCase();
          }
          this.inputEls[i].blur();
          this.state.notify();
          this.focusNext(i);
        });
        on(this.inputEls[i], "keydown", (e) => {
          if (["Backspace", "Delete", "Clear"].includes(e.key)) {
            if (e.target.value === "") {
              requestAnimationFrame(() => this.focusPrev(i));
            }
          } else if (e.key === "ArrowRight") {
            this.focusNext(i);
            e.preventDefault();
          } else if (e.key === "ArrowLeft") {
            this.focusPrev(i);
            e.preventDefault();
          }
        });
        let pointerType;
        on(this.inputEls[i], "pointerdown", (e) => {
          pointerType = e.pointerType;
          if (e.pointerType !== "mouse") return;
          this.focusIndex(Math.min(i, this.nextIndex()));
          e.preventDefault();
        });
        on(this.inputEls[i], "click", (e) => {
          if (pointerType === "mouse") return;
          let index = Math.min(i, this.nextIndex());
          if (document.activeElement === this.inputEls[index]) {
            requestAnimationFrame(() => this.inputEls[index].setSelectionRange(0, 1));
          } else {
            this.focusIndex(index);
          }
        });
        on(this.inputEls[i], "focus", (e) => {
          e.target.setSelectionRange(0, 1);
          this.updateTabIndexes(i);
        });
      }
    }
    trigger() {
      return this.inputEls[this.nextIndex()];
    }
    focusIndex(index) {
      let input = this.inputEls[index];
      if (document.activeElement === input) {
        input.blur();
      }
      input.focus();
    }
    focusPrev(i) {
      this.focusIndex(Math.max(0, i - 1));
    }
    focusNext(i) {
      this.focusIndex(Math.min(i + 1, this.nextIndex()));
    }
    nextIndex() {
      return Math.min(this.state.length, this.length - 1);
    }
    updateTabIndexes(active) {
      for (let i = 0; i < this.length; i++) {
        if (i === active) {
          setAttribute2(this.inputEls[i], "tabindex", "0");
        } else {
          setAttribute2(this.inputEls[i], "tabindex", "-1");
        }
      }
    }
    invalidCharacters() {
      switch (this.config.mode) {
        case "numeric":
          return /[^\d]/g;
        case "alpha":
          return /[^a-zA-Z]/g;
        case "alphanumeric":
          return /[^a-zA-Z0-9]/g;
        default:
          throw new Error(`Unknown OTP input mode "${this.config.mode}"`);
      }
    }
  };
  element("otp", UIOTP);

