  // js/mixins/mixin.js
  var Mixin = class {
    constructor(el, options = {}) {
      this.el = el;
      this.grouped = options.grouped === void 0 ? true : false;
      this.el.mixins = this.el.mixins ? this.el.mixins : /* @__PURE__ */ new Map();
      this.el.mixins.set(this.constructor.name, this);
      this.el[this.constructor.name] = true;
      if (!this.el.use) this.el.use = UIElement.prototype.use.bind(this.el);
      this.opts = options;
      this.boot?.({
        options: (defaults) => {
          let options2 = defaults;
          Object.entries(this.opts).forEach(([key, value3]) => {
            if (value3 !== void 0) {
              options2[key] = value3;
            }
          });
          this.opts = options2;
        }
      });
      queueMicrotask(() => {
        this.mount?.();
      });
    }
    options() {
      return this.opts;
    }
    hasGroup() {
      return !!this.group();
    }
    group() {
      if (this.grouped === false) return;
      return closest(this.el, (i) => i[this.groupedByType.name])?.use(this.groupedByType);
    }
    on(event, handler) {
      return on(this.el, event, handler);
    }
  };
  var MixinGroup = class extends Mixin {
    constructor(el, options = {}) {
      super(el, options);
    }
    walker() {
      return walker(this.el, (el, { skip, reject }) => {
        if (el[this.constructor.name] && el !== this.el) return reject();
        if (!el[this.groupOfType.name]) return skip();
        if (!el.mixins.get(this.groupOfType.name).grouped) return skip();
      });
    }
  };

  // js/mixins/controllable.js
  var Controllable = class extends Mixin {
    boot({ options }) {
      options({
        bubbles: false
      });
      this.initialState = this.el.value;
      this.getterFunc = () => {
      };
      this.setterFunc = (value3) => this.initialState = value3;
      Object.defineProperty(this.el, "value", {
        get: () => {
          return this.getterFunc();
        },
        set: (value3) => {
          this.setterFunc(value3);
        }
      });
    }
    initial(callback) {
      callback(this.initialState);
    }
    getter(func) {
      this.getterFunc = func;
    }
    setter(func) {
      this.setterFunc = func;
    }
    dispatch() {
      this.el.dispatchEvent(new Event("input", {
        bubbles: this.options().bubbles,
        cancelable: true
      }));
      this.el.dispatchEvent(new Event("change", {
        bubbles: this.options().bubbles,
        cancelable: true
      }));
    }
  };

  // js/mixins/dialogable.js
  var lastMouseDownEvent = null;
  document.addEventListener("mousedown", (event) => lastMouseDownEvent = event);
  var Dialogable = class extends Mixin {
    boot({ options }) {
      options({
        clickOutside: true,
        escape: true,
        triggers: []
      });
      this.onChanges = [];
      this.state = false;
      this.stopDialogFromFocusingTheFirstElement();
      let triggers = this.options().triggers;
      let observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.attributeName !== "open") return;
          this.el.hasAttribute("open") ? this.state = true : this.state = false;
        });
        this.onChanges.forEach((i) => i());
      });
      observer.observe(this.el, { attributeFilter: ["open"] });
      if (this.options().clickOutside) {
        let contentEl = this.el.querySelector("[data-os-modal-content], [data-os-modal-content]") || this.el;
        this.el.addEventListener("click", (e) => {
          if (e.target !== contentEl && contentEl?.contains(e.target)) return;
          if (lastMouseDownEvent && clickHappenedOutside(contentEl, lastMouseDownEvent) && clickHappenedOutside(contentEl, e)) {
            this.cancel();
            e.preventDefault();
            e.stopPropagation();
          }
        });
      }
      if (!this.options().escape) {
        setAttribute2(this.el, "closedby", "none");
        this.el.addEventListener("keydown", (e) => {
          if (e.key !== "Escape" && e.key !== "Esc") return;
          e.preventDefault();
        });
        this.el.addEventListener("cancel", (e) => {
          if (!e.isTrusted) return;
          e.preventDefault();
        });
      }
      if (this.el.hasAttribute("open")) {
        this.state = true;
        this.hide();
        this.show();
      }
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    show() {
      if (!this.el.isConnected) return;
      this.el.showModal();
    }
    hide() {
      this.el.close();
    }
    toggle() {
      this.state ? this.hide() : this.show();
    }
    cancel() {
      let event = new Event("cancel", { bubbles: false, cancelable: true });
      this.el.dispatchEvent(event);
      if (!event.defaultPrevented) {
        this.hide();
      }
    }
    getState() {
      return this.state;
    }
    setState(value3) {
      value3 ? this.show() : this.hide();
    }
    // By default, browsers focus the first focusable element inside a dialog when it is opened. This is bad for screen readers because
    // the focus could potentially be at the end of the dialog skipping all of the content. This also causes issues for iOS devices
    // as when inputs are focused and the keyboard is shown, hiding half of the dialog content...
    stopDialogFromFocusingTheFirstElement() {
      let placeholder = document.createElement("div");
      placeholder.setAttribute("data-os-focus-placeholder", "");
      placeholder.setAttribute("data-appended", "");
      placeholder.setAttribute("tabindex", "0");
      this.el.prepend(placeholder);
      this.onChange(() => {
        setAttribute2(placeholder, "style", this.state ? "display: none" : "display: block");
        if (this.state && isSafari() && !this.el.hasAttribute("autofocus") && this.el.querySelectorAll("[autofocus]").length === 0) {
          setTimeout(() => {
            this.el.setAttribute("tabindex", "-1");
            this.el.focus();
            this.el.blur();
          });
        }
      });
    }
  };
  function clickHappenedOutside(el, event) {
    let rect = el.getBoundingClientRect();
    let x = event.clientX;
    let y = event.clientY;
    let isInside = x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom;
    return !isInside;
  }

  // js/mixins/closeable.js
  var Closeable = class extends Mixin {
    boot() {
      this.onCloses = [];
    }
    onClose(callback) {
      this.onCloses.push(callback);
    }
    close() {
      this.onCloses.forEach((callback) => callback());
    }
  };


  // js/mixins/activatable.js
  var ActivatableGroup = class extends MixinGroup {
    groupOfType = Activatable;
    boot({ options }) {
      options({
        wrap: false,
        filter: false
      });
      this.onChanges = [];
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    activated(activeEl) {
      this.onChanges.forEach((i) => i());
    }
    activateFirst() {
      this.filterAwareWalker().first()?.use(Activatable).activate();
    }
    activateBySearch(query) {
      let found = this.filterAwareWalker().find((i) => i.textContent.toLowerCase().trim().startsWith(query.toLowerCase()));
      found?.use(Activatable).activate();
    }
    activateSelectedOrFirst(selectedEl) {
      let isHidden = (el) => el.matches("ui-option, ui-option-create") ? getComputedStyle(el).display === "none" : false;
      if (!selectedEl || isHidden(selectedEl)) {
        this.filterAwareWalker().first()?.use(Activatable).activate();
        return;
      }
      selectedEl?.use(Activatable).activate();
    }
    activateActiveOrFirst() {
      let active = this.getActive();
      if (!active) {
        this.filterAwareWalker().first()?.use(Activatable).activate();
        return;
      }
      active?.use(Activatable).activate();
    }
    activateActiveOrLast() {
      let active = this.getActive();
      if (!active) {
        this.filterAwareWalker().last()?.use(Activatable).activate();
        return;
      }
      active?.use(Activatable).activate();
    }
    activatePrev() {
      let active = this.getActive();
      if (!active) {
        this.filterAwareWalker().last()?.use(Activatable).activate();
        return;
      }
      let found;
      if (this.options.wrap) {
        found = this.filterAwareWalker().prevOrLast(active);
      } else {
        found = this.filterAwareWalker().prev(active);
      }
      found?.use(Activatable).activate();
    }
    activateNext() {
      let active = this.getActive();
      if (!active) {
        this.filterAwareWalker().first()?.use(Activatable).activate();
        return;
      }
      let found;
      if (this.options.wrap) {
        found = this.filterAwareWalker().nextOrFirst(active);
      } else {
        found = this.filterAwareWalker().next(active);
      }
      found?.use(Activatable).activate();
    }
    getActive() {
      return this.walker().find((i) => i.use(Activatable).isActive());
    }
    clearActive() {
      this.getActive()?.use(Activatable).deactivate();
    }
    filterAwareWalker() {
      let isHidden = (el) => el.matches("ui-option, ui-option-create") ? getComputedStyle(el).display === "none" : false;
      return walker(this.el, (el, { skip, reject }) => {
        if (el[this.constructor.name] && el !== this.el) return reject();
        if (!el[this.groupOfType.name]) return skip();
        if (el.hasAttribute("disabled")) return reject();
        if (isHidden(el)) return reject();
      });
    }
  };
  var Activatable = class _Activatable extends Mixin {
    groupedByType = ActivatableGroup;
    mount() {
      this.el.addEventListener("mouseenter", () => {
        this.activate();
      });
      this.el.addEventListener("mouseleave", () => {
        this.deactivate();
      });
    }
    activate(force = false) {
      if (this.group()) {
        this.group().walker().each((item) => item.use(_Activatable).deactivate(false));
      }
      if (this.el.hasAttribute("disabled") && !force) return;
      setAttribute2(this.el, "data-active", "");
      if (isUsingKeyboard()) {
        this.el.scrollIntoView({ block: "nearest" });
      }
      this.group() && this.group().activated(this.el);
    }
    deactivate(notify = true) {
      removeAttribute(this.el, "data-active");
      notify && this.group() && this.group().activated(this.el);
    }
    isActive() {
      return this.el.hasAttribute("data-active");
    }
  };

  // js/mixins/filterable.js
  var FilterableGroup = class extends MixinGroup {
    groupOfType = Filterable;
    boot({ options }) {
      options({});
      this.onChanges = [];
      this.lastSearch = "";
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    filter(search2) {
      if (search2 === "") {
        this.walker().each((i) => {
          i.use(Filterable).unfilter();
        });
      } else {
        this.walker().each((i) => {
          if (this.matches(i, search2)) {
            i.use(Filterable).unfilter();
          } else {
            i.use(Filterable).filter();
          }
        });
      }
      if (this.lastSearch !== search2) {
        this.onChanges.forEach((i) => i());
      }
      this.lastSearch = search2;
    }
    matches(el, search2) {
      return this.normalize(el.textContent).includes(this.normalize(search2));
    }
    // This function normalizes the value to remove diacritics (accents) and convert to lowercase
    // to ensure that the search is case-insensitive and diacritic-insensitive...
    normalize(value3) {
      if (value3 == null) return "";
      return value3.normalize("NFD").replace(/\p{Diacritic}/gu, "").toLowerCase().trim();
    }
    hasResults() {
      return this.walker().some((i) => !i.use(Filterable).isFiltered());
    }
  };
  var Filterable = class extends Mixin {
    groupedByType = FilterableGroup;
    boot({ options }) {
      options({ mirror: null, keep: false });
      this.onChanges = [];
    }
    filter() {
      if (this.options().keep) return;
      setAttribute2(this.el, "data-hidden", "");
      if (this.options().mirror) setAttribute2(this.options().mirror, "data-hidden", "");
    }
    unfilter() {
      if (this.options().keep) return;
      removeAttribute(this.el, "data-hidden");
      if (this.options().mirror) removeAttribute(this.options().mirror, "data-hidden", "");
    }
    isFiltered() {
      return this.el.hasAttribute("data-hidden");
    }
  };

  // js/mixins/popoverable.js
  var currentlyOpenPopoversByScope = /* @__PURE__ */ new Map();
  var Popoverable = class extends Mixin {
    boot({ options }) {
      options({ triggers: [], scope: null });
      let scope = this.options().scope || "global";
      setAttribute2(this.el, "popover", "manual");
      this.triggers = this.options().triggers;
      this.onChanges = [];
      this.state = false;
      on(this.el, "beforetoggle", (e) => {
        let oldState = this.state;
        this.state = e.newState === "open";
        if (this.state) {
          closeOtherOpenPopovers(this.el, scope);
          let controller = new AbortController();
          let activeElement = document.activeElement;
          let triggers = [...this.triggers, activeElement];
          setTimeout(() => {
            closeOnClickOutside(this.el, triggers, controller);
            closeOnFocusAway(this.el, triggers, controller);
            closeOnEscape(this.el, triggers, controller);
          });
          this.el.addEventListener("beforetoggle", (e2) => {
            if (e2.newState === "closed") {
              controller.abort();
              activeElement?.focus({ preventScroll: true });
            }
          }, { signal: controller.signal });
        }
        if (oldState !== this.state) {
          this.onChanges.forEach((i) => i(this.state, oldState));
        }
      });
      on(this.el, "toggle", (e) => {
        if (e.newState === "open") {
          if (!currentlyOpenPopoversByScope.has(scope)) {
            currentlyOpenPopoversByScope.set(scope, /* @__PURE__ */ new Set());
          }
          currentlyOpenPopoversByScope.get(scope).add(this.el);
        } else if (e.newState === "closed") {
          if (!currentlyOpenPopoversByScope.has(scope)) return;
          currentlyOpenPopoversByScope.get(scope).delete(this.el);
          if (currentlyOpenPopoversByScope.get(scope).size === 0) {
            currentlyOpenPopoversByScope.delete(scope);
          }
        }
      });
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    setState(value3) {
      value3 ? this.show() : this.hide();
    }
    getState() {
      return this.state;
    }
    toggle() {
      this.el.isConnected && this.el.togglePopover();
    }
    show() {
      this.el.isConnected && this.el.showPopover();
    }
    hide() {
      this.el.isConnected && this.el.hidePopover();
    }
  };
  function closeOtherOpenPopovers(el, scope) {
    if (!currentlyOpenPopoversByScope.has(scope)) return;
    currentlyOpenPopoversByScope.get(scope).forEach((popoverEl) => {
      if (el.contains(popoverEl) || popoverEl.contains(el)) return;
      popoverEl.hidePopover();
    });
  }
  function closeOnClickOutside(el, except, controller) {
    document.addEventListener("click", (e) => {
      if (el.contains(e.target) || except.includes(e.target)) return;
      el.hidePopover();
    }, { signal: controller.signal });
  }
  function closeOnFocusAway(el, except, controller) {
    document.addEventListener("focusin", (e) => {
      if (el.contains(e.target) || except.includes(e.target)) return;
      controller.abort();
      el.hidePopover();
    }, {
      // Without "capture: true", when you focus away from the popover onto an element that triggers a popover
      // on focus (a tooltip), it will focus back this popover's trigger instead of keeping focus on the tooltip button.
      // It does this because only one popover can be open at a time, so focusing the tooltip, opens a popover, closing this one,
      // which will trigger the "focus back" behavior.
      capture: true,
      signal: controller.signal
    });
  }
  function closeOnEscape(el, except, controller) {
    document.addEventListener("keydown", (e) => {
      if (e.key !== "Escape") return;
      el.hidePopover();
    }, { signal: controller.signal });
  }

  // js/mixins/disableable.js
  var Disableable = class extends Mixin {
    boot({ options }) {
      options({
        disableWithParent: true
      });
      this.onChanges = [];
      Object.defineProperty(this.el, "disabled", {
        get: () => {
          return this.el.hasAttribute("disabled");
        },
        set: (value3) => {
          if (value3) {
            this.el.setAttribute("disabled", "");
          } else {
            this.el.removeAttribute("disabled");
          }
        }
      });
      if (this.el.hasAttribute("disabled")) {
        this.el.disabled = true;
      } else if (this.options().disableWithParent && this.el.parentElement?.closest("[disabled]")) {
        this.el.disabled = true;
      }
      let observer = new MutationObserver((mutations) => {
        this.onChanges.forEach((i) => i(this.el.disabled));
      });
      observer.observe(this.el, { attributeFilter: ["disabled"] });
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    onInitAndChange(callback) {
      callback(this.el.disabled);
      this.onChanges.push(callback);
    }
    enabled(callback) {
      return (...args) => {
        if (this.el.disabled) return;
        return callback(...args);
      };
    }
    disabled(callback) {
      return (...args) => {
        if (!this.el.disabled) return;
        return callback(...args);
      };
    }
    isDisabled() {
      return this.el.disabled;
    }
  };

  // node_modules/@floating-ui/utils/dist/floating-ui.utils.mjs
  var sides = ["top", "right", "bottom", "left"];
  var min = Math.min;
  var max = Math.max;
  var round = Math.round;
  var floor = Math.floor;
  var createCoords = (v) => ({
    x: v,
    y: v
  });
  var oppositeSideMap = {
    left: "right",
    right: "left",
    bottom: "top",
    top: "bottom"
  };
  var oppositeAlignmentMap = {
    start: "end",
    end: "start"
  };
  function clamp(start, value3, end) {
    return max(start, min(value3, end));
  }
  function evaluate(value3, param) {
    return typeof value3 === "function" ? value3(param) : value3;
  }
  function getSide(placement) {
    return placement.split("-")[0];
  }
  function getAlignment(placement) {
    return placement.split("-")[1];
  }
  function getOppositeAxis(axis) {
    return axis === "x" ? "y" : "x";
  }
  function getAxisLength(axis) {
    return axis === "y" ? "height" : "width";
  }
  function getSideAxis(placement) {
    return ["top", "bottom"].includes(getSide(placement)) ? "y" : "x";
  }
  function getAlignmentAxis(placement) {
    return getOppositeAxis(getSideAxis(placement));
  }
  function getAlignmentSides(placement, rects, rtl) {
    if (rtl === void 0) {
      rtl = false;
    }
    const alignment = getAlignment(placement);
    const alignmentAxis = getAlignmentAxis(placement);
    const length = getAxisLength(alignmentAxis);
    let mainAlignmentSide = alignmentAxis === "x" ? alignment === (rtl ? "end" : "start") ? "right" : "left" : alignment === "start" ? "bottom" : "top";
    if (rects.reference[length] > rects.floating[length]) {
      mainAlignmentSide = getOppositePlacement(mainAlignmentSide);
    }
    return [mainAlignmentSide, getOppositePlacement(mainAlignmentSide)];
  }
  function getExpandedPlacements(placement) {
    const oppositePlacement = getOppositePlacement(placement);
    return [getOppositeAlignmentPlacement(placement), oppositePlacement, getOppositeAlignmentPlacement(oppositePlacement)];
  }
  function getOppositeAlignmentPlacement(placement) {
    return placement.replace(/start|end/g, (alignment) => oppositeAlignmentMap[alignment]);
  }
  function getSideList(side, isStart, rtl) {
    const lr = ["left", "right"];
    const rl = ["right", "left"];
    const tb = ["top", "bottom"];
    const bt = ["bottom", "top"];
    switch (side) {
      case "top":
      case "bottom":
        if (rtl) return isStart ? rl : lr;
        return isStart ? lr : rl;
      case "left":
      case "right":
        return isStart ? tb : bt;
      default:
        return [];
    }
  }
  function getOppositeAxisPlacements(placement, flipAlignment, direction, rtl) {
    const alignment = getAlignment(placement);
    let list = getSideList(getSide(placement), direction === "start", rtl);
    if (alignment) {
      list = list.map((side) => side + "-" + alignment);
      if (flipAlignment) {
        list = list.concat(list.map(getOppositeAlignmentPlacement));
      }
    }
    return list;
  }
  function getOppositePlacement(placement) {
    return placement.replace(/left|right|bottom|top/g, (side) => oppositeSideMap[side]);
  }
  function expandPaddingObject(padding) {
    return {
      top: 0,
      right: 0,
      bottom: 0,
      left: 0,
      ...padding
    };
  }
  function getPaddingObject(padding) {
    return typeof padding !== "number" ? expandPaddingObject(padding) : {
      top: padding,
      right: padding,
      bottom: padding,
      left: padding
    };
  }
  function rectToClientRect(rect) {
    const {
      x,
      y,
      width,
      height
    } = rect;
    return {
      width,
      height,
      top: y,
      left: x,
      right: x + width,
      bottom: y + height,
      x,
      y
    };
  }

  // node_modules/@floating-ui/core/dist/floating-ui.core.mjs
  function computeCoordsFromPlacement(_ref, placement, rtl) {
    let {
      reference,
      floating
    } = _ref;
    const sideAxis = getSideAxis(placement);
    const alignmentAxis = getAlignmentAxis(placement);
    const alignLength = getAxisLength(alignmentAxis);
    const side = getSide(placement);
    const isVertical = sideAxis === "y";
    const commonX = reference.x + reference.width / 2 - floating.width / 2;
    const commonY = reference.y + reference.height / 2 - floating.height / 2;
    const commonAlign = reference[alignLength] / 2 - floating[alignLength] / 2;
    let coords;
    switch (side) {
      case "top":
        coords = {
          x: commonX,
          y: reference.y - floating.height
        };
        break;
      case "bottom":
        coords = {
          x: commonX,
          y: reference.y + reference.height
        };
        break;
      case "right":
        coords = {
          x: reference.x + reference.width,
          y: commonY
        };
        break;
      case "left":
        coords = {
          x: reference.x - floating.width,
          y: commonY
        };
        break;
      default:
        coords = {
          x: reference.x,
          y: reference.y
        };
    }
    switch (getAlignment(placement)) {
      case "start":
        coords[alignmentAxis] -= commonAlign * (rtl && isVertical ? -1 : 1);
        break;
      case "end":
        coords[alignmentAxis] += commonAlign * (rtl && isVertical ? -1 : 1);
        break;
    }
    return coords;
  }
  var computePosition = async (reference, floating, config) => {
    const {
      placement = "bottom",
      strategy = "absolute",
      middleware = [],
      platform: platform2
    } = config;
    const validMiddleware = middleware.filter(Boolean);
    const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(floating));
    let rects = await platform2.getElementRects({
      reference,
      floating,
      strategy
    });
    let {
      x,
      y
    } = computeCoordsFromPlacement(rects, placement, rtl);
    let statefulPlacement = placement;
    let middlewareData = {};
    let resetCount = 0;
    for (let i = 0; i < validMiddleware.length; i++) {
      const {
        name,
        fn
      } = validMiddleware[i];
      const {
        x: nextX,
        y: nextY,
        data,
        reset
      } = await fn({
        x,
        y,
        initialPlacement: placement,
        placement: statefulPlacement,
        strategy,
        middlewareData,
        rects,
        platform: platform2,
        elements: {
          reference,
          floating
        }
      });
      x = nextX != null ? nextX : x;
      y = nextY != null ? nextY : y;
      middlewareData = {
        ...middlewareData,
        [name]: {
          ...middlewareData[name],
          ...data
        }
      };
      if (reset && resetCount <= 50) {
        resetCount++;
        if (typeof reset === "object") {
          if (reset.placement) {
            statefulPlacement = reset.placement;
          }
          if (reset.rects) {
            rects = reset.rects === true ? await platform2.getElementRects({
              reference,
              floating,
              strategy
            }) : reset.rects;
          }
          ({
            x,
            y
          } = computeCoordsFromPlacement(rects, statefulPlacement, rtl));
        }
        i = -1;
      }
    }
    return {
      x,
      y,
      placement: statefulPlacement,
      strategy,
      middlewareData
    };
  };
  async function detectOverflow(state, options) {
    var _await$platform$isEle;
    if (options === void 0) {
      options = {};
    }
    const {
      x,
      y,
      platform: platform2,
      rects,
      elements,
      strategy
    } = state;
    const {
      boundary = "clippingAncestors",
      rootBoundary = "viewport",
      elementContext = "floating",
      altBoundary = false,
      padding = 0
    } = evaluate(options, state);
    const paddingObject = getPaddingObject(padding);
    const altContext = elementContext === "floating" ? "reference" : "floating";
    const element2 = elements[altBoundary ? altContext : elementContext];
    const clippingClientRect = rectToClientRect(await platform2.getClippingRect({
      element: ((_await$platform$isEle = await (platform2.isElement == null ? void 0 : platform2.isElement(element2))) != null ? _await$platform$isEle : true) ? element2 : element2.contextElement || await (platform2.getDocumentElement == null ? void 0 : platform2.getDocumentElement(elements.floating)),
      boundary,
      rootBoundary,
      strategy
    }));
    const rect = elementContext === "floating" ? {
      x,
      y,
      width: rects.floating.width,
      height: rects.floating.height
    } : rects.reference;
    const offsetParent = await (platform2.getOffsetParent == null ? void 0 : platform2.getOffsetParent(elements.floating));
    const offsetScale = await (platform2.isElement == null ? void 0 : platform2.isElement(offsetParent)) ? await (platform2.getScale == null ? void 0 : platform2.getScale(offsetParent)) || {
      x: 1,
      y: 1
    } : {
      x: 1,
      y: 1
    };
    const elementClientRect = rectToClientRect(platform2.convertOffsetParentRelativeRectToViewportRelativeRect ? await platform2.convertOffsetParentRelativeRectToViewportRelativeRect({
      elements,
      rect,
      offsetParent,
      strategy
    }) : rect);
    return {
      top: (clippingClientRect.top - elementClientRect.top + paddingObject.top) / offsetScale.y,
      bottom: (elementClientRect.bottom - clippingClientRect.bottom + paddingObject.bottom) / offsetScale.y,
      left: (clippingClientRect.left - elementClientRect.left + paddingObject.left) / offsetScale.x,
      right: (elementClientRect.right - clippingClientRect.right + paddingObject.right) / offsetScale.x
    };
  }
  var flip = function(options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: "flip",
      options,
      async fn(state) {
        var _middlewareData$arrow, _middlewareData$flip;
        const {
          placement,
          middlewareData,
          rects,
          initialPlacement,
          platform: platform2,
          elements
        } = state;
        const {
          mainAxis: checkMainAxis = true,
          crossAxis: checkCrossAxis = true,
          fallbackPlacements: specifiedFallbackPlacements,
          fallbackStrategy = "bestFit",
          fallbackAxisSideDirection = "none",
          flipAlignment = true,
          ...detectOverflowOptions
        } = evaluate(options, state);
        if ((_middlewareData$arrow = middlewareData.arrow) != null && _middlewareData$arrow.alignmentOffset) {
          return {};
        }
        const side = getSide(placement);
        const initialSideAxis = getSideAxis(initialPlacement);
        const isBasePlacement = getSide(initialPlacement) === initialPlacement;
        const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(elements.floating));
        const fallbackPlacements = specifiedFallbackPlacements || (isBasePlacement || !flipAlignment ? [getOppositePlacement(initialPlacement)] : getExpandedPlacements(initialPlacement));
        const hasFallbackAxisSideDirection = fallbackAxisSideDirection !== "none";
        if (!specifiedFallbackPlacements && hasFallbackAxisSideDirection) {
          fallbackPlacements.push(...getOppositeAxisPlacements(initialPlacement, flipAlignment, fallbackAxisSideDirection, rtl));
        }
        const placements2 = [initialPlacement, ...fallbackPlacements];
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const overflows = [];
        let overflowsData = ((_middlewareData$flip = middlewareData.flip) == null ? void 0 : _middlewareData$flip.overflows) || [];
        if (checkMainAxis) {
          overflows.push(overflow[side]);
        }
        if (checkCrossAxis) {
          const sides2 = getAlignmentSides(placement, rects, rtl);
          overflows.push(overflow[sides2[0]], overflow[sides2[1]]);
        }
        overflowsData = [...overflowsData, {
          placement,
          overflows
        }];
        if (!overflows.every((side2) => side2 <= 0)) {
          var _middlewareData$flip2, _overflowsData$filter;
          const nextIndex = (((_middlewareData$flip2 = middlewareData.flip) == null ? void 0 : _middlewareData$flip2.index) || 0) + 1;
          const nextPlacement = placements2[nextIndex];
          if (nextPlacement) {
            return {
              data: {
                index: nextIndex,
                overflows: overflowsData
              },
              reset: {
                placement: nextPlacement
              }
            };
          }
          let resetPlacement = (_overflowsData$filter = overflowsData.filter((d) => d.overflows[0] <= 0).sort((a, b) => a.overflows[1] - b.overflows[1])[0]) == null ? void 0 : _overflowsData$filter.placement;
          if (!resetPlacement) {
            switch (fallbackStrategy) {
              case "bestFit": {
                var _overflowsData$filter2;
                const placement2 = (_overflowsData$filter2 = overflowsData.filter((d) => {
                  if (hasFallbackAxisSideDirection) {
                    const currentSideAxis = getSideAxis(d.placement);
                    return currentSideAxis === initialSideAxis || // Create a bias to the `y` side axis due to horizontal
                    // reading directions favoring greater width.
                    currentSideAxis === "y";
                  }
                  return true;
                }).map((d) => [d.placement, d.overflows.filter((overflow2) => overflow2 > 0).reduce((acc, overflow2) => acc + overflow2, 0)]).sort((a, b) => a[1] - b[1])[0]) == null ? void 0 : _overflowsData$filter2[0];
                if (placement2) {
                  resetPlacement = placement2;
                }
                break;
              }
              case "initialPlacement":
                resetPlacement = initialPlacement;
                break;
            }
          }
          if (placement !== resetPlacement) {
            return {
              reset: {
                placement: resetPlacement
              }
            };
          }
        }
        return {};
      }
    };
  };
  function getSideOffsets(overflow, rect) {
    return {
      top: overflow.top - rect.height,
      right: overflow.right - rect.width,
      bottom: overflow.bottom - rect.height,
      left: overflow.left - rect.width
    };
  }
  function isAnySideFullyClipped(overflow) {
    return sides.some((side) => overflow[side] >= 0);
  }
  var hide = function(options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: "hide",
      options,
      async fn(state) {
        const {
          rects
        } = state;
        const {
          strategy = "referenceHidden",
          ...detectOverflowOptions
        } = evaluate(options, state);
        switch (strategy) {
          case "referenceHidden": {
            const overflow = await detectOverflow(state, {
              ...detectOverflowOptions,
              elementContext: "reference"
            });
            const offsets = getSideOffsets(overflow, rects.reference);
            return {
              data: {
                referenceHiddenOffsets: offsets,
                referenceHidden: isAnySideFullyClipped(offsets)
              }
            };
          }
          case "escaped": {
            const overflow = await detectOverflow(state, {
              ...detectOverflowOptions,
              altBoundary: true
            });
            const offsets = getSideOffsets(overflow, rects.floating);
            return {
              data: {
                escapedOffsets: offsets,
                escaped: isAnySideFullyClipped(offsets)
              }
            };
          }
          default: {
            return {};
          }
        }
      }
    };
  };
  async function convertValueToCoords(state, options) {
    const {
      placement,
      platform: platform2,
      elements
    } = state;
    const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(elements.floating));
    const side = getSide(placement);
    const alignment = getAlignment(placement);
    const isVertical = getSideAxis(placement) === "y";
    const mainAxisMulti = ["left", "top"].includes(side) ? -1 : 1;
    const crossAxisMulti = rtl && isVertical ? -1 : 1;
    const rawValue = evaluate(options, state);
    let {
      mainAxis,
      crossAxis,
      alignmentAxis
    } = typeof rawValue === "number" ? {
      mainAxis: rawValue,
      crossAxis: 0,
      alignmentAxis: null
    } : {
      mainAxis: rawValue.mainAxis || 0,
      crossAxis: rawValue.crossAxis || 0,
      alignmentAxis: rawValue.alignmentAxis
    };
    if (alignment && typeof alignmentAxis === "number") {
      crossAxis = alignment === "end" ? alignmentAxis * -1 : alignmentAxis;
    }
    return isVertical ? {
      x: crossAxis * crossAxisMulti,
      y: mainAxis * mainAxisMulti
    } : {
      x: mainAxis * mainAxisMulti,
      y: crossAxis * crossAxisMulti
    };
  }
  var offset = function(options) {
    if (options === void 0) {
      options = 0;
    }
    return {
      name: "offset",
      options,
      async fn(state) {
        var _middlewareData$offse, _middlewareData$arrow;
        const {
          x,
          y,
          placement,
          middlewareData
        } = state;
        const diffCoords = await convertValueToCoords(state, options);
        if (placement === ((_middlewareData$offse = middlewareData.offset) == null ? void 0 : _middlewareData$offse.placement) && (_middlewareData$arrow = middlewareData.arrow) != null && _middlewareData$arrow.alignmentOffset) {
          return {};
        }
        return {
          x: x + diffCoords.x,
          y: y + diffCoords.y,
          data: {
            ...diffCoords,
            placement
          }
        };
      }
    };
  };
  var shift = function(options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: "shift",
      options,
      async fn(state) {
        const {
          x,
          y,
          placement
        } = state;
        const {
          mainAxis: checkMainAxis = true,
          crossAxis: checkCrossAxis = false,
          limiter = {
            fn: (_ref) => {
              let {
                x: x2,
                y: y2
              } = _ref;
              return {
                x: x2,
                y: y2
              };
            }
          },
          ...detectOverflowOptions
        } = evaluate(options, state);
        const coords = {
          x,
          y
        };
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const crossAxis = getSideAxis(getSide(placement));
        const mainAxis = getOppositeAxis(crossAxis);
        let mainAxisCoord = coords[mainAxis];
        let crossAxisCoord = coords[crossAxis];
        if (checkMainAxis) {
          const minSide = mainAxis === "y" ? "top" : "left";
          const maxSide = mainAxis === "y" ? "bottom" : "right";
          const min2 = mainAxisCoord + overflow[minSide];
          const max2 = mainAxisCoord - overflow[maxSide];
          mainAxisCoord = clamp(min2, mainAxisCoord, max2);
        }
        if (checkCrossAxis) {
          const minSide = crossAxis === "y" ? "top" : "left";
          const maxSide = crossAxis === "y" ? "bottom" : "right";
          const min2 = crossAxisCoord + overflow[minSide];
          const max2 = crossAxisCoord - overflow[maxSide];
          crossAxisCoord = clamp(min2, crossAxisCoord, max2);
        }
        const limitedCoords = limiter.fn({
          ...state,
          [mainAxis]: mainAxisCoord,
          [crossAxis]: crossAxisCoord
        });
        return {
          ...limitedCoords,
          data: {
            x: limitedCoords.x - x,
            y: limitedCoords.y - y,
            enabled: {
              [mainAxis]: checkMainAxis,
              [crossAxis]: checkCrossAxis
            }
          }
        };
      }
    };
  };
  var size = function(options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: "size",
      options,
      async fn(state) {
        var _state$middlewareData, _state$middlewareData2;
        const {
          placement,
          rects,
          platform: platform2,
          elements
        } = state;
        const {
          apply: apply3 = () => {
          },
          ...detectOverflowOptions
        } = evaluate(options, state);
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const side = getSide(placement);
        const alignment = getAlignment(placement);
        const isYAxis = getSideAxis(placement) === "y";
        const {
          width,
          height
        } = rects.floating;
        let heightSide;
        let widthSide;
        if (side === "top" || side === "bottom") {
          heightSide = side;
          widthSide = alignment === (await (platform2.isRTL == null ? void 0 : platform2.isRTL(elements.floating)) ? "start" : "end") ? "left" : "right";
        } else {
          widthSide = side;
          heightSide = alignment === "end" ? "top" : "bottom";
        }
        const maximumClippingHeight = height - overflow.top - overflow.bottom;
        const maximumClippingWidth = width - overflow.left - overflow.right;
        const overflowAvailableHeight = min(height - overflow[heightSide], maximumClippingHeight);
        const overflowAvailableWidth = min(width - overflow[widthSide], maximumClippingWidth);
        const noShift = !state.middlewareData.shift;
        let availableHeight = overflowAvailableHeight;
        let availableWidth = overflowAvailableWidth;
        if ((_state$middlewareData = state.middlewareData.shift) != null && _state$middlewareData.enabled.x) {
          availableWidth = maximumClippingWidth;
        }
        if ((_state$middlewareData2 = state.middlewareData.shift) != null && _state$middlewareData2.enabled.y) {
          availableHeight = maximumClippingHeight;
        }
        if (noShift && !alignment) {
          const xMin = max(overflow.left, 0);
          const xMax = max(overflow.right, 0);
          const yMin = max(overflow.top, 0);
          const yMax = max(overflow.bottom, 0);
          if (isYAxis) {
            availableWidth = width - 2 * (xMin !== 0 || xMax !== 0 ? xMin + xMax : max(overflow.left, overflow.right));
          } else {
            availableHeight = height - 2 * (yMin !== 0 || yMax !== 0 ? yMin + yMax : max(overflow.top, overflow.bottom));
          }
        }
        await apply3({
          ...state,
          availableWidth,
          availableHeight
        });
        const nextDimensions = await platform2.getDimensions(elements.floating);
        if (width !== nextDimensions.width || height !== nextDimensions.height) {
          return {
            reset: {
              rects: true
            }
          };
        }
        return {};
      }
    };
  };

  // node_modules/@floating-ui/utils/dist/floating-ui.utils.dom.mjs
  function hasWindow() {
    return typeof window !== "undefined";
  }
  function getNodeName(node) {
    if (isNode(node)) {
      return (node.nodeName || "").toLowerCase();
    }
    return "#document";
  }
  function getWindow(node) {
    var _node$ownerDocument;
    return (node == null || (_node$ownerDocument = node.ownerDocument) == null ? void 0 : _node$ownerDocument.defaultView) || window;
  }
  function getDocumentElement(node) {
    var _ref;
    return (_ref = (isNode(node) ? node.ownerDocument : node.document) || window.document) == null ? void 0 : _ref.documentElement;
  }
  function isNode(value3) {
    if (!hasWindow()) {
      return false;
    }
    return value3 instanceof Node || value3 instanceof getWindow(value3).Node;
  }
  function isElement(value3) {
    if (!hasWindow()) {
      return false;
    }
    return value3 instanceof Element || value3 instanceof getWindow(value3).Element;
  }
  function isHTMLElement(value3) {
    if (!hasWindow()) {
      return false;
    }
    return value3 instanceof HTMLElement || value3 instanceof getWindow(value3).HTMLElement;
  }
  function isShadowRoot(value3) {
    if (!hasWindow() || typeof ShadowRoot === "undefined") {
      return false;
    }
    return value3 instanceof ShadowRoot || value3 instanceof getWindow(value3).ShadowRoot;
  }
  function isOverflowElement(element2) {
    const {
      overflow,
      overflowX,
      overflowY,
      display
    } = getComputedStyle2(element2);
    return /auto|scroll|overlay|hidden|clip/.test(overflow + overflowY + overflowX) && !["inline", "contents"].includes(display);
  }
  function isTableElement(element2) {
    return ["table", "td", "th"].includes(getNodeName(element2));
  }
  function isTopLayer(element2) {
    return [":popover-open", ":modal"].some((selector) => {
      try {
        return element2.matches(selector);
      } catch (e) {
        return false;
      }
    });
  }
  function isContainingBlock(elementOrCss) {
    const webkit = isWebKit();
    const css = isElement(elementOrCss) ? getComputedStyle2(elementOrCss) : elementOrCss;
    return ["transform", "translate", "scale", "rotate", "perspective"].some((value3) => css[value3] ? css[value3] !== "none" : false) || (css.containerType ? css.containerType !== "normal" : false) || !webkit && (css.backdropFilter ? css.backdropFilter !== "none" : false) || !webkit && (css.filter ? css.filter !== "none" : false) || ["transform", "translate", "scale", "rotate", "perspective", "filter"].some((value3) => (css.willChange || "").includes(value3)) || ["paint", "layout", "strict", "content"].some((value3) => (css.contain || "").includes(value3));
  }
  function getContainingBlock(element2) {
    let currentNode = getParentNode(element2);
    while (isHTMLElement(currentNode) && !isLastTraversableNode(currentNode)) {
      if (isContainingBlock(currentNode)) {
        return currentNode;
      } else if (isTopLayer(currentNode)) {
        return null;
      }
      currentNode = getParentNode(currentNode);
    }
    return null;
  }
  function isWebKit() {
    if (typeof CSS === "undefined" || !CSS.supports) return false;
    return CSS.supports("-webkit-backdrop-filter", "none");
  }
  function isLastTraversableNode(node) {
    return ["html", "body", "#document"].includes(getNodeName(node));
  }
  function getComputedStyle2(element2) {
    return getWindow(element2).getComputedStyle(element2);
  }
  function getNodeScroll(element2) {
    if (isElement(element2)) {
      return {
        scrollLeft: element2.scrollLeft,
        scrollTop: element2.scrollTop
      };
    }
    return {
      scrollLeft: element2.scrollX,
      scrollTop: element2.scrollY
    };
  }
  function getParentNode(node) {
    if (getNodeName(node) === "html") {
      return node;
    }
    const result = (
      // Step into the shadow DOM of the parent of a slotted node.
      node.assignedSlot || // DOM Element detected.
      node.parentNode || // ShadowRoot detected.
      isShadowRoot(node) && node.host || // Fallback.
      getDocumentElement(node)
    );
    return isShadowRoot(result) ? result.host : result;
  }
  function getNearestOverflowAncestor(node) {
    const parentNode = getParentNode(node);
    if (isLastTraversableNode(parentNode)) {
      return node.ownerDocument ? node.ownerDocument.body : node.body;
    }
    if (isHTMLElement(parentNode) && isOverflowElement(parentNode)) {
      return parentNode;
    }
    return getNearestOverflowAncestor(parentNode);
  }
  function getOverflowAncestors(node, list, traverseIframes) {
    var _node$ownerDocument2;
    if (list === void 0) {
      list = [];
    }
    if (traverseIframes === void 0) {
      traverseIframes = true;
    }
    const scrollableAncestor = getNearestOverflowAncestor(node);
    const isBody = scrollableAncestor === ((_node$ownerDocument2 = node.ownerDocument) == null ? void 0 : _node$ownerDocument2.body);
    const win = getWindow(scrollableAncestor);
    if (isBody) {
      const frameElement = getFrameElement(win);
      return list.concat(win, win.visualViewport || [], isOverflowElement(scrollableAncestor) ? scrollableAncestor : [], frameElement && traverseIframes ? getOverflowAncestors(frameElement) : []);
    }
    return list.concat(scrollableAncestor, getOverflowAncestors(scrollableAncestor, [], traverseIframes));
  }
  function getFrameElement(win) {
    return win.parent && Object.getPrototypeOf(win.parent) ? win.frameElement : null;
  }

  // node_modules/@floating-ui/dom/dist/floating-ui.dom.mjs
  function getCssDimensions(element2) {
    const css = getComputedStyle2(element2);
    let width = parseFloat(css.width) || 0;
    let height = parseFloat(css.height) || 0;
    const hasOffset = isHTMLElement(element2);
    const offsetWidth = hasOffset ? element2.offsetWidth : width;
    const offsetHeight = hasOffset ? element2.offsetHeight : height;
    const shouldFallback = round(width) !== offsetWidth || round(height) !== offsetHeight;
    if (shouldFallback) {
      width = offsetWidth;
      height = offsetHeight;
    }
    return {
      width,
      height,
      $: shouldFallback
    };
  }
  function unwrapElement(element2) {
    return !isElement(element2) ? element2.contextElement : element2;
  }
  function getScale(element2) {
    const domElement = unwrapElement(element2);
    if (!isHTMLElement(domElement)) {
      return createCoords(1);
    }
    const rect = domElement.getBoundingClientRect();
    const {
      width,
      height,
      $
    } = getCssDimensions(domElement);
    let x = ($ ? round(rect.width) : rect.width) / width;
    let y = ($ ? round(rect.height) : rect.height) / height;
    if (!x || !Number.isFinite(x)) {
      x = 1;
    }
    if (!y || !Number.isFinite(y)) {
      y = 1;
    }
    return {
      x,
      y
    };
  }
  var noOffsets = /* @__PURE__ */ createCoords(0);
  function getVisualOffsets(element2) {
    const win = getWindow(element2);
    if (!isWebKit() || !win.visualViewport) {
      return noOffsets;
    }
    return {
      x: win.visualViewport.offsetLeft,
      y: win.visualViewport.offsetTop
    };
  }
  function shouldAddVisualOffsets(element2, isFixed, floatingOffsetParent) {
    if (isFixed === void 0) {
      isFixed = false;
    }
    if (!floatingOffsetParent || isFixed && floatingOffsetParent !== getWindow(element2)) {
      return false;
    }
    return isFixed;
  }
  function getBoundingClientRect(element2, includeScale, isFixedStrategy, offsetParent) {
    if (includeScale === void 0) {
      includeScale = false;
    }
    if (isFixedStrategy === void 0) {
      isFixedStrategy = false;
    }
    const clientRect = element2.getBoundingClientRect();
    const domElement = unwrapElement(element2);
    let scale = createCoords(1);
    if (includeScale) {
      if (offsetParent) {
        if (isElement(offsetParent)) {
          scale = getScale(offsetParent);
        }
      } else {
        scale = getScale(element2);
      }
    }
    const visualOffsets = shouldAddVisualOffsets(domElement, isFixedStrategy, offsetParent) ? getVisualOffsets(domElement) : createCoords(0);
    let x = (clientRect.left + visualOffsets.x) / scale.x;
    let y = (clientRect.top + visualOffsets.y) / scale.y;
    let width = clientRect.width / scale.x;
    let height = clientRect.height / scale.y;
    if (domElement) {
      const win = getWindow(domElement);
      const offsetWin = offsetParent && isElement(offsetParent) ? getWindow(offsetParent) : offsetParent;
      let currentWin = win;
      let currentIFrame = getFrameElement(currentWin);
      while (currentIFrame && offsetParent && offsetWin !== currentWin) {
        const iframeScale = getScale(currentIFrame);
        const iframeRect = currentIFrame.getBoundingClientRect();
        const css = getComputedStyle2(currentIFrame);
        const left = iframeRect.left + (currentIFrame.clientLeft + parseFloat(css.paddingLeft)) * iframeScale.x;
        const top = iframeRect.top + (currentIFrame.clientTop + parseFloat(css.paddingTop)) * iframeScale.y;
        x *= iframeScale.x;
        y *= iframeScale.y;
        width *= iframeScale.x;
        height *= iframeScale.y;
        x += left;
        y += top;
        currentWin = getWindow(currentIFrame);
        currentIFrame = getFrameElement(currentWin);
      }
    }
    return rectToClientRect({
      width,
      height,
      x,
      y
    });
  }
  function getWindowScrollBarX(element2, rect) {
    const leftScroll = getNodeScroll(element2).scrollLeft;
    if (!rect) {
      return getBoundingClientRect(getDocumentElement(element2)).left + leftScroll;
    }
    return rect.left + leftScroll;
  }
  function getHTMLOffset(documentElement, scroll, ignoreScrollbarX) {
    if (ignoreScrollbarX === void 0) {
      ignoreScrollbarX = false;
    }
    const htmlRect = documentElement.getBoundingClientRect();
    const x = htmlRect.left + scroll.scrollLeft - (ignoreScrollbarX ? 0 : (
      // RTL <body> scrollbar.
      getWindowScrollBarX(documentElement, htmlRect)
    ));
    const y = htmlRect.top + scroll.scrollTop;
    return {
      x,
      y
    };
  }
  function convertOffsetParentRelativeRectToViewportRelativeRect(_ref) {
    let {
      elements,
      rect,
      offsetParent,
      strategy
    } = _ref;
    const isFixed = strategy === "fixed";
    const documentElement = getDocumentElement(offsetParent);
    const topLayer = elements ? isTopLayer(elements.floating) : false;
    if (offsetParent === documentElement || topLayer && isFixed) {
      return rect;
    }
    let scroll = {
      scrollLeft: 0,
      scrollTop: 0
    };
    let scale = createCoords(1);
    const offsets = createCoords(0);
    const isOffsetParentAnElement = isHTMLElement(offsetParent);
    if (isOffsetParentAnElement || !isOffsetParentAnElement && !isFixed) {
      if (getNodeName(offsetParent) !== "body" || isOverflowElement(documentElement)) {
        scroll = getNodeScroll(offsetParent);
      }
      if (isHTMLElement(offsetParent)) {
        const offsetRect = getBoundingClientRect(offsetParent);
        scale = getScale(offsetParent);
        offsets.x = offsetRect.x + offsetParent.clientLeft;
        offsets.y = offsetRect.y + offsetParent.clientTop;
      }
    }
    const htmlOffset = documentElement && !isOffsetParentAnElement && !isFixed ? getHTMLOffset(documentElement, scroll, true) : createCoords(0);
    return {
      width: rect.width * scale.x,
      height: rect.height * scale.y,
      x: rect.x * scale.x - scroll.scrollLeft * scale.x + offsets.x + htmlOffset.x,
      y: rect.y * scale.y - scroll.scrollTop * scale.y + offsets.y + htmlOffset.y
    };
  }
  function getClientRects(element2) {
    return Array.from(element2.getClientRects());
  }
  function getDocumentRect(element2) {
    const html = getDocumentElement(element2);
    const scroll = getNodeScroll(element2);
    const body = element2.ownerDocument.body;
    const width = max(html.scrollWidth, html.clientWidth, body.scrollWidth, body.clientWidth);
    const height = max(html.scrollHeight, html.clientHeight, body.scrollHeight, body.clientHeight);
    let x = -scroll.scrollLeft + getWindowScrollBarX(element2);
    const y = -scroll.scrollTop;
    if (getComputedStyle2(body).direction === "rtl") {
      x += max(html.clientWidth, body.clientWidth) - width;
    }
    return {
      width,
      height,
      x,
      y
    };
  }
  function getViewportRect(element2, strategy) {
    const win = getWindow(element2);
    const html = getDocumentElement(element2);
    const visualViewport = win.visualViewport;
    let width = html.clientWidth;
    let height = html.clientHeight;
    let x = 0;
    let y = 0;
    if (visualViewport) {
      width = visualViewport.width;
      height = visualViewport.height;
      const visualViewportBased = isWebKit();
      if (!visualViewportBased || visualViewportBased && strategy === "fixed") {
        x = visualViewport.offsetLeft;
        y = visualViewport.offsetTop;
      }
    }
    return {
      width,
      height,
      x,
      y
    };
  }
  function getInnerBoundingClientRect(element2, strategy) {
    const clientRect = getBoundingClientRect(element2, true, strategy === "fixed");
    const top = clientRect.top + element2.clientTop;
    const left = clientRect.left + element2.clientLeft;
    const scale = isHTMLElement(element2) ? getScale(element2) : createCoords(1);
    const width = element2.clientWidth * scale.x;
    const height = element2.clientHeight * scale.y;
    const x = left * scale.x;
    const y = top * scale.y;
    return {
      width,
      height,
      x,
      y
    };
  }
  function getClientRectFromClippingAncestor(element2, clippingAncestor, strategy) {
    let rect;
    if (clippingAncestor === "viewport") {
      rect = getViewportRect(element2, strategy);
    } else if (clippingAncestor === "document") {
      rect = getDocumentRect(getDocumentElement(element2));
    } else if (isElement(clippingAncestor)) {
      rect = getInnerBoundingClientRect(clippingAncestor, strategy);
    } else {
      const visualOffsets = getVisualOffsets(element2);
      rect = {
        x: clippingAncestor.x - visualOffsets.x,
        y: clippingAncestor.y - visualOffsets.y,
        width: clippingAncestor.width,
        height: clippingAncestor.height
      };
    }
    return rectToClientRect(rect);
  }
  function hasFixedPositionAncestor(element2, stopNode) {
    const parentNode = getParentNode(element2);
    if (parentNode === stopNode || !isElement(parentNode) || isLastTraversableNode(parentNode)) {
      return false;
    }
    return getComputedStyle2(parentNode).position === "fixed" || hasFixedPositionAncestor(parentNode, stopNode);
  }
  function getClippingElementAncestors(element2, cache) {
    const cachedResult = cache.get(element2);
    if (cachedResult) {
      return cachedResult;
    }
    let result = getOverflowAncestors(element2, [], false).filter((el) => isElement(el) && getNodeName(el) !== "body");
    let currentContainingBlockComputedStyle = null;
    const elementIsFixed = getComputedStyle2(element2).position === "fixed";
    let currentNode = elementIsFixed ? getParentNode(element2) : element2;
    while (isElement(currentNode) && !isLastTraversableNode(currentNode)) {
      const computedStyle = getComputedStyle2(currentNode);
      const currentNodeIsContaining = isContainingBlock(currentNode);
      if (!currentNodeIsContaining && computedStyle.position === "fixed") {
        currentContainingBlockComputedStyle = null;
      }
      const shouldDropCurrentNode = elementIsFixed ? !currentNodeIsContaining && !currentContainingBlockComputedStyle : !currentNodeIsContaining && computedStyle.position === "static" && !!currentContainingBlockComputedStyle && ["absolute", "fixed"].includes(currentContainingBlockComputedStyle.position) || isOverflowElement(currentNode) && !currentNodeIsContaining && hasFixedPositionAncestor(element2, currentNode);
      if (shouldDropCurrentNode) {
        result = result.filter((ancestor) => ancestor !== currentNode);
      } else {
        currentContainingBlockComputedStyle = computedStyle;
      }
      currentNode = getParentNode(currentNode);
    }
    cache.set(element2, result);
    return result;
  }
  function getClippingRect(_ref) {
    let {
      element: element2,
      boundary,
      rootBoundary,
      strategy
    } = _ref;
    const elementClippingAncestors = boundary === "clippingAncestors" ? isTopLayer(element2) ? [] : getClippingElementAncestors(element2, this._c) : [].concat(boundary);
    const clippingAncestors = [...elementClippingAncestors, rootBoundary];
    const firstClippingAncestor = clippingAncestors[0];
    const clippingRect = clippingAncestors.reduce((accRect, clippingAncestor) => {
      const rect = getClientRectFromClippingAncestor(element2, clippingAncestor, strategy);
      accRect.top = max(rect.top, accRect.top);
      accRect.right = min(rect.right, accRect.right);
      accRect.bottom = min(rect.bottom, accRect.bottom);
      accRect.left = max(rect.left, accRect.left);
      return accRect;
    }, getClientRectFromClippingAncestor(element2, firstClippingAncestor, strategy));
    return {
      width: clippingRect.right - clippingRect.left,
      height: clippingRect.bottom - clippingRect.top,
      x: clippingRect.left,
      y: clippingRect.top
    };
  }
  function getDimensions(element2) {
    const {
      width,
      height
    } = getCssDimensions(element2);
    return {
      width,
      height
    };
  }
  function getRectRelativeToOffsetParent(element2, offsetParent, strategy) {
    const isOffsetParentAnElement = isHTMLElement(offsetParent);
    const documentElement = getDocumentElement(offsetParent);
    const isFixed = strategy === "fixed";
    const rect = getBoundingClientRect(element2, true, isFixed, offsetParent);
    let scroll = {
      scrollLeft: 0,
      scrollTop: 0
    };
    const offsets = createCoords(0);
    if (isOffsetParentAnElement || !isOffsetParentAnElement && !isFixed) {
      if (getNodeName(offsetParent) !== "body" || isOverflowElement(documentElement)) {
        scroll = getNodeScroll(offsetParent);
      }
      if (isOffsetParentAnElement) {
        const offsetRect = getBoundingClientRect(offsetParent, true, isFixed, offsetParent);
        offsets.x = offsetRect.x + offsetParent.clientLeft;
        offsets.y = offsetRect.y + offsetParent.clientTop;
      } else if (documentElement) {
        offsets.x = getWindowScrollBarX(documentElement);
      }
    }
    const htmlOffset = documentElement && !isOffsetParentAnElement && !isFixed ? getHTMLOffset(documentElement, scroll) : createCoords(0);
    const x = rect.left + scroll.scrollLeft - offsets.x - htmlOffset.x;
    const y = rect.top + scroll.scrollTop - offsets.y - htmlOffset.y;
    return {
      x,
      y,
      width: rect.width,
      height: rect.height
    };
  }
  function isStaticPositioned(element2) {
    return getComputedStyle2(element2).position === "static";
  }
  function getTrueOffsetParent(element2, polyfill) {
    if (!isHTMLElement(element2) || getComputedStyle2(element2).position === "fixed") {
      return null;
    }
    if (polyfill) {
      return polyfill(element2);
    }
    let rawOffsetParent = element2.offsetParent;
    if (getDocumentElement(element2) === rawOffsetParent) {
      rawOffsetParent = rawOffsetParent.ownerDocument.body;
    }
    return rawOffsetParent;
  }
  function getOffsetParent(element2, polyfill) {
    const win = getWindow(element2);
    if (isTopLayer(element2)) {
      return win;
    }
    if (!isHTMLElement(element2)) {
      let svgOffsetParent = getParentNode(element2);
      while (svgOffsetParent && !isLastTraversableNode(svgOffsetParent)) {
        if (isElement(svgOffsetParent) && !isStaticPositioned(svgOffsetParent)) {
          return svgOffsetParent;
        }
        svgOffsetParent = getParentNode(svgOffsetParent);
      }
      return win;
    }
    let offsetParent = getTrueOffsetParent(element2, polyfill);
    while (offsetParent && isTableElement(offsetParent) && isStaticPositioned(offsetParent)) {
      offsetParent = getTrueOffsetParent(offsetParent, polyfill);
    }
    if (offsetParent && isLastTraversableNode(offsetParent) && isStaticPositioned(offsetParent) && !isContainingBlock(offsetParent)) {
      return win;
    }
    return offsetParent || getContainingBlock(element2) || win;
  }
  var getElementRects = async function(data) {
    const getOffsetParentFn = this.getOffsetParent || getOffsetParent;
    const getDimensionsFn = this.getDimensions;
    const floatingDimensions = await getDimensionsFn(data.floating);
    return {
      reference: getRectRelativeToOffsetParent(data.reference, await getOffsetParentFn(data.floating), data.strategy),
      floating: {
        x: 0,
        y: 0,
        width: floatingDimensions.width,
        height: floatingDimensions.height
      }
    };
  };
  function isRTL2(element2) {
    return getComputedStyle2(element2).direction === "rtl";
  }
  var platform = {
    convertOffsetParentRelativeRectToViewportRelativeRect,
    getDocumentElement,
    getClippingRect,
    getOffsetParent,
    getElementRects,
    getClientRects,
    getDimensions,
    getScale,
    isElement,
    isRTL: isRTL2
  };
  function rectsAreEqual(a, b) {
    return a.x === b.x && a.y === b.y && a.width === b.width && a.height === b.height;
  }
  function observeMove(element2, onMove) {
    let io = null;
    let timeoutId;
    const root = getDocumentElement(element2);
    function cleanup() {
      var _io;
      clearTimeout(timeoutId);
      (_io = io) == null || _io.disconnect();
      io = null;
    }
    function refresh(skip, threshold) {
      if (skip === void 0) {
        skip = false;
      }
      if (threshold === void 0) {
        threshold = 1;
      }
      cleanup();
      const elementRectForRootMargin = element2.getBoundingClientRect();
      const {
        left,
        top,
        width,
        height
      } = elementRectForRootMargin;
      if (!skip) {
        onMove();
      }
      if (!width || !height) {
        return;
      }
      const insetTop = floor(top);
      const insetRight = floor(root.clientWidth - (left + width));
      const insetBottom = floor(root.clientHeight - (top + height));
      const insetLeft = floor(left);
      const rootMargin = -insetTop + "px " + -insetRight + "px " + -insetBottom + "px " + -insetLeft + "px";
      const options = {
        rootMargin,
        threshold: max(0, min(1, threshold)) || 1
      };
      let isFirstUpdate = true;
      function handleObserve(entries) {
        const ratio = entries[0].intersectionRatio;
        if (ratio !== threshold) {
          if (!isFirstUpdate) {
            return refresh();
          }
          if (!ratio) {
            timeoutId = setTimeout(() => {
              refresh(false, 1e-7);
            }, 1e3);
          } else {
            refresh(false, ratio);
          }
        }
        if (ratio === 1 && !rectsAreEqual(elementRectForRootMargin, element2.getBoundingClientRect())) {
          refresh();
        }
        isFirstUpdate = false;
      }
      try {
        io = new IntersectionObserver(handleObserve, {
          ...options,
          // Handle <iframe>s
          root: root.ownerDocument
        });
      } catch (e) {
        io = new IntersectionObserver(handleObserve, options);
      }
      io.observe(element2);
    }
    refresh(true);
    return cleanup;
  }
  function autoUpdate(reference, floating, update, options) {
    if (options === void 0) {
      options = {};
    }
    const {
      ancestorScroll = true,
      ancestorResize = true,
      elementResize = typeof ResizeObserver === "function",
      layoutShift = typeof IntersectionObserver === "function",
      animationFrame = false
    } = options;
    const referenceEl = unwrapElement(reference);
    const ancestors = ancestorScroll || ancestorResize ? [...referenceEl ? getOverflowAncestors(referenceEl) : [], ...getOverflowAncestors(floating)] : [];
    ancestors.forEach((ancestor) => {
      ancestorScroll && ancestor.addEventListener("scroll", update, {
        passive: true
      });
      ancestorResize && ancestor.addEventListener("resize", update);
    });
    const cleanupIo = referenceEl && layoutShift ? observeMove(referenceEl, update) : null;
    let reobserveFrame = -1;
    let resizeObserver = null;
    if (elementResize) {
      resizeObserver = new ResizeObserver((_ref) => {
        let [firstEntry] = _ref;
        if (firstEntry && firstEntry.target === referenceEl && resizeObserver) {
          resizeObserver.unobserve(floating);
          cancelAnimationFrame(reobserveFrame);
          reobserveFrame = requestAnimationFrame(() => {
            var _resizeObserver;
            (_resizeObserver = resizeObserver) == null || _resizeObserver.observe(floating);
          });
        }
        update();
      });
      if (referenceEl && !animationFrame) {
        resizeObserver.observe(referenceEl);
      }
      resizeObserver.observe(floating);
    }
    let frameId;
    let prevRefRect = animationFrame ? getBoundingClientRect(reference) : null;
    if (animationFrame) {
      frameLoop();
    }
    function frameLoop() {
      const nextRefRect = getBoundingClientRect(reference);
      if (prevRefRect && !rectsAreEqual(prevRefRect, nextRefRect)) {
        update();
      }
      prevRefRect = nextRefRect;
      frameId = requestAnimationFrame(frameLoop);
    }
    update();
    return () => {
      var _resizeObserver2;
      ancestors.forEach((ancestor) => {
        ancestorScroll && ancestor.removeEventListener("scroll", update);
        ancestorResize && ancestor.removeEventListener("resize", update);
      });
      cleanupIo == null || cleanupIo();
      (_resizeObserver2 = resizeObserver) == null || _resizeObserver2.disconnect();
      resizeObserver = null;
      if (animationFrame) {
        cancelAnimationFrame(frameId);
      }
    };
  }
  var offset2 = offset;
  var shift2 = shift;
  var flip2 = flip;
  var size2 = size;
  var hide2 = hide;
  var computePosition2 = (reference, floating, options) => {
    const cache = /* @__PURE__ */ new Map();
    const mergedOptions = {
      platform,
      ...options
    };
    const platformWithCache = {
      ...mergedOptions.platform,
      _c: cache
    };
    return computePosition(reference, floating, {
      ...mergedOptions,
      platform: platformWithCache
    });
  };

  // js/mixins/anchorable.js
  var useFixedStrategy = !(CSS.supports?.("selector(:popover-open)") ?? false);
  var Anchorable = class extends Mixin {
    boot({ options }) {
      options({
        reference: null,
        auto: true,
        position: "bottom start",
        gap: "5",
        offset: "0",
        matchWidth: false,
        crossAxis: false,
        scrollY: true,
        onReferenceHidden: null
      });
      if (this.options().reference === null) return;
      if (this.options().position === null) return;
      let [setPosition, cleanupDurablePositioning] = createDurablePositionSetter(this.el, { scrollY: this.options().scrollY });
      let reposition = anchor(this.el, this.options().reference, setPosition, {
        position: this.options().position,
        gap: this.options().gap,
        offset: this.options().offset,
        matchWidth: this.options().matchWidth,
        crossAxis: this.options().crossAxis,
        scrollY: this.options().scrollY,
        onReferenceHidden: this.options().onReferenceHidden
      });
      let cleanupAutoUpdate = () => {
      };
      this.reposition = (...args) => {
        if (this.options().auto) {
          cleanupAutoUpdate = autoUpdate(this.options().reference, this.el, reposition);
        } else {
          reposition(null, ...args);
        }
      };
      this.cleanup = () => {
        cleanupAutoUpdate();
        cleanupDurablePositioning();
      };
    }
  };
  function anchor(target, invoke, setPosition, { position, offset: offsetValue, gap, matchWidth, crossAxis, scrollY, onReferenceHidden }) {
    let elMaxHeight = window.getComputedStyle(target).maxHeight;
    elMaxHeight = elMaxHeight === "none" ? null : parseFloat(elMaxHeight);
    return (event, forceX, forceY) => {
      computePosition2(invoke, target, {
        strategy: useFixedStrategy ? "fixed" : "absolute",
        placement: compilePlacement(position, target),
        // Placements: ['top', 'top-start', 'top-end', 'right', 'right-start', 'right-end', 'bottom', 'bottom-start', 'bottom-end', 'left', 'left-start', 'left-end']
        middleware: [
          // Offset needs to be first, as per the Floating UI docs...
          offset2({
            mainAxis: Number(gap),
            alignmentAxis: Number(offsetValue)
          }),
          flip2(),
          shift2({ padding: 5, crossAxis }),
          size2({
            padding: 5,
            apply({ rects, elements, availableHeight }) {
              if (matchWidth) {
                Object.assign(elements.floating.style, {
                  width: `${rects.reference.width}px`
                });
              }
              let maxHeight = elMaxHeight;
              if (maxHeight === null) {
                maxHeight = scrollY ? elements.floating.scrollHeight : elements.floating.offsetHeight;
              }
              elements.floating.style.maxHeight = availableHeight > maxHeight ? "" : `${availableHeight}px`;
            }
          }),
          ...onReferenceHidden ? [hide2({ strategy: "referenceHidden" })] : []
        ]
      }).then(({ x, y, middlewareData }) => {
        if (middlewareData.hide?.referenceHidden) {
          onReferenceHidden();
          return;
        }
        setPosition(forceX || x, forceY || y);
      });
    };
  }
  function compilePlacement(anchor2, el) {
    let placement = anchor2.split(" ");
    switch (placement[0]) {
      case "start":
        placement[0] = isRTL(el) ? "right" : "left";
        break;
      case "end":
        placement[0] = isRTL(el) ? "left" : "right";
        break;
    }
    return placement.join("-");
  }
  function createDurablePositionSetter(target, { scrollY = true }) {
    let update = (x, y, width, height) => {
      Object.assign(target.style, {
        position: useFixedStrategy ? "fixed" : "absolute",
        overflowY: scrollY ? "auto" : "hidden",
        left: `${x}px`,
        top: `${y}px`,
        // This is required to reset the `popover` default styles, otherwise the dropdown appears in the middle of the screen...
        right: "auto",
        bottom: "auto",
        width,
        maxHeight: height
      });
    };
    let lastX, lastY, lastWidth, lastHeight;
    let observer = new MutationObserver(() => update(lastX, lastY, lastWidth, lastHeight));
    return [
      (x, y) => {
        lastX = x;
        lastY = y;
        lastWidth = target.style.width;
        lastHeight = target.style.maxHeight;
        observer.disconnect();
        update(lastX, lastY, lastWidth, lastHeight);
        observer.observe(target, { attributeFilter: ["style"] });
      },
      () => {
        observer.disconnect();
      }
    ];
  }

  // js/selected.js
  var UISelected = class extends UIElement {
    boot() {
      this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
      if (!this.querySelector("template")) {
        let template = document.createElement("template");
        template.setAttribute("name", "placeholder");
        template.innerHTML = "<span>" + this.innerHTML + "</span>";
        this.innerHTML = "";
        this.appendChild(template);
      }
      if (!this.querySelector('template[name="options"]')) {
        let template = document.createElement("template");
        template.setAttribute("name", "options");
        template.innerHTML = "<div><slot></slot></div>";
        this.appendChild(template);
      }
      if (!this.querySelector('template[name="option"]')) {
        let template = document.createElement("template");
        template.setAttribute("name", "option");
        template.innerHTML = "<div><slot></slot></div>";
        this.appendChild(template);
      }
      this.templates = {
        placeholder: this.querySelector('template[name="placeholder"]'),
        overflow: this.querySelector('template[name="overflow"]'),
        options: this.querySelector('template[name="options"]'),
        option: this.querySelector('template[name="option"]')
      };
      this.templates.options.elsByValue = /* @__PURE__ */ new Map();
      this.max = this.templates.overflow?.getAttribute("max") ? this.templates.overflow.getAttribute("max") : Infinity;
      this.selecteds = /* @__PURE__ */ new Map();
      this.picker = this.closest("ui-select,ui-pillbox");
      this.multiple = this.picker.hasAttribute("multiple");
    }
    mount() {
      queueMicrotask(() => {
        this.picker._selectable.onInitAndChange(() => {
          this.render(true);
        });
        let optionsEl = this.picker.list();
        if (optionsEl) {
          new MutationObserver((mutations) => {
            queueMicrotask(() => this.render());
          }).observe(optionsEl, { childList: true });
        }
      });
    }
    render(retry) {
      if (!this.multiple) {
        let value3 = this.picker.value;
        if (Array.from(this.selecteds.keys()).includes(value3)) {
          return;
        }
        this.selecteds.clear();
        let selected = this.picker._selectable.findByValue(value3);
        if (selected) {
          this.selecteds.set(value3, selected);
        } else {
          if (!["", null, void 0].includes(value3)) {
            if (retry) return setTimeout(() => {
              this.render();
            });
            else throw `Could not find option for value "${value3}"`;
          }
        }
        this.templates.placeholder?.clearPlaceholder?.();
        this.templates.option?.clearOption?.();
        if (this.selecteds.size > 0) {
          this.renderOption();
        } else {
          this.renderPlaceholder();
        }
      } else {
        let values = this.picker.value;
        let removedValues = Array.from(this.selecteds.keys()).filter((i) => !values.includes(i));
        let newValues = values.filter((i) => !this.selecteds.has(i));
        removedValues.forEach((value3) => this.selecteds.delete(value3));
        let newSelecteds = /* @__PURE__ */ new Map();
        for (let value3 of newValues) {
          let selected = this.picker._selectable.findByValue(value3);
          if (!selected) {
            if (retry) return setTimeout(() => this.render());
            else throw `Could not find option for value "${value3}"`;
          }
          newSelecteds.set(value3, selected);
        }
        newSelecteds.forEach((selected, value3) => this.selecteds.set(value3, selected));
        this.templates.placeholder?.clearPlaceholder?.();
        this.templates.overflow?.clearOverflow?.();
        this.templates.options?.clearOptions?.();
        if (this.selecteds.size > 0) {
          this.renderOptions({
            hasOverflowed: (rendered) => {
              if (this.max === "auto") {
                let willOverflow = false;
                this.renderOverflow(this.selecteds.size, this.selecteds.size - rendered);
                if (this.clientWidth < this.scrollWidth) {
                  willOverflow = true;
                }
                this.templates.overflow?.clearOverflow?.();
                if (willOverflow) {
                  return true;
                }
              }
              return rendered > parseInt(this.max);
            },
            renderOverflow: (remainder) => {
              if (this.templates?.overflow?.getAttribute("mode") !== "append") {
                this.templates.options?.clearOptions?.();
              }
              this.renderOverflow(this.selecteds.size, remainder);
            }
          });
        } else {
          this.renderPlaceholder();
        }
      }
    }
    renderOptions({ hasOverflowed, renderOverflow }) {
      let container = document.createElement("div");
      container.style.display = "contents";
      let optionsEl = hydrateTemplate2(this.templates.options, {
        default: container
      });
      this.templates.options.after(optionsEl);
      this.templates.options.clearOptions = () => {
        optionsEl.remove();
        this.templates.options.clearOptions = () => {
        };
      };
      let rendered = 0;
      let shouldRenderOverflow = false;
      for (let [value3, selected] of this.selecteds) {
        let fragment2 = new DocumentFragment();
        fragment2.append(...selected.el.cloneNode(true).childNodes);
        let optionEl = hydrateTemplate2(this.templates.option, {
          text: selected.getSelectedLabel() ?? selected.getLabel(),
          default: selected.getSelectedLabel() ?? fragment2,
          value: value3
        });
        optionEl.setAttribute("data-value", value3);
        optionEl.setAttribute("data-appended", "");
        optionEl.deselect = () => selected.deselect();
        container.appendChild(optionEl);
        rendered++;
        if (hasOverflowed(rendered)) {
          shouldRenderOverflow = true;
          container.removeChild(optionEl);
          rendered--;
          break;
        }
      }
      let fragment = new DocumentFragment();
      fragment.append(...container.childNodes);
      container.replaceWith(fragment);
      if (shouldRenderOverflow) {
        renderOverflow(this.selecteds.size - rendered);
      }
    }
    renderOption() {
      for (let [value3, selected] of this.selecteds) {
        let fragment = new DocumentFragment();
        fragment.append(...selected.el.cloneNode(true).childNodes);
        let optionEl = hydrateTemplate2(this.templates.option, {
          text: selected.getSelectedLabel() ?? selected.getLabel(),
          default: selected.getSelectedLabel() ?? fragment,
          value: value3
        });
        optionEl.setAttribute("data-value", value3);
        optionEl.setAttribute("data-appended", value3);
        optionEl.deselect = () => selected.deselect();
        this.templates.option.after(optionEl);
        this.templates.option.clearOption = () => {
          optionEl.remove();
          this.templates.option.clearOption = () => {
          };
        };
      }
    }
    renderPlaceholder() {
      if (!this.templates.placeholder) return;
      let el = hydrateTemplate2(this.templates.placeholder);
      el.setAttribute("data-appended", "");
      this.templates.placeholder.after(el);
      this.templates.placeholder.clearPlaceholder = () => {
        el.remove();
        this.templates.placeholder.clearPlaceholder = () => {
        };
      };
    }
    renderOverflow(count, remainder) {
      if (!this.templates.overflow) return;
      let el = hydrateTemplate2(this.templates.overflow, {
        remainder,
        count: this.selecteds.size
      });
      el.setAttribute("data-appended", "");
      this.templates.overflow.after(el);
      this.templates.overflow.clearOverflow = () => {
        el.remove();
        this.templates.placeholder.clearOverflow = () => {
        };
      };
    }
  };
  var UISelectedRemove = class extends UIElement {
    boot() {
      this.addEventListener("click", (e) => {
        e.stopPropagation();
        let value3 = this.closest("[data-value]")?.getAttribute("data-value");
        if (value3 === void 0) return;
        let selectableGroup = this.closest("ui-pillbox")._selectable;
        let selectable = selectableGroup.selectableByValue(value3);
        if (selectable) {
          selectable.deselect();
        } else {
          selectableGroup.deselectByValue(value3);
        }
      });
    }
  };
  function hydrateTemplate2(template, slots = {}) {
    let fragment = template.content.cloneNode(true);
    Object.entries(slots).forEach(([key, value3]) => {
      let slotNodes = key === "default" ? fragment.querySelectorAll("slot:not([name])") : fragment.querySelectorAll(`slot[name="${key}"]`);
      slotNodes.forEach((i) => i.replaceWith(
        typeof value3 === "string" ? document.createTextNode(value3) : value3
      ));
    });
    return fragment.firstElementChild;
  }

  // js/mixins/selectable.js
  var SelectableGroup = class extends MixinGroup {
    groupOfType = Selectable;
    boot({ options }) {
      options({
        multiple: false
      });
      this.state = this.options().multiple ? /* @__PURE__ */ new Set() : null;
      this.onChanges = [];
    }
    onInitAndChange(callback) {
      callback();
      this.onChanges.push(callback);
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    changed(selectable, silent = false) {
      if (selectable.ungrouped) return;
      let value3 = selectable.value;
      let selected = selectable.isSelected();
      let multiple = this.options().multiple;
      if (selected) {
        multiple ? this.state.add(value3) : this.state = value3;
      } else {
        multiple ? this.state.delete(value3) : this.state = null;
      }
      if (!silent) {
        this.onChanges.forEach((i) => i(selectable));
      }
    }
    deselectByValue(value3, silent = false) {
      this.options().multiple ? this.state.delete(value3) : this.state = null;
      if (!silent) {
        this.onChanges.forEach((i) => i());
      }
    }
    getState() {
      return this.options().multiple ? Array.from(this.state) : this.state;
    }
    hasValue(value3) {
      return this.options().multiple ? this.state.has(value3) : this.state === value3;
    }
    setState(value3) {
      if (value3 === null || value3 === "") value3 = this.options().multiple ? [] : "";
      if (this.options().multiple) {
        if (!Array.isArray(value3)) value3 = [value3];
        value3 = value3.map((i) => i + "");
      } else {
        value3 = value3 + "";
      }
      this.state = this.options().multiple ? new Set(value3) : value3;
      let values = this.options().multiple ? value3 : [value3];
      this.walker().each((el) => {
        let selectable = el.use(Selectable);
        if (selectable.ungrouped) return;
        let selected = values.includes(selectable.value);
        if (selected && !selectable.isSelected()) {
          selectable.surgicallySelect();
        } else if (!selected && selectable.isSelected()) {
          selectable.surgicallyDeselect();
        }
      });
      this.onChanges.forEach((i) => i());
    }
    selected() {
      return this.walker().find((item) => item.use(Selectable).isSelected()).use(Selectable);
    }
    selecteds() {
      return this.walker().filter((el) => el.use(Selectable).isSelected()).map((el) => el.use(Selectable));
    }
    selectFirst() {
      this.walker().first()?.use(Selectable).select();
    }
    selectAll() {
      this.walker().filter((el) => !el.use(Selectable).isSelected()).map((el) => el.use(Selectable).select());
    }
    deselectAll() {
      this.walker().filter((el) => el.use(Selectable).isSelected()).map((el) => el.use(Selectable).deselect());
    }
    allAreSelected() {
      let selectableElements = this.walker().filter((el) => true);
      return selectableElements.length > 0 && this.walker().filter((el) => el.use(Selectable).isSelected()).length === selectableElements.length;
    }
    noneAreSelected() {
      return this.state === null || this.state?.size === 0;
    }
    isEmpty() {
      return this.noneAreSelected();
    }
    isNotEmpty() {
      return !this.isEmpty();
    }
    selectableByValue(value3) {
      return this.walker().find((el) => el.use(Selectable).value === value3)?.use(Selectable);
    }
    deselectOthers(except) {
      this.walker().each((el) => {
        if (el === except) return;
        el.use(Selectable).surgicallyDeselect();
      });
    }
    selectedTextValue() {
      if (!this.options().multiple) return this.convertValueStringToElementText(this.state);
      return Array.from(this.state).map((i) => {
        return this.convertValueStringToElementText(i);
      }).join(", ");
    }
    convertValueStringToElementText(value3) {
      let selected = this.findByValue(value3);
      if (selected) {
        return selected.selectedLabel || selected.label || selected.value;
      } else {
        return value3;
      }
    }
    findByValue(value3) {
      return this.selecteds().find((i) => i.value === value3);
    }
    walker() {
      return walker(this.el, (el, { skip, reject }) => {
        if (el[this.constructor.name] && el !== this.el) return reject();
        if (!el[this.groupOfType.name]) return skip();
        if (el.mixins.get(this.groupOfType.name).ungrouped) return skip();
      });
    }
  };
  var Selectable = class extends Mixin {
    boot({ options }) {
      this.groupedByType = SelectableGroup;
      options({
        ungrouped: false,
        togglable: false,
        value: void 0,
        label: void 0,
        selectedLabel: void 0,
        selectedInitially: false,
        dataAttr: "data-selected",
        ariaAttr: "aria-selected"
      });
      this.ungrouped = this.options().ungrouped;
      this.value = this.options().value === void 0 ? this.el.value : this.options().value;
      this.value = this.value + "";
      this.label = this.options().label;
      this.selectedLabel = this.options().selectedLabel;
      let state = this.options().selectedInitially;
      this.onSelects = [];
      this.onUnselects = [];
      this.onChanges = [];
      let initState = () => {
        if (this.group()) {
          if (this.group().hasValue(this.value)) state = true;
        }
        this.multiple = this.hasGroup() ? this.group().options().multiple : false;
        this.toggleable = this.options().toggleable || this.multiple;
        if (state) {
          this.select(true);
        } else {
          this.state = state;
          this.surgicallyDeselect(true);
        }
      };
      initState();
      if (!this.hasGroup() && !this.el.isConnected) {
        queueMicrotask(() => {
          if (this.hasGroup()) {
            initState();
          }
        });
      }
    }
    mount() {
      this.el.hasAttribute(this.options().ariaAttr) || setAttribute2(this.el, this.options().ariaAttr, "false");
    }
    onInitAndChange(callback) {
      callback();
      this.onChanges.push(callback);
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    onSelect(callback) {
      this.onSelects.push(callback);
    }
    onUnselect(callback) {
      this.onUnselects.push(callback);
    }
    setState(value3) {
      value3 ? this.select() : this.deselect();
    }
    getState() {
      return this.state;
    }
    // @todo: depricate in favor of "trigger"...
    press() {
      this.toggleable ? this.toggle() : this.select();
    }
    trigger() {
      this.toggleable ? this.toggle() : this.select();
    }
    toggle() {
      this.isSelected() ? this.deselect() : this.select();
    }
    isSelected() {
      return this.state;
    }
    select(silent = false) {
      let changed = !this.isSelected();
      this.toggleable || this.group()?.deselectOthers(this.el);
      this.state = true;
      setAttribute2(this.el, this.options().ariaAttr, "true");
      setAttribute2(this.el, this.options().dataAttr, "");
      if (changed) {
        if (!silent) {
          this.onSelects.forEach((i) => i());
          this.onChanges.forEach((i) => i());
        }
        this.group()?.changed(this, silent);
      }
    }
    surgicallySelect() {
      let changed = !this.isSelected();
      this.state = true;
      setAttribute2(this.el, this.options().ariaAttr, "true");
      setAttribute2(this.el, this.options().dataAttr, "");
      if (changed) {
        this.onSelects.forEach((i) => i());
        this.onChanges.forEach((i) => i());
      }
    }
    deselect(notify = true) {
      let changed = this.isSelected();
      this.state = false;
      setAttribute2(this.el, this.options().ariaAttr, "false");
      removeAttribute(this.el, this.options().dataAttr);
      if (changed) {
        this.onUnselects.forEach((i) => i());
        this.onChanges.forEach((i) => i());
        notify && this.group()?.changed(this);
      }
    }
    surgicallyDeselect(silent = false) {
      let changed = this.isSelected();
      this.state = false;
      setAttribute2(this.el, this.options().ariaAttr, "false");
      removeAttribute(this.el, this.options().dataAttr);
      if (changed && !silent) {
        this.onUnselects.forEach((i) => i());
        this.onChanges.forEach((i) => i());
      }
    }
    getValue() {
      return this.value;
    }
    getLabel() {
      return this.label;
    }
    getSelectedLabel() {
      return this.selectedLabel;
    }
  };

  // js/mixins/submittable.js
  var Submittable = class extends Mixin {
    boot({ options }) {
      options({
        name: void 0,
        value: void 0,
        includeWhenEmpty: true,
        shouldUpdateValue: true
      });
      this.name = this.options().name;
      this.value = this.options().value === void 0 ? this.el.value : this.options().value;
      this.state = false;
      this.observer = new MutationObserver(() => {
        this.renderHiddenInputs();
      });
      this.observer.observe(this.el, { childList: true });
    }
    mount() {
      this.renderHiddenInputs();
    }
    update(value3) {
      if (this.options().shouldUpdateValue) {
        this.value = value3;
      } else {
        this.state = !!value3;
      }
      this.renderHiddenInputs();
    }
    valueIsEmpty() {
      return this.value === void 0 || this.value === null || this.value === "";
    }
    renderHiddenInputs() {
      this.observer.disconnect();
      if (!this.name) return;
      let children = this.el.children;
      let oldInputs = [];
      for (let i = 0; i < children.length; i++) {
        let child = children[i];
        if (child.hasAttribute("data-os-hidden")) oldInputs.push(child);
      }
      oldInputs.forEach((i) => i.remove());
      let hiddenInputs;
      if (this.options().shouldUpdateValue) {
        hiddenInputs = !this.valueIsEmpty() || this.options().includeWhenEmpty ? this.generateInputs(this.name, this.value) : [];
      } else {
        hiddenInputs = this.state || this.options().includeWhenEmpty ? this.generateInputs(this.name, this.value) : [];
      }
      hiddenInputs.forEach((hiddenInput) => {
        this.el.append(hiddenInput);
      });
      this.observer.observe(this.el, { childList: true });
    }
    generateInputs(name, value3, carry = []) {
      if (this.isObjectOrArray(value3)) {
        for (let key in value3) {
          carry = carry.concat(
            this.generateInputs(`${name}[${key}]`, value3[key])
          );
        }
      } else {
        let el = document.createElement("input");
        el.setAttribute("type", "hidden");
        el.setAttribute("name", name);
        el.setAttribute("value", value3 === null ? "" : "" + value3);
        el.setAttribute("data-os-hidden", "");
        el.setAttribute("data-appended", "");
        return [el];
      }
      return carry;
    }
    isObjectOrArray(value3) {
      return typeof value3 === "object" && value3 !== null;
    }
    submitEnclosingForm() {
      let form = this.getAssociatedForm();
      if (form) {
        form.requestSubmit();
      }
    }
    getAssociatedForm() {
      let formId = this.el.getAttribute("form");
      if (formId) {
        return document.getElementById(formId) || null;
      }
      return this.el.closest("form");
    }
  };


  // js/mixins/disclosable.js
  var Disclosable = class extends Mixin {
    boot({ options }) {
      this.onChanges = [];
      this.state = false;
    }
    onChange(callback) {
      this.onChanges.push(callback);
    }
    getState() {
      return this.state;
    }
    setState(value3) {
      let oldState = this.state;
      this.state = !!value3;
      if (this.state !== oldState) {
        this.onChanges.forEach((i) => i());
      }
    }
  };


  // js/mixins/focusable.js
  var FocusableGroup = class extends MixinGroup {
    groupOfType = Focusable;
    boot({ options }) {
      options({ wrap: false, ensureTabbable: true });
    }
    mount() {
      this.options().ensureTabbable && this.ensureTabbable();
    }
    focusFirst() {
      let target;
      target = target || this.walker().find((i) => i.hasAttribute("autofocus"));
      target = target || this.walker().find((i) => i.getAttribute("tabindex") === "0");
      target = target || this.walker().find((i) => i.getAttribute("tabindex") === "-1");
      target = target || this.walker().find((i) => isFocusable3(i));
      target?.focus();
    }
    focusPrev() {
      this.moveFocus((from) => {
        return this.options().wrap ? this.walker().prevOrLast(from) : this.walker().prev(from);
      });
    }
    focusNext() {
      this.moveFocus((from) => {
        return this.options().wrap ? this.walker().nextOrFirst(from) : this.walker().next(from);
      });
    }
    focusBySearch(query) {
      let found = this.walker().find((i) => i.textContent.toLowerCase().trim().startsWith(query.toLowerCase()));
      found?.use(Focusable).tabbable();
      found?.use(Focusable).focus();
    }
    moveFocus(callback) {
      let tabbable = this.walker().find((i) => i.use(Focusable).isTabbable());
      let to = callback(tabbable);
      to?.use(Focusable).focus();
    }
    ensureTabbable() {
      this.walker().findOrFirst((el) => {
        el.use(Focusable).isTabbable();
      })?.use(Focusable).tabbable();
    }
    wipeTabbables() {
      this.walker().each((el) => {
        el.use(Focusable).untabbable();
      });
    }
    untabbleOthers(except) {
      this.walker().each((el) => {
        if (el === except) return;
        el.use(Focusable).untabbable();
      });
    }
    walker() {
      return walker(this.el, (el, { skip, reject }) => {
        if (el[this.constructor.name] && el !== this.el) return reject();
        if (!el[this.groupOfType.name]) return skip();
        if (el.hasAttribute("disabled")) return reject();
      });
    }
  };
  var Focusable = class extends Mixin {
    groupedByType = FocusableGroup;
    boot({ options }) {
      options({ hover: false, disableable: null, tabbable: false, tabbableAttr: null });
    }
    mount() {
      let disableable = this.options().disableable;
      if (!disableable) throw "Focusable requires a Disableable instance...";
      if (!this.el.hasAttribute("tabindex")) {
        this.options().tabbable ? this.tabbable() : this.untabbable();
      }
      this.pauseFocusListener = this.on("focus", disableable.enabled(() => {
        this.focus(false);
      })).pause;
      this.on("focus", disableable.enabled(() => {
        isUsingKeyboard() && setAttribute2(this.el, "data-focus", "");
      }));
      this.on("blur", disableable.enabled(() => {
        removeAttribute(this.el, "data-focus");
      }));
      this.options().hover && this.on("pointerenter", disableable.enabled(() => {
        this.group()?.untabbleOthers(this.el);
        this.tabbable();
      }));
      this.options().hover && this.on("pointerleave", disableable.enabled((e) => {
        this.untabbable();
      }));
    }
    focus(focusProgramatically = true) {
      this.group()?.untabbleOthers(this.el);
      this.tabbable();
      focusProgramatically && this.pauseFocusListener(() => {
        this.el.focus({ focusVisible: false });
      });
    }
    tabbable() {
      setAttribute2(this.el, "tabindex", "0");
      this.options().tabbableAttr && setAttribute2(this.el, this.options().tabbableAttr, "");
    }
    untabbable() {
      setAttribute2(this.el, "tabindex", "-1");
      this.options().tabbableAttr && removeAttribute(this.el, this.options().tabbableAttr);
    }
    isTabbable() {
      return this.el.getAttribute("tabindex") === "0";
    }
  };

