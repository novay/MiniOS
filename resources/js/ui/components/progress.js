  // js/progress.js
  var UIProgress = class extends UIElement {
    boot() {
      this._controllable = new Controllable(this);
      this._controllable.initial((initial) => initial != null && this.setValue(parseFloat(initial)));
      this._controllable.getter(() => this.currentValue);
      this._controllable.setter((v) => this.setValue(parseFloat(v)));
      setAttribute2(this, "role", "progressbar");
      setAttribute2(this, "aria-valuemin", 0);
      if (this.currentValue === void 0) {
        this.setValue(this.hasAttribute("value") ? parseFloat(this.getAttribute("value")) : 0);
      }
      new MutationObserver(() => this.updateVisual()).observe(this, {
        attributes: true,
        attributeFilter: ["max"]
      });
    }
    setValue(value3) {
      this.currentValue = value3;
      this.updateVisual();
    }
    updateVisual() {
      let max2 = this.hasAttribute("max") ? parseFloat(this.getAttribute("max")) : 100;
      let pct = Math.max(0, Math.min(100, this.currentValue / max2 * 100));
      this.style.setProperty("--os-progress", pct);
      this.style.setProperty("--os-progress-percentage", pct + "%");
      setAttribute2(this, "aria-valuenow", this.currentValue);
      setAttribute2(this, "aria-valuemax", max2);
    }
  };
  element("progress", UIProgress);

