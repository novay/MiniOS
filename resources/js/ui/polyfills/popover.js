  // node_modules/@oddbird/popover-polyfill/dist/popover.js
  var ToggleEvent = class extends Event {
    oldState;
    newState;
    constructor(type, { oldState = "", newState = "", ...init } = {}) {
      super(type, init);
      this.oldState = String(oldState || "");
      this.newState = String(newState || "");
    }
  };
  var popoverToggleTaskQueue = /* @__PURE__ */ new WeakMap();
  function queuePopoverToggleEventTask(element2, oldState, newState) {
    popoverToggleTaskQueue.set(
      element2,
      setTimeout(() => {
        if (!popoverToggleTaskQueue.has(element2)) return;
        element2.dispatchEvent(
          new ToggleEvent("toggle", {
            cancelable: false,
            oldState,
            newState
          })
        );
      }, 0)
    );
  }
  var ShadowRoot2 = globalThis.ShadowRoot || function() {
  };
  var HTMLDialogElement = globalThis.HTMLDialogElement || function() {
  };
  var topLayerElements = /* @__PURE__ */ new WeakMap();
  var autoPopoverList = /* @__PURE__ */ new WeakMap();
  var visibilityState = /* @__PURE__ */ new WeakMap();
  function getPopoverVisibilityState(popover) {
    return visibilityState.get(popover) || "hidden";
  }
  var popoverInvoker = /* @__PURE__ */ new WeakMap();
  function popoverTargetAttributeActivationBehavior(element2) {
    const popover = element2.popoverTargetElement;
    if (!(popover instanceof HTMLElement)) {
      return;
    }
    const visibility = getPopoverVisibilityState(popover);
    if (element2.popoverTargetAction === "show" && visibility === "showing") {
      return;
    }
    if (element2.popoverTargetAction === "hide" && visibility === "hidden") return;
    if (visibility === "showing") {
      hidePopover(popover, true, true);
    } else if (checkPopoverValidity(popover, false)) {
      popoverInvoker.set(popover, element2);
      showPopover(popover);
    }
  }
  function checkPopoverValidity(element2, expectedToBeShowing) {
    if (element2.popover !== "auto" && element2.popover !== "manual") {
      return false;
    }
    if (!element2.isConnected) return false;
    if (expectedToBeShowing && getPopoverVisibilityState(element2) !== "showing") {
      return false;
    }
    if (!expectedToBeShowing && getPopoverVisibilityState(element2) !== "hidden") {
      return false;
    }
    if (element2 instanceof HTMLDialogElement && element2.hasAttribute("open")) {
      return false;
    }
    if (document.fullscreenElement === element2) return false;
    return true;
  }
  function getStackPosition(popover) {
    if (!popover) return 0;
    return Array.from(autoPopoverList.get(popover.ownerDocument) || []).indexOf(
      popover
    ) + 1;
  }
  function topMostClickedPopover(target) {
    const clickedPopover = nearestInclusiveOpenPopover(target);
    const invokerPopover = nearestInclusiveTargetPopoverForInvoker(target);
    if (getStackPosition(clickedPopover) > getStackPosition(invokerPopover)) {
      return clickedPopover;
    }
    return invokerPopover;
  }
  function topMostAutoPopover(document2) {
    const documentPopovers = autoPopoverList.get(document2);
    for (const popover of documentPopovers || []) {
      if (!popover.isConnected) {
        documentPopovers.delete(popover);
      } else {
        return popover;
      }
    }
    return null;
  }
  function getRootNode(node) {
    if (typeof node.getRootNode === "function") {
      return node.getRootNode();
    }
    if (node.parentNode) return getRootNode(node.parentNode);
    return node;
  }
  function nearestInclusiveOpenPopover(node) {
    while (node) {
      if (node instanceof HTMLElement && node.popover === "auto" && visibilityState.get(node) === "showing") {
        return node;
      }
      node = node instanceof Element && node.assignedSlot || node.parentElement || getRootNode(node);
      if (node instanceof ShadowRoot2) node = node.host;
      if (node instanceof Document) return;
    }
  }
  function nearestInclusiveTargetPopoverForInvoker(node) {
    while (node) {
      const nodePopover = node.popoverTargetElement;
      if (nodePopover instanceof HTMLElement) return nodePopover;
      node = node.parentElement || getRootNode(node);
      if (node instanceof ShadowRoot2) node = node.host;
      if (node instanceof Document) return;
    }
  }
  function topMostPopoverAncestor(newPopover) {
    const popoverPositions = /* @__PURE__ */ new Map();
    let i = 0;
    for (const popover of autoPopoverList.get(newPopover.ownerDocument) || []) {
      popoverPositions.set(popover, i);
      i += 1;
    }
    popoverPositions.set(newPopover, i);
    i += 1;
    let topMostPopoverAncestor22 = null;
    function checkAncestor(candidate) {
      const candidateAncestor = nearestInclusiveOpenPopover(candidate);
      if (candidateAncestor === null) return null;
      const candidatePosition = popoverPositions.get(candidateAncestor);
      if (topMostPopoverAncestor22 === null || popoverPositions.get(topMostPopoverAncestor22) < candidatePosition) {
        topMostPopoverAncestor22 = candidateAncestor;
      }
    }
    checkAncestor(newPopover.parentElement || getRootNode(newPopover));
    return topMostPopoverAncestor22;
  }
  function isFocusable(focusTarget) {
    if (focusTarget.hidden || focusTarget instanceof ShadowRoot2) return false;
    if (focusTarget instanceof HTMLButtonElement || focusTarget instanceof HTMLInputElement || focusTarget instanceof HTMLSelectElement || focusTarget instanceof HTMLTextAreaElement || focusTarget instanceof HTMLOptGroupElement || focusTarget instanceof HTMLOptionElement || focusTarget instanceof HTMLFieldSetElement) {
      if (focusTarget.disabled) return false;
    }
    if (focusTarget instanceof HTMLInputElement && focusTarget.type === "hidden") {
      return false;
    }
    if (focusTarget instanceof HTMLAnchorElement && focusTarget.href === "") {
      return false;
    }
    return typeof focusTarget.tabIndex === "number" && focusTarget.tabIndex !== -1;
  }
  function focusDelegate(focusTarget) {
    if (focusTarget.shadowRoot && focusTarget.shadowRoot.delegatesFocus !== true) {
      return null;
    }
    let whereToLook = focusTarget;
    if (whereToLook.shadowRoot) {
      whereToLook = whereToLook.shadowRoot;
    }
    let autoFocusDelegate = whereToLook.querySelector("[autofocus]");
    if (autoFocusDelegate) {
      return autoFocusDelegate;
    } else {
      const slots = whereToLook.querySelectorAll("slot");
      for (const slot of slots) {
        const assignedElements = slot.assignedElements({ flatten: true });
        for (const el of assignedElements) {
          if (el.hasAttribute("autofocus")) {
            return el;
          } else {
            autoFocusDelegate = el.querySelector("[autofocus]");
            if (autoFocusDelegate) {
              return autoFocusDelegate;
            }
          }
        }
      }
    }
    const walker2 = focusTarget.ownerDocument.createTreeWalker(
      whereToLook,
      NodeFilter.SHOW_ELEMENT
    );
    let descendant = walker2.currentNode;
    while (descendant) {
      if (isFocusable(descendant)) {
        return descendant;
      }
      descendant = walker2.nextNode();
    }
  }
  function popoverFocusingSteps(subject) {
    focusDelegate(subject)?.focus();
  }
  var previouslyFocusedElements = /* @__PURE__ */ new WeakMap();
  function showPopover(element2) {
    if (!checkPopoverValidity(element2, false)) {
      return;
    }
    const document2 = element2.ownerDocument;
    if (!element2.dispatchEvent(
      new ToggleEvent("beforetoggle", {
        cancelable: true,
        oldState: "closed",
        newState: "open"
      })
    )) {
      return;
    }
    if (!checkPopoverValidity(element2, false)) {
      return;
    }
    let shouldRestoreFocus = false;
    if (element2.popover === "auto") {
      const originalType = element2.getAttribute("popover");
      const ancestor = topMostPopoverAncestor(element2) || document2;
      hideAllPopoversUntil(ancestor, false, true);
      if (originalType !== element2.getAttribute("popover") || !checkPopoverValidity(element2, false)) {
        return;
      }
    }
    if (!topMostAutoPopover(document2)) {
      shouldRestoreFocus = true;
    }
    previouslyFocusedElements.delete(element2);
    const originallyFocusedElement = document2.activeElement;
    element2.classList.add(":popover-open");
    visibilityState.set(element2, "showing");
    if (!topLayerElements.has(document2)) {
      topLayerElements.set(document2, /* @__PURE__ */ new Set());
    }
    topLayerElements.get(document2).add(element2);
    popoverFocusingSteps(element2);
    if (element2.popover === "auto") {
      if (!autoPopoverList.has(document2)) {
        autoPopoverList.set(document2, /* @__PURE__ */ new Set());
      }
      autoPopoverList.get(document2).add(element2);
      setInvokerAriaExpanded(popoverInvoker.get(element2), true);
    }
    if (shouldRestoreFocus && originallyFocusedElement && element2.popover === "auto") {
      previouslyFocusedElements.set(element2, originallyFocusedElement);
    }
    queuePopoverToggleEventTask(element2, "closed", "open");
  }
  function hidePopover(element2, focusPreviousElement = false, fireEvents = false) {
    if (!checkPopoverValidity(element2, true)) {
      return;
    }
    const document2 = element2.ownerDocument;
    if (element2.popover === "auto") {
      hideAllPopoversUntil(element2, focusPreviousElement, fireEvents);
      if (!checkPopoverValidity(element2, true)) {
        return;
      }
    }
    setInvokerAriaExpanded(popoverInvoker.get(element2), false);
    popoverInvoker.delete(element2);
    if (fireEvents) {
      element2.dispatchEvent(
        new ToggleEvent("beforetoggle", {
          oldState: "open",
          newState: "closed"
        })
      );
      if (!checkPopoverValidity(element2, true)) {
        return;
      }
    }
    topLayerElements.get(document2)?.delete(element2);
    autoPopoverList.get(document2)?.delete(element2);
    element2.classList.remove(":popover-open");
    visibilityState.set(element2, "hidden");
    if (fireEvents) {
      queuePopoverToggleEventTask(element2, "open", "closed");
    }
    const previouslyFocusedElement = previouslyFocusedElements.get(element2);
    if (previouslyFocusedElement) {
      previouslyFocusedElements.delete(element2);
      if (focusPreviousElement) {
        previouslyFocusedElement.focus();
      }
    }
  }
  function closeAllOpenPopovers(document2, focusPreviousElement = false, fireEvents = false) {
    let popover = topMostAutoPopover(document2);
    while (popover) {
      hidePopover(popover, focusPreviousElement, fireEvents);
      popover = topMostAutoPopover(document2);
    }
  }
  function hideAllPopoversUntil(endpoint, focusPreviousElement, fireEvents) {
    const document2 = endpoint.ownerDocument || endpoint;
    if (endpoint instanceof Document) {
      return closeAllOpenPopovers(document2, focusPreviousElement, fireEvents);
    }
    let lastToHide = null;
    let foundEndpoint = false;
    for (const popover of autoPopoverList.get(document2) || []) {
      if (popover === endpoint) {
        foundEndpoint = true;
      } else if (foundEndpoint) {
        lastToHide = popover;
        break;
      }
    }
    if (!foundEndpoint) {
      return closeAllOpenPopovers(document2, focusPreviousElement, fireEvents);
    }
    while (lastToHide && getPopoverVisibilityState(lastToHide) === "showing" && autoPopoverList.get(document2)?.size) {
      hidePopover(lastToHide, focusPreviousElement, fireEvents);
    }
  }
  var popoverPointerDownTargets = /* @__PURE__ */ new WeakMap();
  function lightDismissOpenPopovers(event) {
    if (!event.isTrusted) return;
    const target = event.composedPath()[0];
    if (!target) return;
    const document2 = target.ownerDocument;
    const topMostPopover = topMostAutoPopover(document2);
    if (!topMostPopover) return;
    const ancestor = topMostClickedPopover(target);
    if (ancestor && event.type === "pointerdown") {
      popoverPointerDownTargets.set(document2, ancestor);
    } else if (event.type === "pointerup") {
      const sameTarget = popoverPointerDownTargets.get(document2) === ancestor;
      popoverPointerDownTargets.delete(document2);
      if (sameTarget) {
        hideAllPopoversUntil(ancestor || document2, false, true);
      }
    }
  }
  var initialAriaExpandedValue = /* @__PURE__ */ new WeakMap();
  function setInvokerAriaExpanded(el, force = false) {
    if (!el) return;
    if (!initialAriaExpandedValue.has(el)) {
      initialAriaExpandedValue.set(el, el.getAttribute("aria-expanded"));
    }
    const popover = el.popoverTargetElement;
    if (popover instanceof HTMLElement && popover.popover === "auto") {
      el.setAttribute("aria-expanded", String(force));
    } else {
      const initialValue = initialAriaExpandedValue.get(el);
      if (!initialValue) {
        el.removeAttribute("aria-expanded");
      } else {
        el.setAttribute("aria-expanded", initialValue);
      }
    }
  }
  var ShadowRoot22 = globalThis.ShadowRoot || function() {
  };
  function isSupported() {
    return typeof HTMLElement !== "undefined" && typeof HTMLElement.prototype === "object" && "popover" in HTMLElement.prototype;
  }
  function patchSelectorFn(object, name, mapper) {
    const original = object[name];
    Object.defineProperty(object, name, {
      value(selector) {
        return original.call(this, mapper(selector));
      }
    });
  }
  var nonEscapedPopoverSelector = /(^|[^\\]):popover-open\b/g;
  function hasLayerSupport() {
    return typeof globalThis.CSSLayerBlockRule === "function";
  }
  function getStyles() {
    const useLayer = hasLayerSupport();
    return `
${useLayer ? "@layer popover-polyfill {" : ""}
  :where([popover]) {
    position: fixed;
    z-index: 2147483647;
    inset: 0;
    padding: 0.25em;
    width: fit-content;
    height: fit-content;
    border-width: initial;
    border-color: initial;
    border-image: initial;
    border-style: solid;
    background-color: canvas;
    color: canvastext;
    overflow: auto;
    margin: auto;
  }

  :where([popover]:not(.\\:popover-open)) {
    display: none;
  }

  :where(dialog[popover].\\:popover-open) {
    display: block;
  }

  :where(dialog[popover][open]) {
    display: revert;
  }

  :where([anchor].\\:popover-open) {
    inset: auto;
  }

  :where([anchor]:popover-open) {
    inset: auto;
  }

  @supports not (background-color: canvas) {
    :where([popover]) {
      background-color: white;
      color: black;
    }
  }

  @supports (width: -moz-fit-content) {
    :where([popover]) {
      width: -moz-fit-content;
      height: -moz-fit-content;
    }
  }

  @supports not (inset: 0) {
    :where([popover]) {
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
    }
  }
${useLayer ? "}" : ""}
`;
  }
  var popoverStyleSheet = null;
  function injectStyles(root) {
    const styles = getStyles();
    if (popoverStyleSheet === null) {
      try {
        popoverStyleSheet = new CSSStyleSheet();
        popoverStyleSheet.replaceSync(styles);
      } catch {
        popoverStyleSheet = false;
      }
    }
    if (popoverStyleSheet === false) {
      const sheet = document.createElement("style");
      sheet.textContent = styles;
      if (root instanceof Document) {
        root.head.prepend(sheet);
      } else {
        root.prepend(sheet);
      }
    } else {
      root.adoptedStyleSheets = [popoverStyleSheet, ...root.adoptedStyleSheets];
    }
  }
  function apply() {
    if (typeof window === "undefined") return;
    window.ToggleEvent = window.ToggleEvent || ToggleEvent;
    function rewriteSelector(selector) {
      if (selector?.includes(":popover-open")) {
        selector = selector.replace(
          nonEscapedPopoverSelector,
          "$1.\\:popover-open"
        );
      }
      return selector;
    }
    patchSelectorFn(Document.prototype, "querySelector", rewriteSelector);
    patchSelectorFn(Document.prototype, "querySelectorAll", rewriteSelector);
    patchSelectorFn(Element.prototype, "querySelector", rewriteSelector);
    patchSelectorFn(Element.prototype, "querySelectorAll", rewriteSelector);
    patchSelectorFn(Element.prototype, "matches", rewriteSelector);
    patchSelectorFn(Element.prototype, "closest", rewriteSelector);
    patchSelectorFn(
      DocumentFragment.prototype,
      "querySelectorAll",
      rewriteSelector
    );
    Object.defineProperties(HTMLElement.prototype, {
      popover: {
        enumerable: true,
        configurable: true,
        get() {
          if (!this.hasAttribute("popover")) return null;
          const value3 = (this.getAttribute("popover") || "").toLowerCase();
          if (value3 === "" || value3 == "auto") return "auto";
          return "manual";
        },
        set(value3) {
          if (value3 === null) {
            this.removeAttribute("popover");
          } else {
            this.setAttribute("popover", value3);
          }
        }
      },
      showPopover: {
        enumerable: true,
        configurable: true,
        value() {
          showPopover(this);
        }
      },
      hidePopover: {
        enumerable: true,
        configurable: true,
        value() {
          hidePopover(this, true, true);
        }
      },
      togglePopover: {
        enumerable: true,
        configurable: true,
        value(force) {
          if (visibilityState.get(this) === "showing" && force === void 0 || force === false) {
            hidePopover(this, true, true);
          } else if (force === void 0 || force === true) {
            showPopover(this);
          }
        }
      }
    });
    const originalAttachShadow = Element.prototype.attachShadow;
    if (originalAttachShadow) {
      Object.defineProperties(Element.prototype, {
        attachShadow: {
          enumerable: true,
          configurable: true,
          writable: true,
          value(options) {
            const shadowRoot = originalAttachShadow.call(this, options);
            injectStyles(shadowRoot);
            return shadowRoot;
          }
        }
      });
    }
    const originalAttachInternals = HTMLElement.prototype.attachInternals;
    if (originalAttachInternals) {
      Object.defineProperties(HTMLElement.prototype, {
        attachInternals: {
          enumerable: true,
          configurable: true,
          writable: true,
          value() {
            const internals = originalAttachInternals.call(this);
            if (internals.shadowRoot) {
              injectStyles(internals.shadowRoot);
            }
            return internals;
          }
        }
      });
    }
    const popoverTargetAssociatedElements = /* @__PURE__ */ new WeakMap();
    function applyPopoverInvokerElementMixin(ElementClass) {
      Object.defineProperties(ElementClass.prototype, {
        popoverTargetElement: {
          enumerable: true,
          configurable: true,
          set(targetElement) {
            if (targetElement === null) {
              this.removeAttribute("popovertarget");
              popoverTargetAssociatedElements.delete(this);
            } else if (!(targetElement instanceof Element)) {
              throw new TypeError(
                `popoverTargetElement must be an element or null`
              );
            } else {
              this.setAttribute("popovertarget", "");
              popoverTargetAssociatedElements.set(this, targetElement);
            }
          },
          get() {
            if (this.localName !== "button" && this.localName !== "input") {
              return null;
            }
            if (this.localName === "input" && this.type !== "reset" && this.type !== "image" && this.type !== "button") {
              return null;
            }
            if (this.disabled) {
              return null;
            }
            if (this.form && this.type === "submit") {
              return null;
            }
            const targetElement = popoverTargetAssociatedElements.get(this);
            if (targetElement && targetElement.isConnected) {
              return targetElement;
            } else if (targetElement && !targetElement.isConnected) {
              popoverTargetAssociatedElements.delete(this);
              return null;
            }
            const root = getRootNode(this);
            const idref = this.getAttribute("popovertarget");
            if ((root instanceof Document || root instanceof ShadowRoot22) && idref) {
              return root.getElementById(idref) || null;
            }
            return null;
          }
        },
        popoverTargetAction: {
          enumerable: true,
          configurable: true,
          get() {
            const value3 = (this.getAttribute("popovertargetaction") || "").toLowerCase();
            if (value3 === "show" || value3 === "hide") return value3;
            return "toggle";
          },
          set(value3) {
            this.setAttribute("popovertargetaction", value3);
          }
        }
      });
    }
    applyPopoverInvokerElementMixin(HTMLButtonElement);
    applyPopoverInvokerElementMixin(HTMLInputElement);
    const handleInvokerActivation = (event) => {
      const composedPath = event.composedPath();
      const target = composedPath[0];
      if (!(target instanceof Element) || target?.shadowRoot) {
        return;
      }
      const root = getRootNode(target);
      if (!(root instanceof ShadowRoot22 || root instanceof Document)) {
        return;
      }
      const invoker = composedPath.find(
        (el) => el.matches?.("[popovertargetaction],[popovertarget]")
      );
      if (invoker) {
        popoverTargetAttributeActivationBehavior(invoker);
        event.preventDefault();
        return;
      }
    };
    const onKeydown = (event) => {
      const key = event.key;
      const target = event.target;
      if (!event.defaultPrevented && target && (key === "Escape" || key === "Esc")) {
        hideAllPopoversUntil(target.ownerDocument, true, true);
      }
    };
    const addEventListeners = (root) => {
      root.addEventListener("click", handleInvokerActivation);
      root.addEventListener("keydown", onKeydown);
      root.addEventListener("pointerdown", lightDismissOpenPopovers);
      root.addEventListener("pointerup", lightDismissOpenPopovers);
    };
    addEventListeners(document);
    injectStyles(document);
  }
  if (!isSupported()) apply();

  // node_modules/@oddbird/popover-polyfill/dist/popover-fn.js
  var ToggleEvent2 = class extends Event {
    oldState;
    newState;
    constructor(type, { oldState = "", newState = "", ...init } = {}) {
      super(type, init);
      this.oldState = String(oldState || "");
      this.newState = String(newState || "");
    }
  };
  var popoverToggleTaskQueue2 = /* @__PURE__ */ new WeakMap();
  function queuePopoverToggleEventTask2(element2, oldState, newState) {
    popoverToggleTaskQueue2.set(
      element2,
      setTimeout(() => {
        if (!popoverToggleTaskQueue2.has(element2)) return;
        element2.dispatchEvent(
          new ToggleEvent2("toggle", {
            cancelable: false,
            oldState,
            newState
          })
        );
      }, 0)
    );
  }
  var ShadowRoot3 = globalThis.ShadowRoot || function() {
  };
  var HTMLDialogElement2 = globalThis.HTMLDialogElement || function() {
  };
  var topLayerElements2 = /* @__PURE__ */ new WeakMap();
  var autoPopoverList2 = /* @__PURE__ */ new WeakMap();
  var visibilityState2 = /* @__PURE__ */ new WeakMap();
  function getPopoverVisibilityState2(popover) {
    return visibilityState2.get(popover) || "hidden";
  }
  var popoverInvoker2 = /* @__PURE__ */ new WeakMap();
  function popoverTargetAttributeActivationBehavior2(element2) {
    const popover = element2.popoverTargetElement;
    if (!(popover instanceof HTMLElement)) {
      return;
    }
    const visibility = getPopoverVisibilityState2(popover);
    if (element2.popoverTargetAction === "show" && visibility === "showing") {
      return;
    }
    if (element2.popoverTargetAction === "hide" && visibility === "hidden") return;
    if (visibility === "showing") {
      hidePopover2(popover, true, true);
    } else if (checkPopoverValidity2(popover, false)) {
      popoverInvoker2.set(popover, element2);
      showPopover2(popover);
    }
  }
  function checkPopoverValidity2(element2, expectedToBeShowing) {
    if (element2.popover !== "auto" && element2.popover !== "manual") {
      return false;
    }
    if (!element2.isConnected) return false;
    if (expectedToBeShowing && getPopoverVisibilityState2(element2) !== "showing") {
      return false;
    }
    if (!expectedToBeShowing && getPopoverVisibilityState2(element2) !== "hidden") {
      return false;
    }
    if (element2 instanceof HTMLDialogElement2 && element2.hasAttribute("open")) {
      return false;
    }
    if (document.fullscreenElement === element2) return false;
    return true;
  }
  function getStackPosition2(popover) {
    if (!popover) return 0;
    return Array.from(autoPopoverList2.get(popover.ownerDocument) || []).indexOf(
      popover
    ) + 1;
  }
  function topMostClickedPopover2(target) {
    const clickedPopover = nearestInclusiveOpenPopover2(target);
    const invokerPopover = nearestInclusiveTargetPopoverForInvoker2(target);
    if (getStackPosition2(clickedPopover) > getStackPosition2(invokerPopover)) {
      return clickedPopover;
    }
    return invokerPopover;
  }
  function topMostAutoPopover2(document2) {
    const documentPopovers = autoPopoverList2.get(document2);
    for (const popover of documentPopovers || []) {
      if (!popover.isConnected) {
        documentPopovers.delete(popover);
      } else {
        return popover;
      }
    }
    return null;
  }
  function getRootNode2(node) {
    if (typeof node.getRootNode === "function") {
      return node.getRootNode();
    }
    if (node.parentNode) return getRootNode2(node.parentNode);
    return node;
  }
  function nearestInclusiveOpenPopover2(node) {
    while (node) {
      if (node instanceof HTMLElement && node.popover === "auto" && visibilityState2.get(node) === "showing") {
        return node;
      }
      node = node instanceof Element && node.assignedSlot || node.parentElement || getRootNode2(node);
      if (node instanceof ShadowRoot3) node = node.host;
      if (node instanceof Document) return;
    }
  }
  function nearestInclusiveTargetPopoverForInvoker2(node) {
    while (node) {
      const nodePopover = node.popoverTargetElement;
      if (nodePopover instanceof HTMLElement) return nodePopover;
      node = node.parentElement || getRootNode2(node);
      if (node instanceof ShadowRoot3) node = node.host;
      if (node instanceof Document) return;
    }
  }
  function topMostPopoverAncestor2(newPopover) {
    const popoverPositions = /* @__PURE__ */ new Map();
    let i = 0;
    for (const popover of autoPopoverList2.get(newPopover.ownerDocument) || []) {
      popoverPositions.set(popover, i);
      i += 1;
    }
    popoverPositions.set(newPopover, i);
    i += 1;
    let topMostPopoverAncestor22 = null;
    function checkAncestor(candidate) {
      const candidateAncestor = nearestInclusiveOpenPopover2(candidate);
      if (candidateAncestor === null) return null;
      const candidatePosition = popoverPositions.get(candidateAncestor);
      if (topMostPopoverAncestor22 === null || popoverPositions.get(topMostPopoverAncestor22) < candidatePosition) {
        topMostPopoverAncestor22 = candidateAncestor;
      }
    }
    checkAncestor(newPopover.parentElement || getRootNode2(newPopover));
    return topMostPopoverAncestor22;
  }
  function isFocusable2(focusTarget) {
    if (focusTarget.hidden || focusTarget instanceof ShadowRoot3) return false;
    if (focusTarget instanceof HTMLButtonElement || focusTarget instanceof HTMLInputElement || focusTarget instanceof HTMLSelectElement || focusTarget instanceof HTMLTextAreaElement || focusTarget instanceof HTMLOptGroupElement || focusTarget instanceof HTMLOptionElement || focusTarget instanceof HTMLFieldSetElement) {
      if (focusTarget.disabled) return false;
    }
    if (focusTarget instanceof HTMLInputElement && focusTarget.type === "hidden") {
      return false;
    }
    if (focusTarget instanceof HTMLAnchorElement && focusTarget.href === "") {
      return false;
    }
    return typeof focusTarget.tabIndex === "number" && focusTarget.tabIndex !== -1;
  }
  function focusDelegate2(focusTarget) {
    if (focusTarget.shadowRoot && focusTarget.shadowRoot.delegatesFocus !== true) {
      return null;
    }
    let whereToLook = focusTarget;
    if (whereToLook.shadowRoot) {
      whereToLook = whereToLook.shadowRoot;
    }
    let autoFocusDelegate = whereToLook.querySelector("[autofocus]");
    if (autoFocusDelegate) {
      return autoFocusDelegate;
    } else {
      const slots = whereToLook.querySelectorAll("slot");
      for (const slot of slots) {
        const assignedElements = slot.assignedElements({ flatten: true });
        for (const el of assignedElements) {
          if (el.hasAttribute("autofocus")) {
            return el;
          } else {
            autoFocusDelegate = el.querySelector("[autofocus]");
            if (autoFocusDelegate) {
              return autoFocusDelegate;
            }
          }
        }
      }
    }
    const walker2 = focusTarget.ownerDocument.createTreeWalker(
      whereToLook,
      NodeFilter.SHOW_ELEMENT
    );
    let descendant = walker2.currentNode;
    while (descendant) {
      if (isFocusable2(descendant)) {
        return descendant;
      }
      descendant = walker2.nextNode();
    }
  }
  function popoverFocusingSteps2(subject) {
    focusDelegate2(subject)?.focus();
  }
  var previouslyFocusedElements2 = /* @__PURE__ */ new WeakMap();
  function showPopover2(element2) {
    if (!checkPopoverValidity2(element2, false)) {
      return;
    }
    const document2 = element2.ownerDocument;
    if (!element2.dispatchEvent(
      new ToggleEvent2("beforetoggle", {
        cancelable: true,
        oldState: "closed",
        newState: "open"
      })
    )) {
      return;
    }
    if (!checkPopoverValidity2(element2, false)) {
      return;
    }
    let shouldRestoreFocus = false;
    if (element2.popover === "auto") {
      const originalType = element2.getAttribute("popover");
      const ancestor = topMostPopoverAncestor2(element2) || document2;
      hideAllPopoversUntil2(ancestor, false, true);
      if (originalType !== element2.getAttribute("popover") || !checkPopoverValidity2(element2, false)) {
        return;
      }
    }
    if (!topMostAutoPopover2(document2)) {
      shouldRestoreFocus = true;
    }
    previouslyFocusedElements2.delete(element2);
    const originallyFocusedElement = document2.activeElement;
    element2.classList.add(":popover-open");
    visibilityState2.set(element2, "showing");
    if (!topLayerElements2.has(document2)) {
      topLayerElements2.set(document2, /* @__PURE__ */ new Set());
    }
    topLayerElements2.get(document2).add(element2);
    popoverFocusingSteps2(element2);
    if (element2.popover === "auto") {
      if (!autoPopoverList2.has(document2)) {
        autoPopoverList2.set(document2, /* @__PURE__ */ new Set());
      }
      autoPopoverList2.get(document2).add(element2);
      setInvokerAriaExpanded2(popoverInvoker2.get(element2), true);
    }
    if (shouldRestoreFocus && originallyFocusedElement && element2.popover === "auto") {
      previouslyFocusedElements2.set(element2, originallyFocusedElement);
    }
    queuePopoverToggleEventTask2(element2, "closed", "open");
  }
  function hidePopover2(element2, focusPreviousElement = false, fireEvents = false) {
    if (!checkPopoverValidity2(element2, true)) {
      return;
    }
    const document2 = element2.ownerDocument;
    if (element2.popover === "auto") {
      hideAllPopoversUntil2(element2, focusPreviousElement, fireEvents);
      if (!checkPopoverValidity2(element2, true)) {
        return;
      }
    }
    setInvokerAriaExpanded2(popoverInvoker2.get(element2), false);
    popoverInvoker2.delete(element2);
    if (fireEvents) {
      element2.dispatchEvent(
        new ToggleEvent2("beforetoggle", {
          oldState: "open",
          newState: "closed"
        })
      );
      if (!checkPopoverValidity2(element2, true)) {
        return;
      }
    }
    topLayerElements2.get(document2)?.delete(element2);
    autoPopoverList2.get(document2)?.delete(element2);
    element2.classList.remove(":popover-open");
    visibilityState2.set(element2, "hidden");
    if (fireEvents) {
      queuePopoverToggleEventTask2(element2, "open", "closed");
    }
    const previouslyFocusedElement = previouslyFocusedElements2.get(element2);
    if (previouslyFocusedElement) {
      previouslyFocusedElements2.delete(element2);
      if (focusPreviousElement) {
        previouslyFocusedElement.focus();
      }
    }
  }
  function closeAllOpenPopovers2(document2, focusPreviousElement = false, fireEvents = false) {
    let popover = topMostAutoPopover2(document2);
    while (popover) {
      hidePopover2(popover, focusPreviousElement, fireEvents);
      popover = topMostAutoPopover2(document2);
    }
  }
  function hideAllPopoversUntil2(endpoint, focusPreviousElement, fireEvents) {
    const document2 = endpoint.ownerDocument || endpoint;
    if (endpoint instanceof Document) {
      return closeAllOpenPopovers2(document2, focusPreviousElement, fireEvents);
    }
    let lastToHide = null;
    let foundEndpoint = false;
    for (const popover of autoPopoverList2.get(document2) || []) {
      if (popover === endpoint) {
        foundEndpoint = true;
      } else if (foundEndpoint) {
        lastToHide = popover;
        break;
      }
    }
    if (!foundEndpoint) {
      return closeAllOpenPopovers2(document2, focusPreviousElement, fireEvents);
    }
    while (lastToHide && getPopoverVisibilityState2(lastToHide) === "showing" && autoPopoverList2.get(document2)?.size) {
      hidePopover2(lastToHide, focusPreviousElement, fireEvents);
    }
  }
  var popoverPointerDownTargets2 = /* @__PURE__ */ new WeakMap();
  function lightDismissOpenPopovers2(event) {
    if (!event.isTrusted) return;
    const target = event.composedPath()[0];
    if (!target) return;
    const document2 = target.ownerDocument;
    const topMostPopover = topMostAutoPopover2(document2);
    if (!topMostPopover) return;
    const ancestor = topMostClickedPopover2(target);
    if (ancestor && event.type === "pointerdown") {
      popoverPointerDownTargets2.set(document2, ancestor);
    } else if (event.type === "pointerup") {
      const sameTarget = popoverPointerDownTargets2.get(document2) === ancestor;
      popoverPointerDownTargets2.delete(document2);
      if (sameTarget) {
        hideAllPopoversUntil2(ancestor || document2, false, true);
      }
    }
  }
  var initialAriaExpandedValue2 = /* @__PURE__ */ new WeakMap();
  function setInvokerAriaExpanded2(el, force = false) {
    if (!el) return;
    if (!initialAriaExpandedValue2.has(el)) {
      initialAriaExpandedValue2.set(el, el.getAttribute("aria-expanded"));
    }
    const popover = el.popoverTargetElement;
    if (popover instanceof HTMLElement && popover.popover === "auto") {
      el.setAttribute("aria-expanded", String(force));
    } else {
      const initialValue = initialAriaExpandedValue2.get(el);
      if (!initialValue) {
        el.removeAttribute("aria-expanded");
      } else {
        el.setAttribute("aria-expanded", initialValue);
      }
    }
  }
  var ShadowRoot23 = globalThis.ShadowRoot || function() {
  };
  function isSupported2() {
    return typeof HTMLElement !== "undefined" && typeof HTMLElement.prototype === "object" && "popover" in HTMLElement.prototype;
  }
  function isPolyfilled() {
    return Boolean(
      document.body?.showPopover && !/native code/i.test(document.body.showPopover.toString())
    );
  }
  function patchSelectorFn2(object, name, mapper) {
    const original = object[name];
    Object.defineProperty(object, name, {
      value(selector) {
        return original.call(this, mapper(selector));
      }
    });
  }
  var nonEscapedPopoverSelector2 = /(^|[^\\]):popover-open\b/g;
  function hasLayerSupport2() {
    return typeof globalThis.CSSLayerBlockRule === "function";
  }
  function getStyles2() {
    const useLayer = hasLayerSupport2();
    return `
${useLayer ? "@layer popover-polyfill {" : ""}
  :where([popover]) {
    position: fixed;
    z-index: 2147483647;
    inset: 0;
    padding: 0.25em;
    width: fit-content;
    height: fit-content;
    border-width: initial;
    border-color: initial;
    border-image: initial;
    border-style: solid;
    background-color: canvas;
    color: canvastext;
    overflow: auto;
    margin: auto;
  }

  :where([popover]:not(.\\:popover-open)) {
    display: none;
  }

  :where(dialog[popover].\\:popover-open) {
    display: block;
  }

  :where(dialog[popover][open]) {
    display: revert;
  }

  :where([anchor].\\:popover-open) {
    inset: auto;
  }

  :where([anchor]:popover-open) {
    inset: auto;
  }

  @supports not (background-color: canvas) {
    :where([popover]) {
      background-color: white;
      color: black;
    }
  }

  @supports (width: -moz-fit-content) {
    :where([popover]) {
      width: -moz-fit-content;
      height: -moz-fit-content;
    }
  }

  @supports not (inset: 0) {
    :where([popover]) {
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
    }
  }
${useLayer ? "}" : ""}
`;
  }
  var popoverStyleSheet2 = null;
  function injectStyles2(root) {
    const styles = getStyles2();
    if (popoverStyleSheet2 === null) {
      try {
        popoverStyleSheet2 = new CSSStyleSheet();
        popoverStyleSheet2.replaceSync(styles);
      } catch {
        popoverStyleSheet2 = false;
      }
    }
    if (popoverStyleSheet2 === false) {
      const sheet = document.createElement("style");
      sheet.textContent = styles;
      if (root instanceof Document) {
        root.head.prepend(sheet);
      } else {
        root.prepend(sheet);
      }
    } else {
      root.adoptedStyleSheets = [popoverStyleSheet2, ...root.adoptedStyleSheets];
    }
  }
  function apply2() {
    if (typeof window === "undefined") return;
    window.ToggleEvent = window.ToggleEvent || ToggleEvent2;
    function rewriteSelector(selector) {
      if (selector?.includes(":popover-open")) {
        selector = selector.replace(
          nonEscapedPopoverSelector2,
          "$1.\\:popover-open"
        );
      }
      return selector;
    }
    patchSelectorFn2(Document.prototype, "querySelector", rewriteSelector);
    patchSelectorFn2(Document.prototype, "querySelectorAll", rewriteSelector);
    patchSelectorFn2(Element.prototype, "querySelector", rewriteSelector);
    patchSelectorFn2(Element.prototype, "querySelectorAll", rewriteSelector);
    patchSelectorFn2(Element.prototype, "matches", rewriteSelector);
    patchSelectorFn2(Element.prototype, "closest", rewriteSelector);
    patchSelectorFn2(
      DocumentFragment.prototype,
      "querySelectorAll",
      rewriteSelector
    );
    Object.defineProperties(HTMLElement.prototype, {
      popover: {
        enumerable: true,
        configurable: true,
        get() {
          if (!this.hasAttribute("popover")) return null;
          const value3 = (this.getAttribute("popover") || "").toLowerCase();
          if (value3 === "" || value3 == "auto") return "auto";
          return "manual";
        },
        set(value3) {
          if (value3 === null) {
            this.removeAttribute("popover");
          } else {
            this.setAttribute("popover", value3);
          }
        }
      },
      showPopover: {
        enumerable: true,
        configurable: true,
        value() {
          showPopover2(this);
        }
      },
      hidePopover: {
        enumerable: true,
        configurable: true,
        value() {
          hidePopover2(this, true, true);
        }
      },
      togglePopover: {
        enumerable: true,
        configurable: true,
        value(force) {
          if (visibilityState2.get(this) === "showing" && force === void 0 || force === false) {
            hidePopover2(this, true, true);
          } else if (force === void 0 || force === true) {
            showPopover2(this);
          }
        }
      }
    });
    const originalAttachShadow = Element.prototype.attachShadow;
    if (originalAttachShadow) {
      Object.defineProperties(Element.prototype, {
        attachShadow: {
          enumerable: true,
          configurable: true,
          writable: true,
          value(options) {
            const shadowRoot = originalAttachShadow.call(this, options);
            injectStyles2(shadowRoot);
            return shadowRoot;
          }
        }
      });
    }
    const originalAttachInternals = HTMLElement.prototype.attachInternals;
    if (originalAttachInternals) {
      Object.defineProperties(HTMLElement.prototype, {
        attachInternals: {
          enumerable: true,
          configurable: true,
          writable: true,
          value() {
            const internals = originalAttachInternals.call(this);
            if (internals.shadowRoot) {
              injectStyles2(internals.shadowRoot);
            }
            return internals;
          }
        }
      });
    }
    const popoverTargetAssociatedElements = /* @__PURE__ */ new WeakMap();
    function applyPopoverInvokerElementMixin(ElementClass) {
      Object.defineProperties(ElementClass.prototype, {
        popoverTargetElement: {
          enumerable: true,
          configurable: true,
          set(targetElement) {
            if (targetElement === null) {
              this.removeAttribute("popovertarget");
              popoverTargetAssociatedElements.delete(this);
            } else if (!(targetElement instanceof Element)) {
              throw new TypeError(
                `popoverTargetElement must be an element or null`
              );
            } else {
              this.setAttribute("popovertarget", "");
              popoverTargetAssociatedElements.set(this, targetElement);
            }
          },
          get() {
            if (this.localName !== "button" && this.localName !== "input") {
              return null;
            }
            if (this.localName === "input" && this.type !== "reset" && this.type !== "image" && this.type !== "button") {
              return null;
            }
            if (this.disabled) {
              return null;
            }
            if (this.form && this.type === "submit") {
              return null;
            }
            const targetElement = popoverTargetAssociatedElements.get(this);
            if (targetElement && targetElement.isConnected) {
              return targetElement;
            } else if (targetElement && !targetElement.isConnected) {
              popoverTargetAssociatedElements.delete(this);
              return null;
            }
            const root = getRootNode2(this);
            const idref = this.getAttribute("popovertarget");
            if ((root instanceof Document || root instanceof ShadowRoot23) && idref) {
              return root.getElementById(idref) || null;
            }
            return null;
          }
        },
        popoverTargetAction: {
          enumerable: true,
          configurable: true,
          get() {
            const value3 = (this.getAttribute("popovertargetaction") || "").toLowerCase();
            if (value3 === "show" || value3 === "hide") return value3;
            return "toggle";
          },
          set(value3) {
            this.setAttribute("popovertargetaction", value3);
          }
        }
      });
    }
    applyPopoverInvokerElementMixin(HTMLButtonElement);
    applyPopoverInvokerElementMixin(HTMLInputElement);
    const handleInvokerActivation = (event) => {
      const composedPath = event.composedPath();
      const target = composedPath[0];
      if (!(target instanceof Element) || target?.shadowRoot) {
        return;
      }
      const root = getRootNode2(target);
      if (!(root instanceof ShadowRoot23 || root instanceof Document)) {
        return;
      }
      const invoker = composedPath.find(
        (el) => el.matches?.("[popovertargetaction],[popovertarget]")
      );
      if (invoker) {
        popoverTargetAttributeActivationBehavior2(invoker);
        event.preventDefault();
        return;
      }
    };
    const onKeydown = (event) => {
      const key = event.key;
      const target = event.target;
      if (!event.defaultPrevented && target && (key === "Escape" || key === "Esc")) {
        hideAllPopoversUntil2(target.ownerDocument, true, true);
      }
    };
    const addEventListeners = (root) => {
      root.addEventListener("click", handleInvokerActivation);
      root.addEventListener("keydown", onKeydown);
      root.addEventListener("pointerdown", lightDismissOpenPopovers2);
      root.addEventListener("pointerup", lightDismissOpenPopovers2);
    };
    addEventListeners(document);
    injectStyles2(document);
  }

