  // js/carousel.js
  var UICarousel = class extends UIElement {
    mount() {
      this.track = null;
      this.slides = [];
      this.indicators = this.indicators || [];
      this.state = { current: 0, atStart: true, atEnd: false };
      this.updateFrame = null;
      this.autoplayTimeout = null;
      this.autoplayPauseLocks = /* @__PURE__ */ new Set();
      this.observer = null;
      this.resizeObserver = null;
      this._disableable = new Disableable(this);
      this.syncElementsAndState();
      if (!this.track || this.slides.length === 0) return;
      this._disableable.onInitAndChange((disabled) => this.syncDisabledState(disabled));
      requestAnimationFrame(() => {
        setAttribute2(this, "data-ready", "");
      });
      this.onUnmount(
        on(this.track, "scroll", () => this.queueStateUpdate(), { passive: true }).off
      );
      this.observer = new MutationObserver(() => this.syncElementsAndState());
      this.observer.observe(this.track, { childList: true });
      this.onUnmount(() => this.observer.disconnect());
      this.resizeObserver = new ResizeObserver(() => {
        this.indicators.forEach((indicator) => indicator.render());
        this.queueStateUpdate();
      });
      this.resizeObserver.observe(this.track);
      this.onUnmount(() => this.resizeObserver.disconnect());
      this.setupAutoplay();
    }
    unmount() {
      if (this.updateFrame) cancelAnimationFrame(this.updateFrame);
      this.clearAutoplayTimeout();
    }
    next() {
      if (this.state.atEnd && this.canRewind()) return this.goTo(0);
      this.goTo(this.calculateTargetIndex(1));
    }
    previous() {
      this.goTo(this.calculateTargetIndex(-1));
    }
    goTo(index) {
      if (!this.track || this.slides.length === 0) return;
      if (this.disabled) return;
      index = clamp3(index, 0, this.slides.length - 1);
      this.track.scrollBy({
        left: this.scrollDistanceTo(this.slides[index]),
        behavior: this.scrollBehavior()
      });
      this.updateState();
      this.syncDisabledState(this.disabled);
    }
    syncElementsAndState() {
      this.refreshElements();
      if (!this.track) return;
      this.setupAccessibility();
      this.indicators.forEach((indicator) => {
        indicator.carousel = indicator.carousel || this;
        indicator.render();
      });
      this.updateState();
      this.syncDisabledState(this.disabled);
    }
    refreshElements() {
      this.track = this.querySelector("[data-os-carousel-track]");
      this.slides = Array.from(this.track?.querySelectorAll(":scope > ui-carousel-slide") || []);
      this.indicators = Array.from(/* @__PURE__ */ new Set([
        ...this.indicators,
        ...this.querySelectorAll("ui-carousel-indicators")
      ]));
    }
    setupAccessibility() {
      setDefaultAttribute(this, "role", "group");
      setDefaultAttribute(this, "aria-roledescription", "carousel");
      setDefaultAttribute(this.track, "aria-live", "polite");
      setDefaultAttribute(this.track, "aria-atomic", "false");
      this.slides.forEach((slide, index) => {
        setDefaultAttribute(slide, "role", "group");
        setDefaultAttribute(slide, "aria-roledescription", "slide");
        setDefaultAttribute(slide, "aria-label", `${index + 1} of ${this.slides.length}`);
      });
    }
    queueStateUpdate() {
      if (this.updateFrame) return;
      this.updateFrame = requestAnimationFrame(() => {
        this.updateFrame = null;
        this.updateState();
      });
    }
    updateState() {
      this.state = this.readState();
      this.reflectState();
    }
    readState() {
      return {
        current: this.calculateCurrentIndex(),
        atStart: this.isSlideVisible(this.slides[0]),
        atEnd: this.isSlideVisible(this.slides[this.slides.length - 1])
      };
    }
    reflectState() {
      this.reflectEdgeState();
      this.reflectScrollPercentage();
      this.reflectSelectedSlide();
    }
    reflectEdgeState() {
      let atEnd = this.state.atEnd && !this.canRewind();
      this.state.atStart ? setAttribute2(this, "data-at-start", "") : removeAttribute(this, "data-at-start");
      atEnd ? setAttribute2(this, "data-at-end", "") : removeAttribute(this, "data-at-end");
      this.controlElements("ui-carousel-button").forEach((button) => {
        this.state.atStart ? setAttribute2(button, "data-at-start", "") : removeAttribute(button, "data-at-start");
        atEnd ? setAttribute2(button, "data-at-end", "") : removeAttribute(button, "data-at-end");
      });
      this.syncButtonDisabledStates();
    }
    reflectScrollPercentage() {
      let scrollableWidth = this.track.scrollWidth - this.track.clientWidth;
      let percentage = scrollableWidth > 0 ? Math.abs(this.track.scrollLeft) / scrollableWidth * 100 : 0;
      this.track.style.setProperty("--os-carousel-scroll-percentage", percentage + "%");
      if (scrollableWidth <= 0) {
        this.track.style.setProperty("--os-carousel-fade-left", "100%");
        this.track.style.setProperty("--os-carousel-fade-right", "100%");
        return;
      }
      let fade = "calc(100% - var(--os-carousel-fade-size))";
      let scrolled = percentage + "%";
      let remaining = `calc(100% - ${percentage}%)`;
      this.track.style.setProperty(
        "--os-carousel-fade-left",
        isRTL(this) ? `max(${fade}, ${scrolled})` : `max(${fade}, ${remaining})`
      );
      this.track.style.setProperty(
        "--os-carousel-fade-right",
        isRTL(this) ? `max(${fade}, ${remaining})` : `max(${fade}, ${scrolled})`
      );
    }
    reflectSelectedSlide() {
      this.slides.forEach((slide, index) => {
        index === this.state.current ? setAttribute2(slide, "data-selected", "") : removeAttribute(slide, "data-selected");
      });
      this.indicators.forEach((indicator) => indicator.update(this.state.current));
    }
    syncDisabledState(disabled) {
      disabled ? setAttribute2(this, "aria-disabled", "true") : removeAttribute(this, "aria-disabled");
      this.controlElements().forEach((control) => {
        disabled ? setAttribute2(control, "data-disabled", "") : removeAttribute(control, "data-disabled");
        removeAttribute(control, "aria-disabled");
      });
      this.syncButtonDisabledStates();
      this.syncAutoplay();
    }
    setupAutoplay() {
      if (!this.hasAttribute("autoplay")) return;
      if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
      this.onUnmount(on(this, "mouseenter", () => this.pauseAutoplay("hover")).off);
      this.onUnmount(on(this, "mouseleave", () => this.resumeAutoplay("hover")).off);
      this.onUnmount(on(this, "focusin", () => this.pauseAutoplay("focus")).off);
      this.onUnmount(on(this, "focusout", (event) => {
        if (!this.contains(event.relatedTarget)) this.resumeAutoplay("focus");
      }).off);
      this.onUnmount(on(this.track, "pointerdown", () => this.pauseAutoplay("manual")).off);
      this.onUnmount(on(this.track, "wheel", () => this.pauseAutoplay("manual"), { passive: true }).off);
      this.onUnmount(on(this.track, "touchstart", () => this.pauseAutoplay("manual"), { passive: true }).off);
      this.controlElements().filter((control) => !this.contains(control)).forEach((control) => {
        this.onUnmount(on(control, "mouseenter", () => this.pauseAutoplay("hover")).off);
        this.onUnmount(on(control, "mouseleave", () => this.resumeAutoplay("hover")).off);
        this.onUnmount(on(control, "focusin", () => this.pauseAutoplay("focus")).off);
        this.onUnmount(on(control, "focusout", (event) => {
          if (!control.contains(event.relatedTarget)) this.resumeAutoplay("focus");
        }).off);
      });
      this.scheduleAutoplay();
    }
    syncAutoplay() {
      if (!this.hasAttribute("autoplay")) return;
      this.disabled ? this.clearAutoplayTimeout() : this.scheduleAutoplay();
    }
    scheduleAutoplay() {
      this.clearAutoplayTimeout();
      if (!this.hasAttribute("autoplay")) return;
      if (this.disabled) return;
      if (this.autoplayPauseLocks.size > 0) return;
      if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
      this.autoplayTimeout = setTimeout(() => {
        this.autoplayTimeout = null;
        this.advanceAutoplay();
        this.scheduleAutoplay();
      }, this.autoplayInterval());
    }
    clearAutoplayTimeout() {
      if (!this.autoplayTimeout) return;
      clearTimeout(this.autoplayTimeout);
      this.autoplayTimeout = null;
    }
    pauseAutoplay(reason = "manual") {
      if (!this.hasAttribute("autoplay")) return;
      this.autoplayPauseLocks.add(reason);
      this.clearAutoplayTimeout();
    }
    resumeAutoplay(reason) {
      if (!this.hasAttribute("autoplay")) return;
      this.autoplayPauseLocks.delete(reason);
      this.scheduleAutoplay();
    }
    advanceAutoplay() {
      this.state.atEnd ? this.goTo(0) : this.next();
    }
    autoplayInterval() {
      let interval = Number(this.getAttribute("autoplay-interval") || 5e3);
      return Number.isFinite(interval) ? Math.max(1e3, interval) : 5e3;
    }
    syncButtonDisabledStates() {
      this.controlElements().forEach((control) => {
        control.querySelectorAll("button").forEach((button) => {
          this.buttonIsDisabled(control) ? setAttribute2(button, "disabled", "") : removeAttribute(button, "disabled");
        });
      });
    }
    buttonIsDisabled(control) {
      if (this.disabled) return true;
      if (!control.matches("ui-carousel-button")) return false;
      return control.getAttribute("direction") === "previous" ? this.state.atStart : this.state.atEnd && !this.canRewind();
    }
    canRewind() {
      return this.getAttribute("wrap") === "rewind" && !(this.state.atStart && this.state.atEnd);
    }
    controlElements(selector = "ui-carousel-button, ui-carousel-indicators") {
      let controls = Array.from(this.querySelectorAll(selector));
      if (this.hasAttribute("name")) {
        controls.push(...document.querySelectorAll(
          selector.split(",").map((selector2) => `${selector2.trim()}[name="${CSS.escape(this.getAttribute("name"))}"]`).join(", ")
        ));
      }
      return Array.from(new Set(controls));
    }
    calculateTargetIndex(direction) {
      let current = this.calculateCurrentIndex();
      if (this.getAttribute("advance") !== "page") return current + direction;
      return current + this.visibleSlideCount() * direction;
    }
    visibleSlideCount() {
      return Math.max(1, this.slides.filter((slide) => this.isSlideVisible(slide)).length);
    }
    calculateCurrentIndex() {
      let current = 0;
      let distance = Infinity;
      let trackRect = this.track.getBoundingClientRect();
      this.slides.forEach((slide, index) => {
        let slideRect = slide.getBoundingClientRect();
        let slideDistance = isRTL(this) ? Math.abs(trackRect.right - slideRect.right) : Math.abs(trackRect.left - slideRect.left);
        if (slideDistance < distance) {
          current = index;
          distance = slideDistance;
        }
      });
      return current;
    }
    isSlideVisible(slide) {
      if (!slide) return false;
      let trackRect = this.track.getBoundingClientRect();
      let slideRect = slide.getBoundingClientRect();
      let tolerance = 1;
      return slideRect.left >= trackRect.left - tolerance && slideRect.right <= trackRect.right + tolerance;
    }
    scrollDistanceTo(slide) {
      let trackRect = this.track.getBoundingClientRect();
      let slideRect = slide.getBoundingClientRect();
      return isRTL(this) ? slideRect.right - trackRect.right : slideRect.left - trackRect.left;
    }
    scrollBehavior() {
      if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return "instant";
      return this.getAttribute("scroll") === "instant" ? "instant" : "smooth";
    }
  };
  var UICarouselIndicators = class extends UIElement {
    boot() {
      this.buttons = [];
      this.buttonCleanups = [];
      this.targets = [];
    }
    mount() {
      this.carousel = this.findCarousel();
      if (!this.carousel) return;
      this.carousel.indicators = this.carousel.indicators || [];
      this.render();
      if (!this.carousel.indicators.includes(this)) this.carousel.indicators.push(this);
      this.update(this.carousel.calculateCurrentIndex());
      this.onUnmount(() => {
        this.cleanupButtonListeners();
        this.carousel.indicators = this.carousel.indicators.filter((indicator) => indicator !== this);
      });
    }
    render() {
      this.cleanupButtonListeners();
      let buttons = [];
      let targets = this.calculateTargets();
      this.rendered = renderTemplate(this.querySelector("template"), (hydrate) => {
        return targets.map((target, index) => {
          let label = this.carousel.getAttribute("advance") === "page" ? `Show page ${index + 1}` : `Show slide ${index + 1}`;
          let button = hydrate({ attrs: { "aria-label": label } });
          this.buttonCleanups.push(
            on(button, "click", () => {
              this.carousel.pauseAutoplay();
              this.carousel.goTo(target);
            }).off
          );
          buttons.push(button);
          return button;
        });
      });
      this.buttons = buttons;
      this.targets = targets;
    }
    cleanupButtonListeners() {
      this.buttonCleanups.forEach((cleanup) => cleanup());
      this.buttonCleanups = [];
    }
    update(current) {
      let currentIndicator = this.targets.reduce((closest2, target, index) => {
        return Math.abs(current - target) < Math.abs(current - this.targets[closest2]) ? index : closest2;
      }, 0);
      if (this.carousel.state.atEnd) currentIndicator = this.targets.length - 1;
      this.buttons.forEach((button, index) => {
        if (index === currentIndicator) {
          setAttribute2(button, "data-selected", "");
          setAttribute2(button, "aria-current", "true");
        } else {
          removeAttribute(button, "data-selected");
          removeAttribute(button, "aria-current");
        }
      });
    }
    calculateTargets() {
      let indexes = this.carousel.slides.map((slide, index) => index);
      if (this.carousel.getAttribute("advance") !== "page") return indexes;
      let pageSize = this.carousel.visibleSlideCount();
      return indexes.filter((index) => index % pageSize === 0);
    }
    findCarousel() {
      if (this.hasAttribute("name")) {
        return document.querySelector(`ui-carousel[name="${CSS.escape(this.getAttribute("name"))}"]`);
      }
      return this.closest("ui-carousel");
    }
  };
  var UICarouselButton = class extends UIElement {
    mount() {
      this.onUnmount(
        on(this, "click", (event) => {
          let carousel = this.carousel();
          if (!carousel) return;
          carousel.pauseAutoplay();
          this.getAttribute("direction") === "previous" ? carousel.previous() : carousel.next();
          event.preventDefault();
        }).off
      );
    }
    carousel() {
      if (this.hasAttribute("name")) {
        return document.querySelector(`ui-carousel[name="${CSS.escape(this.getAttribute("name"))}"]`);
      }
      return this.closest("ui-carousel");
    }
  };
  function setDefaultAttribute(el, name, value3) {
    if (!el.hasAttribute(name)) setAttribute2(el, name, value3);
  }
  function clamp3(value3, min2, max2) {
    return Math.min(Math.max(value3, min2), max2);
  }
  element("carousel", UICarousel);
  element("carousel-indicators", UICarouselIndicators);
  element("carousel-button", UICarouselButton);

