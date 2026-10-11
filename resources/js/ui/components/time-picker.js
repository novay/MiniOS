  // js/time-picker/selectable.js
  var SelectableModes = class {
    static SINGLE = "single";
    static MULTIPLE = "multiple";
  };
  var ObservableTrigger = class {
    static SELECTION = "selection";
    static ACTIVATION = "activation";
    static VIEW_CHANGE = "view-change";
  };
  var Selectable2 = class _Selectable {
    constructor(selection, config, observable) {
      this.config = config;
      this.observable = observable;
      this.selection = selection;
    }
    select(value3) {
      let previousSelection = this.selection;
      this.selection = this.selection.select(value3, this.config);
      if (this.selection.shouldNotify(previousSelection)) this.observable.notify(ObservableTrigger.SELECTION);
      else this.observable.notify(ObservableTrigger.ACTIVATION);
    }
    setValue(value3) {
      let previousSelection = this.selection;
      this.selection = _Selectable.createSelectionFromValue(value3, this.config);
      if (this.selection.shouldNotify(previousSelection)) this.observable.notify(ObservableTrigger.SELECTION);
      else this.observable.notify(ObservableTrigger.ACTIVATION);
    }
    getValue() {
      return this.selection.value();
    }
    isSelectable(value3) {
      return this.selection.selectable(value3, this.config);
    }
    /** Passthrough methods */
    contains(...params) {
      return this.selection.contains(...params);
    }
    hasSelection() {
      return this.selection.hasSelection();
    }
    lowerBound(...params) {
      return this.selection.lowerBound(...params);
    }
    upperBound(...params) {
      return this.selection.upperBound(...params);
    }
    display(...params) {
      return this.selection.display(this.config, ...params);
    }
    attributes(...params) {
      return this.selection.attributes(...params);
    }
    static createSelectionFromValue(value3, config) {
      if (config.mode === SelectableModes.MULTIPLE) {
        value3 ??= [];
        return new MultipleSelection(value3);
      } else {
        if (!value3) return new NoneSelection();
        return new SingleSelection(value3);
      }
    }
    static createFromValueStringAttribute(value3, config, observable) {
      let selection = _Selectable.createSelectionFromValueStringAttribute(value3, config);
      return new _Selectable(selection, config, observable);
    }
    static createSelectionFromValueStringAttribute(value3, config) {
      if (config.mode === SelectableModes.MULTIPLE) {
        value3 = value3 ? value3.split(",").map((i) => i.trim()) : [];
      }
      return _Selectable.createSelectionFromValue(value3, config);
    }
  };
  var Selection = class {
    contains() {
    }
    shouldNotify(previousSelection) {
      return true;
    }
    hasSelection() {
      return false;
    }
    lowerBound(fallback) {
      return value(fallback);
    }
    upperBound(fallback) {
      return this.lowerBound(fallback);
    }
    selectable(value3, config) {
      return true;
    }
    display(config) {
    }
    value() {
    }
    attributes(el, value3) {
      if (this.contains(value3)) {
        setAttribute2(el, "data-selected", "");
        setAttribute2(el, "aria-selected", "true");
      } else {
        removeAttribute(el, "data-selected");
        setAttribute2(el, "aria-selected", "false");
      }
    }
  };
  var NoneSelection = class _NoneSelection extends Selection {
    contains() {
      return false;
    }
    shouldNotify(selection) {
      return !(selection instanceof _NoneSelection);
    }
    hasSelection() {
      return false;
    }
    lowerBound(fallback) {
      return value(fallback);
    }
    select(value3) {
      return new SingleSelection(value3);
    }
    display(config) {
      return "";
    }
    value() {
      return null;
    }
  };
  var SingleSelection = class _SingleSelection extends Selection {
    constructor(value3) {
      super();
      this._value = value3;
    }
    contains(value3) {
      return this._value === value3;
    }
    shouldNotify(previousSelection) {
      if (previousSelection instanceof _SingleSelection && this._value === previousSelection._value) return false;
      return true;
    }
    hasSelection() {
      return true;
    }
    lowerBound() {
      return this._value;
    }
    select(value3) {
      if (this._value === value3) return new NoneSelection();
      return new _SingleSelection(value3);
    }
    display(config) {
      return formatTime(this._value, config.timeFormat, config.locale);
    }
    value() {
      return this._value;
    }
  };
  var MultipleSelection = class _MultipleSelection extends Selection {
    constructor(values) {
      super();
      this._values = values.sort((a, b) => timeInMinutes(a) < timeInMinutes(b) ? -1 : 1);
    }
    contains(value3) {
      return this._values.some((i) => i === value3);
    }
    shouldNotify(previousSelection) {
      if (previousSelection instanceof _MultipleSelection && this._values.every((i) => previousSelection._values.includes(i)) && previousSelection._values.every((i) => this._values.includes(i))) return false;
      return true;
    }
    hasSelection() {
      return this._values.length > 0;
    }
    lowerBound(fallback) {
      let sortedValues = this._values.sort((a, b) => timeInMinutes(a) < timeInMinutes(b) ? -1 : 1);
      return sortedValues[0] || value(fallback);
    }
    upperBound(fallback) {
      let sortedValues = this._values.sort((a, b) => timeInMinutes(a) < timeInMinutes(b) ? -1 : 1);
      return sortedValues[sortedValues.length - 1] || value(fallback);
    }
    select(value3) {
      let containsValue = this._values.some((i) => i === value3);
      if (containsValue) return new _MultipleSelection(this._values.filter((i) => i !== value3));
      return new _MultipleSelection([...this._values, value3]);
    }
    display(config) {
      return this._values.sort((a, b) => timeInMinutes(a) < timeInMinutes(b) ? -1 : 1).map((i) => formatTime(i, config.timeFormat, config.locale)).join(", ");
    }
    value() {
      return this._values;
    }
  };
  function value(i = null) {
    return typeof i === "function" ? i() : i;
  }

  // js/time-picker/trigger.js
  var UITimePickerTrigger = class extends UIElement {
    boot() {
      this.picker = this.closest("ui-time-picker");
      let inputs = this.querySelectorAll("input");
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
      if (inputs.length === 0) return;
      if (inputs.length < 2) throw new Error("Time picker inputs missing");
      this.hourInput = inputs[0];
      this.minuteInput = inputs[1];
      this.meridiemInput = inputs[2] || null;
      if (this.meridiemInput && this.use24HourFormat()) {
        this.meridiemInput.style.display = "none";
        this.meridiemInput = null;
      }
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          this.hourInput.setAttribute("disabled", "");
          this.minuteInput.setAttribute("disabled", "");
          this.meridiemInput?.setAttribute("disabled", "");
        } else {
          this.hourInput.removeAttribute("disabled");
          this.minuteInput.removeAttribute("disabled");
          this.meridiemInput?.removeAttribute("disabled");
        }
      });
      this.hourInput.placeholder = this.meridiemInput ? "hh" : "HH";
      this.minuteInput.placeholder = "mm";
      this.meridiemInput && (this.meridiemInput.placeholder = getLocalisedMeridiem(0, this.picker.config.locale) || "AM");
      this.clearInputs();
      initCursorSelectionListeners(this.hourInput);
      initCursorSelectionListeners(this.minuteInput);
      this.meridiemInput && initCursorSelectionListeners(this.meridiemInput);
      this.hourInput.addEventListener("keydown", (e) => {
        if (e.key === "ArrowRight") return this.minuteInput.focus();
      });
      this.minuteInput.addEventListener("keydown", (e) => {
        if (e.key === "ArrowLeft") return this.hourInput.focus();
        if (e.key === "ArrowRight") return this.meridiemInputForTimeFormat()?.focus();
      });
      this.meridiemInput?.addEventListener("keydown", (e) => {
        if (e.key === "ArrowLeft") return this.minuteInput.focus();
      });
      blockDefaultInputs(this.hourInput);
      blockDefaultInputs(this.minuteInput);
      this.meridiemInput && blockDefaultInputs(this.meridiemInput, { isMeridiem: true });
      this.initHourInputListeners();
      this.initMinuteInputListeners();
      this.meridiemInput && this.initMeridiemInputListeners();
      this.meridiemInput && this.setMeridiemWidthBasedOnLocale();
      this._lastFocusedInput = null;
      this.addEventListener("mousedown", (e) => {
        if (e.target.closest("input")) return;
        if (this.picker._popoverable?.state) return;
        let active = document.activeElement;
        if (active === this.hourInput || active === this.minuteInput || active === this.meridiemInput) {
          this._lastFocusedInput = active;
        } else {
          this._lastFocusedInput = null;
        }
      });
      this.picker.observable.subscribe(ObservableTrigger.SELECTION, () => {
        this.updateInputsFromSelectable(this.picker.selectable);
      });
      this.updateInputsFromSelectable(this.picker.selectable);
    }
    focus() {
      if (this.hourInput) return this.hourInput.focus();
      this.querySelector("button,ui-button")?.focus();
    }
    use24HourFormat() {
      if (this.picker.config.timeFormat === "24-hour") return true;
      if (this.picker.config.timeFormat === "12-hour") return false;
      return localeIs24HourFormat(this.picker.config.locale);
    }
    meridiemInputForTimeFormat() {
      if (this.use24HourFormat()) return null;
      return this.meridiemInput;
    }
    processTime() {
      let meridiemInput = this.meridiemInputForTimeFormat();
      if (this.hourInput.value === "" || this.minuteInput.value === "") return;
      if (meridiemInput && meridiemInput.value === "") return;
      let meridiem = null;
      if (meridiemInput?.value) {
        let locale = this.picker.config.locale;
        let am = getLocalisedMeridiem(0, locale);
        meridiem = meridiemInput.value === am ? "AM" : "PM";
      }
      let time = getTime(
        this.hourInput.value,
        this.minuteInput.value,
        meridiem
      );
      if (!time) return;
      this.updateSelectable(time);
      this.updateInputsFromSelectable(this.picker.selectable);
    }
    updateSelectable(time) {
      this.picker.selectable.setValue(time);
    }
    updateInputsFromSelectable(selectable) {
      let time = selectable.getValue();
      this.updateInputs(time);
    }
    // Update the hour and meridiem display without touching minute...
    // When forceMeridiem is true (e.g., from arrow keys), always update meridiem.
    // When false (e.g., from typing), only update if meridiem is empty or the hour forces a conversion (13+, 0).
    updateHourDisplay(hours, { forceMeridiem = false } = {}) {
      hours = parseInt(hours);
      if (isNaN(hours)) return;
      let meridiemInput = this.meridiemInputForTimeFormat();
      if (meridiemInput) {
        let hasExistingMeridiem = meridiemInput.value && !("showingPlaceholder" in meridiemInput.dataset);
        if (forceMeridiem || !hasExistingMeridiem || hours > 12 || hours === 0) {
          let meridiem = getLocalisedMeridiem(hours, this.picker.config.locale);
          meridiemInput.value = meridiem;
          meridiemInput.style.color = "";
          delete meridiemInput.dataset.showingPlaceholder;
        }
        if (hours > 12) {
          hours = hours - 12;
        } else if (hours === 0) {
          hours = 12;
        }
      }
      this.hourInput.value = hours.toString().padStart(2, "0");
      this.hourInput.style.color = "";
      this.hourInput.classList.remove("font-mono");
    }
    // Time is in 24 hour format
    updateInputs(time) {
      if (!time) return this.clearInputs();
      removeAttribute(this.picker, "data-empty");
      let [hours, minutes] = time.split(":");
      let meridiem = null;
      let meridiemInput = this.meridiemInputForTimeFormat();
      if (meridiemInput) {
        hours = parseInt(hours);
        meridiem = getLocalisedMeridiem(hours, this.picker.config.locale);
        if (hours > 12) {
          hours = hours - 12;
        } else if (hours === 0) {
          hours = 12;
        }
        hours = hours.toString();
      }
      this.hourInput.value = hours.padStart(2, "0");
      this.hourInput.style.color = "";
      this.hourInput.classList.remove("font-mono");
      this.minuteInput.value = minutes.padStart(2, "0");
      this.minuteInput.style.color = "";
      this.minuteInput.classList.remove("font-mono");
      if (meridiemInput) {
        meridiemInput.value = meridiem;
        meridiemInput.style.color = "";
        delete meridiemInput.dataset.showingPlaceholder;
      }
    }
    clearInputs() {
      this.hourInput.value = "";
      this.hourInput.style.color = "";
      this.hourInput.classList.add("font-mono");
      this.minuteInput.value = "";
      this.minuteInput.style.color = "";
      this.minuteInput.classList.add("font-mono");
      if (this.meridiemInput) {
        this.meridiemInput.value = "";
        this.meridiemInput.style.color = "";
      }
      let focused = [this.hourInput, this.minuteInput, this.meridiemInput].find((i) => i === document.activeElement);
      if (focused) highlightInputContents(focused);
      setAttribute2(this.picker, "data-empty", "");
    }
    initHourInputListeners() {
      let isEditing = false;
      this.hourInput.addEventListener("keydown", (e) => {
        if (e.key === "Backspace") {
          if (this.hourInput.value === "" || this.hourInput.value === this.hourInput.placeholder) return;
          isEditing = false;
          this.hourInput.value = "";
          this.hourInput.style.color = "";
          this.hourInput.classList.add("font-mono");
          highlightInputContents(this.hourInput);
          return;
        }
        if (!["ArrowUp", "ArrowDown"].includes(e.key)) return;
        e.preventDefault();
        e.stopPropagation();
        let hours = parseInt(this.hourInput.value);
        let meridiemInput = this.meridiemInputForTimeFormat();
        if (!isNaN(hours) && meridiemInput && meridiemInput.value && !("showingPlaceholder" in meridiemInput.dataset)) {
          let isPM = meridiemInput.value === getLocalisedMeridiem(12, this.picker.config.locale);
          if (isPM && hours !== 12) hours += 12;
          if (!isPM && hours === 12) hours = 0;
        }
        if (e.key === "ArrowUp") {
          if (isNaN(hours) || hours === 23) {
            hours = 0;
          } else {
            hours++;
          }
        } else if (e.key === "ArrowDown") {
          if (isNaN(hours) || hours === 0) {
            hours = 23;
          } else {
            hours--;
          }
        }
        this.updateHourDisplay(hours, { forceMeridiem: true });
        this.processTime();
        highlightInputContents(this.hourInput);
      });
      this.hourInput.addEventListener("keydown", (e) => {
        if (!/[0-9]/.test(e.key)) return;
        let number = parseInt(e.key);
        if (this.hourInput.value === "" || !isEditing) {
          isEditing = true;
          this.hourInput.value = `0${number}`;
          this.hourInput.style.color = "";
          this.hourInput.classList.remove("font-mono");
          number > 2 ? this.minuteInput.focus() : highlightInputContents(this.hourInput);
          return;
        }
        let existingNumber = parseInt(this.hourInput.value);
        if (existingNumber === 2 && (number >= 0 && number <= 3)) {
          this.hourInput.value = `${existingNumber}${number}`;
          this.minuteInput.focus();
          return;
        }
        if (existingNumber === 1 || existingNumber === 0) {
          this.hourInput.value = `${existingNumber}${number}`;
          this.minuteInput.focus();
          return;
        }
        this.hourInput.value = `0${number}`;
        number > 2 ? this.minuteInput.focus() : highlightInputContents(this.hourInput);
      });
      this.hourInput.addEventListener("blur", (e) => {
        isEditing = false;
        if (this.hourInput.value !== "" && this.hourInput.value !== this.hourInput.placeholder) {
          this.updateHourDisplay(this.hourInput.value);
        }
        this.processTime();
      });
    }
    initMinuteInputListeners() {
      let isEditing = false;
      this.minuteInput.addEventListener("keydown", (e) => {
        if (e.key === "Backspace") {
          if (this.minuteInput.value === "" || this.minuteInput.value === this.minuteInput.placeholder) {
            this.hourInput.focus();
            return;
          }
          isEditing = false;
          this.minuteInput.value = "";
          this.minuteInput.style.color = "";
          this.minuteInput.classList.add("font-mono");
          highlightInputContents(this.minuteInput);
          return;
        }
        if (!["ArrowUp", "ArrowDown"].includes(e.key)) return;
        e.preventDefault();
        e.stopPropagation();
        let number = parseInt(this.minuteInput.value);
        if (e.key === "ArrowUp") {
          if (isNaN(number) || number === 59) {
            number = 0;
          } else {
            number = number + 1;
          }
        } else if (e.key === "ArrowDown") {
          if (isNaN(number) || number === 0) {
            number = 59;
          } else {
            number = number - 1;
          }
        }
        this.minuteInput.value = number.toString().padStart(2, "0");
        this.minuteInput.style.color = "";
        this.minuteInput.classList.remove("font-mono");
        this.processTime();
        highlightInputContents(this.minuteInput);
      });
      this.minuteInput.addEventListener("keydown", (e) => {
        if (!/[0-9]/.test(e.key)) return;
        let number = parseInt(e.key);
        if (this.minuteInput.value === "" || !isEditing) {
          isEditing = true;
          this.minuteInput.value = `0${number}`;
          this.minuteInput.style.color = "";
          this.minuteInput.classList.remove("font-mono");
          this.processTime();
          number > 5 ? this.meridiemInputForTimeFormat()?.focus() : highlightInputContents(this.minuteInput);
          return;
        }
        let existingNumber = parseInt(this.minuteInput.value);
        if (existingNumber <= 5) {
          this.minuteInput.value = `${existingNumber}${number}`;
          this.processTime();
          this.meridiemInputForTimeFormat()?.focus();
          return;
        }
        this.minuteInput.value = `0${number}`;
        this.processTime();
        number > 5 ? this.meridiemInputForTimeFormat()?.focus() : highlightInputContents(this.minuteInput);
      });
      this.minuteInput.addEventListener("blur", (e) => {
        isEditing = false;
        this.processTime();
      });
    }
    initMeridiemInputListeners() {
      this.meridiemInput?.addEventListener("keydown", (e) => {
        if (e.key === "Backspace") {
          if (this.meridiemInput.value === "" || "showingPlaceholder" in this.meridiemInput.dataset) {
            this.minuteInput.focus();
            return;
          }
          this.meridiemInput.value = "";
          this.meridiemInput.style.color = "";
          highlightInputContents(this.meridiemInput);
          return;
        }
        if (!["ArrowUp", "ArrowDown"].includes(e.key)) return;
        e.preventDefault();
        e.stopPropagation();
        clearPlaceholderValue(this.meridiemInput);
        let locale = this.picker.config.locale;
        let am = getLocalisedMeridiem(0, locale);
        let pm = getLocalisedMeridiem(12, locale);
        let value3 = this.meridiemInput.value;
        if (e.key === "ArrowUp") {
          if (value3 === "" || value3 === pm) {
            value3 = am;
          } else {
            value3 = pm;
          }
        } else if (e.key === "ArrowDown") {
          if (value3 === "" || value3 === am) {
            value3 = pm;
          } else {
            value3 = am;
          }
        }
        this.meridiemInput.value = value3;
        this.meridiemInput.style.color = "";
        delete this.meridiemInput.dataset.showingPlaceholder;
        this.processTime();
        highlightInputContents(this.meridiemInput);
      });
      this.meridiemInput?.addEventListener("keydown", (e) => {
        if (!["a", "p", "A", "P"].includes(e.key)) return;
        clearPlaceholderValue(this.meridiemInput);
        let locale = this.picker.config.locale;
        if (e.key === "a" || e.key === "A") {
          this.meridiemInput.value = getLocalisedMeridiem(0, locale);
        } else if (e.key === "p" || e.key === "P") {
          this.meridiemInput.value = getLocalisedMeridiem(12, locale);
        }
        this.meridiemInput.style.color = "";
        delete this.meridiemInput.dataset.showingPlaceholder;
        this.processTime();
        highlightInputContents(this.meridiemInput);
      });
    }
    // Not all locales have the same width meridiem, like US is `AM`, Canada is `a.m.`, and Japan is `午後`.
    // Alphabetic scripts (Latin, Greek, etc.) use ch units since character widths are similar to `0`.
    // CJK, Hangul, and other wide scripts are measured from a rendered span instead...
    setMeridiemWidthBasedOnLocale() {
      let meridiem = getLocalisedMeridiem(12, this.picker.config.locale);
      if (!meridiem) return;
      let isAlphabetic = /^[\u0000-\u024F\u0370-\u03FF\u1E00-\u1EFF\s.]+$/.test(meridiem);
      if (isAlphabetic) {
        this.meridiemInput.style.width = `calc(${meridiem.length}ch + 2px)`;
        return;
      }
      let span = document.createElement("span");
      span.style.cssText = "position:absolute;visibility:hidden;white-space:nowrap";
      span.style.font = getComputedStyle(this.meridiemInput).font;
      span.textContent = meridiem;
      document.body.appendChild(span);
      this.meridiemInput.style.width = `${span.getBoundingClientRect().width}px`;
      span.remove();
    }
  };
  function initCursorSelectionListeners(input) {
    input.addEventListener("focus", (e) => {
      highlightInputContents(input);
    });
    input.addEventListener("blur", (e) => {
      clearPlaceholderValue(input);
    });
    input.addEventListener("mousedown", (e) => {
      e.preventDefault();
      input.focus();
      highlightInputContents(input);
    });
    input.addEventListener("mouseup", (e) => {
      e.preventDefault();
      highlightInputContents(input);
    });
  }
  function blockDefaultInputs(input, { isMeridiem = false } = {}) {
    input.addEventListener("keydown", (e) => {
      if (/[0-9]/.test(e.key) && !isMeridiem) {
        clearPlaceholderValue(input);
        input.classList.remove("font-mono");
      }
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      if (["Tab", "Backspace"].includes(e.key)) return;
      e.preventDefault();
    });
  }
  function highlightInputContents(input) {
    if (!input.value && input.placeholder) {
      input.style.color = getComputedStyle(input, "::placeholder").color;
      input.value = input.placeholder;
      input.classList.add("font-mono");
      input.dataset.showingPlaceholder = "";
    }
    input.setSelectionRange(0, input.value.length);
  }
  function clearPlaceholderValue(input) {
    if ("showingPlaceholder" in input.dataset) {
      input.value = "";
      input.style.color = "";
      delete input.dataset.showingPlaceholder;
    }
  }
  function getLocalisedMeridiem(hours, locale) {
    let formatter = new Intl.DateTimeFormat(locale, {
      hour: "numeric",
      hour12: true
    });
    let parts = formatter.formatToParts(new Date(2024, 0, 1, hours, 0));
    let medridiem = parts.find((part) => part.type === "dayPeriod")?.value;
    if (medridiem) return medridiem;
    return hours >= 12 ? "PM" : "AM";
  }
  function getTime(hour, minute, meridiem = null) {
    hour = hour.padStart(2, "0");
    minute = minute.padStart(2, "0");
    let time = meridiem ? hour + ":" + minute + " " + meridiem : hour + ":" + minute;
    return convertTimeStringTo24HourFormat(time);
  }
  function localeIs24HourFormat(locale) {
    let formatter = new Intl.DateTimeFormat(locale, { hour: "numeric" });
    let midnight = formatter.format(new Date(2024, 0, 1, 0, 0));
    return midnight.startsWith("00");
  }
  function convertTimeStringTo24HourFormat(time) {
    let match = time.trim().match(/^(\d{1,2}):(\d{2})(?:\s*(am|pm|AM|PM))?$/i);
    if (!match) throw new Error(`Invalid time string: ${time}`);
    let hours = parseInt(match[1], 10);
    let minutes = parseInt(match[2], 10);
    let meridiem = match[3]?.toUpperCase();
    if (minutes > 59 || hours > 23 || meridiem !== "AM" && meridiem !== "PM" && meridiem !== void 0) throw new Error(`Invalid time string: ${time}`);
    if (hours >= 13 || hours === 0) {
      return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`;
    }
    if (meridiem) {
      if (hours === 12) {
        hours = meridiem === "AM" ? 0 : 12;
      } else if (meridiem === "PM") {
        hours += 12;
      }
    }
    return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`;
  }

  // js/time-picker/index.js
  var UITimePicker = class extends UIControl {
    boot() {
      this.config = {
        timeFormat: this.hasAttribute("time-format") ? this.getAttribute("time-format") : "auto",
        interval: this.interval(),
        min: this.min(),
        max: this.max(),
        unavailable: this.unavailable(),
        openTo: this.openTo(),
        locale: this.hasAttribute("locale") ? this.getAttribute("locale") : navigator.language,
        mode: this.hasAttribute("multiple") ? SelectableModes.MULTIPLE : SelectableModes.SINGLE,
        dropdown: this.hasAttribute("dropdown") && this.getAttribute("dropdown") === "false" ? false : true
      };
      this.observable = new Observable();
      this._controllable = new Controllable(this, { bubbles: true });
      this._submittable = new Submittable(this, {
        name: this.getAttribute("name"),
        value: this.getAttribute("value"),
        includeWhenEmpty: false
      });
      this.selectable = Selectable2.createFromValueStringAttribute(this.getAttribute("value"), this.config, this.observable);
      this._controllable.getter(() => this.selectable.getValue());
      let detangled = detangle();
      this._controllable.setter(detangled((value3) => {
        this.selectable.setValue(value3);
      }));
      this.observable.subscribe(ObservableTrigger.SELECTION, detangled(() => {
        this._controllable.dispatch();
        this.dispatchEvent(new CustomEvent("input", { bubbles: false }));
      }));
      this.observable.subscribe(ObservableTrigger.SELECTION, () => {
        this._submittable.update(this.selectable.getValue());
      });
      this._disableable = new Disableable(this);
      if (!this.config.dropdown) return;
      let popoverEl = this.querySelector("[popover]");
      if (!popoverEl) return;
      let triggerEl = this.trigger();
      let optionsEl = this.querySelector("ui-time-picker-options");
      let optionsId = initOptions(optionsEl, this.config.mode === SelectableModes.MULTIPLE);
      this._popoverable = new Popoverable(popoverEl, {
        triggers: [triggerEl]
      });
      this._anchorable = new Anchorable(popoverEl, {
        reference: triggerEl,
        position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
        gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
        offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0,
        matchWidth: true
      });
      this._activatable = new ActivatableGroup(optionsEl, { filter: "data-hidden" });
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(triggerEl, "disabled", "");
          triggerEl.querySelectorAll("button,ui-button").forEach((button) => setAttribute2(button, "disabled", ""));
        } else {
          removeAttribute(triggerEl, "disabled");
          triggerEl.querySelectorAll("button,ui-button").forEach((button) => removeAttribute(button, "disabled"));
        }
      });
      triggerEl.addEventListener("click", (e) => {
        this._popoverable.toggle();
      });
      this._popoverable.onChange((isOpen) => {
        if (!isOpen && triggerEl._lastFocusedInput) {
          let input = triggerEl._lastFocusedInput;
          triggerEl._lastFocusedInput = null;
          input.focus();
        }
      });
      triggerEl.querySelectorAll("button,ui-button").forEach((button) => {
        initTriggerButton(button, this._popoverable, optionsId);
        trackActiveDescendant2(button, this._activatable, this.observable);
      });
      initPopover2(this, popoverEl, this._popoverable, this._anchorable);
      preventScrollWhenPopoverIsOpen2(this, this._popoverable);
      controlPopoverWithKeyboard(triggerEl, this._popoverable);
      controlPopoverWithEscapeKey(this, this._popoverable);
      handleKeyboardNavigation(triggerEl, this._activatable);
      handleKeyboardSearchNavigation(triggerEl, this._activatable, this._popoverable);
      handleKeyboardSelection(this, triggerEl, this._activatable);
      controlActivationWithPopover2(this._popoverable, this._activatable, this.selectable, this.config.openTo);
      handlePopoverClosing2(this, this.config.mode, this.observable, this.selectable, this._popoverable);
      let observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (!["interval", "unavailable", "max", "min", "open-to"].includes(mutation.attributeName)) return;
          this.config.interval = this.interval();
          this.config.unavailable = this.unavailable();
          this.config.min = this.min();
          this.config.max = this.max();
          this.config.openTo = this.openTo();
          this.observable.notify(ObservableTrigger.VIEW_CHANGE);
        });
      });
      observer.observe(this, { attributes: true });
    }
    trigger() {
      return this.querySelector("ui-time-picker-trigger");
    }
    list() {
      return this.querySelector("ui-options") || this;
    }
    clear() {
      this.selectable.setValue(null);
      this.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
    }
    interval() {
      if (this.hasAttribute("interval")) {
        return parseInt(this.getAttribute("interval"));
      }
      return 30;
    }
    min() {
      if (this.hasAttribute("min")) {
        let min2 = this.getAttribute("min");
        if (min2 === "now") {
          return (/* @__PURE__ */ new Date()).toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit", hour12: false });
        }
        if (validateTimeString(min2)) {
          return min2;
        }
        return console.warn(`Invalid min attribute: ${min2}`);
      }
      return void 0;
    }
    max() {
      if (this.hasAttribute("max")) {
        let max2 = this.getAttribute("max");
        if (max2 === "now") {
          return (/* @__PURE__ */ new Date()).toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit", hour12: false });
        }
        if (validateTimeString(max2)) {
          return max2;
        }
        return console.warn(`Invalid max attribute: ${max2}`);
      }
      return void 0;
    }
    unavailable() {
      if (this.hasAttribute("unavailable")) {
        let unavailable = this.getAttribute("unavailable").split(",").map((i) => i.trim());
        if (unavailable.every((i) => i.includes("-") ? validateTimeRangeString(i) : validateTimeString(i))) {
          return unavailable;
        }
        return console.warn(`Invalid unavailable attribute: ${unavailable}`);
      }
      return [];
    }
    openTo() {
      if (this.hasAttribute("open-to")) {
        let openTo = this.getAttribute("open-to");
        if (openTo === "now") {
          return (/* @__PURE__ */ new Date()).toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit", hour12: false });
        }
        if (validateTimeString(openTo)) {
          return openTo;
        }
        return console.warn(`Invalid open-to attribute: ${openTo}`);
      }
      return void 0;
    }
  };
  var UISelectedTime = class extends UIElement {
    boot() {
      this.picker = this.closest("ui-time-picker");
      if (!this.querySelector('template[name="time"]')) {
        let template = document.createElement("template");
        template.setAttribute("name", "time");
        template.innerHTML = "<div><slot></slot></div>";
      }
      this.templates = {
        placeholder: this.querySelector('template[name="placeholder"]'),
        time: this.querySelector('template[name="time"]')
      };
      this.picker.observable.subscribe(ObservableTrigger.SELECTION, () => {
        this.render(this.picker);
      });
      this.render(this.picker);
    }
    mount() {
      this.render(this.picker);
    }
    render(picker) {
      this.templates.placeholder?.clearPlaceholder?.();
      this.templates.time?.clearTime?.();
      if (picker.selectable.hasSelection()) {
        let { cleanup } = renderTemplate2(this.templates.time, (hydrate) => {
          return hydrate({ slots: { default: picker.selectable.display(picker.config.locale) } });
        });
        this.templates.time.clearTime = cleanup;
      } else {
        if (!this.templates.placeholder) return;
        let { cleanup } = renderTemplate2(this.templates.placeholder, (hydrate) => {
          return hydrate({ slots: {} });
        });
        this.templates.placeholder.clearPlaceholder = cleanup;
      }
    }
  };
  var UITimePickerOptions = class extends UIElement {
    boot() {
      this.picker = this.closest("ui-time-picker");
      this.render();
      this.picker.observable.subscribe(ObservableTrigger.SELECTION, () => {
        this.render();
      });
      this.picker.observable.subscribe(ObservableTrigger.VIEW_CHANGE, () => {
        this.render();
      });
      let { enable: enableListeners, disable: disableListeners } = initializeEventListeners(this, this.picker.selectable);
      this.picker._disableable.onInitAndChange((disabled) => {
        disabled ? disableListeners() : enableListeners();
      });
    }
    render() {
      let template = this.querySelector('template[name="option"]');
      if (!template) return;
      renderTemplate2(template, (hydrate) => {
        let times = generateTimes(this.picker.config);
        return times.map(({ value: value3, label }) => {
          let isDisabled = this.picker.config.unavailable.some((unavailableTime) => timesAreOverlapping(value3, unavailableTime));
          let attrs = {
            "data-time": value3,
            "role": "option"
          };
          if (isDisabled) attrs.disabled = "";
          let el = hydrate({ attrs, slots: { default: label } });
          this.picker.selectable.attributes(el, value3);
          el._activatable = new Activatable(el);
          assignId(el, "option");
          return el;
        });
      });
    }
  };
  element("time-picker", UITimePicker);
  element("time-picker-trigger", UITimePickerTrigger);
  element("selected-time", UISelectedTime);
  element("time-picker-options", UITimePickerOptions);
  function initOptions(options, multiple) {
    let optionsId = assignId(options, "options");
    setAttribute2(options, "role", "listbox");
    setAttribute2(options, "aria-multiselectable", multiple ? "true" : "false");
    return optionsId;
  }
  function initTriggerButton(el, popoverable, optionsId) {
    setAttribute2(el, "role", "combobox");
    setAttribute2(el, "aria-controls", optionsId);
    setAttribute2(el, "aria-haspopup", "listbox");
    linkExpandedStateToPopover2(el, popoverable);
    on(el, "click", (e) => {
      e.stopPropagation();
      popoverable.setState(!popoverable.getState());
      el.focus();
    });
  }
  function initPopover2(root, popover, popoverable, anchorable) {
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
  function preventScrollWhenPopoverIsOpen2(root, popoverable) {
    let { lock, unlock } = lockScroll(popoverable.el);
    popoverable.onChange(() => {
      popoverable.getState() ? lock() : unlock();
    });
  }
  function linkExpandedStateToPopover2(el, popoverable) {
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
  function trackActiveDescendant2(el, activatable) {
    activatable.onChange(() => {
      let activeEl = activatable.getActive();
      activeEl ? setAttribute2(el, "aria-activedescendant", activeEl.id) : removeAttribute(el, "aria-activedescendant");
    });
  }
  function handlePopoverClosing2(root, mode, observable, selectable, popoverable) {
    if (mode === SelectableModes.MULTIPLE) return;
    observable.subscribe(ObservableTrigger.SELECTION, () => {
      if (selectable.hasSelection()) {
        popoverable.setState(false);
      }
    });
  }
  function initializeEventListeners(el, selectable) {
    let blocked = false;
    let readonly = el.hasAttribute("readonly");
    on(el, "click", (e) => {
      if (blocked || readonly) return;
      let option = e.target.closest("[data-time]");
      if (!option) return;
      let time = option.getAttribute("data-time");
      setTimeout(() => {
        validateTimeString(time) && selectable.select(time);
      });
    });
    return {
      enable: () => {
      },
      disable: () => {
      }
    };
  }
  function validateTimeString(time) {
    return new RegExp(`^${timeRegex()}$`).test(time);
  }
  function validateTimeRangeString(time) {
    return new RegExp(`^${timeRegex()}-${timeRegex()}$`).test(time);
  }
  function timeRegex() {
    return "([01]?[0-9]|2[0-3]):[0-5][0-9]";
  }
  function timesAreOverlapping(first, second) {
    let [firstStart, firstEnd] = first.split("-").map((t) => {
      let [hours, minutes] = t.split(":").map(Number);
      return hours * 60 + minutes;
    });
    let [secondStart, secondEnd] = second.split("-").map((t) => {
      let [hours, minutes] = t.split(":").map(Number);
      return hours * 60 + minutes;
    });
    if (isNaN(firstEnd)) firstEnd = firstStart;
    if (isNaN(secondEnd)) secondEnd = secondStart;
    return !(firstEnd < secondStart || firstStart > secondEnd);
  }
  function formatTime(time, timeFormat, locale) {
    let [hours, minutes] = time.split(":").map(Number);
    let hour12Map = {
      "12-hour": true,
      "24-hour": false,
      "auto": void 0
    };
    let formatter = new Intl.DateTimeFormat(locale, {
      hour: "numeric",
      minute: "numeric",
      hour12: hour12Map[timeFormat]
    });
    return formatter.format(new Date(2024, 0, 1, hours, minutes));
  }
  function timeInMinutes(time) {
    if (!time) return null;
    let [hours, minutes] = time.split(":");
    if (isNaN(hours) || isNaN(minutes)) return null;
    return parseInt(hours) * 60 + parseInt(minutes);
  }
  function controlActivationWithPopover2(popoverable, activatable, selectable, openTo = null) {
    popoverable.onChange(() => {
      if (popoverable.getState()) {
        let selectedValues = selectable.getValue();
        if (!Array.isArray(selectedValues)) {
          selectedValues = selectedValues === null ? [] : [selectedValues];
        }
        let selectables = activatable.filterAwareWalker();
        let firstSelectedOption = selectables.find((el) => selectedValues.includes(el.getAttribute("data-time")));
        let firstOptionToActivate = null;
        if (!firstSelectedOption && openTo) {
          firstOptionToActivate = findCloestOptionToOpenTo(activatable, openTo);
        }
        setTimeout(() => {
          let optionToScrollTo = firstSelectedOption || firstOptionToActivate;
          activatable.activateSelectedOrFirst(optionToScrollTo);
          optionToScrollTo?.scrollIntoView({ block: "nearest" });
        });
      } else {
        activatable.clearActive();
      }
    });
  }
  function findCloestOptionToOpenTo(activatable, openTo) {
    let openToInMinutes = timeInMinutes(openTo);
    let closestOption = null;
    let closestOptionTimeInMinutes = null;
    activatable.filterAwareWalker().walk((option, bail) => {
      let optionTime = option.getAttribute("data-time");
      let optionTimeInMinutes = timeInMinutes(optionTime);
      if (optionTimeInMinutes > openToInMinutes) return;
      if (Math.abs(optionTimeInMinutes - openToInMinutes) < Math.abs(closestOptionTimeInMinutes - openToInMinutes)) {
        closestOption = option;
        closestOptionTimeInMinutes = optionTimeInMinutes;
      }
    });
    return closestOption;
  }
  function generateTimes({ interval = 30, timeFormat = "auto", locale = navigator.language, min: min2, max: max2 } = {}) {
    let times = [];
    let minInMinutes = 0;
    let maxInMinutes = 24 * 60 - 1;
    if (min2) {
      let minDate = new Date(2024, 0, 1);
      let [minHours, minMinutes] = min2.split(":");
      minDate.setHours(minHours, minMinutes);
      minInMinutes = minDate.getHours() * 60 + minDate.getMinutes();
    }
    if (max2) {
      let maxDate = new Date(2024, 0, 1, 23, 59, 59);
      let [maxHours, maxMinutes] = max2.split(":");
      maxDate.setHours(maxHours, maxMinutes);
      maxInMinutes = maxDate.getHours() * 60 + maxDate.getMinutes();
    }
    for (let minutes = minInMinutes; minutes <= maxInMinutes; minutes += interval) {
      let hours = Math.floor(minutes / 60);
      let mins = minutes % 60;
      let value3 = `${String(hours).padStart(2, "0")}:${String(mins).padStart(2, "0")}`;
      let label = formatTime(value3, timeFormat, locale);
      times.push({ value: value3, label });
    }
    return times;
  }

