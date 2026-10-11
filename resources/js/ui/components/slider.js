  // js/slider.js
  var UISlider = class extends UIElement {
    boot() {
      this.thumbs = this.querySelectorAll("[data-os-slider-thumb]");
      this.indicator = this.querySelector("[data-os-slider-indicator]");
      this.inputEls = this.querySelectorAll('input[type="range"]');
      this.config = {
        range: this.range(),
        min: this.min(),
        max: this.max(),
        step: this.step(),
        bigStep: this.bigStep(),
        minStepsBetween: this.minStepsBetween(),
        rangeStartString: this.rangeStartString(),
        rangeEndString: this.rangeEndString()
      };
      this.state = {
        onChanges: [],
        getValue: () => {
          return this.config.range ? [parseFloat(this.inputEls[0].value), parseFloat(this.inputEls[1].value)] : parseFloat(this.inputEls[0].value);
        },
        getValueAsArray: () => {
          return this.config.range ? this.state.getValue() : [this.state.getValue()];
        },
        setValue: (value3) => {
          if (this.config.range) {
            this.inputEls[0].value = value3[0];
            this.inputEls[1].value = value3[1];
          } else {
            this.inputEls[0].value = value3;
          }
          this.state.onChanges.forEach((i) => i(this.state.getValue()));
        },
        setValueInRange: (index, value3) => {
          let newValues = [...this.state.getValue()];
          newValues[index] = value3;
          this.state.setValue(newValues);
        },
        setValueFromString: (value3) => {
          if (this.config.range) {
            this.state.setValue(value3.split(","));
          } else {
            this.state.setValue(value3);
          }
        },
        increment: (index, increment) => {
          let values = this.state.getValueAsArray();
          if (this.config.range) {
            values[index] = this.constrainRangeValue(index, values[index] + increment);
            this.state.setValueInRange(index, values[index]);
          } else {
            this.state.setValue(clamp2(values[index] + increment, this.config.min, this.config.max));
          }
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
        this.repositionThumbsAndIndicator();
        this.updateTicks();
        this._submittable.update(value3);
        if (this.config.range) {
          this.inputEls[0].ariaValueText = `${value3[0]} ${this.config.rangeStartString}`;
          this.inputEls[1].ariaValueText = `${value3[1]} ${this.config.rangeEndString}`;
        }
      });
      this._controllable.initial((initial) => initial && this.state.setValue(initial));
      this._controllable.getter(() => this.state.getValue());
      this._controllable.setter((v) => this.state.setValue(v));
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          this.inputEls.forEach((el) => el.setAttribute("disabled", ""));
        } else {
          this.inputEls.forEach((el) => el.removeAttribute("disabled"));
        }
      });
      this.setNativeInputAttributes();
      this.setInitialValue();
      this.setupExtraInputIfExists();
      this.repositionTicks();
      this.registerNativeInputListeners();
      this.registerPointerListeners();
      this.registerMutationObserver();
      new ResizeObserver(() => {
        this.repositionThumbsAndIndicator();
        this.repositionTicks();
      }).observe(this);
    }
    mount() {
      this.setupFieldIfExists();
    }
    setNativeInputAttributes() {
      for (let i = 0; i < this.inputEls.length; i++) {
        this.inputEls[i].setAttribute("min", this.config.min);
        this.inputEls[i].setAttribute("max", this.config.max);
        this.inputEls[i].setAttribute("step", this.config.step);
      }
    }
    setInitialValue() {
      if (this.hasAttribute("value")) {
        this.state.setValueFromString(this.getAttribute("value"));
      } else {
        this.state.setValue(this.state.getValue());
      }
    }
    setupExtraInputIfExists() {
      let extraInput = this.closest("ui-field")?.querySelector("[data-os-input] > input");
      if (!extraInput) return;
      this.extraInput = extraInput;
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(extraInput, "disabled", "");
        } else {
          removeAttribute(extraInput, "disabled");
        }
      });
    }
    setupFieldIfExists() {
      let field = this.closest("ui-field");
      if (!field) return;
      queueMicrotask(() => {
        if (field.label) on(field.label, "click", () => this.inputEls[0].focus());
        if (field.label && this.extraInput) setAttribute2(this.extraInput, "aria-labelledby", field.label.id);
        if (field.description && this.extraInput) setAttribute2(this.extraInput, "aria-describedby", field.description.id);
        for (let i = 0; i < this.inputEls.length; i++) {
          if (field.label) setAttribute2(this.inputEls[i], "aria-labelledby", field.label.id);
          if (field.description) setAttribute2(this.inputEls[i], "aria-describedby", field.description.id);
        }
      });
    }
    registerNativeInputListeners() {
      for (let i = 0; i < this.inputEls.length; i++) {
        on(this.inputEls[i], "input", (e) => {
          e.stopPropagation();
        });
        on(this.inputEls[i], "change", (e) => {
          let value3 = parseFloat(e.target.value);
          if (this.config.range) {
            value3 = this.constrainRangeValue(i, value3);
            this.state.setValueInRange(i, value3);
          } else {
            this.state.setValue(value3);
          }
          this._controllable.dispatch();
          e.stopPropagation();
        });
        on(this.inputEls[i], "keydown", (e) => {
          if (!e.shiftKey) return;
          switch (e.key) {
            case "ArrowUp":
              this.state.increment(i, this.config.bigStep);
              break;
            case "ArrowDown":
              this.state.increment(i, this.config.bigStep * -1);
              break;
            case "ArrowRight":
              this.state.increment(i, this.config.bigStep * (isRTL(this) ? -1 : 1));
              break;
            case "ArrowLeft":
              this.state.increment(i, this.config.bigStep * (isRTL(this) ? 1 : -1));
              break;
            default:
              return;
          }
          this._controllable.dispatch();
          e.preventDefault();
        });
        on(this.inputEls[i], "focus", () => this.raiseThumb(i));
        on(this.inputEls[i], "blur", () => this.lowerThumb(i));
      }
    }
    registerPointerListeners() {
      on(this, "pointerdown", (e) => {
        if (this._disableable.isDisabled()) return;
        if (e.button !== 0) return;
        let index = this.getThumbIndexFromX(e.clientX);
        let thumbRect = this.thumbs[index].getBoundingClientRect();
        let thumbMidpoint = thumbRect.left + thumbRect.width / 2;
        let pointerOffset = 0;
        if (Math.abs(e.clientX - thumbMidpoint) < thumbRect.width / 2) {
          pointerOffset = e.clientX - thumbMidpoint;
        }
        let initialValues = this.state.getValueAsArray();
        let lastValues = this.state.getValueAsArray();
        let setValue = (e2) => {
          let value3 = this.getValueFromX(e2.clientX - pointerOffset);
          if (this.config.range) {
            value3 = this.constrainRangeValue(index, value3);
          }
          let newValues = [...lastValues];
          newValues[index] = value3;
          if (lastValues.toString() !== newValues.toString()) {
            lastValues = newValues;
            this.config.range ? this.state.setValueInRange(index, value3) : this.state.setValue(value3);
            this.dispatchEvent(new Event("input", {
              bubbles: false,
              cancelable: true
            }));
          }
        };
        setValue(e);
        this.setPointerCapture(e.pointerId);
        requestAnimationFrame(() => {
          this.inputEls[index].focus();
        });
        document.addEventListener("pointermove", setValue);
        document.addEventListener("pointerup", (e2) => {
          document.removeEventListener("pointermove", setValue);
          this.releasePointerCapture(e2.pointerId);
          requestAnimationFrame(() => {
            this.lowerThumb(index);
          });
          if (initialValues.toString() !== lastValues.toString()) {
            this.dispatchEvent(new Event("change", {
              bubbles: false,
              cancelable: true
            }));
          }
        }, { once: true });
      });
    }
    registerMutationObserver() {
      let observer = new MutationObserver(() => {
        this.config.min = this.min();
        this.config.max = this.max();
        this.config.step = this.step();
        this.config.bigStep = this.bigStep();
        this.config.minStepsBetween = this.minStepsBetween();
        this.setNativeInputAttributes();
        this.repositionThumbsAndIndicator();
        this.repositionTicks();
      });
      observer.observe(this, {
        attributes: true,
        attributeFilter: ["min", "max", "step", "big-step", "min-steps-between"]
      });
    }
    repositionThumbsAndIndicator() {
      if (this.config.range) {
        let value3 = this.state.getValue();
        let startPercentage = this.getPercentageFromValue(value3[0]);
        let endPercentage = this.getPercentageFromValue(value3[1]);
        this.thumbs[0].style.insetInlineStart = startPercentage + "%";
        this.thumbs[1].style.insetInlineStart = endPercentage + "%";
        this.indicator.style.insetInlineStart = startPercentage + "%";
        this.indicator.style.insetInlineEnd = 100 - endPercentage + "%";
      } else {
        let value3 = this.state.getValue();
        let percentage = this.getPercentageFromValue(value3);
        this.thumbs[0].style.insetInlineStart = percentage + "%";
        this.indicator.style.insetInlineStart = 0;
        this.indicator.style.insetInlineEnd = 100 - percentage + "%";
      }
    }
    repositionTicks() {
      this.querySelectorAll("[data-os-slider-tick]").forEach((tick) => {
        let tickValue = parseFloat(tick.getAttribute("data-value"));
        if (isNaN(tickValue)) return;
        let percentage = this.getPercentageFromValue(tickValue);
        setAttribute2(tick, "style", `inset-inline-start: ${percentage}%`);
      });
    }
    updateTicks() {
      let values = this.state.getValueAsArray();
      let activeRange = this.config.range ? values : [this.state.min, values[0]];
      this.querySelectorAll("[data-os-slider-tick]").forEach((tick) => {
        let tickValue = parseFloat(tick.getAttribute("data-value"));
        if (isNaN(tickValue)) return;
        for (let i = 0; i < values.length; i++) {
          if (values[i] == tickValue) {
            setAttribute2(tick, "data-current", "");
          } else {
            removeAttribute(tick, "data-current");
          }
        }
        if (tickValue >= activeRange[0] || tickValue <= activeRange[1]) {
          setAttribute2(tick, "data-active", "");
        } else {
          removeAttribute(tick, "data-active");
        }
      });
    }
    raiseThumb(index) {
      this.thumbs[index].style.zIndex = 10;
    }
    lowerThumb(index) {
      this.thumbs[index].style.zIndex = null;
    }
    getThumbIndexFromX(x) {
      let closestIndex;
      let closestDistance;
      for (let i = 0; i < this.thumbs.length; i++) {
        let rect = this.thumbs[i].getBoundingClientRect();
        let midpoint = (rect.left + rect.right) / 2;
        let distance = Math.abs(midpoint - x);
        if (closestDistance && Math.abs(closestDistance - distance) < rect.width / 2) {
          let trackRect = this.getBoundingClientRect();
          let distanceFromMax = trackRect.right - rect.right;
          if (distanceFromMax < rect.width / 4) {
            continue;
          }
        }
        if (closestDistance === void 0 || distance <= closestDistance) {
          closestDistance = distance;
          closestIndex = i;
        }
      }
      return closestIndex;
    }
    getPercentageFromValue(value3) {
      let current = value3 - this.config.min;
      let total = this.config.max - this.config.min;
      if (total == 0) return 0;
      let fraction = clamp2(current / total, 0, 100);
      let trackRect = this.getBoundingClientRect();
      let thumbRect = this.thumbs[0].getBoundingClientRect();
      let trackPadding = thumbRect.width / 2;
      let width = trackRect.width - trackPadding * 2;
      let offset3 = width * fraction + trackPadding;
      let adjustedPercentage = offset3 / trackRect.width * 100;
      return adjustedPercentage;
    }
    getValueFromX(x) {
      let min2 = this.config.min;
      let max2 = this.config.max;
      let step = this.config.step;
      let trackRect = this.getBoundingClientRect();
      let thumbRect = this.thumbs[0].getBoundingClientRect();
      let trackPadding = thumbRect.width / 2;
      let trackWidth = trackRect.width - trackPadding * 2;
      let trackLeft = trackRect.left + trackPadding;
      let relativeX = x - trackLeft;
      let rescaled = clamp2(relativeX / trackWidth, 0, 1);
      rescaled = isRTL(this) ? 1 - rescaled : rescaled;
      let value3 = (max2 - min2) * rescaled + min2;
      value3 = roundValueToStep(value3, step, min2);
      value3 = clamp2(value3, min2, max2);
      return value3;
    }
    constrainRangeValue(index, value3) {
      let newValues = [...this.state.getValue()];
      newValues[index] = value3;
      let stepsBetween = (newValues[1] - newValues[0]) / this.config.step;
      if (stepsBetween < this.config.minStepsBetween) {
        let limits = [
          newValues[1] - this.config.minStepsBetween * this.config.step,
          newValues[0] + this.config.minStepsBetween * this.config.step
        ];
        return limits[index];
      }
      return value3;
    }
    range() {
      return this.hasAttribute("range");
    }
    min() {
      return this.hasAttribute("min") ? parseFloat(this.getAttribute("min")) : 0;
    }
    max() {
      return this.hasAttribute("max") ? parseFloat(this.getAttribute("max")) : 100;
    }
    step() {
      if (!this.hasAttribute("step")) {
        return 1;
      }
      let step = parseFloat(this.getAttribute("step"));
      return step <= 0 || isNaN(step) ? 1 : step;
    }
    bigStep() {
      if (!this.hasAttribute("big-step")) {
        return this.step();
      }
      let bigStep = parseFloat(this.getAttribute("big-step"));
      return bigStep <= 0 || isNaN(bigStep) ? this.step() : bigStep;
    }
    minStepsBetween() {
      return this.hasAttribute("min-steps-between") ? parseFloat(this.getAttribute("min-steps-between")) : 0;
    }
    rangeStartString() {
      return this.getAttribute("data-os-aria-range-start") || "start range";
    }
    rangeEndString() {
      return this.getAttribute("data-os-aria-range-end") || "end range";
    }
  };
  function clamp2(value3, min2, max2) {
    return Math.min(Math.max(value3, min2), max2);
  }
  function roundValueToStep(value3, step, min2) {
    let precision;
    if (Math.abs(step) >= 1) {
      precision = step.toString().split(".")[1]?.length || 0;
    } else {
      let parts = step.toExponential().split("e-");
      let matissaDecimalPart = parts[0].split(".")[1];
      precision = (matissaDecimalPart ? matissaDecimalPart.length : 0) + parseInt(parts[1], 10);
    }
    let nearest = Math.round((value3 - min2) / step) * step + min2;
    return Number(nearest.toFixed(precision));
  }
  element("slider", UISlider);

