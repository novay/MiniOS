  // js/utils.js
  var styleSheet;
  function inject(callback) {
    let styles = callback({
      css: (strings, ...values) => `@layer base { ${strings.raw[0] + values.join("")} }`
    });
    if (document.adoptedStyleSheets === void 0) {
      let styleElement = document.createElement("style");
      styleElement.textContent = styles;
      document.head.appendChild(styleElement);
      return;
    }
    if (styleSheet === void 0) {
      styleSheet = new CSSStyleSheet();
      document.adoptedStyleSheets = [...document.adoptedStyleSheets, styleSheet];
    }
    styleSheet.insertRule(styles);
  }
  function closest(el, condition) {
    let current = el;
    while (current) {
      if (condition(current)) return current;
      current = current.parentElement;
    }
  }
  function walker(el, callback) {
    let walker2 = document.createTreeWalker(
      el,
      NodeFilter.SHOW_ELEMENT,
      callback ? {
        acceptNode: (el2) => {
          let skipped, rejected;
          callback(el2, {
            skip: () => skipped = true,
            reject: () => rejected = true
          });
          if (skipped) return NodeFilter.FILTER_SKIP;
          if (rejected) return NodeFilter.FILTER_REJECT;
          return NodeFilter.FILTER_ACCEPT;
        }
      } : {}
    );
    return new Traverse(walker2);
  }
  var Traverse = class {
    constructor(walker2) {
      this.walker = walker2;
    }
    from(el) {
      this.walker.currentNode = el;
      return this;
    }
    first() {
      return this.walker.firstChild();
    }
    last() {
      return this.walker.lastChild();
    }
    next(el) {
      this.walker.currentNode = el;
      return this.walker.nextSibling();
    }
    nextOrFirst(el) {
      let found = this.next(el);
      if (found) return found;
      this.walker.currentNode = this.walker.root;
      return this.first();
    }
    prev(el) {
      this.walker.currentNode = el;
      return this.walker.previousSibling();
    }
    prevOrLast(el) {
      let found = this.prev(el);
      if (found) return found;
      this.walker.currentNode = this.walker.root;
      return this.last();
    }
    closest(el, condition) {
      let walker2 = this.from(el).walker;
      while (walker2.currentNode) {
        if (condition(walker2.currentNode)) return walker2.currentNode;
        walker2.parentNode();
      }
    }
    contains(el) {
      return this.find((i) => i === el);
    }
    find(callback) {
      return this.walk((el, bail) => {
        callback(el) && bail(el);
      });
    }
    findOrFirst(callback) {
      let found = this.find(callback);
      if (!found) this.walker.currentNode = this.walker.root;
      return this.first();
    }
    each(callback) {
      this.walk((el) => callback(el));
    }
    some(callback) {
      return !!this.find(callback);
    }
    every(callback) {
      let every = true;
      this.walk((el) => {
        callback(el) || (every = false);
      });
      return every;
    }
    map(callback) {
      let els = [];
      this.walk((el) => els.push(callback(el)));
      return els;
    }
    filter(callback) {
      let els = [];
      this.walk((el) => callback(el) && els.push(el));
      return els;
    }
    walk(callback) {
      let current;
      let walker2 = this.walker;
      let bailed;
      while (walker2.nextNode()) {
        current = walker2.currentNode;
        callback(current, (bailValue) => bailed = bailValue);
        if (bailed !== void 0) {
          break;
        }
      }
      return bailed;
    }
  };
  function element(name, type) {
    customElements.define(`ui-${name}`, type);
  }
  function on(target, event, handler, options = {}) {
    target.addEventListener(event, handler, options);
    return {
      off: () => target.removeEventListener(event, handler),
      pause: (callback) => {
        target.removeEventListener(event, handler), callback();
        target.addEventListener(event, handler);
      }
    };
  }
  function isFocusable3(el) {
    let selectors = [
      "a[href]",
      "area[href]",
      "input:not([disabled])",
      "select:not([disabled])",
      "textarea:not([disabled])",
      "button:not([disabled])",
      "iframe",
      "object",
      "embed",
      "[tabindex]",
      "[contenteditable]"
    ];
    return selectors.some((selector) => el.matches(selector)) && el.tabIndex >= 0;
  }
  function throttle(func, limit) {
    let inThrottle;
    return function() {
      let context = this, args = arguments;
      if (!inThrottle) {
        func.apply(context, args);
        inThrottle = true;
        setTimeout(() => inThrottle = false, limit);
      }
    };
  }
  function timeout(callback, delay) {
    let timerId;
    let start;
    let remaining = delay;
    let active = false;
    let timeout2 = {
      pause: () => {
        if (!active) return;
        clearTimeout(timerId);
        remaining -= Date.now() - start;
        active = false;
      },
      resume: () => {
        if (active) return;
        start = Date.now();
        timerId = setTimeout(callback, remaining);
        active = true;
      },
      cancel: () => {
        clearTimeout(timerId);
        active = false;
        remaining = delay;
      }
    };
    timeout2.resume();
    return timeout2;
  }
  var using = "pointer";
  document.addEventListener("keydown", () => using = "keyboard", { capture: true });
  document.addEventListener("pointerdown", (e) => {
    using = e.pointerType === "mouse" ? "mouse" : "touch";
  }, { capture: true });
  document.addEventListener("pointermove", (e) => {
    using = e.pointerType === "mouse" ? "mouse" : "touch";
  }, { capture: true });
  function isUsingKeyboard() {
    return using === "keyboard";
  }
  function isUsingTouch() {
    return using === "touch";
  }
  function search(el, callback) {
    let runningQuery = "";
    let clearRunningQuery = debounce(() => {
      runningQuery = "";
    }, 300);
    el.addEventListener("keydown", (e) => {
      if (e.key.length === 1 && /[a-zA-Z]/.test(e.key)) {
        runningQuery += e.key;
        callback(runningQuery);
        e.stopPropagation();
      }
      clearRunningQuery();
    });
  }
  function dispenseId(el, prefix) {
    return "lofi-" + (prefix ? prefix + "-" : "") + Math.random().toString(16).slice(2);
  }
  function assignId(el, prefix) {
    let id = el.hasAttribute("id") ? el.getAttribute("id") : dispenseId(el, prefix);
    setAttribute2(el, "id", id);
    if (!el._x_bindings) el._x_bindings = {};
    if (!el._x_bindings.id) el._x_bindings.id = id;
    return id;
  }
  function detangle() {
    let blocked = false;
    return (callback) => (...args) => {
      if (blocked) return;
      blocked = true;
      try {
        callback(...args);
      } finally {
        blocked = false;
      }
    };
  }
  function interest(trigger, panel, { gain, lose, focusable, useSafeArea }) {
    let engaged = false;
    let focusInHander = (e) => {
      if (!isUsingKeyboard()) return;
      if (trigger.contains(e.target) || panel.contains(e.target)) {
        engaged = true;
        gain();
      } else {
        engaged = false;
        lose();
      }
    };
    focusable && document.addEventListener("focusin", focusInHander);
    let removeFocusInHandler = () => {
      document.removeEventListener("focusin", focusInHander);
    };
    let removeSafeArea = () => {
    };
    let removePointerMoveHandler = () => {
    };
    let disinterest = () => {
      engaged = false;
      lose();
      removeSafeArea();
      removePointerMoveHandler();
    };
    let clear = () => {
      engaged = false;
      removeSafeArea();
      removePointerMoveHandler();
    };
    let pointerEnterHandler = (e) => {
      if (isUsingTouch()) return;
      if (engaged) return;
      engaged = true;
      gain();
      setTimeout(() => {
        let { safeArea, redraw: redrawSafeArea, remove: remove2 } = useSafeArea ? createSafeArea(trigger, panel, e.clientX, e.clientY) : nullSafeArea();
        removeSafeArea = remove2;
        let pointerStoppedOverSafeAreaTimeout;
        let pointerMoveHandler = throttle((e2) => {
          let panelRect = panel.getBoundingClientRect();
          let triggerRect = trigger.getBoundingClientRect();
          let mouseState;
          if (safeArea.contains(e2.target) && mouseIsExclusivelyInsideSafeArea(triggerRect, panelRect, e2.clientX, e2.clientY)) mouseState = "safeArea";
          else if (panel.contains(e2.target)) mouseState = "panel";
          else if (trigger.contains(e2.target)) mouseState = "trigger";
          else mouseState = "outside";
          if (pointerStoppedOverSafeAreaTimeout) {
            clearTimeout(pointerStoppedOverSafeAreaTimeout);
          }
          switch (mouseState) {
            case "outside":
              disinterest();
              break;
            case "trigger":
              redrawSafeArea(e2.clientX, e2.clientY);
              break;
            case "panel":
              removeSafeArea();
              break;
            case "safeArea":
              redrawSafeArea(e2.clientX, e2.clientY);
              pointerStoppedOverSafeAreaTimeout = setTimeout(() => {
                disinterest();
              }, 300);
              break;
            default:
              break;
          }
        }, 100);
        document.addEventListener("pointermove", pointerMoveHandler);
        removePointerMoveHandler = () => document.removeEventListener("pointermove", pointerMoveHandler);
      });
    };
    trigger.addEventListener("pointerenter", pointerEnterHandler);
    let removePointerEnterHandler = () => {
      trigger.removeEventListener("pointerenter", pointerEnterHandler);
    };
    let remove = () => {
      clear();
      removePointerEnterHandler();
      removeFocusInHandler();
    };
    return { clear, remove };
  }
  function createSafeArea(trigger, panel, x, y) {
    let safeArea = document.createElement("div");
    let panelRect = panel.getBoundingClientRect();
    let triggerRect = trigger.getBoundingClientRect();
    safeArea.style.position = "fixed";
    setAttribute2(safeArea, "data-safe-area", "");
    let draw = (x2, y2) => {
      if (panelRect.top === 0 && panelRect.bottom === 0) return;
      let direction;
      if (panelRect.left < triggerRect.left) direction = "left";
      if (panelRect.right > triggerRect.right) direction = "right";
      if (panelRect.top < triggerRect.top && panelRect.bottom < y2) direction = "up";
      if (panelRect.bottom > triggerRect.bottom && panelRect.top > y2) direction = "down";
      if (direction === void 0) direction = "right";
      let left, right, width, top, bottom, height, offset3, shape;
      let padding = 10;
      switch (direction) {
        case "left":
          left = panelRect.right;
          right = Math.max(panelRect.right, x2) + 5;
          width = right - left;
          top = Math.min(triggerRect.top, panelRect.top) - padding;
          bottom = Math.max(triggerRect.bottom, panelRect.bottom) + padding;
          height = bottom - top;
          offset3 = y2 - top;
          shape = `polygon(0% 0%, 100% ${offset3}px, 0% 100%)`;
          break;
        case "right":
          left = Math.min(panelRect.left, x2) - 5;
          right = panelRect.left;
          width = right - left;
          top = Math.min(triggerRect.top, panelRect.top) - padding;
          bottom = Math.max(triggerRect.bottom, panelRect.bottom) + padding;
          height = bottom - top;
          offset3 = y2 - top;
          shape = `polygon(0% ${offset3}px, 100% 0%, 100% 100%)`;
          break;
        case "up":
          left = Math.min(x2, panelRect.left) - padding;
          right = Math.max(x2, panelRect.right) + padding;
          width = right - left;
          top = panelRect.bottom;
          bottom = Math.max(panelRect.bottom, y2) + 5;
          height = bottom - top;
          offset3 = x2 - left;
          shape = `polygon(0% 0%, 100% 0%, ${offset3}px 100%)`;
          break;
        case "down":
          left = Math.min(x2, panelRect.left) - padding;
          right = Math.max(x2, panelRect.right) + padding;
          width = right - left;
          top = Math.min(panelRect.top, y2) - 5;
          bottom = panelRect.top;
          height = bottom - top;
          offset3 = x2 - left;
          shape = `polygon(${offset3}px 0%, 100% 100%, 0% 100%)`;
          break;
      }
      safeArea.style.left = `${left}px`;
      safeArea.style.top = `${top}px`;
      safeArea.style.width = `${width}px`;
      safeArea.style.height = `${height}px`;
      safeArea.style.clipPath = shape;
    };
    return {
      safeArea,
      redraw: (x2, y2) => {
        if (!safeArea.isConnected) trigger.appendChild(safeArea);
        draw(x2, y2);
      },
      remove: () => {
        safeArea.remove();
      }
    };
  }
  function mouseIsExclusivelyInsideSafeArea(triggerRect, panelRect, x, y) {
    return !mouseIsOverTrigger(triggerRect, x, y) && !mouseIsOverPanel(panelRect, x, y);
  }
  function mouseIsOverTrigger(triggerRect, x, y) {
    if (triggerRect.left <= x && x <= triggerRect.right && (triggerRect.top <= y && y <= triggerRect.bottom)) return true;
    return false;
  }
  function mouseIsOverPanel(panelRect, x, y) {
    if (panelRect.left <= x && x <= panelRect.right && (panelRect.top <= y && y <= panelRect.bottom)) return true;
    return false;
  }
  function setAttribute2(el, name, value3) {
    if (el._durableAttributeObserver === void 0) {
      el._durableAttributeObserver = attributeObserver(el, [name]);
    }
    if (!el._durableAttributeObserver.hasAttribute(name)) {
      el._durableAttributeObserver.addAttribute(name);
    }
    el._durableAttributeObserver.pause(() => {
      el.setAttribute(name, value3);
    });
  }
  function removeAndReleaseAttribute(el, name) {
    removeAttribute(el, name);
    releaseAttribute(el, name);
  }
  function removeAttribute(el, name) {
    if (el._durableAttributeObserver === void 0) {
      el._durableAttributeObserver = attributeObserver(el, [name]);
    }
    if (!el._durableAttributeObserver.hasAttribute(name)) {
      el._durableAttributeObserver.addAttribute(name);
    }
    el._durableAttributeObserver.pause(() => {
      el.removeAttribute(name);
    });
  }
  function releaseAttribute(el, name) {
    if (!el?._durableAttributeObserver?.hasAttribute(name)) return;
    el._durableAttributeObserver.releaseAttribute(name);
  }
  function attributeObserver(el, initialAttributes) {
    let processMutations = (mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.oldValue === null) {
          el._durableAttributeObserver.pause(() => removeAttribute(el, mutation.attributeName));
        } else {
          el._durableAttributeObserver.pause(() => setAttribute2(el, mutation.attributeName, mutation.oldValue));
        }
      });
    };
    let observer = new MutationObserver((mutations) => processMutations(mutations));
    observer.observe(el, { attributeFilter: initialAttributes, attributeOldValue: true });
    return {
      attributes: initialAttributes,
      hasAttribute(name) {
        return this.attributes.includes(name);
      },
      addAttribute(name) {
        this.attributes.includes(name) || this.attributes.push(name);
        observer.observe(el, { attributeFilter: this.attributes, attributeOldValue: true });
      },
      releaseAttribute(name) {
        if (!this.hasAttribute(name)) return;
        observer.observe(el, { attributeFilter: this.attributes, attributeOldValue: true });
      },
      pause(callback) {
        processMutations(observer.takeRecords());
        observer.disconnect();
        callback();
        observer.observe(el, { attributeFilter: this.attributes, attributeOldValue: true });
      }
    };
  }
  function nullSafeArea() {
    return {
      safeArea: { contains: () => false },
      redraw: () => {
      },
      remove: () => {
      }
    };
  }
  function debounce(callback, delay) {
    let timeout2;
    return (...args) => {
      clearTimeout(timeout2);
      timeout2 = setTimeout(() => {
        callback(...args);
      }, delay);
    };
  }
  var lockCount = 0;
  var pointerEventsLocked = false;
  inject(({ css }) => css`[data-os-allow-scroll] { pointer-events: auto; }`);
  function lockScroll(el = null, allowScroll = false, except = []) {
    if (allowScroll) return { lock: () => {
    }, unlock: () => {
    } };
    let applyDocumentLockStyles = (disablePointerEvents = false) => {
      undoLockStyles(document.documentElement);
      let lockStyles = {
        overflow: "hidden",
        ...disablePointerEvents ? { pointerEvents: "none" } : {}
      };
      if (window.CSS && CSS.supports && CSS.supports("scrollbar-gutter: stable")) {
        if (document.documentElement.scrollHeight > document.documentElement.clientHeight && window.getComputedStyle(document.documentElement).overflowY !== "hidden") {
          lockStyles.scrollbarGutter = "stable";
        }
      } else {
        lockStyles.paddingRight = `calc(${window.innerWidth - document.documentElement.clientWidth}px + ${window.getComputedStyle(document.documentElement).paddingRight})`;
      }
      setLockStyles(document.documentElement, lockStyles);
      if (disablePointerEvents) {
        setAttribute2(el, "data-os-allow-scroll", "");
        except.forEach((el2) => {
          setAttribute2(el2, "data-os-allow-scroll", "");
        });
        pointerEventsLocked = true;
      }
    };
    let removeDocumentLockStyles = (enablePointerEvents = false) => {
      undoLockStyles(document.documentElement);
      if (enablePointerEvents) {
        removeAndReleaseAttribute(el, "data-os-allow-scroll");
        except.forEach((el2) => {
          removeAttribute(el2, "data-os-allow-scroll");
        });
        pointerEventsLocked = false;
      }
    };
    return {
      lock() {
        lockCount++;
        if (lockCount > 1 && el !== null && pointerEventsLocked) return;
        applyDocumentLockStyles(el !== null && !pointerEventsLocked);
      },
      unlock() {
        lockCount = Math.max(0, lockCount - 1);
        if (lockCount > 0 && el !== null && !pointerEventsLocked) return;
        removeDocumentLockStyles(el !== null && pointerEventsLocked);
        if (lockCount > 0) {
          applyDocumentLockStyles(false);
        }
      }
    };
  }
  function setLockStyles(element2, styles) {
    let unlockedStyles = JSON.parse(element2.getAttribute(`data-os-scroll-unlock`) || "{}");
    Object.entries(styles).forEach(([style, value3]) => {
      if (unlockedStyles[style] === void 0) {
        unlockedStyles[style] = element2.style[style];
        element2.style[style] = value3;
      }
    });
    element2.setAttribute(`data-os-scroll-unlock`, JSON.stringify(unlockedStyles));
  }
  function undoLockStyles(element2) {
    let unlockedStyles = JSON.parse(element2.getAttribute(`data-os-scroll-unlock`) || "{}");
    Object.entries(unlockedStyles).forEach(([style, value3]) => {
      element2.style[style] = value3;
    });
    element2.removeAttribute(`data-os-scroll-unlock`);
  }
  function setStyle(element2, style, value3) {
    let currentValue = element2.style[style];
    element2.style[style] = value3;
    return () => {
      element2.style[style] = currentValue;
    };
  }
  function initFauxButton(el, isDisabled, action) {
    let ifKey = (key, callback) => (e) => {
      if (e.key === key && !isDisabled()) {
        callback();
        e.preventDefault();
        e.stopPropagation();
      }
    };
    setAttribute2(el, "role", "button");
    let syncDisabledAttributes = () => {
      if (el.hasAttribute("disabled")) {
        setAttribute2(el, "aria-disabled", "true");
        setAttribute2(el, "tabindex", "-1");
      } else {
        removeAttribute(el, "aria-disabled");
        setAttribute2(el, "tabindex", "0");
      }
    };
    let observer = new MutationObserver(() => syncDisabledAttributes());
    observer.observe(el, { attributes: true, attributeFilter: ["disabled"] });
    syncDisabledAttributes();
    on(el, "click", () => action());
    on(el, "keydown", ifKey("Enter", () => action()));
    on(el, "keydown", ifKey(" ", () => {
    }));
    on(el, "keyup", ifKey(" ", () => action()));
  }
  function getLocale() {
    return navigator?.language || document.documentElement.lang || "en-US";
  }
  function renderTemplate(template, callback) {
    if (!template) return;
    let cleanup = () => {
      let sibling = template.nextElementSibling;
      while (sibling && sibling.hasAttribute("data-appended")) {
        let toRemove = sibling;
        sibling = sibling.nextElementSibling;
        toRemove.remove();
      }
    };
    cleanup();
    let hydrated = callback((slotsAndAttributes = { slots: {}, attrs: {} }) => {
      return hydrateTemplate(template, slotsAndAttributes);
    });
    hydrated = Array.isArray(hydrated) ? hydrated : [hydrated];
    hydrated.reverse().forEach((node) => {
      template.after(node);
    });
    return { cleanup };
  }
  function hydrateTemplate(template, slotsAndAttributes = { slots: {}, attrs: {} }) {
    let { slots = {}, attrs = {} } = slotsAndAttributes;
    let clone = template.content.cloneNode(true).firstElementChild;
    Object.entries(slots).forEach(([key, value3]) => {
      let slotNodes = key === "default" ? clone.querySelectorAll("slot:not([name])") : clone.querySelectorAll(`slot[name="${key}"]`);
      slotNodes.forEach((i) => i.replaceWith(
        typeof value3 === "string" ? document.createTextNode(value3) : value3
      ));
    });
    clone.querySelectorAll("slot").forEach((slot) => slot.remove());
    Object.entries(attrs).forEach(([key, value3]) => {
      clone.setAttribute(key, value3);
    });
    clone.setAttribute("data-appended", "");
    return clone;
  }
  function isRTL(el) {
    return getComputedStyle(el).direction === "rtl";
  }
  function isSafari() {
    return /^((?!chrome|android).)*safari/i.test(navigator.userAgent) && !navigator.userAgent.includes("CriOS") && !navigator.userAgent.includes("FxiOS");
  }
  function isIOS() {
    return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
  }
  var Observable = class {
    constructor() {
      this.subscribers = [];
    }
    subscribe(reason, callback) {
      this.subscribers.push({ reason, callback });
    }
    notify(reason, data) {
      this.subscribers.forEach(({ reason: subReason, callback }) => {
        if (reason === subReason) {
          callback(data);
        }
      });
    }
  };

