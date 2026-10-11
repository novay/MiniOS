(function() {
	//#region packages/novay/minios/resources/js/ui/polyfills/popover.js
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
		popoverToggleTaskQueue.set(element2, setTimeout(() => {
			if (!popoverToggleTaskQueue.has(element2)) return;
			element2.dispatchEvent(new ToggleEvent("toggle", {
				cancelable: false,
				oldState,
				newState
			}));
		}, 0));
	}
	var ShadowRoot2 = globalThis.ShadowRoot || function() {};
	var HTMLDialogElement = globalThis.HTMLDialogElement || function() {};
	var topLayerElements = /* @__PURE__ */ new WeakMap();
	var autoPopoverList = /* @__PURE__ */ new WeakMap();
	var visibilityState = /* @__PURE__ */ new WeakMap();
	function getPopoverVisibilityState(popover) {
		return visibilityState.get(popover) || "hidden";
	}
	var popoverInvoker = /* @__PURE__ */ new WeakMap();
	function popoverTargetAttributeActivationBehavior(element2) {
		const popover = element2.popoverTargetElement;
		if (!(popover instanceof HTMLElement)) return;
		const visibility = getPopoverVisibilityState(popover);
		if (element2.popoverTargetAction === "show" && visibility === "showing") return;
		if (element2.popoverTargetAction === "hide" && visibility === "hidden") return;
		if (visibility === "showing") hidePopover(popover, true, true);
		else if (checkPopoverValidity(popover, false)) {
			popoverInvoker.set(popover, element2);
			showPopover(popover);
		}
	}
	function checkPopoverValidity(element2, expectedToBeShowing) {
		if (element2.popover !== "auto" && element2.popover !== "manual") return false;
		if (!element2.isConnected) return false;
		if (expectedToBeShowing && getPopoverVisibilityState(element2) !== "showing") return false;
		if (!expectedToBeShowing && getPopoverVisibilityState(element2) !== "hidden") return false;
		if (element2 instanceof HTMLDialogElement && element2.hasAttribute("open")) return false;
		if (document.fullscreenElement === element2) return false;
		return true;
	}
	function getStackPosition(popover) {
		if (!popover) return 0;
		return Array.from(autoPopoverList.get(popover.ownerDocument) || []).indexOf(popover) + 1;
	}
	function topMostClickedPopover(target) {
		const clickedPopover = nearestInclusiveOpenPopover(target);
		const invokerPopover = nearestInclusiveTargetPopoverForInvoker(target);
		if (getStackPosition(clickedPopover) > getStackPosition(invokerPopover)) return clickedPopover;
		return invokerPopover;
	}
	function topMostAutoPopover(document2) {
		const documentPopovers = autoPopoverList.get(document2);
		for (const popover of documentPopovers || []) if (!popover.isConnected) documentPopovers.delete(popover);
		else return popover;
		return null;
	}
	function getRootNode(node) {
		if (typeof node.getRootNode === "function") return node.getRootNode();
		if (node.parentNode) return getRootNode(node.parentNode);
		return node;
	}
	function nearestInclusiveOpenPopover(node) {
		while (node) {
			if (node instanceof HTMLElement && node.popover === "auto" && visibilityState.get(node) === "showing") return node;
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
			if (topMostPopoverAncestor22 === null || popoverPositions.get(topMostPopoverAncestor22) < candidatePosition) topMostPopoverAncestor22 = candidateAncestor;
		}
		checkAncestor(newPopover.parentElement || getRootNode(newPopover));
		return topMostPopoverAncestor22;
	}
	function isFocusable(focusTarget) {
		if (focusTarget.hidden || focusTarget instanceof ShadowRoot2) return false;
		if (focusTarget instanceof HTMLButtonElement || focusTarget instanceof HTMLInputElement || focusTarget instanceof HTMLSelectElement || focusTarget instanceof HTMLTextAreaElement || focusTarget instanceof HTMLOptGroupElement || focusTarget instanceof HTMLOptionElement || focusTarget instanceof HTMLFieldSetElement) {
			if (focusTarget.disabled) return false;
		}
		if (focusTarget instanceof HTMLInputElement && focusTarget.type === "hidden") return false;
		if (focusTarget instanceof HTMLAnchorElement && focusTarget.href === "") return false;
		return typeof focusTarget.tabIndex === "number" && focusTarget.tabIndex !== -1;
	}
	function focusDelegate(focusTarget) {
		if (focusTarget.shadowRoot && focusTarget.shadowRoot.delegatesFocus !== true) return null;
		let whereToLook = focusTarget;
		if (whereToLook.shadowRoot) whereToLook = whereToLook.shadowRoot;
		let autoFocusDelegate = whereToLook.querySelector("[autofocus]");
		if (autoFocusDelegate) return autoFocusDelegate;
		else {
			const slots = whereToLook.querySelectorAll("slot");
			for (const slot of slots) {
				const assignedElements = slot.assignedElements({ flatten: true });
				for (const el of assignedElements) if (el.hasAttribute("autofocus")) return el;
				else {
					autoFocusDelegate = el.querySelector("[autofocus]");
					if (autoFocusDelegate) return autoFocusDelegate;
				}
			}
		}
		const walker2 = focusTarget.ownerDocument.createTreeWalker(whereToLook, NodeFilter.SHOW_ELEMENT);
		let descendant = walker2.currentNode;
		while (descendant) {
			if (isFocusable(descendant)) return descendant;
			descendant = walker2.nextNode();
		}
	}
	function popoverFocusingSteps(subject) {
		focusDelegate(subject)?.focus();
	}
	var previouslyFocusedElements = /* @__PURE__ */ new WeakMap();
	function showPopover(element2) {
		if (!checkPopoverValidity(element2, false)) return;
		const document2 = element2.ownerDocument;
		if (!element2.dispatchEvent(new ToggleEvent("beforetoggle", {
			cancelable: true,
			oldState: "closed",
			newState: "open"
		}))) return;
		if (!checkPopoverValidity(element2, false)) return;
		let shouldRestoreFocus = false;
		if (element2.popover === "auto") {
			const originalType = element2.getAttribute("popover");
			hideAllPopoversUntil(topMostPopoverAncestor(element2) || document2, false, true);
			if (originalType !== element2.getAttribute("popover") || !checkPopoverValidity(element2, false)) return;
		}
		if (!topMostAutoPopover(document2)) shouldRestoreFocus = true;
		previouslyFocusedElements.delete(element2);
		const originallyFocusedElement = document2.activeElement;
		element2.classList.add(":popover-open");
		visibilityState.set(element2, "showing");
		if (!topLayerElements.has(document2)) topLayerElements.set(document2, /* @__PURE__ */ new Set());
		topLayerElements.get(document2).add(element2);
		popoverFocusingSteps(element2);
		if (element2.popover === "auto") {
			if (!autoPopoverList.has(document2)) autoPopoverList.set(document2, /* @__PURE__ */ new Set());
			autoPopoverList.get(document2).add(element2);
			setInvokerAriaExpanded(popoverInvoker.get(element2), true);
		}
		if (shouldRestoreFocus && originallyFocusedElement && element2.popover === "auto") previouslyFocusedElements.set(element2, originallyFocusedElement);
		queuePopoverToggleEventTask(element2, "closed", "open");
	}
	function hidePopover(element2, focusPreviousElement = false, fireEvents = false) {
		if (!checkPopoverValidity(element2, true)) return;
		const document2 = element2.ownerDocument;
		if (element2.popover === "auto") {
			hideAllPopoversUntil(element2, focusPreviousElement, fireEvents);
			if (!checkPopoverValidity(element2, true)) return;
		}
		setInvokerAriaExpanded(popoverInvoker.get(element2), false);
		popoverInvoker.delete(element2);
		if (fireEvents) {
			element2.dispatchEvent(new ToggleEvent("beforetoggle", {
				oldState: "open",
				newState: "closed"
			}));
			if (!checkPopoverValidity(element2, true)) return;
		}
		topLayerElements.get(document2)?.delete(element2);
		autoPopoverList.get(document2)?.delete(element2);
		element2.classList.remove(":popover-open");
		visibilityState.set(element2, "hidden");
		if (fireEvents) queuePopoverToggleEventTask(element2, "open", "closed");
		const previouslyFocusedElement = previouslyFocusedElements.get(element2);
		if (previouslyFocusedElement) {
			previouslyFocusedElements.delete(element2);
			if (focusPreviousElement) previouslyFocusedElement.focus();
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
		if (endpoint instanceof Document) return closeAllOpenPopovers(document2, focusPreviousElement, fireEvents);
		let lastToHide = null;
		let foundEndpoint = false;
		for (const popover of autoPopoverList.get(document2) || []) if (popover === endpoint) foundEndpoint = true;
		else if (foundEndpoint) {
			lastToHide = popover;
			break;
		}
		if (!foundEndpoint) return closeAllOpenPopovers(document2, focusPreviousElement, fireEvents);
		while (lastToHide && getPopoverVisibilityState(lastToHide) === "showing" && autoPopoverList.get(document2)?.size) hidePopover(lastToHide, focusPreviousElement, fireEvents);
	}
	var popoverPointerDownTargets = /* @__PURE__ */ new WeakMap();
	function lightDismissOpenPopovers(event) {
		if (!event.isTrusted) return;
		const target = event.composedPath()[0];
		if (!target) return;
		const document2 = target.ownerDocument;
		if (!topMostAutoPopover(document2)) return;
		const ancestor = topMostClickedPopover(target);
		if (ancestor && event.type === "pointerdown") popoverPointerDownTargets.set(document2, ancestor);
		else if (event.type === "pointerup") {
			const sameTarget = popoverPointerDownTargets.get(document2) === ancestor;
			popoverPointerDownTargets.delete(document2);
			if (sameTarget) hideAllPopoversUntil(ancestor || document2, false, true);
		}
	}
	var initialAriaExpandedValue = /* @__PURE__ */ new WeakMap();
	function setInvokerAriaExpanded(el, force = false) {
		if (!el) return;
		if (!initialAriaExpandedValue.has(el)) initialAriaExpandedValue.set(el, el.getAttribute("aria-expanded"));
		const popover = el.popoverTargetElement;
		if (popover instanceof HTMLElement && popover.popover === "auto") el.setAttribute("aria-expanded", String(force));
		else {
			const initialValue = initialAriaExpandedValue.get(el);
			if (!initialValue) el.removeAttribute("aria-expanded");
			else el.setAttribute("aria-expanded", initialValue);
		}
	}
	var ShadowRoot22 = globalThis.ShadowRoot || function() {};
	function isSupported() {
		return typeof HTMLElement !== "undefined" && typeof HTMLElement.prototype === "object" && "popover" in HTMLElement.prototype;
	}
	function patchSelectorFn(object, name, mapper) {
		const original = object[name];
		Object.defineProperty(object, name, { value(selector) {
			return original.call(this, mapper(selector));
		} });
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
		if (popoverStyleSheet === null) try {
			popoverStyleSheet = new CSSStyleSheet();
			popoverStyleSheet.replaceSync(styles);
		} catch {
			popoverStyleSheet = false;
		}
		if (popoverStyleSheet === false) {
			const sheet = document.createElement("style");
			sheet.textContent = styles;
			if (root instanceof Document) root.head.prepend(sheet);
			else root.prepend(sheet);
		} else root.adoptedStyleSheets = [popoverStyleSheet, ...root.adoptedStyleSheets];
	}
	function apply() {
		if (typeof window === "undefined") return;
		window.ToggleEvent = window.ToggleEvent || ToggleEvent;
		function rewriteSelector(selector) {
			if (selector?.includes(":popover-open")) selector = selector.replace(nonEscapedPopoverSelector, "$1.\\:popover-open");
			return selector;
		}
		patchSelectorFn(Document.prototype, "querySelector", rewriteSelector);
		patchSelectorFn(Document.prototype, "querySelectorAll", rewriteSelector);
		patchSelectorFn(Element.prototype, "querySelector", rewriteSelector);
		patchSelectorFn(Element.prototype, "querySelectorAll", rewriteSelector);
		patchSelectorFn(Element.prototype, "matches", rewriteSelector);
		patchSelectorFn(Element.prototype, "closest", rewriteSelector);
		patchSelectorFn(DocumentFragment.prototype, "querySelectorAll", rewriteSelector);
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
					if (value3 === null) this.removeAttribute("popover");
					else this.setAttribute("popover", value3);
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
					if (visibilityState.get(this) === "showing" && force === void 0 || force === false) hidePopover(this, true, true);
					else if (force === void 0 || force === true) showPopover(this);
				}
			}
		});
		const originalAttachShadow = Element.prototype.attachShadow;
		if (originalAttachShadow) Object.defineProperties(Element.prototype, { attachShadow: {
			enumerable: true,
			configurable: true,
			writable: true,
			value(options) {
				const shadowRoot = originalAttachShadow.call(this, options);
				injectStyles(shadowRoot);
				return shadowRoot;
			}
		} });
		const originalAttachInternals = HTMLElement.prototype.attachInternals;
		if (originalAttachInternals) Object.defineProperties(HTMLElement.prototype, { attachInternals: {
			enumerable: true,
			configurable: true,
			writable: true,
			value() {
				const internals = originalAttachInternals.call(this);
				if (internals.shadowRoot) injectStyles(internals.shadowRoot);
				return internals;
			}
		} });
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
						} else if (!(targetElement instanceof Element)) throw new TypeError(`popoverTargetElement must be an element or null`);
						else {
							this.setAttribute("popovertarget", "");
							popoverTargetAssociatedElements.set(this, targetElement);
						}
					},
					get() {
						if (this.localName !== "button" && this.localName !== "input") return null;
						if (this.localName === "input" && this.type !== "reset" && this.type !== "image" && this.type !== "button") return null;
						if (this.disabled) return null;
						if (this.form && this.type === "submit") return null;
						const targetElement = popoverTargetAssociatedElements.get(this);
						if (targetElement && targetElement.isConnected) return targetElement;
						else if (targetElement && !targetElement.isConnected) {
							popoverTargetAssociatedElements.delete(this);
							return null;
						}
						const root = getRootNode(this);
						const idref = this.getAttribute("popovertarget");
						if ((root instanceof Document || root instanceof ShadowRoot22) && idref) return root.getElementById(idref) || null;
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
			if (!(target instanceof Element) || target?.shadowRoot) return;
			const root = getRootNode(target);
			if (!(root instanceof ShadowRoot22 || root instanceof Document)) return;
			const invoker = composedPath.find((el) => el.matches?.("[popovertargetaction],[popovertarget]"));
			if (invoker) {
				popoverTargetAttributeActivationBehavior(invoker);
				event.preventDefault();
				return;
			}
		};
		const onKeydown = (event) => {
			const key = event.key;
			const target = event.target;
			if (!event.defaultPrevented && target && (key === "Escape" || key === "Esc")) hideAllPopoversUntil(target.ownerDocument, true, true);
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
	globalThis.ShadowRoot;
	globalThis.HTMLDialogElement;
	globalThis.ShadowRoot;
	//#endregion
	//#region packages/novay/minios/resources/js/ui/core/utils.js
	var styleSheet;
	function inject$1(callback) {
		let styles = callback({ css: (strings, ...values) => `@layer base { ${strings.raw[0] + values.join("")} }` });
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
	document.addEventListener("keydown", () => "keyboard", { capture: true });
	document.addEventListener("pointerdown", (e) => {
		e.pointerType;
	}, { capture: true });
	document.addEventListener("pointermove", (e) => {
		e.pointerType;
	}, { capture: true });
	inject$1(({ css }) => css`[data-os-allow-scroll] { pointer-events: auto; }`);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/core/mixins.js
	document.addEventListener("mousedown", (event) => event);
	CSS.supports?.("selector(:popover-open)");
	UIElement;
	UIElement;
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/modal.js
	var UIModal = class extends UIElement {
		boot() {
			this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
			this._controllable = new Controllable(this, { disabled: this.hasAttribute("disabled") });
			let button = this.button();
			let dialog = this.dialog();
			if (!dialog) return;
			dialog._dialogable = new Dialogable(dialog, {
				clickOutside: !this.hasAttribute("disable-click-outside"),
				escape: !this.hasAttribute("disable-escape")
			});
			dialog._closeable = new Closeable(dialog);
			dialog._closeable.onClose(() => dialog._dialogable.hide());
			this._controllable.initial((initial) => initial && dialog._dialogable.show());
			this._controllable.getter(() => dialog._dialogable.getState());
			let detangled = detangle();
			this._controllable.setter(detangled((value3) => {
				dialog._dialogable.setState(value3);
			}));
			dialog._dialogable.onChange(detangled(() => {
				this._controllable.dispatch();
			}));
			let refresh = () => {
				if (dialog._dialogable.getState()) {
					setAttribute2(this, "data-open", "");
					button?.setAttribute("data-open", "");
					setAttribute2(dialog, "data-open", "");
				} else {
					removeAttribute(this, "data-open");
					button?.removeAttribute("data-open");
					removeAttribute(dialog, "data-open");
				}
			};
			dialog._dialogable.onChange(() => refresh());
			refresh();
			let { lock, unlock } = lockScroll();
			dialog._dialogable.onChange(() => {
				dialog._dialogable.getState() ? lock() : unlock();
			});
			button && on(button, "click", (e) => {
				dialog._dialogable.show();
			});
		}
		unmount() {
			if (this.dialog()?._dialogable?.getState()) {
				let { unlock } = lockScroll();
				unlock();
			}
		}
		button() {
			let button = this.querySelector("button,ui-button");
			if (this.dialog()?.contains(button)) return;
			return button;
		}
		dialog() {
			return this.querySelector("dialog");
		}
		showModal() {
			let dialog = this.dialog();
			if (!dialog) return;
			dialog.showModal();
		}
	};
	inject(({ css }) => css`dialog, ::backdrop { margin: auto; }`);
	element("modal", UIModal);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/select.js
	var UISelect$1 = class extends UIControl {
		boot() {
			let list = this.list();
			this._controllable = new Controllable(this, { bubbles: true });
			this._selectable = new SelectableGroup(list, { multiple: this.hasAttribute("multiple") });
			this._submittable = new Submittable(this, {
				name: this.getAttribute("name"),
				value: this._selectable.getState()
			});
			this._controllable.initial((initial) => initial && this._selectable.setState(initial));
			this._controllable.getter(() => this._selectable.getState());
			let detangled = detangle();
			this._controllable.setter(detangled((value3) => {
				this._selectable.setState(value3);
			}));
			this._selectable.onChange(detangled(() => {
				this._controllable.dispatch();
				this.dispatchEvent(new CustomEvent("select", { bubbles: false }));
			}));
			this._selectable.onInitAndChange(() => {
				this._submittable.update(this._selectable.getState());
			});
		}
		mount() {
			this._disableable = new Disableable(this);
			queueMicrotask(() => {
				this._submittable.update(this._selectable.getState());
			});
			let input = this.input();
			let button = this.button();
			let list = this.list();
			let multiple = this.hasAttribute("multiple");
			let autocomplete = this.hasAttribute("autocomplete");
			let strictAutocomplete = this.hasAttribute("autocomplete") && this.getAttribute("autocomplete").trim().split(" ").includes("strict");
			let listbox = this.querySelector("ui-options") || this;
			let listId = initListbox(listbox, "options", multiple);
			this._activatable = new ActivatableGroup(listbox, { filter: "data-hidden" });
			if (!input && !button) this._disableable.onInitAndChange((disabled) => {
				disabled ? this.removeAttribute("tabindex") : this.setAttribute("tabindex", "0");
			});
			if (this.hasAttribute("filter") && this.getAttribute("filter") !== "manual") {
				this._filterable = new FilterableGroup(list);
				this._filterable.onChange(() => {
					this._activatable.clearActive();
					if (this._filterable.hasResults()) this._activatable.activateFirst();
				});
				this.addEventListener("open", () => {
					if (this._filterable) this._filterable.filter("");
				});
				this.addEventListener("close", () => {
					if (this._filterable) requestAnimationFrame(() => {
						this._filterable.filter("");
					});
				});
			}
			let popoverEl = this.querySelector("[popover]:not(ui-tooltip > [popover])");
			let popoverInputEl = popoverEl?.querySelector("input:not([type=\"hidden\"])");
			let inputEl = this.querySelector("input:not([type=\"hidden\"])");
			inputEl = popoverEl?.contains(inputEl) ? null : inputEl;
			let buttonEl = this.querySelector("button,ui-button");
			buttonEl = popoverEl?.contains(buttonEl) ? null : buttonEl;
			if (!(popoverEl || inputEl)) {
				handleKeyboardNavigation$1(this, this._activatable);
				handleKeyboardSelection$1(this, this, this._activatable);
				handleActivationOnFocus(this, this._activatable, this._selectable);
			} else if (!popoverEl && inputEl) {
				let input2 = inputEl;
				this._disableable.onInitAndChange((disabled) => {
					if (disabled) input2 && setAttribute2(input2, "disabled", "");
					else input2 && removeAttribute(input2, "disabled");
				});
				handleInputClearing(this, input2, this._selectable, this._popoverable);
				handleActivationOnFocus(input2, this._activatable, this._selectable);
				handleAutocomplete(autocomplete, strictAutocomplete, this, input2, this._selectable, this._popoverable, this._filterable);
				preventInputEventsFromBubblingToSelectRoot(input2);
				highlightInputContentsWhenFocused(input2);
				this._filterable && filterResultsByInput(list, input2, this._filterable);
				trackActiveDescendant(input2, this._activatable, this._selectable);
				handleKeyboardNavigation$1(input2, this._activatable);
				handleKeyboardSelection$1(this, input2, this._activatable);
				handleMouseSelection(this, this._activatable);
			} else if (popoverEl && inputEl) {
				let input2 = inputEl;
				setAttribute2(input2, "role", "combobox");
				setAttribute2(input2, "aria-controls", listId);
				let popover = popoverEl;
				this._popoverable = new Popoverable(popover);
				this._anchorable = new Anchorable(popover, {
					reference: input2,
					matchWidth: true,
					position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
					gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
					offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
				});
				handleAutocomplete(autocomplete, strictAutocomplete, this, input2, this._selectable, this._popoverable, this._filterable);
				this._disableable.onInitAndChange((disabled) => {
					if (disabled) input2 && setAttribute2(input2, "disabled", "");
					else input2 && removeAttribute(input2, "disabled");
				});
				this.querySelectorAll("button,ui-button").forEach((button2) => {
					if (popover.contains(button2)) return;
					setAttribute2(button2, "tabindex", "-1");
					setAttribute2(button2, "aria-controls", listId);
					setAttribute2(button2, "aria-haspopup", "listbox");
					linkExpandedStateToPopover(button2, this._popoverable);
					on(button2, "click", () => {
						this._popoverable.toggle();
						if (!isUsingTouch()) input2.focus();
					});
				});
				handleInputClearing(this, input2, this._selectable, this._popoverable);
				initPopover(this, input2, popover, this._popoverable, this._anchorable);
				preventScrollWhenPopoverIsOpen(this, this._popoverable, [input2]);
				linkExpandedStateToPopover(input2, this._popoverable);
				preventInputEventsFromBubblingToSelectRoot(input2);
				highlightInputContentsWhenFocused(input2);
				this._filterable && filterResultsByInput(list, input2, this._filterable);
				trackActiveDescendant(input2, this._activatable, this._selectable);
				controlPopoverWithInput(input2, this._popoverable);
				controlPopoverWithKeyboard$1(input2, this._popoverable, this._activatable, this._selectable);
				openPopoverWithMouse(input2, this._popoverable);
				controlPopoverWithEscapeKey$1(this, this._popoverable);
				handleKeyboardNavigation$1(input2, this._activatable);
				handleKeyboardSelection$1(this, input2, this._activatable);
				handleMouseSelection(this, this._activatable);
				controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
				handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
			} else if (popoverEl && popoverInputEl) {
				let button2 = buttonEl;
				let input2 = popoverInputEl;
				let popover = popoverEl;
				setAttribute2(button2, "role", "combobox");
				setAttribute2(input2, "role", "combobox");
				setAttribute2(button2, "aria-controls", listId);
				this._disableable.onInitAndChange((disabled) => {
					if (disabled) {
						button2 && setAttribute2(button2, "disabled", "");
						input2 && setAttribute2(input2, "disabled", "");
					} else {
						button2 && removeAttribute(button2, "disabled");
						input2 && removeAttribute(input2, "disabled");
					}
				});
				this._popoverable = new Popoverable(popover);
				this._anchorable = new Anchorable(popover, {
					reference: button2,
					matchWidth: true,
					position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
					gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
					offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0,
					scrollY: false
				});
				preventInputEventsFromBubblingToSelectRoot(input2);
				highlightInputContentsWhenFocused(input2);
				this._filterable && filterResultsByInput(list, input2, this._filterable);
				trackActiveDescendant(input2, this._activatable, this._selectable);
				focusInputWhenPopoverOpens(input2, this._popoverable);
				initPopover(this, button2, popover, this._popoverable, this._anchorable);
				preventScrollWhenPopoverIsOpen(this, this._popoverable, [input2]);
				linkExpandedStateToPopover(button2, this._popoverable);
				handleInputClearing(this, input2, this._selectable, this._popoverable);
				controlPopoverWithKeyboard$1(button2, this._popoverable, this._activatable, this._selectable);
				togglePopoverWithMouse(button2, this._popoverable);
				controlPopoverWithEscapeKey$1(this, this._popoverable);
				handleKeyboardNavigation$1(input2, this._activatable);
				handleKeyboardSearchNavigation$1(button2, this._activatable, this._popoverable);
				handleKeyboardSelection$1(this, input2, this._activatable);
				handleMouseSelection(this, this._activatable);
				controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
				handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
			} else if (popoverEl) {
				let button2 = buttonEl;
				let popover = popoverEl;
				setAttribute2(button2, "role", "combobox");
				setAttribute2(button2, "aria-controls", listId);
				this._disableable.onInitAndChange((disabled) => {
					if (disabled) {
						button2 && setAttribute2(button2, "disabled", "");
						input && setAttribute2(input, "disabled", "");
					} else {
						button2 && removeAttribute(button2, "disabled");
						input && removeAttribute(input, "disabled");
					}
				});
				this._popoverable = new Popoverable(popover);
				this._anchorable = new Anchorable(popover, {
					reference: button2,
					matchWidth: true,
					position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
					gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
					offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
				});
				initPopover(this, button2, popover, this._popoverable, this._anchorable);
				preventScrollWhenPopoverIsOpen(this, this._popoverable);
				linkExpandedStateToPopover(button2, this._popoverable);
				controlPopoverWithKeyboard$1(button2, this._popoverable, this._activatable, this._selectable);
				togglePopoverWithMouse(button2, this._popoverable);
				trackActiveDescendant(button2, this._activatable, this._selectable);
				controlPopoverWithEscapeKey$1(this, this._popoverable);
				handleKeyboardNavigation$1(button2, this._activatable);
				handleKeyboardSearchNavigation$1(button2, this._activatable, this._popoverable);
				handleKeyboardSelection$1(this, button2, this._activatable);
				handleMouseSelection(this, this._activatable);
				controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
				handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
			}
			new MutationObserver(() => {
				requestAnimationFrame(() => {
					if (!this._popoverable || this._popoverable.getState()) {
						let firstSelectedOption = this._selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
						this._activatable.activateSelectedOrFirst(firstSelectedOption);
					} else this._activatable.clearActive();
				});
			}).observe(list, { childList: true });
		}
		unmount() {
			if (this._popoverable?.getState()) {
				let { unlock } = lockScroll();
				unlock();
			}
		}
		trigger() {
			return this.button() || this.input();
		}
		button() {
			return Array.from(this.querySelectorAll("button,ui-button")).find((button) => button.nextElementSibling?.matches("[popover]")) || null;
		}
		input() {
			return this.querySelector("input:not([type=\"hidden\"])");
		}
		list() {
			return this.querySelector("ui-options") || this;
		}
		clear() {
			this._selectable.setState(null);
			this.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
		}
		open() {
			this._popoverable.setState(true);
		}
		close() {
			this._popoverable.setState(false);
		}
		deselectLast() {
			if (!this.hasAttribute("multiple") && this.value !== null) {
				this.value = null;
				this.dispatchEvent(new Event("input", { bubbles: false }));
				this.dispatchEvent(new Event("change", { bubbles: false }));
			}
			if (this.hasAttribute("multiple") && this.value.length !== 0) {
				this.value = this.value.slice(0, -1);
				this.dispatchEvent(new Event("input", { bubbles: false }));
				this.dispatchEvent(new Event("change", { bubbles: false }));
			}
		}
	};
	element("selected-remove", UISelectedRemove);
	element("selected", UISelected);
	element("select", UISelect$1);
	function handleKeyboardNavigation$1(el, activatable) {
		on(el, "keydown", (e) => {
			if (!["ArrowDown", "ArrowUp"].includes(e.key)) return;
			if (e.key === "ArrowDown") {
				activatable.activateNext();
				e.preventDefault();
				e.stopPropagation();
			} else if (e.key === "ArrowUp") {
				activatable.activatePrev();
				e.preventDefault();
				e.stopPropagation();
			}
		});
	}
	function handleKeyboardSearchNavigation$1(el, activatable, popoverable) {
		search(el, (query) => {
			activatable.activateBySearch(query);
			if (!popoverable.getState()) activatable.getActive()?.click();
		});
	}
	function handleKeyboardSelection$1(root, el, activatable) {
		on(el, "keydown", (e) => {
			if (e.key === "Enter") {
				let activeEl = activatable.getActive();
				e.preventDefault();
				e.stopPropagation();
				if (!activeEl) return;
				if (activeEl._disableable?.isDisabled()) return;
				activeEl.click();
				root.dispatchEvent(new CustomEvent("interaction", {
					bubbles: false,
					cancelable: false,
					detail: { optionEl: activeEl }
				}));
				if (activeEl.hasAttribute("action")) root.dispatchEvent(new CustomEvent("action", {
					bubbles: false,
					cancelable: false,
					detail: { optionEl: activeEl }
				}));
			}
		});
	}
	function handleMouseSelection(root, activatable, pointerdown = false) {
		on(root, pointerdown ? "pointerdown" : "click", (e) => {
			if (e.target.closest("ui-option, ui-option-create")) {
				let option = e.target.closest("ui-option, ui-option-create");
				if (option._disableable.isDisabled()) return;
				option._selectable?.trigger();
				root.dispatchEvent(new CustomEvent("interaction", {
					bubbles: false,
					cancelable: false,
					detail: { optionEl: option }
				}));
				if (option.hasAttribute("action")) root.dispatchEvent(new CustomEvent("action", {
					bubbles: false,
					cancelable: false,
					detail: { optionEl: option }
				}));
				e.preventDefault();
				e.stopPropagation();
			}
		});
	}
	function handleActivationOnFocus(el, activatable, selectable) {
		on(el, "focus", () => {
			let firstSelectedOption = selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
			activatable.activateSelectedOrFirst(firstSelectedOption);
		});
		on(el, "blur", () => {
			activatable.clearActive();
		});
	}
	function initListbox(el, multiple) {
		let listId = assignId(el, "options");
		setAttribute2(el, "role", "listbox");
		setAttribute2(el, "aria-multiselectable", multiple ? "true" : "false");
		return listId;
	}
	function linkExpandedStateToPopover(el, popoverable) {
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
	function initPopover(root, trigger, popover, popoverable, anchorable) {
		let refreshPopover = () => {
			Array.from([root, popover]).forEach((i) => {
				popoverable.getState() ? setAttribute2(i, "data-open", "") : removeAttribute(i, "data-open", "");
			});
			popoverable.getState() ? anchorable.reposition() : anchorable.cleanup();
		};
		popoverable.onChange(() => refreshPopover());
		refreshPopover();
		popoverable.onChange(() => {
			if (popoverable.getState()) root.dispatchEvent(new Event("open", {
				bubbles: false,
				cancelable: false
			}));
			else root.dispatchEvent(new Event("close", {
				bubbles: false,
				cancelable: false
			}));
		});
	}
	function controlActivationWithPopover(popoverable, activatable, selectable) {
		popoverable.onChange(() => {
			if (popoverable.getState()) {
				let firstSelectedOption = selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
				setTimeout(() => {
					activatable.activateSelectedOrFirst(firstSelectedOption);
				});
			} else activatable.clearActive();
		});
	}
	function controlPopoverWithEscapeKey$1(root, popoverable) {
		on(root, "keydown", (e) => {
			if (e.key === "Escape" && popoverable.getState()) {
				popoverable.setState(false);
				e.preventDefault();
				e.stopImmediatePropagation();
			}
		});
	}
	function controlPopoverWithKeyboard$1(button, popoverable) {
		on(button, "keydown", (e) => {
			if (!["ArrowDown", "ArrowUp"].includes(e.key)) return;
			if (e.key === "ArrowDown") {
				if (!popoverable.getState()) {
					popoverable.setState(true);
					e.preventDefault();
					e.stopImmediatePropagation();
				}
			} else if (e.key === "ArrowUp") {
				if (!popoverable.getState()) {
					popoverable.setState(true);
					e.preventDefault();
					e.stopImmediatePropagation();
				}
			}
		});
	}
	function openPopoverWithMouse(el, popoverable) {
		on(el, "click", () => {
			if (!popoverable.getState()) {
				popoverable.setState(true);
				el.focus();
			}
		});
	}
	function togglePopoverWithMouse(button, popoverable) {
		on(button, "click", () => {
			popoverable.setState(!popoverable.getState());
			button.focus();
		});
	}
	function focusInputWhenPopoverOpens(input, popoverable) {
		popoverable.onChange(() => {
			if (popoverable.getState() && !isUsingTouch()) setTimeout(() => input.focus());
		});
	}
	function filterResultsByInput(list, input, filterable) {
		on(input, "input", (e) => {
			filterable.filter(e.target.value);
		});
		let original = Object.getOwnPropertyDescriptor(Object.getPrototypeOf(input), "value");
		Object.defineProperty(input, "value", {
			get() {
				return original.get.call(this);
			},
			set(value3) {
				original.set.call(this, value3);
				filterable.filter(value3);
			}
		});
		new MutationObserver(() => {
			requestAnimationFrame(() => {
				filterable.filter(filterable.lastSearch);
			});
		}).observe(list, { childList: true });
	}
	function highlightInputContentsWhenFocused(input) {
		on(input, "focus", () => input.select());
	}
	function preventInputEventsFromBubblingToSelectRoot(input) {
		on(input, "change", (e) => e.stopPropagation());
		on(input, "input", (e) => e.stopPropagation());
	}
	function controlPopoverWithInput(input, popoverable) {
		on(input, "keydown", (e) => {
			if (/^[a-zA-Z0-9]$/.test(e.key) || e.key === "Backspace") popoverable.getState() || popoverable.setState(true);
		});
	}
	function handleInputClearing(root, input, selectable, popoverable) {
		if (!root.hasAttribute("clear")) return;
		let setInputValue = (value3) => {
			if (input.value === value3) return;
			input.value = value3;
			input.dispatchEvent(new Event("input", { bubbles: false }));
		};
		let clear = root.getAttribute("clear");
		let clearOnAction = clear === "" || clear.split(" ").includes("action");
		let clearOnSelect = clear === "" || clear.split(" ").includes("select");
		let clearOnClose = clear === "" || clear.split(" ").includes("close");
		let clearOnEsc = clear === "" || clear.split(" ").includes("esc");
		if (clear === "none") clearOnAction = clearOnSelect = clearOnClose = clearOnEsc = false;
		if (clearOnAction) root.addEventListener("action", (e) => {
			setInputValue("");
		});
		else if (clearOnSelect) selectable.onChange(() => {
			queueMicrotask(() => setInputValue(""));
		});
		if (clearOnClose) popoverable.onChange(() => {
			if (!popoverable.getState()) setInputValue("");
		});
		if (clearOnEsc) on(input, "keydown", (e) => {
			if (e.key === "Escape") setInputValue("");
		});
	}
	function handlePopoverClosing(root, selectable, popoverable, multiple) {
		let closeOnInteraction = !multiple;
		let closeOnAction = !multiple;
		let closeOnSelect = !multiple;
		if (root.hasAttribute("close")) {
			let close = root.getAttribute("close");
			closeOnInteraction = close === "";
			closeOnAction = close.split(" ").includes("action");
			closeOnSelect = close.split(" ").includes("select");
			if (close === "none") return;
		}
		if (closeOnInteraction || closeOnAction) root.addEventListener("interaction", (e) => {
			let optionEl = e.detail.optionEl;
			if (!optionEl.hasAttribute("action")) {
				popoverable.setState(false);
				return;
			}
			setTimeout(() => {
				if (!optionEl.hasAttribute("data-os-loading") && !optionEl.hasAttribute("data-loading")) {
					popoverable.setState(false);
					return;
				}
				let observer = new MutationObserver(() => {
					requestAnimationFrame(() => {
						if (!root.querySelector("input[data-invalid]")) popoverable.setState(false);
					});
					observer.disconnect();
				});
				observer.observe(optionEl, {
					attributes: true,
					attributeFilter: ["data-loading", "data-os-loading"]
				});
			}, 10);
		});
		else if (closeOnSelect) selectable.onChange(() => {
			popoverable.setState(false);
		});
	}
	function trackActiveDescendant(input, activatable, selectable) {
		activatable.onChange(() => {
			let activeEl = activatable.getActive();
			activeEl ? setAttribute2(input, "aria-activedescendant", activeEl.id) : removeAttribute(input, "aria-activedescendant");
		});
		selectable.onChange((selectableItem) => {
			if (!selectableItem) return;
			let activeEl = selectableItem.el;
			activeEl && setAttribute2(input, "aria-activedescendant", activeEl.id);
		});
	}
	function handleAutocomplete(autocomplete, isStrict, root, input, selectable, popoverable, filterable) {
		if (!autocomplete) {
			setAttribute2(input, "autocomplete", "off");
			setAttribute2(input, "aria-autocomplete", "none");
			return;
		}
		let setInputValue = (value3) => {
			if (input.value === value3) return;
			input.value = value3;
			input.dispatchEvent(new Event("input", { bubbles: false }));
		};
		setAttribute2(input, "autocomplete", "off");
		setAttribute2(input, "aria-autocomplete", "list");
		if (input.value !== "" && selectable.isEmpty()) selectable.setState(input.value);
		let sync = () => {
			if (selectable.isNotEmpty()) requestAnimationFrame(() => {
				setInputValue(selectable.selectedTextValue());
				if (isStrict) setAttribute2(input, "data-selected", selectable.selectedTextValue());
			});
			else {
				setInputValue("");
				removeAttribute(input, "data-selected");
			}
		};
		queueMicrotask(() => {
			if (selectable.isEmpty() && input.value !== "") selectable.setState(input.value);
			selectable.onInitAndChange(() => sync());
		});
		root.addEventListener("interaction", (e) => {
			if (e.detail.optionEl._selectable?.isSelected()) sync();
		});
		if (isStrict) popoverable.onChange(() => {
			if (!popoverable.getState()) requestAnimationFrame(() => {
				setInputValue(input.getAttribute("data-selected") || "");
			});
		});
	}
	function preventScrollWhenPopoverIsOpen(root, popoverable, except = []) {
		let { lock, unlock } = lockScroll(popoverable.el, false, except);
		popoverable.onChange(() => {
			popoverable.getState() ? lock() : unlock();
		});
	}
	var UIPillbox = class extends UISelect$1 {
		mount() {
			this._disableable = new Disableable(this);
			queueMicrotask(() => {
				this._submittable.update(this._selectable.getState());
			});
			let input = this.input();
			let list = this.list();
			let multiple = this.hasAttribute("multiple");
			let triggerEl = this.querySelector("ui-pillbox-trigger");
			let listbox = this.querySelector("ui-options") || this;
			let listId = initListbox(listbox, "options", multiple);
			this._activatable = new ActivatableGroup(listbox, { filter: "data-hidden" });
			if (this.hasAttribute("filter") && this.getAttribute("filter") !== "manual") {
				this._filterable = new FilterableGroup(list);
				this._filterable.onChange(() => {
					this._activatable.clearActive();
					if (this._filterable.hasResults()) this._activatable.activateFirst();
				});
				this.addEventListener("close", () => {
					if (this._filterable) this._filterable.filter("");
				});
			}
			let popoverEl = this.querySelector("[popover]:not(ui-tooltip > [popover])");
			let popoverInputEl = popoverEl?.querySelector("input:not([type=\"hidden\"])");
			let inlineInputEl = this.querySelector("ui-selected input");
			if (!popoverEl) throw new Error("Popover element not found");
			if (popoverInputEl) {
				let trigger = triggerEl;
				let input2 = popoverInputEl;
				let popover = popoverEl;
				setAttribute2(trigger, "role", "button");
				setAttribute2(input2, "aria-autocomplete", "list");
				setAttribute2(input2, "aria-controls", listId);
				setAttribute2(input2, "role", "combobox");
				this._disableable.onInitAndChange((disabled) => {
					if (disabled) {
						trigger && setAttribute2(trigger, "disabled", "");
						input2 && setAttribute2(input2, "disabled", "");
					} else {
						trigger && removeAttribute(trigger, "disabled");
						input2 && removeAttribute(input2, "disabled");
					}
				});
				this._popoverable = new Popoverable(popover);
				this._anchorable = new Anchorable(popover, {
					reference: trigger,
					matchWidth: true,
					position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
					gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
					offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0,
					scrollY: false
				});
				trackActiveDescendant(input2, this._activatable, this._selectable);
				preventInputEventsFromBubblingToSelectRoot(input2);
				highlightInputContentsWhenFocused(input2);
				this._filterable && filterResultsByInput(list, input2, this._filterable);
				focusInputWhenPopoverOpens(input2, this._popoverable);
				initPopover(this, trigger, popover, this._popoverable, this._anchorable);
				preventScrollWhenPopoverIsOpen(this, this._popoverable, [input2]);
				linkExpandedStateToPopover(trigger, this._popoverable);
				handleInputClearing(this, input2, this._selectable, this._popoverable);
				controlPopoverWithKeyboard$1(trigger, this._popoverable, this._activatable, this._selectable);
				togglePopoverWithMouse(trigger, this._popoverable);
				controlPopoverWithEscapeKey$1(this, this._popoverable);
				handleKeyboardNavigation$1(input2, this._activatable);
				handleKeyboardSearchNavigation$1(trigger, this._activatable, this._popoverable);
				handleKeyboardSelection$1(this, input2, this._activatable);
				handleMouseSelection(this, this._activatable);
				controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
				handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
				openPopoverWithSpacebar(trigger);
			} else if (inlineInputEl) {
				let trigger = triggerEl;
				let input2 = inlineInputEl;
				let popover = popoverEl;
				setAttribute2(trigger, "role", "button");
				setAttribute2(input2, "aria-autocomplete", "list");
				setAttribute2(input2, "aria-controls", listId);
				setAttribute2(input2, "role", "combobox");
				this._disableable.onInitAndChange((disabled) => {
					if (disabled) {
						trigger && setAttribute2(trigger, "disabled", "");
						input2 && setAttribute2(input2, "disabled", "");
					} else {
						trigger && removeAttribute(trigger, "disabled");
						input2 && removeAttribute(input2, "disabled");
					}
				});
				this._popoverable = new Popoverable(popover, { triggers: [input2, trigger] });
				this._anchorable = new Anchorable(popover, {
					reference: trigger,
					matchWidth: true,
					position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
					gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
					offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
				});
				trackActiveDescendant(input2, this._activatable, this._selectable);
				preventInputEventsFromBubblingToSelectRoot(input2);
				highlightInputContentsWhenFocused(input2);
				this._filterable && filterResultsByInput(list, input2, this._filterable);
				focusInputWhenPopoverOpens(input2, this._popoverable);
				initPopover(this, trigger, popover, this._popoverable, this._anchorable);
				preventScrollWhenPopoverIsOpen(this, this._popoverable, [trigger]);
				linkExpandedStateToPopover(trigger, this._popoverable);
				handleInputClearing(this, input2, this._selectable, this._popoverable);
				controlPopoverWithInput(input2, this._popoverable);
				controlPopoverWithKeyboard$1(input2, this._popoverable, this._activatable, this._selectable);
				openPopoverWithMouse(trigger, this._popoverable, input2);
				controlPopoverWithEscapeKey$1(this, this._popoverable);
				handleKeyboardNavigation$1(input2, this._activatable);
				handleKeyboardSearchNavigation$1(trigger, this._activatable, this._popoverable);
				handleKeyboardSelection$1(this, input2, this._activatable);
				handleMouseSelection(this, this._activatable);
				controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
				handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
				focusInputOnSelection(this, this._selectable, input2);
				hideInputPlaceholderWhenNotEmpty(this._selectable, input2);
				deselectLastItemWithBackspace(this._selectable, input2);
				forwardClicksToInputWhenPopoverIsOpen(this._popoverable, trigger, input2);
			} else {
				let trigger = triggerEl;
				let popover = popoverEl;
				setAttribute2(trigger, "role", "combobox");
				setAttribute2(trigger, "aria-controls", listId);
				setAttribute2(trigger, "aria-autocomplete", "none");
				this._disableable.onInitAndChange((disabled) => {
					if (disabled) {
						trigger && setAttribute2(trigger, "disabled", "");
						input && setAttribute2(input, "disabled", "");
					} else {
						trigger && removeAttribute(trigger, "disabled");
						input && removeAttribute(input, "disabled");
					}
				});
				this._popoverable = new Popoverable(popover);
				this._anchorable = new Anchorable(popover, {
					reference: trigger,
					matchWidth: true,
					position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
					gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
					offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
				});
				initPopover(this, trigger, popover, this._popoverable, this._anchorable);
				preventScrollWhenPopoverIsOpen(this, this._popoverable);
				linkExpandedStateToPopover(trigger, this._popoverable);
				controlPopoverWithKeyboard$1(trigger, this._popoverable, this._activatable, this._selectable);
				togglePopoverWithMouse(trigger, this._popoverable);
				trackActiveDescendant(trigger, this._activatable, this._selectable);
				controlPopoverWithEscapeKey$1(this, this._popoverable);
				handleKeyboardNavigation$1(trigger, this._activatable);
				handleKeyboardSearchNavigation$1(trigger, this._activatable, this._popoverable);
				handleKeyboardSelection$1(this, trigger, this._activatable);
				handleMouseSelection(this, this._activatable);
				controlActivationWithPopover(this._popoverable, this._activatable, this._selectable);
				handlePopoverClosing(this, this._selectable, this._popoverable, multiple);
				openPopoverWithSpacebar(trigger);
			}
			new MutationObserver(() => {
				requestAnimationFrame(() => {
					if (!this._popoverable || this._popoverable.getState()) {
						let firstSelectedOption = this._selectable.selecteds().find((selected) => !selected.el._disableable.isDisabled())?.el;
						this._activatable.activateSelectedOrFirst(firstSelectedOption);
					} else this._activatable.clearActive();
				});
			}).observe(list, { childList: true });
		}
		list() {
			return this.querySelector("ui-options") || this;
		}
	};
	var UIPillboxTrigger = class extends UIElement {
		boot() {
			if (this.querySelector("input")) setAttribute2(this, "tabindex", "-1");
			else setAttribute2(this, "tabindex", "0");
			this._disableable = new Disableable(this);
			let disableClick = (e) => {
				e.preventDefault();
				e.stopImmediatePropagation();
			};
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) this.addEventListener("click", disableClick, true);
				else this.removeEventListener("click", disableClick, true);
			});
		}
		mount() {}
	};
	element("pillbox-trigger", UIPillboxTrigger);
	element("pillbox", UIPillbox);
	function openPopoverWithSpacebar(el) {
		on(el, "keydown", (e) => {
			if (e.key === " ") {
				e.preventDefault();
				e.stopImmediatePropagation();
				el.click();
			}
		});
	}
	function focusInputOnSelection(root, selectable, input) {
		selectable.onChange(() => {
			if (document.activeElement !== input && document.activeElement.closest("ui-pillbox") === root) input.focus();
		});
	}
	function hideInputPlaceholderWhenNotEmpty(selectable, input) {
		selectable.onInitAndChange(() => {
			if (selectable.noneAreSelected()) setAttribute2(input, "placeholder", input.getAttribute("data-placeholder"));
			else setAttribute2(input, "placeholder", "");
		});
	}
	function deselectLastItemWithBackspace(selectable, input) {
		on(input, "keydown", (e) => {
			if (input.value == "" && e.key === "Backspace") selectable.findByValue(selectable.getState().at(-1))?.deselect();
		});
	}
	function forwardClicksToInputWhenPopoverIsOpen(popoverable, trigger, input) {
		on(trigger, "click", (e) => {
			if (popoverable.getState()) {
				input.focus();
				e.preventDefault();
				e.stopImmediatePropagation();
			}
		});
	}
	var UIOptions$1 = class extends UIElement {
		boot() {
			setAttribute2(this, "tabindex", "-1");
			if (this.hasAttribute("popover")) this.addEventListener("lofi-close-popovers", () => {
				this.hidePopover();
			});
			setAttribute2(this, "role", "listbox");
		}
		mount() {
			let picker = this.closest("ui-autocomplete, ui-combobox, ui-select, ui-pillbox");
			let input = picker?.input();
			if (input) displayEmptyAndCreateOptions(this, input, picker._filterable);
		}
	};
	var UIOption = class extends UIElement {
		mount() {
			let target = this;
			this._disableable = new Disableable(target, { disableWithParent: false });
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) setAttribute2(target, "aria-disabled", "true");
				else removeAttribute(target, "aria-disabled");
			});
			assignId(target, "option");
			setAttribute2(target, "role", "option");
			this._filterable = new Filterable(target, {
				mirror: this,
				keep: !!this.closest("ui-option-empty, ui-empty") || this.getAttribute("filter") === "manual"
			});
			this._activatable = new Activatable(target);
			if (!this.hasAttribute("action")) this._selectable = new Selectable(target, {
				value: this._pendingValue ?? this.getValue(),
				label: this.getLabel(),
				selectedLabel: this.getSelectedLabel(),
				selectedInitially: this.hasAttribute("selected")
			});
			new MutationObserver((mutations) => {
				mutations.forEach((mutation) => {
					if (mutation.attributeName === "selected") {
						if (this.hasAttribute("selected")) this._selectable.setState(true);
						else this._selectable.setState(false);
					}
					if (mutation.attributeName === "value") {
						if (this._selectable) this._selectable.value = this.getValue();
					}
				});
			}).observe(this, { attributeFilter: ["selected", "value"] });
		}
		get value() {
			return this._selectable?.value ?? this._pendingValue ?? this.getValue();
		}
		set value(val) {
			if (!this._selectable) {
				this._pendingValue = val + "";
				return;
			}
			this._selectable.value = val + "";
		}
		get selected() {
			if (!this?._selectable) return false;
			return this._selectable.isSelected();
		}
		set selected(value3) {
			if (!this?._selectable) return false;
			this._selectable.setState(value3);
		}
		getSelectedLabel() {
			return this.getAttribute("selected-label");
		}
		getLabel() {
			return this.hasAttribute("label") ? this.getAttribute("label") : this.textContent.trim();
		}
		getValue() {
			return this.hasAttribute("value") ? this.getAttribute("value") : this.textContent.trim();
		}
	};
	var UIOptionCreate = class extends UIElement {
		mount() {
			this._activatable = new Activatable(this);
			this._filterable = new Filterable(this, { keep: true });
			this._disableable = new Disableable(this, { disableWithParent: false });
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) setAttribute2(this, "aria-disabled", "true");
				else removeAttribute(this, "aria-disabled");
			});
		}
	};
	var UIOptionEmpty = class extends UIElement {};
	var UIEmpty = class extends UIOptionEmpty {};
	element("option-create", UIOptionCreate);
	element("options", UIOptions$1);
	element("option", UIOption);
	element("option-empty", UIOptionEmpty);
	element("empty", UIEmpty);
	function displayEmptyAndCreateOptions(list, input, filterable) {
		let empty = Array.from(list.children).find((element2) => element2.matches("ui-option-empty, ui-empty"));
		let create = Array.from(list.children).find((element2) => element2.matches("ui-option-create"));
		let minLength = create?.hasAttribute("min-length") ? parseInt(create.getAttribute("min-length")) : void 0;
		setAttribute2(empty, "data-hidden", "");
		if (create) setAttribute2(create, "data-hidden", "");
		let isHidden = (el) => el.hasAttribute("data-hidden");
		let isUnique = (el) => el.textContent.toLowerCase().trim() !== input.value.toLowerCase().trim();
		let getItems = () => Array.from(list.querySelectorAll("ui-option")).filter((option) => option.closest("ui-options") === list);
		let showLoading = () => {
			if (empty.hasAttribute("when-loading")) {
				let emptyContent = empty.textContent;
				empty.textContent = empty.getAttribute("when-loading");
				empty.__hideLoading = () => {
					empty.textContent = emptyContent;
					empty.__hideLoading = void 0;
				};
			}
		};
		let revertDisable;
		let debounceEnable;
		let refresh = () => {
			clearTimeout(revertDisable);
			clearTimeout(debounceEnable);
			if (input.hasAttribute("data-os-loading") || input.hasAttribute("data-loading")) return;
			let items = getItems();
			let isEmpty = items.every((i) => isHidden(i));
			if (create) {
				if (minLength && input.value.trim().length < minLength) setAttribute2(create, "data-hidden", "");
				else if (items.every((i) => isUnique(i))) removeAttribute(create, "data-hidden");
				else setAttribute2(create, "data-hidden", "");
				if (isEmpty) create._activatable.activate(true);
				let enable = () => {
					removeAttribute(create, "disabled");
				};
				if (filterable) enable();
				else debounceEnable = setTimeout(enable, 150);
			}
			if (isEmpty && (!create || isHidden(create))) removeAttribute(empty, "data-hidden");
			else setAttribute2(empty, "data-hidden", "");
			empty.__hideLoading?.();
		};
		if (filterable) {
			filterable.onChange(() => refresh());
			if (create) on(input, "input", () => {
				if (minLength && input.value.trim().length < minLength) setAttribute2(create, "data-hidden", "");
			});
		} else {
			let observer = new MutationObserver(() => {
				requestAnimationFrame(() => refresh());
			});
			observer.observe(list, { childList: true });
			observer.observe(input, {
				attributes: true,
				attributeFilter: ["data-loading", "data-os-loading"]
			});
			if (create) on(input, "input", (e) => {
				clearTimeout(revertDisable);
				clearTimeout(debounceEnable);
				let isEmpty = getItems().every((i) => isHidden(i));
				if (isEmpty && input.value.trim() === "" && !isHidden(create)) {
					showLoading();
					removeAttribute(empty, "data-hidden");
					setAttribute2(create, "data-hidden", "");
				} else if (input.value.trim() === "" && !isHidden(create)) setAttribute2(create, "data-hidden", "");
				if (minLength && input.value.trim().length >= minLength) removeAttribute(create, "data-hidden");
				setAttribute2(create, "disabled", "");
				revertDisable = setTimeout(() => {
					removeAttribute(create, "disabled");
					if (isEmpty) create._activatable.activate();
				}, 200);
			});
		}
		queueMicrotask(() => refresh());
	}
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/calendar.js
	var DateValue = class _DateValue {
		constructor(year, month, day = 1) {
			this._date = new Date(Date.UTC(year, month - 1, day));
		}
		isBetween(min2, max2) {
			if (!min2 && !max2) return true;
			if (!min2) return this._date <= max2._date;
			if (!max2) return this._date >= min2._date;
			return this._date >= min2._date && this._date <= max2._date;
		}
		isSameDay(date) {
			if (!date) return false;
			return this._date.getUTCDate() === date._date.getUTCDate() && this._date.getUTCMonth() === date._date.getUTCMonth() && this._date.getUTCFullYear() === date._date.getUTCFullYear();
		}
		isBefore(date) {
			if (!date) return false;
			return this._date < date._date;
		}
		isAfter(date) {
			if (!date) return false;
			return this._date > date._date;
		}
		/** Immutable manipulators */
		incrementDays(days) {
			let copy = this.getCopy();
			copy._date.setUTCDate(copy._date.getUTCDate() + days);
			return copy;
		}
		addMonths(months) {
			let copy = this.getCopy();
			copy._date.setUTCMonth(copy._date.getUTCMonth() + months);
			return copy;
		}
		addDays(days) {
			let copy = this.getCopy();
			copy._date.setUTCDate(copy._date.getUTCDate() + days);
			return copy;
		}
		/** Getters */
		getYear() {
			return this._date.getUTCFullYear();
		}
		getMonth() {
			return this._date.getUTCMonth() + 1;
		}
		getPaddedMonth() {
			return String(this.getMonth()).padStart(2, "0");
		}
		getDay() {
			return this._date.getUTCDate();
		}
		getPaddedDay() {
			return String(this.getDay()).padStart(2, "0");
		}
		getDate() {
			return this._date;
		}
		getCopy() {
			return new _DateValue(this.getYear(), this.getMonth(), this.getDay());
		}
		getDayOfWeek() {
			return this._date.getUTCDay();
		}
		getDaysInMonth() {
			return new _DateValue(this.getYear(), this.getMonth() + 1, 0).getDay();
		}
		getFirstDayOfMonth() {
			return new _DateValue(this.getYear(), this.getMonth(), 1).getDayOfWeek();
		}
		getWeekNumber() {
			let firstDayOfYear = new Date(Date.UTC(this._date.getUTCFullYear(), 0, 1));
			let firstThursday = new Date(firstDayOfYear);
			firstThursday.setUTCDate(1 + (11 - firstDayOfYear.getUTCDay()) % 7);
			let targetThursday = new Date(this._date);
			targetThursday.setUTCDate(this._date.getUTCDate() + (11 - this._date.getUTCDay()) % 7);
			return Math.floor((targetThursday.getTime() - firstThursday.getTime()) / 6048e5) + 1;
		}
		/** Setters */
		setDay(day) {
			this._date.setUTCDate(day);
		}
		setMonth(month) {
			this._date.setUTCMonth(month - 1);
		}
		setYear(year) {
			this._date.setUTCFullYear(year);
		}
		/** Static factories: */
		static fromIsoDateString(isoString) {
			if (!isoString) return null;
			let [year, month, day] = isoString.split("T")[0]?.split("-")?.map(Number);
			return new _DateValue(year, month, day);
		}
		static fromLocalDate(date) {
			if (!date) return null;
			return new _DateValue(date.getUTCFullYear(), date.getUTCMonth() + 1, date.getUTCDate());
		}
		static fromDate(date) {
			if (!date) return null;
			return new _DateValue(date.getFullYear(), date.getMonth() + 1, date.getDate());
		}
		static fromParts(year, month, day) {
			return new _DateValue(year, month, day);
		}
		static today() {
			return _DateValue.fromDate(/* @__PURE__ */ new Date());
		}
		static firstDayOfMonth(date) {
			return new _DateValue(date.getYear(), date.getMonth(), 1);
		}
		/** Formats */
		toIsoDateString() {
			return [
				this.getYear(),
				this.getPaddedMonth(),
				this.getPaddedDay()
			].join("-");
		}
		toParts() {
			return [
				this.getYear(),
				this.getMonth(),
				this.getDay()
			];
		}
		toDate() {
			return new Date(this.getYear(), this.getMonth() - 1, this.getDay());
		}
		toFormattedString(locale, options) {
			return this.toDate().toLocaleDateString(locale, options);
		}
	};
	function renderMonth(monthEl, config, viewState, metadata) {
		let headingTemplate = monthEl.querySelector("template[name=\"heading\"]");
		let weekdayTemplate = monthEl.querySelector("template[name=\"weekday\"]");
		let weekTemplate = monthEl.querySelector("template[name=\"week\"]");
		headingTemplate && renderHeading(headingTemplate, config, viewState);
		weekdayTemplate && renderWeekdays(weekdayTemplate, config);
		weekTemplate && renderDates(weekTemplate, config, viewState, metadata);
	}
	function renderHeading(template, config, viewState) {
		renderTemplate2$1(template, (hydrate) => {
			return hydrate({ slots: { default: new Intl.DateTimeFormat(config.locale, {
				month: template.hasAttribute("display") ? template.getAttribute("display") : "long",
				year: "numeric",
				timeZone: "UTC"
			}).format(new DateValue(viewState.year, viewState.month).getDate()) } });
		});
	}
	function renderWeekdays(template, config) {
		let format = (date) => {
			if (template.hasAttribute("display")) return new Intl.DateTimeFormat(config.locale, { weekday: template.getAttribute("display") }).format(date);
			if ([
				"en",
				"es",
				"de",
				"fr",
				"it",
				"pt"
			].includes(config.locale.split("-")[0])) return new Intl.DateTimeFormat(config.locale, { weekday: "short" }).format(date).slice(0, 2);
			return new Intl.DateTimeFormat(config.locale, { weekday: "narrow" }).format(date);
		};
		let weekdays = Array.from({ length: 7 }, (_, idx) => {
			let adjustedIdx = (idx + config.startDay) % 7;
			let date = new Date(2024, 0, adjustedIdx + 7);
			return format(date);
		});
		renderTemplate2$1(template, (hydrate) => {
			return weekdays.map((weekday) => hydrate({ slots: { default: weekday } }));
		});
	}
	function renderDates(template, config, viewState, metadata) {
		let month = viewState.month;
		let year = viewState.year;
		let firstDay = (new DateValue(year, month).getFirstDayOfMonth() - config.startDay + 7) % 7;
		let totalDays = new DateValue(year, month).getDaysInMonth();
		let [prevYear, prevMonth] = new DateValue(year, month).addMonths(-1).toParts();
		let prevMonthDays = new DateValue(prevYear, prevMonth).getDaysInMonth();
		let [displayDates, actualDates] = [[], []];
		for (let i = 0; i < firstDay; i++) {
			displayDates.push(0);
			let prevDate = prevMonthDays - firstDay + i + 1;
			actualDates.push(new DateValue(prevYear, prevMonth, prevDate).toIsoDateString());
		}
		for (let i = 1; i <= totalDays; i++) {
			displayDates.push(i);
			actualDates.push(new DateValue(year, month, i).toIsoDateString());
		}
		let remainingDays = (7 - (firstDay + totalDays) % 7) % 7;
		let [nextYear, nextMonth] = new DateValue(year, month).addMonths(1).toParts();
		for (let i = 1; i <= remainingDays; i++) {
			displayDates.push(0);
			actualDates.push(new DateValue(nextYear, nextMonth, i).toIsoDateString());
		}
		if (config.fixedWeeks) {
			let currentWeeks = Math.ceil(displayDates.length / 7);
			let targetWeeks = config.fixedWeeks;
			let weeksDiff = targetWeeks - currentWeeks;
			if (weeksDiff > 0) for (let i = 0; i < weeksDiff * 7; i++) {
				displayDates.push(0);
				actualDates.push(DateValue.fromIsoDateString(actualDates[actualDates.length - 1]).addDays(1).toIsoDateString());
			}
			else if (weeksDiff < 0) {
				displayDates.length = targetWeeks * 7;
				actualDates.length = targetWeeks * 7;
			}
		}
		let splitIntoWeeks = (arr) => Array.from({ length: Math.ceil(arr.length / 7) }, (_, idx) => arr.slice(idx * 7, (idx + 1) * 7));
		let displayWeeks = splitIntoWeeks(displayDates);
		let actualWeeks = splitIntoWeeks(actualDates);
		renderTemplate2$1(template, (hydrate) => {
			return displayWeeks.map((week, weekIdx) => {
				let weekEl = hydrate();
				let actualWeekDates = actualWeeks[weekIdx];
				let numberTemplate = weekEl.querySelector("template[name=\"number\"]");
				if (numberTemplate) {
					let fourthDayIdx = (4 - config.startDay + 7) % 7;
					let weekNumber = DateValue.fromIsoDateString(actualWeekDates[fourthDayIdx]).getWeekNumber();
					renderTemplate2$1(numberTemplate, (hydrate2) => {
						return hydrate2({ slots: { default: weekNumber } });
					});
				}
				renderTemplate2$1(weekEl.querySelector("template[name=\"day\"]"), (hydrate2) => {
					return week.map((day, dayIdx) => {
						if (day === 0) return hydrate2({
							slots: { default: DateValue.fromIsoDateString(actualWeekDates[dayIdx]).getDay() },
							attrs: {
								"data-date": actualWeekDates[dayIdx],
								"data-outside": ""
							}
						});
						let date = DateValue.fromIsoDateString(actualWeekDates[dayIdx]);
						let dayEl = hydrate2({
							slots: { default: day },
							attrs: { "data-date": actualWeekDates[dayIdx] }
						});
						let subtext = metadata.subtext(date);
						let details = metadata.details(date);
						let variant = metadata.variant(date);
						if (![
							false,
							null,
							void 0
						].includes(variant)) dayEl.dataset.dateVariant = variant;
						if (![
							false,
							null,
							void 0
						].includes(subtext)) {
							let template2 = dayEl.querySelector("template[name=\"subtext\"]");
							template2 && renderTemplate2$1(template2, (hydrate3) => hydrate3({ slots: { default: subtext } }));
						}
						if (![
							false,
							null,
							void 0
						].includes(details)) {
							let template2 = dayEl.querySelector("template[name=\"details\"]");
							template2 && renderTemplate2$1(template2, (hydrate3) => hydrate3({ slots: { default: details } }));
						}
						return dayEl;
					});
				});
				return weekEl;
			});
		});
	}
	function updateMonth(el, disabled, config, viewState, selectable, validator) {
		let getFirstDateToMakeTabbable = (month, year) => {
			return selectable.lowerBound(() => {
				return DateValue.today().getMonth() === month && DateValue.today().getYear() === year ? DateValue.today() : new DateValue(year, month);
			});
		};
		let tabbable = getFirstDateToMakeTabbable(viewState.month, viewState.year);
		el.querySelectorAll("[data-date]").forEach((cell) => {
			setAttribute2(cell, "role", "gridcell");
			let button = cell.querySelector("button,ui-button");
			let date = dateFromCell(cell);
			if (!date) return;
			button ? setAttribute2(button, "aria-label", date.toFormattedString(config.locale, {
				day: "numeric",
				month: "long",
				year: "numeric",
				weekday: "long"
			})) : setAttribute2(cell, "aria-label", date.toFormattedString(config.locale, {
				day: "numeric",
				month: "long",
				year: "numeric",
				weekday: "long"
			}));
			if (!validator.isValid(date)) {
				setAttribute2(cell, "disabled", "");
				setAttribute2(cell, "aria-disabled", "true");
				button && setAttribute2(button, "disabled", "");
				validator.isUnavailable(date) && setAttribute2(cell, "data-unavailable", "");
			} else if (disabled) {
				setAttribute2(cell, "disabled", "");
				setAttribute2(cell, "aria-disabled", "true");
				button && setAttribute2(button, "disabled", "");
			} else {
				removeAttribute(cell, "disabled");
				removeAttribute(cell, "data-unavailable");
				removeAttribute(cell, "aria-disabled");
				button && removeAttribute(button, "disabled");
			}
			if (DateValue.today().isSameDay(date)) setAttribute2(cell, "data-today", "");
			else removeAttribute(cell, "data-today", "");
			selectable.attributes(cell, date);
			if (date.isSameDay(tabbable)) button && setAttribute2(button, "tabindex", "0");
			else button && setAttribute2(button, "tabindex", "-1");
		});
	}
	function renderTemplate2$1(template, callback) {
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
		let hydrated = callback(({ slots = {}, attrs = {} } = {}) => {
			let clone = template.content.cloneNode(true).firstElementChild;
			Object.entries(slots).forEach(([key, value3]) => {
				(key === "default" ? clone.querySelectorAll("slot:not([name])") : clone.querySelectorAll(`slot[name="${key}"]`)).forEach((i) => i.replaceWith(typeof value3 === "string" ? document.createTextNode(value3) : value3));
			});
			clone.querySelectorAll("slot").forEach((slot) => slot.remove());
			Object.entries(attrs).forEach(([key, value3]) => {
				clone.setAttribute(key, value3);
			});
			return clone;
		});
		hydrated = Array.isArray(hydrated) ? hydrated : [hydrated];
		hydrated.reverse().forEach((node) => {
			setAttribute2(node, "data-appended", "");
			template.after(node);
		});
		return { cleanup };
	}
	function dateFromCell(cell) {
		if (!cell) return null;
		if (!cell.hasAttribute("data-date")) return null;
		return DateValue.fromIsoDateString(cell.getAttribute("data-date"));
	}
	var ObservableTrigger2 = class {
		static SELECTION = 1;
		static ACTIVATION = 2;
		static VIEW_CHANGE = 3;
	};
	var Observable2 = class {
		constructor() {
			this.subscribers = [];
			this.subscriberTypes = /* @__PURE__ */ new WeakMap();
		}
		subscribe(type, callback) {
			let types = Array.isArray(type) ? type : [type];
			this.subscribers.push(callback);
			if (!this.subscriberTypes.has(callback)) this.subscriberTypes.set(callback, []);
			this.subscriberTypes.get(callback).push(...types);
		}
		notify(type) {
			let types = Array.isArray(type) ? type : [type];
			this.subscribers.forEach((callback) => {
				if (types.length && !this.subscriberTypes.get(callback)?.some((t) => types.includes(t))) return;
				callback();
			});
		}
	};
	var CalendarModes = class {
		static SINGLE = "single";
		static MULTIPLE = "multiple";
		static RANGE = "range";
	};
	var Selectable3 = class _Selectable {
		constructor(selection, config, observable) {
			this.config = config;
			this.observable = observable;
			this.selection = selection;
			this.active = null;
			this.displayResolver = (i) => i;
		}
		setDisplayResolver(resolver) {
			this.displayResolver = resolver;
		}
		select(date) {
			let previousSelection = this.selection;
			this.selection = this.selection.select(date, this.config);
			if (this.selection.shouldNotify(previousSelection)) this.observable.notify(ObservableTrigger2.SELECTION);
			else this.observable.notify(ObservableTrigger2.ACTIVATION);
		}
		activate(date) {
			let memo = this.active;
			if (!date) this.active = null;
			else this.active = date;
			this.selection.activate(date);
			if (memo !== this.active) this.observable.notify(ObservableTrigger2.ACTIVATION);
		}
		setValue(value3) {
			let previousSelection = this.selection;
			this.selection = this.createSelectionFromValue(value3, this.config);
			if (this.selection.shouldNotify(previousSelection)) this.observable.notify(ObservableTrigger2.SELECTION);
			else this.observable.notify(ObservableTrigger2.ACTIVATION);
		}
		getValue() {
			return this.selection.value();
		}
		isSelectable(date) {
			return this.selection.selectable(date, this.config);
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
			return this.displayResolver(this.selection.display(...params));
		}
		attributes(...params) {
			return this.selection.attributes(...params);
		}
		createSelectionFromValue(value3, config) {
			if (config.mode === CalendarModes.MULTIPLE) return new MultipleSelection2(value3 ? value3.map((i) => DateValue.fromIsoDateString(i)) : []);
			else if (config.mode === CalendarModes.RANGE) {
				let { start, end, preset } = value3 || {};
				if (preset && presets[preset] && preset !== "custom") return new PresetRangeSelection(preset, config);
				if (!start && !end) return new EmptyRangeSelection();
				if (!end) return new PartialRangeSelection(DateValue.fromIsoDateString(start));
				return new RangeSelection(DateValue.fromIsoDateString(start), DateValue.fromIsoDateString(end));
			} else {
				if (!value3) return new NoneSelection2();
				return new SingleSelection2(DateValue.fromIsoDateString(value3));
			}
		}
		static createFromValueStringAttribute(value3, config, observable) {
			let selection = _Selectable.createSelectionFromValueStringAttribute(value3, config);
			return new _Selectable(selection, config, observable);
		}
		static createSelectionFromValueStringAttribute(value3, config) {
			if (config.mode === CalendarModes.MULTIPLE) return new MultipleSelection2(value3 ? value3.split(",").map((i) => i.trim()).map((i) => DateValue.fromIsoDateString(i)) : []);
			else if (config.mode === CalendarModes.RANGE) {
				if (!value3) return new EmptyRangeSelection();
				let [start, end] = value3.split("/").map((i) => i.trim());
				if (!end && start && presets[start] && start !== "custom") return new PresetRangeSelection(start, config);
				start = DateValue.fromIsoDateString(start);
				if (!end) return new PartialRangeSelection(start);
				end = DateValue.fromIsoDateString(end);
				return new RangeSelection(start, end);
			} else {
				if (!value3) return new NoneSelection2();
				return new SingleSelection2(DateValue.fromIsoDateString(value3));
			}
		}
	};
	var DeferredSelectable = class {
		constructor(selectable, observable) {
			this.selectable = selectable;
			this.observable = observable;
			this._selection = this.selectable.selection;
			this.selectable.observable.subscribe(ObservableTrigger2.SELECTION, () => {
				this._blockSync() || this.sync();
			});
			this._blockSync = () => {};
		}
		blockSyncIf(condition) {
			this._blockSync = condition;
		}
		sync() {
			this._selection = this.selectable.selection;
			this.observable.notify(ObservableTrigger2.SELECTION);
		}
		setValue(value3) {
			return this.selectable.setValue(value3);
		}
		getValue() {
			return this._selection.value();
		}
		isSelectable(date) {
			return this._selection.selectable(date, this.config);
		}
		hasSelection() {
			return this._selection.hasSelection();
		}
		display(...params) {
			return this.selectable.displayResolver(this._selection.display(...params));
		}
	};
	var Selection2 = class {
		contains() {}
		shouldNotify(previousSelection) {
			return true;
		}
		hasSelection() {
			return false;
		}
		lowerBound(fallback) {
			return value2(fallback);
		}
		upperBound(fallback) {
			return this.lowerBound(fallback);
		}
		selectable(date, config) {
			return true;
		}
		display() {}
		value() {}
		activate() {}
		attributes(el, date) {
			if (this.contains(date)) {
				setAttribute2(el, "data-selected", "");
				setAttribute2(el, "aria-selected", "true");
			} else {
				removeAttribute(el, "data-selected");
				setAttribute2(el, "aria-selected", "false");
			}
		}
	};
	var NoneSelection2 = class _NoneSelection extends Selection2 {
		shouldNotify(selection) {
			return !(selection instanceof _NoneSelection);
		}
		hasSelection() {
			return false;
		}
		contains() {
			return false;
		}
		select(date) {
			return new SingleSelection2(date);
		}
		lowerBound(fallback) {
			return value2(fallback);
		}
		display(locale) {
			return "";
		}
		value() {
			return null;
		}
	};
	var SingleSelection2 = class _SingleSelection extends Selection2 {
		constructor(date) {
			super();
			this._date = date;
		}
		hasSelection() {
			return true;
		}
		shouldNotify(previousSelection) {
			if (previousSelection instanceof _SingleSelection && this._date.isSameDay(previousSelection._date)) return false;
			return true;
		}
		select(date) {
			if (this._date.isSameDay(date)) return new NoneSelection2();
			return new _SingleSelection(date);
		}
		contains(date) {
			return this._date.isSameDay(date);
		}
		lowerBound() {
			return this._date;
		}
		display(locale) {
			return this._date.toFormattedString(locale, {
				day: "numeric",
				month: "short",
				year: "numeric"
			});
		}
		value() {
			return this._date.toIsoDateString();
		}
	};
	var MultipleSelection2 = class _MultipleSelection extends Selection2 {
		constructor(dates) {
			super();
			this._dates = dates;
		}
		hasSelection() {
			return this._dates.length > 0;
		}
		shouldNotify(previousSelection) {
			if (previousSelection instanceof _MultipleSelection && this._dates.every((i) => previousSelection._dates.includes(i)) && previousSelection._dates.every((i) => this._dates.includes(i))) return false;
			return true;
		}
		select(date) {
			if (this._dates.some((i) => i.isSameDay(date))) return new _MultipleSelection(this._dates.filter((i) => !i.isSameDay(date)));
			return new _MultipleSelection([...this._dates, date]);
		}
		contains(date) {
			return this._dates.some((i) => i.isSameDay(date));
		}
		lowerBound(fallback) {
			return this._dates.sort((a, b) => a.isBefore(b) ? -1 : 1)[0] || value2(fallback);
		}
		upperBound(fallback) {
			let sortedDates = this._dates.sort((a, b) => a.isBefore(b) ? -1 : 1);
			return sortedDates[sortedDates.length - 1] || value2(fallback);
		}
		display(locale) {
			return this._dates.map((i) => i.toFormattedString(locale, {
				day: "numeric",
				month: "short",
				year: "numeric"
			})).join(", ");
		}
		value() {
			return this._dates.map((i) => i.toIsoDateString());
		}
	};
	var EmptyRangeSelection = class _EmptyRangeSelection extends Selection2 {
		constructor() {
			super();
		}
		hasSelection() {
			return false;
		}
		shouldNotify(previousSelection) {
			return !(previousSelection instanceof _EmptyRangeSelection || previousSelection instanceof PartialRangeSelection);
		}
		select(date) {
			return new PartialRangeSelection(date);
		}
		contains() {
			return false;
		}
		lowerBound(fallback) {
			return value2(fallback);
		}
		display(locale) {
			return "";
		}
		value() {
			return null;
		}
	};
	var PartialRangeSelection = class _PartialRangeSelection extends Selection2 {
		constructor(start) {
			super();
			this._start = start;
			this._endPreview = null;
		}
		hasSelection() {
			return false;
		}
		shouldNotify(previousSelection) {
			return false;
		}
		select(date, config) {
			if (date.isBefore(this._start)) return new _PartialRangeSelection(date);
			if (date.isSameDay(this._start) && config.minRange > 1) return new EmptyRangeSelection();
			return new RangeSelection(this._start, date);
		}
		activate(date) {
			this._endPreview = date;
		}
		selectable(date, config) {
			if (config.maxRange) {
				if (date.isBefore(this._start)) return false;
				if (this._start.addDays(config.maxRange - 1).isBefore(date)) return false;
			}
			if (config.minRange) {
				if (this._start.isSameDay(date)) return true;
				if (date.isBefore(this._start)) return false;
				if (this._start.addDays(config.minRange - 1).isAfter(date)) return false;
			}
			let nextUnavailable = config.unavailable.filter((d) => d.isAfter(this._start)).sort((a, b) => a.toDate() - b.toDate())[0];
			let prevUnavailable = config.unavailable.filter((d) => d.isBefore(this._start)).sort((a, b) => b.toDate() - a.toDate())[0];
			if (date.isAfter(this._start)) return !nextUnavailable || date.isBefore(nextUnavailable);
			if (date.isBefore(this._start)) return !prevUnavailable || date.isAfter(prevUnavailable);
			return true;
		}
		contains(date) {
			return date.isSameDay(this._start);
		}
		lowerBound() {
			return this._start;
		}
		display(locale) {
			return new Intl.DateTimeFormat(locale, {
				day: "numeric",
				month: "short",
				year: "numeric",
				timeZone: "UTC"
			}).format(this._start.getDate()) + " -";
		}
		value() {
			return null;
		}
		attributes(el, date) {
			if (this._endPreview && date.isBetween(this._start, this._endPreview)) {
				setAttribute2(el, "data-in-range", "");
				setAttribute2(el, "aria-selected", "true");
			} else {
				removeAttribute(el, "data-in-range");
				removeAttribute(el, "aria-selected");
			}
			date.isSameDay(this._start) ? setAttribute2(el, "data-selected", "") : removeAttribute(el, "data-selected");
			date.isSameDay(this._start) ? setAttribute2(el, "aria-selected", "true") : removeAttribute(el, "aria-selected");
			date.isSameDay(this._start) ? setAttribute2(el, "data-start", "") : removeAttribute(el, "data-start");
			date.isSameDay(this._endPreview) ? setAttribute2(el, "data-end-preview", "") : removeAttribute(el, "data-end-preview");
		}
	};
	var RangeSelection = class _RangeSelection extends Selection2 {
		constructor(start, end) {
			super();
			this._start = start;
			this._end = end;
		}
		hasSelection() {
			return true;
		}
		shouldNotify(previousSelection) {
			if (previousSelection instanceof _RangeSelection && this._start.isSameDay(previousSelection._start) && this._end.isSameDay(previousSelection._end)) return false;
			return true;
		}
		select(date) {
			return new PartialRangeSelection(date);
		}
		contains(date) {
			return date.isBetween(this._start, this._end);
		}
		lowerBound() {
			return this._start;
		}
		upperBound() {
			return this._end;
		}
		display(locale) {
			return new Intl.DateTimeFormat(locale, {
				day: "numeric",
				month: "short",
				year: "numeric",
				timeZone: "UTC"
			}).formatRange(this._start.getDate(), this._end.getDate()).replaceAll(" ", " ");
		}
		value() {
			return {
				start: this._start.toIsoDateString(),
				end: this._end.toIsoDateString()
			};
		}
		attributes(el, date) {
			date.isSameDay(this._start) || date.isSameDay(this._end) ? setAttribute2(el, "data-selected", "") : removeAttribute(el, "data-selected");
			date.isSameDay(this._start) ? setAttribute2(el, "data-start", "") : removeAttribute(el, "data-start");
			date.isSameDay(this._end) ? setAttribute2(el, "data-end", "") : removeAttribute(el, "data-end");
			if (this.contains(date)) {
				setAttribute2(el, "data-in-range", "");
				setAttribute2(el, "aria-selected", "true");
			} else {
				removeAttribute(el, "data-in-range");
				removeAttribute(el, "aria-selected");
			}
		}
	};
	var PresetRangeSelection = class _PresetRangeSelection extends Selection2 {
		constructor(preset, config) {
			super();
			this._preset = preset;
			this._config = config;
		}
		get _start() {
			let [start] = presets[this._preset](this._config);
			return this._config.min && start.isBefore(this._config.min) ? this._config.min : start;
		}
		get _end() {
			let [, end] = presets[this._preset](this._config);
			return this._config.max && end.isAfter(this._config.max) ? this._config.max : end;
		}
		hasSelection() {
			return true;
		}
		shouldNotify(previousSelection) {
			return !(previousSelection instanceof _PresetRangeSelection && this._preset === previousSelection._preset);
		}
		select(date) {
			return new PartialRangeSelection(date, this._config);
		}
		contains(date) {
			return date.isBetween(this._start, this._end);
		}
		lowerBound() {
			if (this._preset === "allTime") return DateValue.today();
			return this._start;
		}
		upperBound() {
			if (this._preset === "allTime") return DateValue.today();
			return DateValue.today();
		}
		display(locale) {
			return new Intl.DateTimeFormat(locale, {
				day: "numeric",
				month: "short",
				year: "numeric",
				timeZone: "UTC"
			}).formatRange(this._start.getDate(), this._end.getDate()).replaceAll(" ", " ");
		}
		value() {
			return {
				start: this._start.toIsoDateString(),
				end: this._end.toIsoDateString(),
				preset: this._preset
			};
		}
		attributes(el, date) {
			date.isSameDay(this._start) || date.isSameDay(this._end) ? setAttribute2(el, "data-selected", "") : removeAttribute(el, "data-selected");
			date.isSameDay(this._start) ? setAttribute2(el, "data-start", "") : removeAttribute(el, "data-start");
			date.isSameDay(this._end) ? setAttribute2(el, "data-end", "") : removeAttribute(el, "data-end");
			if (this.contains(date)) {
				setAttribute2(el, "data-in-range", "");
				setAttribute2(el, "aria-selected", "true");
			} else {
				removeAttribute(el, "data-in-range");
				removeAttribute(el, "aria-selected");
			}
		}
	};
	function value2(i = null) {
		return typeof i === "function" ? i() : i;
	}
	var presets = {
		today: () => {
			return [DateValue.today(), DateValue.today()];
		},
		yesterday: () => {
			let date = DateValue.today().incrementDays(-1);
			return [date, date];
		},
		thisWeek: (config) => {
			let start = DateValue.today().getCopy().incrementDays(config.startDay - DateValue.today().getDayOfWeek());
			return [start, start.incrementDays(6)];
		},
		lastWeek: (config) => {
			let start = DateValue.today().incrementDays(config.startDay - (DateValue.today().getDayOfWeek() + 7));
			return [start, start.incrementDays(6)];
		},
		last7Days: () => {
			let end = DateValue.today();
			return [DateValue.today().incrementDays(-6), end];
		},
		thisMonth: () => {
			return [DateValue.fromParts(DateValue.today().getYear(), DateValue.today().getMonth(), 1), DateValue.fromParts(DateValue.today().getYear(), DateValue.today().getMonth(), DateValue.today().getDaysInMonth())];
		},
		lastMonth: () => {
			let month = DateValue.today().getMonth() - 1;
			let year = DateValue.today().getYear();
			if (month < 1) {
				month += 12;
				year--;
			}
			let start = DateValue.fromParts(year, month, 1);
			return [start, DateValue.fromParts(year, month, start.getDaysInMonth())];
		},
		thisQuarter: () => {
			let startMonth = Math.floor((DateValue.today().getMonth() - 1) / 3) * 3 + 1;
			let endMonth = startMonth + 2;
			let start = DateValue.fromParts(DateValue.today().getYear(), startMonth, 1);
			let endMonthStart = DateValue.fromParts(DateValue.today().getYear(), endMonth, 1);
			return [start, DateValue.fromParts(DateValue.today().getYear(), endMonth, endMonthStart.getDaysInMonth())];
		},
		lastQuarter: () => {
			let startMonth = Math.floor((DateValue.today().getMonth() - 4) / 3) * 3 + 1;
			let endMonth = startMonth + 2;
			let year = DateValue.today().getYear();
			if (startMonth < 1) {
				startMonth += 12;
				endMonth += 12;
				year--;
			}
			let start = DateValue.fromParts(year, startMonth, 1);
			let endMonthStart = DateValue.fromParts(year, endMonth, 1);
			return [start, DateValue.fromParts(year, endMonth, endMonthStart.getDaysInMonth())];
		},
		thisYear: () => {
			return [DateValue.fromParts(DateValue.today().getYear(), 1, 1), DateValue.fromParts(DateValue.today().getYear(), 12, 31)];
		},
		lastYear: () => {
			let year = DateValue.today().getYear() - 1;
			return [DateValue.fromParts(year, 1, 1), DateValue.fromParts(year, 12, 31)];
		},
		last14Days: () => {
			let end = DateValue.today();
			return [DateValue.today().incrementDays(-13), end];
		},
		last30Days: () => {
			let end = DateValue.today();
			return [DateValue.today().incrementDays(-29), end];
		},
		last3Months: () => {
			let end = DateValue.today();
			let day = DateValue.today().getDay() + 1;
			let month = DateValue.today().getMonth() - 3;
			let year = DateValue.today().getYear();
			if (month < 1) {
				month += 12;
				year--;
			}
			return [DateValue.fromParts(year, month, day), end];
		},
		last6Months: () => {
			let end = DateValue.today();
			let day = DateValue.today().getDay() + 1;
			let month = DateValue.today().getMonth() - 6;
			let year = DateValue.today().getYear();
			if (month < 1) {
				month += 12;
				year--;
			}
			return [DateValue.fromParts(year, month, day), end];
		},
		yearToDate: () => {
			return [DateValue.fromParts(DateValue.today().getYear(), 1, 1), DateValue.today()];
		},
		tomorrow: () => {
			let date = DateValue.today().incrementDays(1);
			return [date, date];
		},
		nextWeek: (config) => {
			let start = DateValue.today().incrementDays(7 + config.startDay - DateValue.today().getDayOfWeek());
			return [start, start.incrementDays(6)];
		},
		next7Days: () => {
			return [DateValue.today(), DateValue.today().incrementDays(6)];
		},
		nextMonth: () => {
			let month = DateValue.today().getMonth() + 1;
			let year = DateValue.today().getYear();
			if (month > 12) {
				month -= 12;
				year++;
			}
			let start = DateValue.fromParts(year, month, 1);
			return [start, DateValue.fromParts(year, month, start.getDaysInMonth())];
		},
		nextQuarter: () => {
			let startMonth = (Math.floor((DateValue.today().getMonth() - 1) / 3) + 1) * 3 + 1;
			let year = DateValue.today().getYear();
			if (startMonth > 12) {
				startMonth -= 12;
				year++;
			}
			let endMonth = startMonth + 2;
			if (endMonth > 12) {
				endMonth -= 12;
				year++;
			}
			let start = DateValue.fromParts(year, startMonth, 1);
			let endMonthStart = DateValue.fromParts(year, endMonth, 1);
			return [start, DateValue.fromParts(year, endMonth, endMonthStart.getDaysInMonth())];
		},
		nextYear: () => {
			let year = DateValue.today().getYear() + 1;
			return [DateValue.fromParts(year, 1, 1), DateValue.fromParts(year, 12, 31)];
		},
		next14Days: () => {
			return [DateValue.today(), DateValue.today().incrementDays(13)];
		},
		next30Days: () => {
			return [DateValue.today(), DateValue.today().incrementDays(29)];
		},
		next3Months: () => {
			let start = DateValue.today();
			let day = DateValue.today().getDay();
			let month = DateValue.today().getMonth() + 3;
			let year = DateValue.today().getYear();
			if (month > 12) {
				month -= 12;
				year++;
			}
			return [start, DateValue.fromParts(year, month, day).incrementDays(-1)];
		},
		next6Months: () => {
			let start = DateValue.today();
			let day = DateValue.today().getDay();
			let month = DateValue.today().getMonth() + 6;
			let year = DateValue.today().getYear();
			if (month > 12) {
				month -= 12;
				year++;
			}
			return [start, DateValue.fromParts(year, month, day).incrementDays(-1)];
		},
		allTime: (config) => {
			if (!config.min) throw new Error("Min date is required for allTime preset");
			return [config.min, DateValue.today()];
		}
	};
	var CalendarViewState = class {
		constructor(month, year, config, observable) {
			this.month = month;
			this.year = year;
			this.config = config;
			this.observable = observable;
		}
		generateOffsetState(offset3) {
			return new CalendarOffsetViewState(this, offset3);
		}
		canNavigatePrevious() {
			let [prevYear, prevMonth] = new DateValue(this.year, this.month).addMonths(-1).toParts();
			return !(this.config.min && DateValue.fromParts(prevYear, prevMonth).isBefore(DateValue.firstDayOfMonth(this.config.min)));
		}
		canNavigateNext() {
			let [nextYear, nextMonth] = new DateValue(this.year, this.month).addMonths(this.config.months).toParts();
			return !(this.config.max && DateValue.fromParts(nextYear, nextMonth).isAfter(this.config.max));
		}
		nextMonth() {
			if (!this.canNavigateNext()) return;
			this.adjustMonth(1);
		}
		previousMonth() {
			this.adjustMonth(-1);
		}
		setMonth(month) {
			if (!month) return;
			if (this.isWithinMinAndMax(month, this.year)) this.month = month;
			this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
		}
		setYear(year) {
			if (!year) return;
			let month = this.month;
			if (!this.isWithinMinAndMax(month, year)) {
				if (this.config.min && DateValue.fromParts(year, month).isBefore(DateValue.firstDayOfMonth(this.config.min))) month = this.config.min.getMonth();
				else if (this.config.max && DateValue.fromParts(year, month).isAfter(this.config.max)) month = this.config.max.getMonth();
			}
			if (this.isWithinMinAndMax(month, year)) {
				this.month = month;
				this.year = year;
			}
			this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
		}
		setDate(date) {
			if (!date) return;
			if (this.isWithinMinAndMax(date.getMonth(), date.getYear())) {
				this.month = date.getMonth();
				this.year = date.getYear();
			}
			this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
		}
		isWithinMinAndMax(month, year) {
			if (this.config.min && DateValue.fromParts(year, month).isBefore(DateValue.firstDayOfMonth(this.config.min))) return false;
			if (this.config.max && DateValue.fromParts(year, month).isAfter(this.config.max)) return false;
			return true;
		}
		adjustMonth(delta) {
			let [nextYear, nextMonth] = new DateValue(this.year, this.month).addMonths(delta).toParts();
			if (this.config.min && DateValue.fromParts(nextYear, nextMonth).isBefore(DateValue.firstDayOfMonth(this.config.min))) return;
			if (this.config.max && DateValue.fromParts(nextYear, nextMonth).isAfter(this.config.max)) return;
			[this.month, this.year] = [nextMonth, nextYear];
			this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
		}
	};
	var CalendarOffsetViewState = class {
		constructor(viewState, offset3 = 0) {
			this.offset = offset3;
			this.viewState = viewState;
		}
		get month() {
			let [, newMonth] = new DateValue(this.viewState.year, this.viewState.month).addMonths(this.offset).toParts();
			return newMonth;
		}
		get year() {
			let [newYear] = new DateValue(this.viewState.year, this.viewState.month).addMonths(this.offset).toParts();
			return newYear;
		}
	};
	var Validator = class {
		constructor(selectable, metadata, config) {
			this.min = config.min;
			this.max = config.max;
			this.unavailable = config.unavailable;
			this.selectable = selectable;
			this.metadata = metadata;
		}
		isBetweenMinMax(date) {
			return date.isBetween(this.min, this.max);
		}
		isUnavailable(date) {
			return this.unavailable.some((i) => i.isSameDay(date)) || this.metadata.unavailable(date);
		}
		isBlocked(date) {
			return !this.selectable.isSelectable(date);
		}
		isValid(date) {
			return this.isBetweenMinMax(date) && !this.isUnavailable(date) && !this.isBlocked(date);
		}
	};
	var DateMetadata = class {
		constructor(observable) {
			this.metadata = {};
			this.observable = observable;
		}
		resetFromJSON(json) {
			this.reset(JSON.parse(json));
		}
		appendFromJSON(json) {
			this.append(JSON.parse(json));
		}
		append(object) {
			this.metadata = {
				...this.metadata,
				...object
			};
			this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
		}
		reset(object) {
			this.metadata = object;
			this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
		}
		get(date) {
			return this.metadata[date.toIsoDateString()];
		}
		has(date) {
			return this.metadata[date.toIsoDateString()] !== void 0;
		}
		subtext(date) {
			return this.metadata[date.toIsoDateString()]?.subtext;
		}
		details(date) {
			return this.metadata[date.toIsoDateString()]?.details;
		}
		variant(date) {
			return this.metadata[date.toIsoDateString()]?.variant;
		}
		unavailable(date) {
			return !!this.metadata[date.toIsoDateString()]?.unavailable;
		}
	};
	var UICalendar = class extends UIElement {
		boot() {
			let elDrivingConfig = this.closest("ui-date-picker") || this;
			this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
			let [months, onMonthsChange] = responsiveMonthsAttributeValue(this, elDrivingConfig);
			this.observable = new Observable2();
			let locale = elDrivingConfig.hasAttribute("locale") ? elDrivingConfig.getAttribute("locale") : getLocale();
			this.config = {
				mode: elDrivingConfig.getAttribute("mode") === "range" ? CalendarModes.RANGE : elDrivingConfig.hasAttribute("multiple") ? CalendarModes.MULTIPLE : CalendarModes.SINGLE,
				months,
				min: this.getMinDate(elDrivingConfig),
				max: this.getMaxDate(elDrivingConfig),
				maxRange: elDrivingConfig.hasAttribute("max-range") ? parseInt(elDrivingConfig.getAttribute("max-range")) : null,
				minRange: elDrivingConfig.hasAttribute("min-range") ? parseInt(elDrivingConfig.getAttribute("min-range")) : null,
				startDay: elDrivingConfig.hasAttribute("start-day") ? parseInt(elDrivingConfig.getAttribute("start-day")) : (new Intl.Locale(locale).getWeekInfo?.()?.firstDay ?? new Intl.Locale(locale).weekInfo?.firstDay ?? 7) % 7,
				unavailable: this.getUnavailableDates(elDrivingConfig),
				locale,
				fixedWeeks: elDrivingConfig.hasAttribute("fixed-weeks") ? 6 : null
			};
			onMonthsChange((months2) => {
				this.config.months = months2;
				this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
			});
			this.selectable = Selectable3.createFromValueStringAttribute(elDrivingConfig.getAttribute("value"), this.config, this.observable);
			this.metadata = new DateMetadata(this.observable);
			this.validator = new Validator(this.selectable, this.metadata, this.config);
			let openToDate = elDrivingConfig.hasAttribute("open-to") && (elDrivingConfig.hasAttribute("force-open-to") || this.selectable.lowerBound() === null) ? this.getOpenTo(elDrivingConfig) : this.selectable.lowerBound(() => DateValue.today());
			this.viewState = new CalendarViewState(openToDate.getMonth(), openToDate.getYear(), this.config, this.observable);
			this._disableable = new Disableable(this, { disableWithParent: false });
			if (!this.hasAttribute("static")) {
				let { enable: enableListeners, disable: disableListeners } = initializeEventListeners2(this, this.selectable, this.viewState, this.validator);
				this._disableable.onInitAndChange((disabled) => {
					disabled ? disableListeners() : enableListeners();
				});
			}
			this._controllable = new Controllable(this, { bubbles: true });
			this._submittable = new Submittable(this, {
				name: this.getAttribute("name"),
				value: this.selectable.getValue()
			});
			let detangled = detangle();
			this._controllable.initial((initial) => initial && this.selectable.setValue(initial));
			this._controllable.getter(() => this.selectable.getValue());
			this._controllable.setter(detangled((value3) => {
				this.selectable.setValue(value3);
			}));
			this.observable.subscribe(ObservableTrigger2.SELECTION, detangled(() => {
				this.dispatchEvent(new Event("select", {
					bubbles: false,
					cancelable: true
				}));
				this._controllable.dispatch();
			}));
			this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
				this._submittable.update(this.selectable.getValue());
			});
			this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
				this.anchorSelection(elDrivingConfig);
			});
			this.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
				this.dispatchEvent(new Event("navigate", {
					bubbles: false,
					cancelable: true
				}));
			});
			queueMicrotask(() => {
				this.closest("ui-date-picker")?.bootWithCalendar(this);
			});
			new MutationObserver((mutations) => {
				mutations.forEach((mutation) => {
					if (![
						"unavailable",
						"max",
						"min"
					].includes(mutation.attributeName)) return;
					this.config.unavailable = this.getUnavailableDates(elDrivingConfig);
					this.config.min = this.getMinDate(elDrivingConfig);
					this.config.max = this.getMaxDate(elDrivingConfig);
					this.validator.unavailable = this.config.unavailable;
					this.validator.min = this.config.min;
					this.validator.max = this.config.max;
					this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
				});
			}).observe(elDrivingConfig, { attributes: true });
		}
		getOpenTo(el) {
			if (!el.hasAttribute("open-to")) return;
			let openTo = el.getAttribute("open-to");
			openTo = openTo === "today" ? DateValue.today() : DateValue.fromIsoDateString(openTo);
			if (!this.validator.isBetweenMinMax(openTo)) return this.config.min;
			return openTo;
		}
		navigateNext() {
			this.viewState.nextMonth();
		}
		navigatePrevious() {
			this.viewState.previousMonth();
		}
		anchorSelection(elDrivingConfig) {
			let focusedEl = document.activeElement;
			if (this.contains(focusedEl) && focusedEl.closest("[data-date]") !== null) return;
			if (elDrivingConfig.hasAttribute("force-open-to")) return;
			this.viewState.setDate(this.selectable.lowerBound(DateValue.today()));
		}
		appendMetadata(object) {
			this.metadata.append(object);
		}
		resetMetadata(object = {}) {
			this.metadata.reset(object);
		}
		visibleDateRange() {
			return [DateValue.fromParts(this.viewState.year, this.viewState.month, 1).toIsoDateString(), DateValue.fromParts(this.viewState.year, this.viewState.month + 1, 0).toIsoDateString()];
		}
		getUnavailableDates(el) {
			return el.hasAttribute("unavailable") ? el.getAttribute("unavailable").split(",").map((i) => DateValue.fromIsoDateString(i.trim())) : [];
		}
		getMinDate(el) {
			return el.hasAttribute("min") ? el.getAttribute("min") === "today" ? DateValue.today() : DateValue.fromIsoDateString(el.getAttribute("min")) : null;
		}
		getMaxDate(el) {
			return el.hasAttribute("max") ? el.getAttribute("max") === "today" ? DateValue.today() : DateValue.fromIsoDateString(el.getAttribute("max")) : null;
		}
	};
	var UICalendarPrevious = class extends UIElement {
		boot() {
			let calendar = this.closest("ui-calendar");
			this._disableable = calendar._disableable;
			initFauxButton(this, () => this._disableable.isDisabled(), () => calendar.navigatePrevious());
			let setDisabled = () => {
				if (calendar.viewState.canNavigatePrevious() && !this._disableable.isDisabled()) removeAttribute(this, "disabled");
				else setAttribute2(this, "disabled", "");
			};
			calendar.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, setDisabled);
			this._disableable.onInitAndChange(() => {
				setDisabled();
			});
		}
	};
	var UICalendarNext = class extends UIElement {
		boot() {
			let calendar = this.closest("ui-calendar");
			this._disableable = calendar._disableable;
			initFauxButton(this, () => this._disableable.isDisabled(), () => calendar.navigateNext());
			let setDisabled = () => {
				if (calendar.viewState.canNavigateNext() && !this._disableable.isDisabled()) removeAttribute(this, "disabled");
				else setAttribute2(this, "disabled", "");
			};
			calendar.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, setDisabled);
			this._disableable.onInitAndChange(() => {
				setDisabled();
			});
		}
	};
	var UICalendarMonth = class extends UIElement {
		boot() {
			let calendar = this.closest("ui-calendar");
			this._disableable = calendar._disableable;
			this.offset = this.hasAttribute("offset") ? parseInt(this.getAttribute("offset")) : 0;
			this.config = calendar.config;
			this.viewState = calendar.viewState;
			this.offsetState = this.viewState.generateOffsetState(this.offset);
			this.validator = calendar.validator;
			let select = this.querySelector("select");
			let display = this.hasAttribute("display") ? this.getAttribute("display") : "long";
			if (select) {
				this.renderSelectOptions(select, display);
				select.value = this.viewState.month;
				select.addEventListener("change", this._disableable.enabled(() => {
					this.viewState.setMonth(parseInt(select.value));
				}));
				calendar.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
					this.renderSelectOptions(select, display);
					select.value = this.viewState.month;
				});
				this._disableable.onInitAndChange((disabled) => {
					if (disabled) setAttribute2(select, "disabled", "");
					else removeAttribute(select, "disabled");
				});
			} else {
				let update = () => this.textContent = new Intl.DateTimeFormat(this.config.locale, {
					month: this.hasAttribute("display") ? this.getAttribute("display") : "long",
					timeZone: "UTC"
				}).format(new DateValue(this.offsetState.year, this.offsetState.month).getDate());
				update();
				this.viewState.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => update());
			}
		}
		renderSelectOptions(select, display) {
			let renderableMonths = Array.from({ length: 12 }, (_, idx) => idx + 1).map((month) => {
				let firstDayOfMonth = new DateValue(this.viewState.year, month);
				let lastDayOfMonth = new DateValue(this.viewState.year, month + 1, 0);
				if (!this.validator.isBetweenMinMax(firstDayOfMonth) && !this.validator.isBetweenMinMax(lastDayOfMonth)) return null;
				return {
					month,
					label: new Intl.DateTimeFormat(this.config.locale, {
						month: display,
						timeZone: "UTC"
					}).format(new DateValue(2024, month).getDate())
				};
			}).filter(Boolean);
			renderTemplate2$1(select.querySelector("template"), (hydrate) => {
				if (renderableMonths.length === 0) {
					let month = this.viewState.month;
					let option = hydrate({ slots: { default: new Intl.DateTimeFormat(this.config.locale, {
						month: display,
						timeZone: "UTC"
					}).format(new DateValue(2024, month).getDate()) } });
					option.setAttribute("value", month);
					return option;
				}
				return renderableMonths.map((month) => {
					let option = hydrate({ slots: { default: month.label } });
					option.setAttribute("value", month.month);
					return option;
				});
			});
		}
	};
	var UICalendarYear = class extends UIElement {
		boot() {
			let calendar = this.closest("ui-calendar");
			this._disableable = calendar._disableable;
			this.offset = this.hasAttribute("offset") ? parseInt(this.getAttribute("offset")) : 0;
			this.config = calendar.config;
			this.viewState = calendar.viewState;
			this.offsetState = this.viewState.generateOffsetState(this.offset);
			this.numberOfPastYears = 100;
			this.numberOfFutureYears = 10;
			this.validator = calendar.validator;
			let select = this.querySelector("select");
			if (select) {
				if (this.config.min) this.numberOfPastYears = this.viewState.year - this.config.min.getYear();
				if (this.config.max) this.numberOfFutureYears = this.config.max.getYear() - this.viewState.year;
				this.renderSelectOptions(select);
				select.value = this.viewState.year;
				select.addEventListener("change", this._disableable.enabled(() => {
					this.viewState.setYear(parseInt(select.value));
				}));
				calendar.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
					if (this.yearIsLessThanMinimum(this.viewState.year)) {
						this.numberOfPastYears += 100;
						this.renderSelectOptions(select);
					}
					if (this.yearIsGreaterThanMaximum(this.viewState.year)) {
						this.numberOfFutureYears += 10;
						this.renderSelectOptions(select);
					}
					select.value = this.viewState.year;
				});
				this._disableable.onInitAndChange((disabled) => {
					if (disabled) setAttribute2(select, "disabled", "");
					else removeAttribute(select, "disabled");
				});
			} else {
				let update = () => this.textContent = new Intl.DateTimeFormat(this.config.locale, {
					year: this.hasAttribute("display") ? this.getAttribute("display") : "numeric",
					timeZone: "UTC"
				}).format(new DateValue(this.offsetState.year, this.offsetState.month).getDate());
				update();
				this.viewState.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => update());
			}
		}
		yearIsLessThanMinimum(year) {
			return year < this.minimumYear();
		}
		yearIsGreaterThanMaximum(year) {
			return year > this.maximumYear();
		}
		minimumYear() {
			return (/* @__PURE__ */ new Date()).getFullYear() - this.numberOfPastYears;
		}
		maximumYear() {
			return (/* @__PURE__ */ new Date()).getFullYear() + this.numberOfFutureYears;
		}
		renderSelectOptions(select) {
			let currentYear = (/* @__PURE__ */ new Date()).getFullYear();
			let renderableYears = Array.from({ length: this.numberOfPastYears + this.numberOfFutureYears + 1 }, (_, i) => currentYear - this.numberOfPastYears + i).map((year) => {
				let yearStart = new DateValue(year, 1);
				let yearEnd = new DateValue(year, 12, 31);
				if (this.config.min && yearEnd.isBefore(this.config.min)) return null;
				if (this.config.max && yearStart.isAfter(this.config.max)) return null;
				return year;
			}).filter(Boolean);
			renderTemplate2$1(select.querySelector("template"), (hydrate) => {
				if (renderableYears.length === 0) {
					let year = this.viewState.year;
					return hydrate({ slots: { default: year } });
				}
				return renderableYears.map((year) => hydrate({ slots: { default: year } }));
			});
		}
	};
	var UICalendarMonths = class extends UIElement {
		boot() {
			let calendar = this.closest("ui-calendar");
			this._disableable = calendar._disableable;
			this.offset = this.hasAttribute("offset") ? parseInt(this.getAttribute("offset")) : 0;
			this.config = calendar.config;
			this.selectable = calendar.selectable;
			this.observable = calendar.observable;
			this.metadata = calendar.metadata;
			this.validator = calendar.validator;
			this.viewState = calendar.viewState;
			this.offsetState = this.viewState.generateOffsetState(this.offset);
			this.monthEls = [];
			this.renderMonths();
			let render = () => this.monthEls.forEach((month) => renderMonth(month, this.config, month.offsetState, this.metadata));
			let update = () => this.monthEls.forEach((month) => updateMonth(month, this._disableable.isDisabled(), this.config, month.offsetState, this.selectable, this.validator));
			render();
			update();
			let months = this.config.months;
			this.viewState.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
				if (months !== this.config.months) {
					months = this.config.months;
					this.renderMonths();
				}
				render();
				update();
			});
			this.observable.subscribe([ObservableTrigger2.SELECTION, ObservableTrigger2.ACTIVATION], () => {
				update();
			});
			this._disableable.onChange(() => update());
		}
		renderMonths() {
			renderTemplate2$1(this.querySelector("template:not([name]), template[name=\"month\"]"), (hydrate) => {
				let monthOffsetTemplate = Array.from({ length: this.config.months }).map((_, idx) => idx);
				this.monthEls = monthOffsetTemplate.map((offset3) => {
					let offsetState = this.viewState.generateOffsetState(offset3);
					let monthEl = hydrate({ attrs: { "data-month": "" } });
					monthEl.offsetState = offsetState;
					let head = monthEl.querySelector("thead")?.set;
					setAttribute2(monthEl, "role", "grid");
					head && setAttribute2(head, "aria-hidden", "true");
					return monthEl;
				});
				return this.monthEls;
			});
		}
	};
	var UICalendarToday = class extends UIElement {
		boot() {
			let calendar = this.closest("ui-calendar, ui-date-picker");
			this._disableable = calendar._disableable;
			this.behavior = this.hasAttribute("behavior") ? this.getAttribute("behavior") : "auto";
			if (calendar.hasAttribute("static")) this.behavior = "none";
			initFauxButton(this, () => this._disableable.isDisabled(), () => this.navigateAndSelectToday());
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) setAttribute2(this, "disabled", "");
				else removeAttribute(this, "disabled");
			});
			let template = this.querySelector("template");
			template && renderTemplate2$1(template, (hydrate) => {
				return hydrate({ slots: { default: DateValue.today().getDay() } });
			});
		}
		navigateAndSelectToday() {
			let calendar = this.closest("ui-calendar, ui-date-picker");
			let isViewingCurrentMonth = () => {
				let viewState = calendar.viewState;
				let today = DateValue.today();
				return viewState.year === today.getYear() && viewState.month === today.getMonth();
			};
			if (this.behavior === "select" || this.behavior === "auto" && isViewingCurrentMonth()) {
				if (calendar.config.mode === CalendarModes.RANGE) calendar.selectable.setValue({
					start: DateValue.today().toIsoDateString(),
					end: DateValue.today().toIsoDateString()
				});
				else if (calendar.config.mode === CalendarModes.MULTIPLE) calendar.selectable.setValue([DateValue.today().toIsoDateString()]);
				else calendar.selectable.setValue(DateValue.today().toIsoDateString());
			}
			calendar.viewState.setMonth(DateValue.today().getMonth());
			calendar.viewState.setYear(DateValue.today().getYear());
		}
	};
	var UICalendarPresets = class extends UIElement {
		boot() {
			let calendar = this.closest("ui-calendar");
			this._disableable = calendar._disableable;
			this.offset = this.hasAttribute("offset") ? parseInt(this.getAttribute("offset")) : 0;
			this.selectable = calendar.selectable;
			this.observable = calendar.observable;
			this.config = calendar.config;
			this.viewState = calendar.viewState;
			this.offsetState = this.viewState.generateOffsetState(this.offset);
			if (this.config.mode !== CalendarModes.RANGE) throw "Presets can only be used with range calendars";
			let selectable = this.firstElementChild;
			if (selectable) {
				let getLabelFromPreset = (preset) => {
					if (selectable._selectable instanceof SelectableGroup) {
						let option = selectable._selectable.findByValue(preset);
						if (!option) return null;
						return option?.el?.textContent;
					} else if (selectable instanceof HTMLSelectElement) return selectable.querySelector(`option[value="${preset}"]`)?.textContent;
					return null;
				};
				this.selectable.setDisplayResolver((i) => {
					let preset = this.selectable.getValue()?.preset || "";
					if (preset) {
						let label = getLabelFromPreset(preset);
						if (label) return label;
					}
					return i;
				});
				selectable.value = this.selectable.getValue()?.preset || "custom";
				selectable.addEventListener("change", this._disableable.enabled(() => {
					if (["", "custom"].includes(selectable.value)) {
						let { start, end } = this.selectable.getValue() || {};
						if (start && end) this.selectable.setValue({
							start,
							end
						});
					} else this.selectable.setValue({ preset: selectable.value });
				}));
				this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
					let value3 = this.selectable.getValue();
					selectable.value = value3?.preset || "custom";
				});
				this._disableable.onInitAndChange((disabled) => {
					if (disabled) setAttribute2(selectable, "disabled", "");
					else removeAttribute(selectable, "disabled");
				});
			}
		}
	};
	var UICalendarInputs = class extends UIElement {
		boot() {
			let calendar = this.closest("ui-calendar");
			this._disableable = calendar._disableable;
			this.offset = this.hasAttribute("offset") ? parseInt(this.getAttribute("offset")) : 0;
			this.selectable = calendar.selectable;
			this.observable = calendar.observable;
			this.config = calendar.config;
			this.viewState = calendar.viewState;
			this.offsetState = this.viewState.generateOffsetState(this.offset);
			if (this.querySelector("ui-date-picker-trigger")) return;
			let [firstInput, secondInput] = this.querySelectorAll("input");
			if (this.config.mode === CalendarModes.RANGE) syncInputStateWithRangeSelectionState(firstInput, secondInput, calendar);
			else syncInputStateWithSingleSelectionState(firstInput, calendar);
		}
	};
	element("calendar", UICalendar);
	element("calendar-today", UICalendarToday);
	element("calendar-presets", UICalendarPresets);
	element("calendar-previous", UICalendarPrevious);
	element("calendar-inputs", UICalendarInputs);
	element("calendar-next", UICalendarNext);
	element("calendar-months", UICalendarMonths);
	element("calendar-month", UICalendarMonth);
	element("calendar-year", UICalendarYear);
	function initializeEventListeners2(el, selectable, viewState, validator) {
		let blocked = false;
		let readonly = el.hasAttribute("readonly");
		on(el, "click", (e) => {
			if (blocked || readonly) return;
			let cell = e.target.closest("[data-date]");
			if (!cell) return;
			let date = dateFromCell2(cell);
			validator.isValid(date) && selectable.select(date);
		});
		on(el, "mouseover", (e) => {
			if (blocked || readonly) return;
			let cell = e.target.closest("[data-date]");
			if (!cell) return;
			let date = dateFromCell2(cell);
			if (!validator.isValid(date)) return;
			selectable.activate(date);
		});
		on(el, "mouseout", () => {
			if (blocked || readonly) return;
			selectable.activate(null);
		});
		on(el, "focusin", (e) => {
			if (blocked || readonly) return;
			let cell = e.target.closest("[data-date]");
			if (!cell) return;
			let date = dateFromCell2(cell);
			if (!validator.isValid(date)) return;
			selectable.activate(date);
		});
		on(el, "keydown", (e) => {
			if (blocked) return;
			let cell = e.target.closest("[data-date]");
			if (!cell) return;
			let handled = true;
			let direction;
			let focusedDate = dateFromCell2(cell);
			let adjustActive = (adjustment) => {
				let activeDate = focusedDate.getCopy();
				let direction2 = Math.sign(adjustment);
				let safetyCounter = 366;
				activeDate.setDay(activeDate.getDay() + adjustment);
				while (!validator.isValid(activeDate) && safetyCounter > 0) {
					activeDate.setDay(activeDate.getDay() + direction2);
					safetyCounter--;
				}
				focusedDate = activeDate;
			};
			switch (e.key) {
				case "ArrowRight":
					direction = "forward";
					adjustActive(1);
					break;
				case "ArrowLeft":
					direction = "backward";
					adjustActive(-1);
					break;
				case "ArrowUp":
					direction = "backward";
					adjustActive(-7);
					break;
				case "ArrowDown":
					direction = "forward";
					adjustActive(7);
					break;
				case "Home":
					viewState.previousMonth();
					break;
				case "End":
					viewState.nextMonth();
					break;
				case "PageUp":
					if (e.shiftKey) viewState.previousMonth();
					else viewState.previousMonth();
					break;
				case "PageDown":
					if (e.shiftKey) viewState.nextMonth();
					else viewState.nextMonth();
					break;
				default: handled = false;
			}
			if (handled) {
				e.preventDefault();
				e.stopPropagation();
				let cell2 = Array.from(el.querySelectorAll("[data-date]")).find((cell3) => dateFromCell2(cell3).isSameDay(focusedDate));
				if (!cell2 && direction) {
					if (direction === "forward") viewState.nextMonth();
					else viewState.previousMonth();
					cell2 = Array.from(el.querySelectorAll("[data-date]")).find((cell3) => dateFromCell2(cell3).isSameDay(focusedDate));
				}
				cell2?.querySelector("button,ui-button")?.focus();
			}
		});
		return {
			enable() {
				blocked = false;
			},
			disable() {
				blocked = true;
			}
		};
	}
	function dateFromCell2(cell) {
		if (!cell) return null;
		if (!cell.hasAttribute("data-date")) return null;
		return DateValue.fromIsoDateString(cell.getAttribute("data-date"));
	}
	function responsiveMonthsAttributeValue(el, configEl) {
		let months = parseInt(configEl.getAttribute("sm:months") || configEl.getAttribute("months") || 1);
		let callback;
		let media = matchMedia("(min-width: 640px)");
		let onChange = () => callback?.(media.matches ? months : 1);
		media.addEventListener("change", onChange);
		el.onUnmount(() => media.removeEventListener("change", onChange));
		return [media.matches ? months : 1, (fn) => callback = fn];
	}
	function syncInputStateWithSingleSelectionState(input, subject) {
		preventInputEventsFromBubblingToSelectRoot2(input);
		let detangled = detangle();
		let sync = detangled(() => {
			input.value = subject.selectable.getValue() || "";
		});
		subject.observable.subscribe(ObservableTrigger2.SELECTION, sync);
		sync();
		input.addEventListener("change", detangled(() => {
			let date = input.valueAsDate;
			if (date) {
				subject.selectable.setValue(DateValue.fromLocalDate(date).toIsoDateString());
				subject.viewState.setDate(DateValue.fromLocalDate(date));
			} else subject.selectable.setValue("");
		}));
	}
	function syncInputStateWithRangeSelectionState(input, secondInput, subject) {
		preventInputEventsFromBubblingToSelectRoot2(input);
		preventInputEventsFromBubblingToSelectRoot2(secondInput);
		let detangled = detangle();
		let setInputsFromValue = detangled(() => {
			let { start, end } = subject.selectable.getValue() || {};
			input.value = start || "";
			secondInput.value = end || "";
		});
		let setValueFromInputs = detangled(() => {
			let start = input.valueAsDate;
			let end = secondInput.valueAsDate;
			if (start && end) subject.selectable.setValue({
				start: DateValue.fromLocalDate(start).toIsoDateString(),
				end: DateValue.fromLocalDate(end).toIsoDateString()
			});
			else if (start) subject.selectable.setValue({
				start: DateValue.fromLocalDate(start).toIsoDateString(),
				end: null
			});
			else subject.selectable.setValue({
				start: null,
				end: null
			});
		});
		subject.observable.subscribe(ObservableTrigger2.SELECTION, setInputsFromValue);
		setInputsFromValue();
		input.addEventListener("change", setValueFromInputs);
		secondInput.addEventListener("change", setValueFromInputs);
	}
	function preventInputEventsFromBubblingToSelectRoot2(input) {
		on(input, "change", (e) => e.stopPropagation());
		on(input, "input", (e) => e.stopPropagation());
	}
	var UIDatePickerTrigger = class extends UIControl {
		boot() {
			this.picker = this.closest("ui-date-picker");
			this._disableable = new Disableable(this);
			let disableClick = (e) => {
				e.preventDefault();
				e.stopImmediatePropagation();
			};
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) this.addEventListener("click", disableClick, true);
				else this.removeEventListener("click", disableClick, true);
			});
			this.dayInput = this.querySelector("[data-os-day-input]");
			this.monthInput = this.querySelector("[data-os-month-input]");
			this.yearInput = this.querySelector("[data-os-year-input]");
			if (!this.dayInput || !this.monthInput || !this.yearInput) return;
			let { order: inputOrder, separator, placeholders } = getLocaleInfo(this.picker?.getAttribute("locale") || navigator.language);
			this.dayInput.setAttribute("placeholder", placeholders.day);
			this.monthInput.setAttribute("placeholder", placeholders.month);
			this.yearInput.setAttribute("placeholder", placeholders.year);
			this.inputs = inputOrder.map((type) => {
				if (type === "day") return this.dayInput;
				if (type === "month") return this.monthInput;
				if (type === "year") return this.yearInput;
			});
			let container = this.querySelector("[data-os-date-inputs]");
			if (container) {
				while (container.firstChild) container.removeChild(container.firstChild);
				this.inputs.forEach((input, i) => {
					container.appendChild(input);
					if (i < this.inputs.length - 1) {
						let separatorEl = document.createElement("span");
						separatorEl.textContent = ` ${separator} `;
						separatorEl.style.userSelect = "none";
						container.appendChild(separatorEl);
					}
				});
			}
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) {
					this.dayInput.setAttribute("disabled", "");
					this.monthInput.setAttribute("disabled", "");
					this.yearInput.setAttribute("disabled", "");
				} else {
					this.dayInput.removeAttribute("disabled");
					this.monthInput.removeAttribute("disabled");
					this.yearInput.removeAttribute("disabled");
				}
			});
			this.clearInputs();
			initCursorSelectionListeners2(this.dayInput);
			initCursorSelectionListeners2(this.monthInput);
			initCursorSelectionListeners2(this.yearInput);
			initInputListeners(this.inputs, (input, previousValue) => {
				this.processInputValue(input, previousValue, inputOrder);
			});
			this.inputs.forEach((input, i) => {
				input.addEventListener("keydown", (e) => {
					if (e.key === "ArrowRight" && i < this.inputs.length - 1) return this.inputs[i + 1].focus();
					if (e.key === "ArrowLeft" && i > 0) return this.inputs[i - 1].focus();
					if (e.key === " " && !this.hasAttribute("data-bare")) {
						e.preventDefault();
						this.picker?._dialogable?.toggle();
					}
				});
			});
			blockDefaultInputs2(this.dayInput);
			blockDefaultInputs2(this.monthInput);
			blockDefaultInputs2(this.yearInput);
			this.initTwoDigitInputListeners(this.dayInput, {
				max: () => this.maxDay(),
				advanceThreshold: 3,
				handleSecondDigit: (existing, digit) => {
					if (existing === 3 && digit <= 1) return `${existing}${digit}`;
					if (existing <= 2) {
						let v = `${existing}${digit}`;
						return v === "00" ? "01" : v;
					}
					return null;
				}
			});
			this.initTwoDigitInputListeners(this.monthInput, {
				max: 12,
				advanceThreshold: 1,
				handleSecondDigit: (existing, digit) => {
					if (existing === 1 && digit <= 2) return `${existing}${digit}`;
					if (existing === 0) return digit === 0 ? "01" : `0${digit}`;
					return null;
				},
				onValueChange: () => this.clampDay()
			});
			this.initYearInputListeners();
		}
		connectToPicker(picker, rangeRole = null) {
			this.picker = picker;
			this.rangeRole = rangeRole;
			queueMicrotask(() => {
				let pickerLabelledBy = this.picker.getAttribute("aria-labelledby");
				let triggerLabelledBy = this.getAttribute("aria-labelledby");
				if (pickerLabelledBy && triggerLabelledBy && pickerLabelledBy !== triggerLabelledBy) setAttribute2(this, "aria-labelledby", `${pickerLabelledBy} ${triggerLabelledBy}`);
			});
			this.picker.observable.subscribe(ObservableTrigger2.SELECTION, () => {
				this.updateInputsFromSelectable(this.picker.selectable);
			});
			this.updateInputsFromSelectable(this.picker.selectable);
			this.addEventListener("mousedown", (event) => {
				if (!event.target.closest("input")) event.preventDefault();
			});
			if (!this.hasAttribute("data-bare")) this.addEventListener("click", () => {
				this.picker._dialogable.toggle();
			});
		}
		focus() {
			if (this.inputs?.length) return this.inputs[0].focus();
			this.querySelector("button,ui-button")?.focus();
		}
		processDate() {
			let day = this.dayInput.value;
			let month = this.monthInput.value;
			let year = this.yearInput.value;
			if (day === "" || month === "" || year === "") return;
			let dayNum = parseInt(day);
			let monthNum = parseInt(month);
			let yearNum = parseInt(year);
			if (isNaN(dayNum) || isNaN(monthNum) || isNaN(yearNum)) return;
			if (monthNum < 1 || monthNum > 12) return;
			if (dayNum < 1) return;
			if (yearNum < 100) return;
			let maxDay = new Date(yearNum, monthNum, 0).getDate();
			if (dayNum > maxDay) {
				dayNum = maxDay;
				this.dayInput.value = dayNum.toString().padStart(2, "0");
			}
			let isoDate = `${yearNum.toString().padStart(4, "0")}-${monthNum.toString().padStart(2, "0")}-${dayNum.toString().padStart(2, "0")}`;
			if (this.rangeRole) {
				this.picker._rangeTriggerDates = this.picker._rangeTriggerDates || {
					start: null,
					end: null
				};
				this.picker._rangeTriggerDates[this.rangeRole] = isoDate;
				let { start, end } = this.picker._rangeTriggerDates;
				if (start && end) this.picker.selectable.setValue({
					start,
					end
				});
				else if (start) this.picker.selectable.setValue({
					start,
					end: null
				});
				else this.picker.selectable.setValue({
					start: null,
					end: null
				});
			} else this.picker.selectable.setValue(isoDate);
			this.picker.viewState.setDate(DateValue.fromIsoDateString(isoDate));
		}
		updateInputsFromSelectable(selectable) {
			let value3 = selectable.getValue();
			if (this.rangeRole && value3 && typeof value3 === "object") {
				this.picker._rangeTriggerDates = this.picker._rangeTriggerDates || {
					start: null,
					end: null
				};
				this.picker._rangeTriggerDates[this.rangeRole] = value3[this.rangeRole] || null;
				this.updateInputs(value3[this.rangeRole] || null);
				return;
			}
			this.updateInputs(value3);
		}
		updateInputs(isoDate) {
			if (!isoDate) return this.clearInputs();
			removeAttribute(this.picker, "data-empty");
			let [year, month, day] = isoDate.split("T")[0].split("-");
			this.dayInput.value = day.padStart(2, "0");
			this.dayInput.style.color = "";
			this.dayInput.classList.remove("font-mono");
			this.monthInput.value = month.padStart(2, "0");
			this.monthInput.style.color = "";
			this.monthInput.classList.remove("font-mono");
			this.yearInput.value = year.padStart(4, "0");
			this.yearInput.style.color = "";
			this.yearInput.classList.remove("font-mono");
		}
		clearInputs() {
			this.dayInput.value = "";
			this.dayInput.style.color = "";
			this.dayInput.classList.add("font-mono");
			this.monthInput.value = "";
			this.monthInput.style.color = "";
			this.monthInput.classList.add("font-mono");
			this.yearInput.value = "";
			this.yearInput.style.color = "";
			this.yearInput.classList.add("font-mono");
			let focused = this.inputs.find((s) => s === document.activeElement);
			if (focused) highlightInputContents2(focused);
			setAttribute2(this.picker, "data-empty", "");
		}
		maxDay() {
			let month = parseInt(this.monthInput.value);
			let year = parseInt(this.yearInput.value);
			if (isNaN(month) || month < 1 || month > 12) return 31;
			if (isNaN(year) || year < 1) year = 2e3;
			return new Date(year, month, 0).getDate();
		}
		clampDay() {
			let day = parseInt(this.dayInput.value);
			if (isNaN(day)) return;
			let max2 = this.maxDay();
			if (day > max2) this.dayInput.value = max2.toString().padStart(2, "0");
		}
		expandTwoDigitYear(year) {
			let rangeEnd = DateValue.today().getYear() + 20;
			let expandedYear = Math.floor(rangeEnd / 100) * 100 + year;
			return expandedYear > rangeEnd ? expandedYear - 100 : expandedYear;
		}
		initTwoDigitInputListeners(input, { max: max2, advanceThreshold, handleSecondDigit, onValueChange }) {
			let isEditing = false;
			input.addEventListener("keydown", (e) => {
				if (e.key === "Backspace") {
					if (input.value === "" || input.value === input.placeholder) {
						let idx = this.inputs.indexOf(input);
						if (idx > 0) this.inputs[idx - 1].focus();
						return;
					}
					input.value = "";
					highlightInputContents2(input);
					return;
				}
				if (!["ArrowUp", "ArrowDown"].includes(e.key)) return;
				e.preventDefault();
				e.stopPropagation();
				let number = parseInt(input.value);
				let maxVal = typeof max2 === "function" ? max2() : max2;
				if (e.key === "ArrowUp") {
					if (isNaN(number) || number >= maxVal) number = 1;
					else number++;
				} else if (e.key === "ArrowDown") {
					if (isNaN(number) || number === 1) number = maxVal;
					else number--;
				}
				input.value = number.toString().padStart(2, "0");
				input.style.color = "";
				input.classList.remove("font-mono");
				onValueChange?.();
				this.processDate();
				highlightInputContents2(input);
			});
			let nextInput = this.inputs[this.inputs.indexOf(input) + 1];
			input.addEventListener("keydown", (e) => {
				if (!/[0-9]/.test(e.key)) return;
				let digit = parseInt(e.key);
				if (input.value === "" || !isEditing) {
					isEditing = true;
					input.value = `0${digit}`;
					onValueChange?.();
					digit > advanceThreshold ? nextInput?.focus() : highlightInputContents2(input);
					return;
				}
				let completed = handleSecondDigit(parseInt(input.value), digit);
				if (completed) {
					input.value = completed;
					onValueChange?.();
					nextInput?.focus();
					return;
				}
				input.value = `0${digit}`;
				onValueChange?.();
				digit > advanceThreshold ? nextInput?.focus() : highlightInputContents2(input);
			});
			input.addEventListener("blur", () => {
				isEditing = false;
				if (input.value === "00") input.value = "01";
				onValueChange?.();
				this.processDate();
			});
		}
		initYearInputListeners() {
			let buffer = "";
			this.yearInput.addEventListener("keydown", (e) => {
				if (e.key === "Backspace") {
					buffer = "";
					if (this.yearInput.value === "" || this.yearInput.value === this.yearInput.placeholder) {
						let idx = this.inputs.indexOf(this.yearInput);
						if (idx > 0) this.inputs[idx - 1].focus();
						return;
					}
					this.yearInput.value = "";
					highlightInputContents2(this.yearInput);
					return;
				}
				if (!["ArrowUp", "ArrowDown"].includes(e.key)) return;
				e.preventDefault();
				e.stopPropagation();
				let number = parseInt(this.yearInput.value);
				if (e.key === "ArrowUp") {
					if (isNaN(number)) number = (/* @__PURE__ */ new Date()).getFullYear();
					else number++;
				} else if (e.key === "ArrowDown") {
					if (isNaN(number)) number = (/* @__PURE__ */ new Date()).getFullYear();
					else if (number > 1) number--;
				}
				this.yearInput.value = number.toString().padStart(4, "0");
				this.yearInput.style.color = "";
				this.yearInput.classList.remove("font-mono");
				buffer = "";
				this.clampDay();
				this.processDate();
				highlightInputContents2(this.yearInput);
			});
			let nextInput = this.inputs[this.inputs.indexOf(this.yearInput) + 1];
			this.yearInput.addEventListener("keydown", (e) => {
				if (!/[0-9]/.test(e.key)) return;
				let number = parseInt(e.key);
				buffer += number.toString();
				if (buffer.length >= 4) {
					this.yearInput.value = buffer.slice(-4) === "0000" ? "0001" : buffer.slice(-4);
					buffer = "";
					this.clampDay();
					this.processDate();
					if (nextInput) nextInput.focus();
					else highlightInputContents2(this.yearInput);
					return;
				}
				this.yearInput.value = buffer.padStart(4, "0");
				highlightInputContents2(this.yearInput);
			});
			this.yearInput.addEventListener("blur", () => {
				if (buffer.length > 0 && buffer.length <= 2) this.yearInput.value = this.expandTwoDigitYear(parseInt(buffer)).toString();
				else if (buffer.length > 0) this.yearInput.value = buffer.padStart(4, "0");
				buffer = "";
				if (this.yearInput.value === "0000") this.yearInput.value = "0001";
				this.clampDay();
				this.processDate();
			});
		}
		processInputValue(input, previousValue, inputOrder) {
			let value3 = input.value;
			let date = parseDateInput(value3, inputOrder);
			if (!date) {
				if (!this.isValidInputValue(input, value3)) {
					input.value = previousValue;
					highlightInputContents2(input);
					return;
				}
				if (value3 !== "") {
					let length = input === this.yearInput ? 4 : 2;
					let number = parseInt(value3);
					if (input === this.yearInput && value3.length <= 2) number = this.expandTwoDigitYear(number);
					input.value = number.toString().padStart(length, "0");
					input.style.color = "";
					input.classList.remove("font-mono");
				}
				this.clampDay();
				this.processDate();
				return;
			}
			this.dayInput.value = date.day.toString().padStart(2, "0");
			this.monthInput.value = date.month.toString().padStart(2, "0");
			this.yearInput.value = date.year.toString().padStart(4, "0");
			this.processDate();
			let lastInput = this.inputs.at(-1);
			lastInput.focus();
			highlightInputContents2(lastInput);
		}
		isValidInputValue(input, value3) {
			if (value3 === "") return true;
			if (!/^\d+$/.test(value3)) return false;
			let number = parseInt(value3);
			if (input === this.dayInput) return value3.length <= 2 && number >= 1 && number <= 31;
			if (input === this.monthInput) return value3.length <= 2 && number >= 1 && number <= 12;
			if (input === this.yearInput) return value3.length <= 4;
			return false;
		}
	};
	function parseDateInput(value3, inputOrder) {
		let isoMatch = value3.trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
		if (isoMatch) return {
			year: parseInt(isoMatch[1]),
			month: parseInt(isoMatch[2]),
			day: parseInt(isoMatch[3])
		};
		let parts = value3.trim().split(/\D+/);
		if (parts.at(-1) === "") parts.pop();
		if (parts.length !== inputOrder.length) return null;
		if (parts.some((part) => !/^\d+$/.test(part))) return null;
		let date = {};
		inputOrder.forEach((type, index) => {
			date[type] = parseInt(parts[index]);
		});
		return date;
	}
	function getLocaleInfo(locale) {
		try {
			let parts = new Intl.DateTimeFormat(locale, {
				year: "numeric",
				month: "2-digit",
				day: "2-digit"
			}).formatToParts(new Date(2024, 2, 15));
			let order = parts.filter((p) => [
				"day",
				"month",
				"year"
			].includes(p.type)).map((p) => p.type);
			let separator = parts.find((p) => p.type === "literal")?.value?.trim() || "/";
			let placeholders = {
				day: "dd",
				month: "mm",
				year: "yyyy"
			};
			parts.forEach((p) => {
				if (p.type === "day") placeholders.day = p.value.replace(/\d/g, "d");
				if (p.type === "month") placeholders.month = p.value.replace(/\d/g, "m");
				if (p.type === "year") placeholders.year = p.value.replace(/\d/g, "y");
			});
			return {
				order,
				separator,
				placeholders
			};
		} catch {
			return {
				order: [
					"day",
					"month",
					"year"
				],
				separator: "/",
				placeholders: {
					day: "dd",
					month: "mm",
					year: "yyyy"
				}
			};
		}
	}
	function initInputListeners(inputs, handleInput) {
		inputs.forEach((input) => {
			let previousValue = input.value;
			input.addEventListener("focus", () => {
				previousValue = input.value;
			});
			input.addEventListener("beforeinput", () => {
				previousValue = input.value;
			});
			input.addEventListener("input", (event) => {
				event.stopPropagation();
				handleInput(input, previousValue);
				previousValue = input.value;
			});
		});
	}
	function initCursorSelectionListeners2(input) {
		input.addEventListener("focus", () => {
			highlightInputContents2(input);
		});
		input.addEventListener("blur", () => {
			clearPlaceholderValue2(input);
		});
		input.addEventListener("mousedown", (e) => {
			e.preventDefault();
			input.focus();
			highlightInputContents2(input);
		});
		input.addEventListener("mouseup", (e) => {
			e.preventDefault();
			highlightInputContents2(input);
		});
	}
	function blockDefaultInputs2(input) {
		input.addEventListener("keydown", (e) => {
			if (/[0-9]/.test(e.key)) {
				clearPlaceholderValue2(input);
				input.classList.remove("font-mono");
			}
			if (e.ctrlKey || e.metaKey || e.altKey) return;
			if (["Tab"].includes(e.key)) return;
			e.preventDefault();
		});
	}
	function highlightInputContents2(input) {
		if (!input.value && input.placeholder) {
			input.style.color = getComputedStyle(input, "::placeholder").color;
			input.value = input.placeholder;
			input.classList.add("font-mono");
		}
		input.setSelectionRange(0, input.value.length);
	}
	function clearPlaceholderValue2(input) {
		if (input.value === input.placeholder) {
			input.value = "";
			input.style.color = "";
		}
	}
	var UIDatePicker = class extends UIControl {
		bootWithCalendar(calendar) {
			let isRange = calendar.config.mode === CalendarModes.RANGE;
			this.calendar = calendar;
			this._disableable = new Disableable(this);
			this._disableable.onInitAndChange((disabled) => {
				this.calendar.disabled = disabled;
			});
			this.observable = new Observable2();
			this.selectable = new DeferredSelectable(this.calendar.selectable, this.observable);
			this.viewState = this.calendar.viewState;
			this.querySelector("ui-selected-date")?.bootWithCalendar();
			this.querySelector("ui-date-picker-select")?.bootWithCalendar();
			let dropdownEnabled = !(this.hasAttribute("dropdown") && this.getAttribute("dropdown") === "false");
			let popoverEl = this.querySelector("dialog");
			if (!popoverEl) return;
			let dateFieldEls = Array.from(this.querySelectorAll("ui-date-picker-trigger")).filter((el) => !popoverEl?.contains(el));
			let dateFieldEl = dateFieldEls[0] || null;
			let secondDateFieldEl = dateFieldEls[1] || null;
			let inputEl = dateFieldEl ? null : this.querySelector("input");
			inputEl = popoverEl?.contains(inputEl) ? null : inputEl;
			let secondInputEl = dateFieldEl ? null : Array.from(this.querySelectorAll("input")).find((i) => i !== inputEl);
			secondInputEl = popoverEl?.contains(secondInputEl) ? null : secondInputEl;
			if (!dateFieldEl) {
				inputEl && inputEl.addEventListener("click", (e) => e.preventDefault());
				secondInputEl && secondInputEl.addEventListener("click", (e) => e.preventDefault());
			}
			let buttonEl = this.querySelector("button,ui-button");
			buttonEl = popoverEl?.contains(buttonEl) ? null : buttonEl;
			let calendarId = assignId(this.calendar, "calendar");
			this.triggerEl = dateFieldEl || inputEl || buttonEl;
			if (!this.triggerEl) return;
			if (dateFieldEl) setAttribute2(this.triggerEl, "role", "group");
			else setAttribute2(this.triggerEl, "role", "combobox");
			setAttribute2(this.triggerEl, "aria-controls", calendarId);
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) setAttribute2(this.triggerEl, "disabled", "");
				else removeAttribute(this.triggerEl, "disabled");
			});
			if (dropdownEnabled) {
				this._dialogable = new Dialogable(popoverEl, {
					clickOutside: !this.hasAttribute("disable-click-outside"),
					triggers: dateFieldEl ? [buttonEl].filter(Boolean) : [
						buttonEl,
						inputEl,
						secondInputEl
					].filter(Boolean)
				});
				this._closeable = new Closeable(this);
				this._closeable.onClose(() => this._dialogable.hide());
				this._anchorable = new Anchorable(popoverEl, {
					reference: this.triggerEl,
					position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
					gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
					offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
				});
				if (isIOS() && !dateFieldEl) {
					inputEl && this.showIOSOverlay(inputEl);
					secondInputEl && this.showIOSOverlay(secondInputEl);
				}
				this.querySelectorAll("button,ui-button").forEach((button) => {
					if (button === this.triggerEl) return;
					if (popoverEl.contains(button)) return;
					setAttribute2(button, "aria-controls", calendarId);
					setAttribute2(button, "aria-haspopup", "combobox");
					linkExpandedStateToPopover3(button, this._dialogable);
					on(button, "click", () => this._dialogable.toggle());
				});
				initPopover3(this, this.triggerEl, popoverEl, this._dialogable, this._anchorable);
				if (!dateFieldEl) linkExpandedStateToPopover3(this.triggerEl, this._dialogable);
				preventScrollWhenPopoverIsOpen3(this, this._dialogable);
				if (!dateFieldEl) controlPopoverWithKeyboard2(this.triggerEl, this._dialogable);
				handlePopoverClosing3(this, this.calendar.config.mode, this.observable, this.selectable, this._dialogable);
				let bareTriggerEls = Array.from(popoverEl.querySelectorAll("ui-date-picker-trigger[data-bare]"));
				if (bareTriggerEls.length === 2 && isRange) {
					bareTriggerEls[0].connectToPicker(this, "start");
					bareTriggerEls[1].connectToPicker(this, "end");
				} else if (bareTriggerEls.length === 1) bareTriggerEls[0].connectToPicker(this);
			}
			if (dateFieldEl && secondDateFieldEl && isRange) {
				dateFieldEl.connectToPicker(this, "start");
				secondDateFieldEl.connectToPicker(this, "end");
			} else if (dateFieldEl) dateFieldEl.connectToPicker(this);
			else if (isRange && popoverEl && inputEl && secondInputEl) {
				highlightInputContentsWhenFocused2(inputEl);
				highlightInputContentsWhenFocused2(secondInputEl);
				syncInputStateWithRangeSelectionState(inputEl, secondInputEl, this);
			} else if (popoverEl && inputEl) {
				let input = inputEl;
				highlightInputContentsWhenFocused2(input);
				syncInputStateWithSingleSelectionState(input, this);
			} else if (popoverEl) togglePopoverWithMouse2(buttonEl, this._dialogable);
			this._controllable = new Controllable(this, { bubbles: true });
			this.calendar.addEventListener("change", (e) => e.stopPropagation());
			this.calendar.addEventListener("input", (e) => e.stopPropagation());
			this._submittable = new Submittable(this, {
				name: this.getAttribute("name"),
				value: this.selectable.getValue()
			});
			let detangled = detangle();
			this._controllable.initial((initial) => initial && this.selectable.setValue(initial));
			this._controllable.getter(() => this.selectable.getValue());
			this._controllable.setter(detangled((value3) => this.selectable.setValue(value3)));
			this.observable.subscribe(ObservableTrigger2.SELECTION, detangled(() => {
				this.dispatchEvent(new Event("select", {
					bubbles: false,
					cancelable: true
				}));
				this._controllable.dispatch();
			}));
			this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
				this._submittable.update(this.selectable.getValue());
			});
			this.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
				this.dispatchEvent(new Event("navigate", {
					bubbles: false,
					cancelable: true
				}));
			});
		}
		unmount() {
			if (this._dialogable?.getState()) {
				let { unlock } = lockScroll();
				unlock();
			}
		}
		showIOSOverlay(inputEl) {
			let overlay = document.createElement("button");
			overlay.setAttribute("data-os-ios-overlay", "");
			overlay.setAttribute("data-appended", "");
			overlay.classList.add("absolute", "inset-0");
			inputEl.after(overlay);
		}
		input() {
			return this.querySelector("input");
		}
		clear() {
			this.selectable.setValue(null);
			this.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
		}
		open() {
			this._dialogable.setState(true);
		}
		close() {
			this._dialogable.setState(false);
		}
		trigger() {
			return this.triggerEl;
		}
	};
	var UISelectedDate = class extends UIElement {
		bootWithCalendar() {
			this.picker = this.closest("ui-date-picker");
			this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
			if (!this.querySelector("template[name=\"date\"]")) {
				let template = document.createElement("template");
				template.setAttribute("name", "date");
				template.innerHTML = "<div><slot></slot></div>";
				this.appendChild(template);
			}
			this.templates = {
				placeholder: this.querySelector("template[name=\"placeholder\"]"),
				date: this.querySelector("template[name=\"date\"]")
			};
			this.picker.observable.subscribe(ObservableTrigger2.SELECTION, () => {
				this.render(this.picker);
			});
			this.render(this.picker);
		}
		render(picker) {
			this.templates.placeholder?.clearPlaceholder?.();
			this.templates.date?.clearDate?.();
			if (picker.selectable.hasSelection()) {
				let { cleanup } = renderTemplate2$1(this.templates.date, (hydrate) => {
					return hydrate({ slots: { default: picker.selectable.display(this.picker.calendar.config.locale) } });
				});
				this.templates.date.clearDate = cleanup;
			} else {
				if (!this.templates.placeholder) return;
				let { cleanup } = renderTemplate2$1(this.templates.placeholder, (hydrate) => {
					return hydrate({ slots: {} });
				});
				this.templates.placeholder.clearPlaceholder = cleanup;
			}
		}
	};
	var UIDatePickerSelect = class extends UIElement {
		bootWithCalendar() {
			this.picker = this.closest("ui-date-picker");
			this.picker.selectable.blockSyncIf(() => {
				return this.getBoundingClientRect().width > 0;
			});
			initFauxButton(this, () => this.picker._disableable.isDisabled(), () => {
				this.picker.selectable.sync();
				this.picker._dialogable.setState(false);
			});
		}
	};
	element("date-picker", UIDatePicker);
	element("date-picker-select", UIDatePickerSelect);
	element("selected-date", UISelectedDate);
	element("date-picker-trigger", UIDatePickerTrigger);
	inject(({ css }) => css`
/* For Chrome, Safari, Edge */
ui-date-picker input[type="date"]::-webkit-calendar-picker-indicator {
  display: none;
  -webkit-appearance: none;
}
`);
	function linkExpandedStateToPopover3(el, dialogable) {
		setAttribute2(el, "aria-haspopup", "listbox");
		let refreshPopover = () => {
			setAttribute2(el, "aria-expanded", dialogable.getState() ? "true" : "false");
			dialogable.getState() ? setAttribute2(el, "data-open", "") : removeAttribute(el, "data-open", "");
		};
		dialogable.onChange(() => {
			refreshPopover();
		});
		refreshPopover();
	}
	function initPopover3(root, trigger, popover, dialogable, anchorable) {
		let refreshPopover = () => {
			Array.from([root, popover]).forEach((i) => {
				dialogable.getState() ? setAttribute2(i, "data-open", "") : removeAttribute(i, "data-open", "");
			});
			dialogable.getState() ? anchorable.reposition() : anchorable.cleanup();
		};
		dialogable.onChange(() => refreshPopover());
		refreshPopover();
		dialogable.onChange(() => {
			if (dialogable.getState()) root.dispatchEvent(new Event("open", {
				bubbles: false,
				cancelable: false
			}));
			else root.dispatchEvent(new Event("close", {
				bubbles: false,
				cancelable: false
			}));
		});
	}
	function controlPopoverWithKeyboard2(button, dialogable) {
		on(button, "keydown", (e) => {
			if (![
				"ArrowDown",
				"ArrowUp",
				"Escape"
			].includes(e.key)) return;
			if (e.key === "ArrowDown") {
				if (!dialogable.getState()) {
					dialogable.setState(true);
					e.preventDefault();
					e.stopImmediatePropagation();
				}
			} else if (e.key === "ArrowUp") {
				if (!dialogable.getState()) {
					dialogable.setState(true);
					e.preventDefault();
					e.stopImmediatePropagation();
				}
			} else if (e.key === "Escape") {
				if (dialogable.getState()) dialogable.setState(false);
			}
		});
	}
	function togglePopoverWithMouse2(button, dialogable) {
		on(button, "click", () => {
			dialogable.setState(!dialogable.getState());
		});
	}
	function highlightInputContentsWhenFocused2(input) {
		on(input, "focus", () => input.select());
	}
	function preventScrollWhenPopoverIsOpen3(root, dialogable) {
		let { lock, unlock } = lockScroll(dialogable.el);
		dialogable.onChange(() => {
			dialogable.getState() ? lock() : unlock();
		});
	}
	function handlePopoverClosing3(root, mode, observable, selectable, dialogable) {
		let closeOnSelect = mode !== CalendarModes.MULTIPLE;
		if (root.hasAttribute("close")) {
			let close = root.getAttribute("close");
			closeOnSelect = close.split(" ").includes("auto");
			if (close === "manual") closeOnSelect = false;
		}
		if (!closeOnSelect) return;
		observable.subscribe(ObservableTrigger2.SELECTION, () => {
			if (selectable.hasSelection()) dialogable.setState(false);
		});
	}
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/time-picker.js
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
			if (config.mode === SelectableModes.MULTIPLE) value3 = value3 ? value3.split(",").map((i) => i.trim()) : [];
			return _Selectable.createSelectionFromValue(value3, config);
		}
	};
	var Selection = class {
		contains() {}
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
		display(config) {}
		value() {}
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
			return this._values.sort((a, b) => timeInMinutes(a) < timeInMinutes(b) ? -1 : 1)[0] || value(fallback);
		}
		upperBound(fallback) {
			let sortedValues = this._values.sort((a, b) => timeInMinutes(a) < timeInMinutes(b) ? -1 : 1);
			return sortedValues[sortedValues.length - 1] || value(fallback);
		}
		select(value3) {
			if (this._values.some((i) => i === value3)) return new _MultipleSelection(this._values.filter((i) => i !== value3));
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
				if (disabled) this.addEventListener("click", disableClick, true);
				else this.removeEventListener("click", disableClick, true);
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
				if (active === this.hourInput || active === this.minuteInput || active === this.meridiemInput) this._lastFocusedInput = active;
				else this._lastFocusedInput = null;
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
			let time = getTime(this.hourInput.value, this.minuteInput.value, meridiem);
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
		updateHourDisplay(hours, { forceMeridiem = false } = {}) {
			hours = parseInt(hours);
			if (isNaN(hours)) return;
			let meridiemInput = this.meridiemInputForTimeFormat();
			if (meridiemInput) {
				let hasExistingMeridiem = meridiemInput.value && !("showingPlaceholder" in meridiemInput.dataset);
				if (forceMeridiem || !hasExistingMeridiem || hours > 12 || hours === 0) {
					meridiemInput.value = getLocalisedMeridiem(hours, this.picker.config.locale);
					meridiemInput.style.color = "";
					delete meridiemInput.dataset.showingPlaceholder;
				}
				if (hours > 12) hours = hours - 12;
				else if (hours === 0) hours = 12;
			}
			this.hourInput.value = hours.toString().padStart(2, "0");
			this.hourInput.style.color = "";
			this.hourInput.classList.remove("font-mono");
		}
		updateInputs(time) {
			if (!time) return this.clearInputs();
			removeAttribute(this.picker, "data-empty");
			let [hours, minutes] = time.split(":");
			let meridiem = null;
			let meridiemInput = this.meridiemInputForTimeFormat();
			if (meridiemInput) {
				hours = parseInt(hours);
				meridiem = getLocalisedMeridiem(hours, this.picker.config.locale);
				if (hours > 12) hours = hours - 12;
				else if (hours === 0) hours = 12;
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
			let focused = [
				this.hourInput,
				this.minuteInput,
				this.meridiemInput
			].find((i) => i === document.activeElement);
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
					if (isNaN(hours) || hours === 23) hours = 0;
					else hours++;
				} else if (e.key === "ArrowDown") {
					if (isNaN(hours) || hours === 0) hours = 23;
					else hours--;
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
				if (existingNumber === 2 && number >= 0 && number <= 3) {
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
				if (this.hourInput.value !== "" && this.hourInput.value !== this.hourInput.placeholder) this.updateHourDisplay(this.hourInput.value);
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
					if (isNaN(number) || number === 59) number = 0;
					else number = number + 1;
				} else if (e.key === "ArrowDown") {
					if (isNaN(number) || number === 0) number = 59;
					else number = number - 1;
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
					if (value3 === "" || value3 === pm) value3 = am;
					else value3 = pm;
				} else if (e.key === "ArrowDown") {
					if (value3 === "" || value3 === am) value3 = pm;
					else value3 = am;
				}
				this.meridiemInput.value = value3;
				this.meridiemInput.style.color = "";
				delete this.meridiemInput.dataset.showingPlaceholder;
				this.processTime();
				highlightInputContents(this.meridiemInput);
			});
			this.meridiemInput?.addEventListener("keydown", (e) => {
				if (![
					"a",
					"p",
					"A",
					"P"
				].includes(e.key)) return;
				clearPlaceholderValue(this.meridiemInput);
				let locale = this.picker.config.locale;
				if (e.key === "a" || e.key === "A") this.meridiemInput.value = getLocalisedMeridiem(0, locale);
				else if (e.key === "p" || e.key === "P") this.meridiemInput.value = getLocalisedMeridiem(12, locale);
				this.meridiemInput.style.color = "";
				delete this.meridiemInput.dataset.showingPlaceholder;
				this.processTime();
				highlightInputContents(this.meridiemInput);
			});
		}
		setMeridiemWidthBasedOnLocale() {
			let meridiem = getLocalisedMeridiem(12, this.picker.config.locale);
			if (!meridiem) return;
			if (/^[\u0000-\u024F\u0370-\u03FF\u1E00-\u1EFF\s.]+$/.test(meridiem)) {
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
		let medridiem = new Intl.DateTimeFormat(locale, {
			hour: "numeric",
			hour12: true
		}).formatToParts(new Date(2024, 0, 1, hours, 0)).find((part) => part.type === "dayPeriod")?.value;
		if (medridiem) return medridiem;
		return hours >= 12 ? "PM" : "AM";
	}
	function getTime(hour, minute, meridiem = null) {
		hour = hour.padStart(2, "0");
		minute = minute.padStart(2, "0");
		return convertTimeStringTo24HourFormat(meridiem ? hour + ":" + minute + " " + meridiem : hour + ":" + minute);
	}
	function localeIs24HourFormat(locale) {
		return new Intl.DateTimeFormat(locale, { hour: "numeric" }).format(new Date(2024, 0, 1, 0, 0)).startsWith("00");
	}
	function convertTimeStringTo24HourFormat(time) {
		let match = time.trim().match(/^(\d{1,2}):(\d{2})(?:\s*(am|pm|AM|PM))?$/i);
		if (!match) throw new Error(`Invalid time string: ${time}`);
		let hours = parseInt(match[1], 10);
		let minutes = parseInt(match[2], 10);
		let meridiem = match[3]?.toUpperCase();
		if (minutes > 59 || hours > 23 || meridiem !== "AM" && meridiem !== "PM" && meridiem !== void 0) throw new Error(`Invalid time string: ${time}`);
		if (hours >= 13 || hours === 0) return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`;
		if (meridiem) {
			if (hours === 12) hours = meridiem === "AM" ? 0 : 12;
			else if (meridiem === "PM") hours += 12;
		}
		return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`;
	}
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
			this._popoverable = new Popoverable(popoverEl, { triggers: [triggerEl] });
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
			new MutationObserver((mutations) => {
				mutations.forEach((mutation) => {
					if (![
						"interval",
						"unavailable",
						"max",
						"min",
						"open-to"
					].includes(mutation.attributeName)) return;
					this.config.interval = this.interval();
					this.config.unavailable = this.unavailable();
					this.config.min = this.min();
					this.config.max = this.max();
					this.config.openTo = this.openTo();
					this.observable.notify(ObservableTrigger.VIEW_CHANGE);
				});
			}).observe(this, { attributes: true });
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
			if (this.hasAttribute("interval")) return parseInt(this.getAttribute("interval"));
			return 30;
		}
		min() {
			if (this.hasAttribute("min")) {
				let min2 = this.getAttribute("min");
				if (min2 === "now") return (/* @__PURE__ */ new Date()).toLocaleTimeString("en-US", {
					hour: "2-digit",
					minute: "2-digit",
					hour12: false
				});
				if (validateTimeString(min2)) return min2;
				return console.warn(`Invalid min attribute: ${min2}`);
			}
		}
		max() {
			if (this.hasAttribute("max")) {
				let max2 = this.getAttribute("max");
				if (max2 === "now") return (/* @__PURE__ */ new Date()).toLocaleTimeString("en-US", {
					hour: "2-digit",
					minute: "2-digit",
					hour12: false
				});
				if (validateTimeString(max2)) return max2;
				return console.warn(`Invalid max attribute: ${max2}`);
			}
		}
		unavailable() {
			if (this.hasAttribute("unavailable")) {
				let unavailable = this.getAttribute("unavailable").split(",").map((i) => i.trim());
				if (unavailable.every((i) => i.includes("-") ? validateTimeRangeString(i) : validateTimeString(i))) return unavailable;
				return console.warn(`Invalid unavailable attribute: ${unavailable}`);
			}
			return [];
		}
		openTo() {
			if (this.hasAttribute("open-to")) {
				let openTo = this.getAttribute("open-to");
				if (openTo === "now") return (/* @__PURE__ */ new Date()).toLocaleTimeString("en-US", {
					hour: "2-digit",
					minute: "2-digit",
					hour12: false
				});
				if (validateTimeString(openTo)) return openTo;
				return console.warn(`Invalid open-to attribute: ${openTo}`);
			}
		}
	};
	var UISelectedTime = class extends UIElement {
		boot() {
			this.picker = this.closest("ui-time-picker");
			if (!this.querySelector("template[name=\"time\"]")) {
				let template = document.createElement("template");
				template.setAttribute("name", "time");
				template.innerHTML = "<div><slot></slot></div>";
			}
			this.templates = {
				placeholder: this.querySelector("template[name=\"placeholder\"]"),
				time: this.querySelector("template[name=\"time\"]")
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
			let template = this.querySelector("template[name=\"option\"]");
			if (!template) return;
			renderTemplate2(template, (hydrate) => {
				return generateTimes(this.picker.config).map(({ value: value3, label }) => {
					let isDisabled = this.picker.config.unavailable.some((unavailableTime) => timesAreOverlapping(value3, unavailableTime));
					let attrs = {
						"data-time": value3,
						"role": "option"
					};
					if (isDisabled) attrs.disabled = "";
					let el = hydrate({
						attrs,
						slots: { default: label }
					});
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
			if (popoverable.getState()) root.dispatchEvent(new Event("open", {
				bubbles: false,
				cancelable: false
			}));
			else root.dispatchEvent(new Event("close", {
				bubbles: false,
				cancelable: false
			}));
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
			if (selectable.hasSelection()) popoverable.setState(false);
		});
	}
	function initializeEventListeners(el, selectable) {
		let readonly = el.hasAttribute("readonly");
		on(el, "click", (e) => {
			if (readonly) return;
			let option = e.target.closest("[data-time]");
			if (!option) return;
			let time = option.getAttribute("data-time");
			setTimeout(() => {
				validateTimeString(time) && selectable.select(time);
			});
		});
		return {
			enable: () => {},
			disable: () => {}
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
		return new Intl.DateTimeFormat(locale, {
			hour: "numeric",
			minute: "numeric",
			hour12: {
				"12-hour": true,
				"24-hour": false,
				"auto": void 0
			}[timeFormat]
		}).format(new Date(2024, 0, 1, hours, minutes));
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
				if (!Array.isArray(selectedValues)) selectedValues = selectedValues === null ? [] : [selectedValues];
				let firstSelectedOption = activatable.filterAwareWalker().find((el) => selectedValues.includes(el.getAttribute("data-time")));
				let firstOptionToActivate = null;
				if (!firstSelectedOption && openTo) firstOptionToActivate = findCloestOptionToOpenTo(activatable, openTo);
				setTimeout(() => {
					let optionToScrollTo = firstSelectedOption || firstOptionToActivate;
					activatable.activateSelectedOrFirst(optionToScrollTo);
					optionToScrollTo?.scrollIntoView({ block: "nearest" });
				});
			} else activatable.clearActive();
		});
	}
	function findCloestOptionToOpenTo(activatable, openTo) {
		let openToInMinutes = timeInMinutes(openTo);
		let closestOption = null;
		let closestOptionTimeInMinutes = null;
		activatable.filterAwareWalker().walk((option, bail) => {
			let optionTimeInMinutes = timeInMinutes(option.getAttribute("data-time"));
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
		let maxInMinutes = 1439;
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
			times.push({
				value: value3,
				label
			});
		}
		return times;
	}
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/disclosure.js
	var UIDisclosure = class extends UIElement {
		boot() {
			let button = this.button();
			let details = this.details();
			if (!button) return console.warn("ui-disclosure: no trigger element found", this);
			this._disableable = new Disableable(this);
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) {
					if (!button.hasAttribute("disabled")) setAttribute2(button, "disabled", "");
				} else if (button.hasAttribute("disabled")) removeAttribute(button, "disabled");
			});
			if (!details) return;
			this._controllable = new Controllable(this, { disabled: this.disabled });
			details._disclosable = new Disclosable(details);
			this._controllable.initial((initial) => initial && details._disclosable.setState(true));
			this._controllable.getter(() => details._disclosable.getState());
			this._controllable.setter((value3) => details._disclosable.setState(value3));
			details._disclosable.onChange(() => {
				this.dispatchEvent(new CustomEvent("lofi-disclosable-change", { bubbles: true }));
				this._controllable.dispatch();
			});
			let transitions = details.getAttributeNames().some((name) => {
				return name === "x-collapse" || name.startsWith("x-collapse.");
			});
			let refresh = (initial = false) => {
				if (details._disclosable.getState()) {
					setAttribute2(this, "data-open", "");
					setAttribute2(button, "data-open", "");
					setAttribute2(details, "data-open", "");
					removeAttribute(details, "hidden");
				} else {
					removeAttribute(this, "data-open");
					removeAttribute(button, "data-open");
					removeAttribute(details, "data-open");
					if (!transitions || initial) setAttribute2(details, "hidden", "until-found");
				}
			};
			details._disclosable.onChange(() => refresh());
			refresh(true);
			if (transitions) on(details, "transitionend", (e) => {
				if (e.target !== details || e.propertyName !== "height") return;
				if (details._disclosable.getState()) return;
				setAttribute2(details, "hidden", "until-found");
			});
			on(details, "beforematch", () => {
				details._disclosable.setState(true);
			});
			on(button, "click", (e) => {
				if (!this.disabled) details._disclosable.setState(!details._disclosable.getState());
			});
			let id = assignId(details, "disclosure");
			setAttribute2(button, "aria-controls", id);
			setAttribute2(button, "aria-expanded", "false");
			details._disclosable.onChange(() => {
				details._disclosable.getState() ? setAttribute2(button, "aria-expanded", "true") : setAttribute2(button, "aria-expanded", "false");
			});
			if (this.hasAttribute("open")) details._disclosable.setState(true);
		}
		button() {
			return this.querySelector("button,ui-button");
		}
		details() {
			let details = this.lastElementChild;
			return details === this.button() ? null : details;
		}
	};
	var UIDisclosureGroup = class _UIDisclosureGroup extends UIElement {
		boot() {
			this.exclusive = this.hasAttribute("exclusive");
			if (this.exclusive) on(this, "lofi-disclosable-change", (e) => {
				e.stopPropagation();
				if (e.target.localName === "ui-disclosure" && e.target.value) this.disclosureWalker().each((el) => {
					if (el === e.target) return;
					el.value = false;
				});
			});
		}
		disclosureWalker() {
			return walker(this, (el, { skip, reject }) => {
				if (el instanceof _UIDisclosureGroup && el !== this) return reject();
				if (el.localName !== "ui-disclosure") return reject();
			});
		}
	};
	element("disclosure", UIDisclosure);
	element("disclosure-group", UIDisclosureGroup);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/resizable.js
	var UIResizable = class extends UIElement {
		boot() {}
	};
	var UIGrip = class extends UIElement {
		boot() {
			let dimension = this.hasAttribute("resize") ? this.getAttribute("resize") : "both";
			let shrink = this.hasAttribute("shrink");
			let container = this.closest("ui-resizable");
			let maxWidth, maxHeight;
			let hasAttemptedAResizeYet = false;
			if (!container) throw "Resizable container not found";
			this.addEventListener("pointerdown", (e) => {
				if (!hasAttemptedAResizeYet) {
					maxWidth = container.offsetWidth;
					maxHeight = container.offsetHeight;
					hasAttemptedAResizeYet = true;
				}
				let startX = e.clientX;
				let startY = e.clientY;
				let startWidth = parseInt(getComputedStyle(container).width, 10);
				let startHeight = parseInt(getComputedStyle(container).height, 10);
				let resize = (e2) => {
					let width = startWidth + (e2.clientX - startX);
					let height = startHeight + (e2.clientY - startY);
					if (dimension === "width" || dimension === "both") {
						if (shrink) container.style.width = `${Math.min(width, maxWidth)}px`;
						else container.style.width = `${width}px`;
					}
					if (dimension === "height" || dimension === "both") {
						if (shrink) container.style.height = `${Math.min(height, maxHeight)}px`;
						else container.style.height = `${height}px`;
					}
				};
				document.addEventListener("pointermove", resize);
				document.addEventListener("pointerup", () => {
					this.releasePointerCapture(e.pointerId);
					document.removeEventListener("pointermove", resize);
				}, { once: true });
				this.setPointerCapture(e.pointerId);
			});
		}
	};
	element("resizable", UIResizable);
	element("grip", UIGrip);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/checkbox.js
	var UICheckboxGroup = class _UICheckboxGroup extends UIControl {
		boot() {
			this._disableable = new Disableable(this);
			let undoDisableds = [];
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) this.walker().each((el) => {
					if (!el.hasAttribute("disabled")) {
						el.setAttribute("disabled", "");
						undoDisableds.push(() => el.removeAttribute("disabled"));
					}
				});
				else {
					undoDisableds.forEach((fn) => fn());
					undoDisableds = [];
				}
			});
			this._selectable = new SelectableGroup(this, { multiple: true });
			this._controllable = new Controllable(this, {
				disabled: this._disabled,
				bubbles: true
			});
			this.walker().each((el) => {
				el.addEventListener("input", (e) => e.stopPropagation());
				el.addEventListener("change", (e) => e.stopPropagation());
			});
			this._submittable = new Submittable(this, {
				name: this.getAttribute("name"),
				value: this.getAttribute("value"),
				includeWhenEmpty: false
			});
			this._controllable.initial((initial) => initial && this._selectable.setState(initial));
			this._controllable.getter(() => this._selectable.getState());
			this._detangled = detangle();
			this._controllable.setter(this._detangled((value3) => {
				this._selectable.setState(value3);
			}));
			this._selectable.onChange(this._detangled(() => {
				this._controllable.dispatch();
			}));
			this._selectable.onInitAndChange(() => {
				this._submittable.update(this._selectable.getState());
			});
			setAttribute2(this, "role", "group");
			queueMicrotask(() => {
				this._submittable.update(this._selectable.getState());
			});
		}
		initCheckAll(checkAll) {
			let detangled = detangle();
			checkAll._selectable.onChange(detangled(() => {
				if (checkAll.indeterminate) {
					this.selectAll();
					checkAll.checked = true;
					checkAll.indeterminate = false;
				} else if (checkAll.checked) {
					this.selectAll();
					checkAll.checked = true;
					checkAll.indeterminate = false;
				} else {
					this.deselectAll();
					checkAll.checked = false;
					checkAll.indeterminate = false;
				}
			}));
			let setCheckAllIndeterminate = () => {
				if (this.allAreSelected()) {
					checkAll.indeterminate = false;
					checkAll._selectable.select();
				} else if (this.noneAreSelected()) {
					checkAll.indeterminate = false;
					checkAll._selectable.deselect();
				} else checkAll.indeterminate = true;
			};
			this._selectable.onChange(detangled(() => {
				setCheckAllIndeterminate();
			}));
			setCheckAllIndeterminate();
		}
		selectAll() {
			this.walker({ checkable: true }).filter((el) => !el.use(Selectable).isSelected()).map((el) => el.use(Selectable).select());
		}
		deselectAll() {
			this.walker({ checkable: true }).filter((el) => el.use(Selectable).isSelected()).map((el) => el.use(Selectable).deselect());
		}
		allAreSelected() {
			let selectableElements = this.walker({ checkable: true }).filter((el) => true);
			return selectableElements.length > 0 && this.walker({ checkable: true }).filter((el) => el.use(Selectable).isSelected()).length === selectableElements.length;
		}
		noneAreSelected() {
			return this.walker({ checkable: true }).filter((el) => el.use(Selectable).isSelected()).length === 0;
		}
		walker({ checkable } = {}) {
			return walker(this, (el, { skip, reject }) => {
				if (el instanceof _UICheckboxGroup) return reject();
				if (!(el.localName === "ui-checkbox")) return skip();
				if (checkable && (el.disabled || el.use(Selectable).ungrouped)) return skip();
			});
		}
	};
	var UICheckbox = class extends UIControl {
		boot() {
			let button = this;
			this.isIndeterminate = false;
			this._disableable = new Disableable(this);
			if (this.hasAttribute("all")) {
				this._selectable = new Selectable(button, {
					ungrouped: true,
					toggleable: true,
					value: this.hasAttribute("value") ? this.getAttribute("value") : Math.random().toString(36).substring(2, 10),
					label: this.hasAttribute("label") ? this.getAttribute("label") : null,
					selectedInitially: this.hasAttribute("checked"),
					dataAttr: "data-checked",
					ariaAttr: "aria-checked"
				});
				queueMicrotask(() => {
					this.closest("ui-checkbox-group")?.initCheckAll(this);
				});
			} else {
				this._selectable = new Selectable(button, {
					toggleable: true,
					dataAttr: "data-checked",
					ariaAttr: "aria-checked",
					value: this.hasAttribute("value") ? this.getAttribute("value") : Math.random().toString(36).substring(2, 10),
					label: this.hasAttribute("label") ? this.getAttribute("label") : null,
					selectedInitially: this.hasAttribute("checked")
				});
				this._submittable = new Submittable(this, {
					name: this.getAttribute("name"),
					value: this.getAttribute("value") ?? "on",
					includeWhenEmpty: false,
					shouldUpdateValue: false
				});
				this._selectable.onChange(() => {
					if (this.indeterminate) this.indeterminate = false;
				});
				this._selectable.onInitAndChange(() => {
					this._submittable.update(this._selectable.isSelected());
				});
				queueMicrotask(() => {
					this._submittable.update(this._selectable.isSelected());
				});
			}
			this._detangled = detangle();
			this._selectable.onChange(this._detangled(() => {
				this.dispatchEvent(new Event("input", {
					bubbles: true,
					cancelable: true
				}));
				this.dispatchEvent(new Event("change", {
					bubbles: true,
					cancelable: true
				}));
			}));
			setAttribute2(button, "role", "checkbox");
			this._disableable.onInitAndChange((disabled) => {
				disabled ? removeAttribute(button, "tabindex", "0") : setAttribute2(button, "tabindex", "0");
			});
			on(button, "click", this._disableable.disabled((e) => {
				e.preventDefault();
				e.stopPropagation();
			}), { capture: true });
			on(button, "click", this._disableable.enabled((e) => {
				this._selectable.press();
			}));
			on(button, "keydown", this._disableable.enabled((e) => {
				if (e.key === "Enter") this.closest("form")?.requestSubmit();
			}));
			on(button, "keydown", this._disableable.enabled((e) => {
				if (e.key === " ") {
					e.preventDefault();
					e.stopPropagation();
				}
			}));
			on(button, "keyup", this._disableable.enabled((e) => {
				if (e.key === " ") {
					this._selectable.press();
					e.preventDefault();
					e.stopPropagation();
				}
			}));
			respondToLabelClick(button);
		}
		get value() {
			return this._selectable?.getValue();
		}
		set value(val) {
			if (!this._selectable) return;
			this._selectable.value = val + "";
			if (this._submittable) this._submittable.value = val + "";
		}
		get checked() {
			return this._selectable.isSelected();
		}
		set checked(value3) {
			let groupDetangled = this.closest("ui-checkbox-group")?._detangled || ((i) => i);
			this._detangled(groupDetangled(() => {
				value3 ? this._selectable.select() : this._selectable.deselect();
			}))();
		}
		get indeterminate() {
			return this.isIndeterminate;
		}
		set indeterminate(value3) {
			this.isIndeterminate = !!value3;
			if (this.isIndeterminate) setAttribute2(this, "data-indeterminate", "");
			else removeAttribute(this, "data-indeterminate");
		}
	};
	element("checkbox-group", UICheckboxGroup);
	element("checkbox", UICheckbox);
	function respondToLabelClick(el) {
		el.closest("label")?.addEventListener("click", (e) => {
			if (!el.contains(e.target)) el._selectable.press();
		});
	}
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/dropdown.js
	var UIDropdown = class extends UIElement {
		boot() {
			let trigger = this.trigger();
			let overlay = this.overlay();
			let isTooltip = this.hasAttribute("data-os-tooltip") || this.hasAttribute("data-os-tooltip");
			if (!trigger) return console.warn("ui-dropdown: no trigger element found", this);
			else if (!overlay) return console.warn("ui-dropdown: no [popover] overlay found", this);
			this._disabled = this.hasAttribute("disabled");
			this._controllable = new Controllable(this);
			overlay._popoverable = new Popoverable(overlay);
			overlay._anchorable = new Anchorable(overlay, {
				reference: trigger,
				position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
				gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
				offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0,
				onReferenceHidden: isTooltip ? () => overlay._popoverable.hide() : null
			});
			overlay._popoverable.onChange(() => {
				overlay._popoverable.getState() ? overlay._anchorable.reposition() : overlay._anchorable.cleanup();
			});
			this._locksScroll = !this.hasAttribute("hover") && !isTooltip;
			if (this._locksScroll) {
				let { lock, unlock } = lockScroll(overlay._popoverable.el);
				overlay._popoverable.onChange(() => {
					overlay._popoverable.getState() ? lock() : unlock();
				});
			}
			this._controllable.initial((initial) => overlay._popoverable.setState(initial));
			this._controllable.getter(() => overlay._popoverable.getState());
			let detangled = detangle();
			this._controllable.setter((value3) => overlay._popoverable.setState(value3));
			overlay._popoverable.onChange(detangled(() => this._controllable.dispatch()));
			if (this.hasAttribute("hover")) {
				let off = () => {};
				interest(trigger, overlay, {
					gain() {
						overlay._openedViaHover = true;
						overlay._popoverable.setState(true);
						off = on(document, "scroll", () => {
							if (overlay._popoverable.getState()) {
								overlay._popoverable.setState(false);
								off();
							}
						}).off;
					},
					lose() {
						overlay._popoverable.setState(false);
						off();
					},
					focusable: false
				});
			}
			on(trigger, "click", () => overlay._popoverable.toggle());
			closeNavmenuOverlayBeforeNavigation(overlay);
			if (overlay._popoverable.getState()) {
				setAttribute2(this, "data-open", "");
				setAttribute2(trigger, "data-open", "");
				setAttribute2(overlay, "data-open", "");
			} else {
				removeAttribute(this, "data-open");
				removeAttribute(trigger, "data-open");
				removeAttribute(overlay, "data-open");
			}
			overlay._popoverable.onChange(() => {
				if (overlay._popoverable.getState()) {
					setAttribute2(this, "data-open", "");
					setAttribute2(trigger, "data-open", "");
					setAttribute2(overlay, "data-open", "");
				} else {
					removeAttribute(this, "data-open");
					removeAttribute(trigger, "data-open");
					removeAttribute(overlay, "data-open");
				}
			});
			let id = assignId(overlay, "dropdown");
			setAttribute2(trigger, "aria-haspopup", "true");
			setAttribute2(trigger, "aria-controls", id);
			setAttribute2(trigger, "aria-expanded", overlay._popoverable.getState() ? "true" : "false");
			overlay._popoverable.onChange(() => {
				setAttribute2(trigger, "aria-expanded", overlay._popoverable.getState() ? "true" : "false");
			});
			overlay._popoverable.onChange(() => {
				setTimeout(() => overlay._popoverable.getState() ? overlay.onPopoverShow?.() : overlay.onPopoverHide?.());
			});
		}
		unmount() {
			if (this.overlay()?._popoverable?.getState() && this._locksScroll) {
				let { unlock } = lockScroll();
				unlock();
			}
		}
		trigger() {
			return this.querySelector("button,ui-button,a");
		}
		overlay() {
			return this.lastElementChild?.matches("[popover]") && this.lastElementChild;
		}
	};
	element("dropdown", UIDropdown);
	function closeNavmenuOverlayBeforeNavigation(overlay) {
		if (!overlay.matches("[data-os-navmenu]")) return;
		let close = (target) => {
			if (!target.closest?.("[data-os-navmenu-item]")) return;
			overlay._popoverable.hide();
		};
		on(overlay, "mouseup", (e) => close(e.target));
		on(overlay, "click", (e) => !e.isTrusted && close(e.target));
	}
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/file-upload.js
	var UIFileUpload = class extends UIElement {
		boot() {
			this.type = "file";
		}
		mount() {
			this._disableable = new Disableable(this);
			this._files = [];
			this.inputEl = this.querySelector("input[data-slot=\"receiver\"]");
			if (this.inputEl) {
				this.forwardAcceptAttribute();
				this.configureSRInput();
			}
			this.triggerEl = this.findTriggerElement();
			this.rootEl = this.closest("ui-file-upload");
			let hasMultiple = this.hasAttribute("multiple") || this.rootEl && this.rootEl.hasAttribute("multiple");
			if (hasMultiple) setAttribute2(this.inputEl, "multiple", "");
			Object.defineProperty(this, "multiple", { get() {
				return hasMultiple;
			} });
			Object.defineProperty(this, "files", {
				get() {
					return this._files;
				},
				set(newValue) {
					if (newValue === null || newValue === "") this._files = [];
					else if (Array.isArray(newValue)) this._files = newValue;
					else if (newValue instanceof FileList) this._files = Array.from(newValue);
					else this._files = [newValue];
					this.dispatchEvent(new Event("change", { bubbles: true }));
				}
			});
			this.observer = new MutationObserver(() => {
				this.ensureInputsExist();
			});
			this.observer.observe(this, { childList: true });
			this.style.setProperty("--os-file-upload-progress", "0%");
			this.style.setProperty("--os-file-upload-progress-as-string", "'0%'");
			on(this, "livewire-upload-start", () => {
				setAttribute2(this, "data-loading", "");
				this.disabled = true;
				this.style.setProperty("--os-file-upload-progress", "0");
				this.style.setProperty("--os-file-upload-progress-as-string", "'0%'");
			});
			on(this, "livewire-upload-finish", () => {
				removeAttribute(this, "data-loading");
				this.disabled = false;
				this.style.setProperty("--os-file-upload-progress", "100%");
				this.style.setProperty("--os-file-upload-progress-as-string", "'100%'");
			});
			on(this, "livewire-upload-cancel", () => {
				removeAttribute(this, "data-loading");
				this.disabled = false;
				this.style.setProperty("--os-file-upload-progress", "0%");
				this.style.setProperty("--os-file-upload-progress-as-string", "'0%'");
			});
			on(this, "livewire-upload-error", () => {
				removeAttribute(this, "data-loading");
				this.disabled = false;
				this.style.setProperty("--os-file-upload-progress", "0%");
				this.style.setProperty("--os-file-upload-progress-as-string", "'0%'");
			});
			on(this, "livewire-upload-progress", (e) => {
				let percentage = e.detail.progress;
				this.style.setProperty("--os-file-upload-progress", `${percentage}%`);
				this.style.setProperty("--os-file-upload-progress-as-string", `'${percentage}%'`);
			});
			this._disableable.onInitAndChange((disabled) => {
				this.inputEl.disabled = disabled;
				if (this.triggerEl) {
					if (disabled) removeAttribute(this.triggerEl, "tabindex");
					else setAttribute2(this.triggerEl, "tabindex", "0");
				}
			});
			on(this.inputEl, "click", (e) => {
				e.stopPropagation();
				this.clear();
			});
			this.initDragListeners();
			this.initClickListeners();
		}
		addFiles(newFiles) {
			let files = Array.from(newFiles);
			if (this.multiple) this._files = [...this._files, ...files];
			else this._files = files.slice(0, 1);
			this.dispatchEvent(new Event("change", { bubbles: true }));
		}
		removeFile(index) {
			this._files = this._files.filter((_, i) => i !== index);
			this.dispatchEvent(new Event("change", { bubbles: true }));
		}
		clear() {
			this.inputEl.value = null;
			this._files = [];
		}
		forwardAcceptAttribute() {
			if (this.hasAttribute("accept")) this.inputEl.setAttribute("accept", this.getAttribute("accept"));
		}
		configureSRInput() {
			this.inputEl.tabIndex = -1;
			Object.assign(this.inputEl.style, {
				position: "absolute",
				width: "1px",
				height: "1px",
				padding: "0",
				margin: "-1px",
				overflow: "hidden",
				clip: "rect(0, 0, 0, 0)",
				whiteSpace: "nowrap",
				border: "0"
			});
		}
		findTriggerElement() {
			return Array.from(this.children).find((child) => !child.hasAttribute("data-slot"));
		}
		ensureInputsExist() {
			if (!this.inputEl || !this.querySelector("input[data-slot=\"receiver\"]")) {
				this.inputEl = this.querySelector("input[data-slot=\"receiver\"]");
				if (this.inputEl) {
					this.configureSRInput();
					if (this.hasAttribute("multiple") || this.rootEl && this.rootEl.hasAttribute("multiple")) setAttribute2(this.inputEl, "multiple", "");
					this.inputEl.disabled = this.disabled;
				}
			}
			if (!this.triggerEl || !this.contains(this.triggerEl)) {
				this.triggerEl = this.findTriggerElement();
				if (this.triggerEl && !this.disabled) setAttribute2(this.triggerEl, "tabindex", "0");
				else if (this.triggerEl && this.disabled) removeAttribute(this.triggerEl, "tabindex");
			}
		}
		initDragListeners() {
			if (!this.triggerEl) return;
			on(this.triggerEl, "dragenter", this._disableable.enabled((e) => {
				e.preventDefault();
				setAttribute2(this, "data-dragging", "");
				this.clear();
			}));
			on(this.triggerEl, "dragover", this._disableable.enabled((e) => {
				e.preventDefault();
				setAttribute2(this, "data-dragging", "");
			}));
			on(this.triggerEl, "drop", this._disableable.enabled((e) => {
				e.preventDefault();
				removeAttribute(this, "data-dragging");
				if (e.dataTransfer.files && e.dataTransfer.files.length > 0) this.addFiles(e.dataTransfer.files);
			}));
			on(this.triggerEl, "dragleave", (e) => {
				removeAttribute(this, "data-dragging");
			});
		}
		initClickListeners() {
			if (!this.triggerEl) return;
			on(this.triggerEl, "click", this._disableable.disabled((e) => {
				e.preventDefault();
				e.stopPropagation();
			}), { capture: true });
			on(this.triggerEl, "click", this._disableable.enabled((e) => {
				e.preventDefault();
				this.inputEl.click();
			}));
			on(this.inputEl, "change", (e) => {
				e.stopPropagation();
				if (this.inputEl.files && this.inputEl.files.length > 0 && !this.disabled) this.addFiles(this.inputEl.files);
			});
			on(this.triggerEl, "keydown", this._disableable.enabled((e) => {
				if (["Enter", " "].includes(e.key)) e.preventDefault();
				if (["Enter"].includes(e.key)) this.inputEl.click();
			}));
			on(this.triggerEl, "keyup", this._disableable.enabled((e) => {
				if (e.key === " ") {
					e.preventDefault();
					this.inputEl.click();
				}
			}));
			on(this.triggerEl, "focus", this._disableable.enabled(() => {
				setAttribute2(this, "data-focus", "");
			}));
			on(this.triggerEl, "blur", () => {
				removeAttribute(this, "data-focus");
			});
		}
	};
	element("file-upload", UIFileUpload);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/composer.js
	var UIComposer = class extends UIControl {
		boot() {
			setAttribute2(this, "role", "group");
			this.config = {
				submit: this.getAttribute("submit") || "cmd-enter",
				rows: Number(this.getAttribute("rows")) || 2,
				maxRows: Number(this.getAttribute("max-rows")) || 10
			};
			let textarea = this.querySelector("textarea");
			let editor = this.querySelector("ui-editor");
			if (textarea) {
				this._inputEl = textarea;
				this._initTextarea();
			} else {
				this._inputEl = editor;
				this._initEditor();
			}
			this.state = {
				onChanges: [],
				getValue: () => {
					return this._inputEl.value;
				},
				setValue: (value3) => {
					this._inputEl.value = value3;
					this.state.onChanges.forEach((i) => i(this.state.getValue()));
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
				this._submittable.update(value3);
			});
			this._controllable.initial((initial) => initial && this.state.setValue(initial));
			this._controllable.getter(() => this.state.getValue());
			this._controllable.setter((v) => this.state.setValue(v));
			let undoDisableActions = () => {};
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) {
					undoDisableActions = this._disableActions();
					setAttribute2(this._inputEl, "disabled", "");
				} else {
					undoDisableActions();
					undoDisableActions = () => {};
					removeAttribute(this._inputEl, "disabled");
				}
			});
			on(this._inputEl, "input", (e) => {
				this._controllable.dispatch();
				e.stopPropagation();
			});
		}
		_resizeTextarea() {
			setAttribute2(this._inputEl, "style", "height: auto");
			let style = window.getComputedStyle(this._inputEl);
			let height = this._inputEl.scrollHeight;
			let lineHeight = parseFloat(style.lineHeight);
			if (isNaN(lineHeight)) lineHeight = parseFloat(style.fontSize) * 1.2;
			let borderTop = parseFloat(style.borderTopWidth);
			let borderBottom = parseFloat(style.borderBottomWidth);
			let paddingTop = parseFloat(style.paddingTop);
			let paddingBottom = parseFloat(style.paddingBottom);
			let minHeight = this.config.rows * lineHeight;
			let maxHeight = this.config.maxRows * lineHeight;
			if (style.boxSizing === "border-box") {
				height += borderTop + borderBottom;
				minHeight += paddingTop + paddingBottom + borderTop + borderBottom;
				maxHeight += paddingTop + paddingBottom + borderTop + borderBottom;
			} else height -= paddingTop + paddingBottom;
			if (height < minHeight) height = minHeight;
			let overflow = "hidden";
			if (height > maxHeight) {
				height = maxHeight;
				overflow = "auto";
			}
			setAttribute2(this._inputEl, "style", `height: ${height}px; overflow-y: ${overflow}`);
		}
		focusInput() {
			this._inputEl.focus();
		}
		_resizeEditor() {
			let contentEl = this._inputEl.querySelector("[data-slot=\"content\"]");
			if (!contentEl) return;
			let style = window.getComputedStyle(contentEl);
			let lineHeight = parseFloat(style.lineHeight);
			if (isNaN(lineHeight)) lineHeight = parseFloat(style.fontSize) * 1.2;
			let borderTop = parseFloat(style.borderTopWidth);
			let borderBottom = parseFloat(style.borderBottomWidth);
			let paddingTop = parseFloat(style.paddingTop);
			let paddingBottom = parseFloat(style.paddingBottom);
			let minHeight = this.config.rows * lineHeight;
			let maxHeight = this.config.maxRows * lineHeight;
			if (style.boxSizing === "border-box") {
				minHeight += paddingTop + paddingBottom + borderTop + borderBottom;
				maxHeight += paddingTop + paddingBottom + borderTop + borderBottom;
			}
			setAttribute2(contentEl, "style", `min-height: ${minHeight}px; max-height: ${maxHeight}px; height: auto; overflow-y: auto;`);
		}
		_initTextarea() {
			if (!this._inputEl.hasAttribute("rows")) setAttribute2(this._inputEl, "rows", this.config.rows);
			if (this.hasAttribute("placeholder")) setAttribute2(this._inputEl, "placeholder", this.getAttribute("placeholder"));
			this._inputEl.addEventListener("input", () => this._resizeTextarea());
			this._resizeTextarea();
			on(this._inputEl, "keydown", (e) => this._handleSubmitKeydown(e));
		}
		_initEditor() {
			if (this.hasAttribute("placeholder")) setAttribute2(this._inputEl, "placeholder", this.getAttribute("placeholder"));
			this.addEventListener("os:editor:ready", () => {
				this._resizeEditor();
			});
			on(this._inputEl, "keydown", (e) => this._handleSubmitKeydown(e), { capture: true });
		}
		_handleSubmitKeydown(e) {
			if ([
				"cmd-enter",
				"super-enter",
				"ctrl-enter"
			].includes(this.config.submit) && (e.metaKey || e.ctrlKey) && e.key === "Enter") {
				e.preventDefault();
				e.stopPropagation();
				this._submittable.submitEnclosingForm();
			} else if (this.config.submit === "enter" && e.key === "Enter" && !e.shiftKey) {
				e.preventDefault();
				e.stopPropagation();
				this._submittable.submitEnclosingForm();
			} else if (this.config.submit === "shift-enter" && e.key === "Enter" && e.shiftKey) {
				e.preventDefault();
				e.stopPropagation();
				this._submittable.submitEnclosingForm();
			}
		}
		_disableActions() {
			let undos = [];
			this.querySelectorAll("button").forEach((el) => {
				if (!el.hasAttribute("disabled")) {
					setAttribute2(el, "disabled", "");
					undos.push(() => {
						removeAndReleaseAttribute(el, "disabled");
					});
				}
			});
			return () => {
				undos.forEach((undo) => undo());
				undos = [];
			};
		}
	};
	element("composer", UIComposer);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/progress.js
	var UIProgress = class extends UIElement {
		boot() {
			this._controllable = new Controllable(this);
			this._controllable.initial((initial) => initial != null && this.setValue(parseFloat(initial)));
			this._controllable.getter(() => this.currentValue);
			this._controllable.setter((v) => this.setValue(parseFloat(v)));
			setAttribute2(this, "role", "progressbar");
			setAttribute2(this, "aria-valuemin", 0);
			if (this.currentValue === void 0) this.setValue(this.hasAttribute("value") ? parseFloat(this.getAttribute("value")) : 0);
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
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/context.js
	var UIContext = class extends UIElement {
		boot() {
			let trigger = this.trigger();
			let overlay = this.overlay();
			this._disabled = this.hasAttribute("disabled");
			this._popoverable = new Popoverable(overlay);
			let virtualReference = { getBoundingClientRect: () => ({
				x: 0,
				y: 0,
				width: 0,
				height: 0,
				top: 0,
				left: 0,
				right: 0,
				bottom: 0
			}) };
			this._anchorable = new Anchorable(overlay, {
				reference: virtualReference,
				auto: false,
				position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
				gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
				offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
			});
			let { lock, unlock } = lockScroll(this._popoverable.el);
			this._popoverable.onChange(() => {
				this._popoverable.getState() ? lock() : unlock();
			});
			on(trigger, "contextmenu", (e) => {
				e.preventDefault();
				virtualReference.getBoundingClientRect = () => ({
					x: e.clientX,
					y: e.clientY,
					width: 0,
					height: 0,
					top: e.clientY,
					left: e.clientX,
					right: e.clientX,
					bottom: e.clientY
				});
				this._anchorable.reposition();
				this._popoverable.setState(true);
				if (this.hasAttribute("detail")) setAttribute2(overlay, "data-detail", this.getAttribute("detail"));
			});
			if (this.hasAttribute("detail")) setAttribute2(overlay, "data-detail", "");
		}
		unmount() {
			if (this.overlay()?._popoverable?.getState()) {
				let { unlock } = lockScroll();
				unlock();
			}
		}
		trigger() {
			return this.firstElementChild;
		}
		overlay() {
			if (this.hasAttribute("target")) {
				let target = this.getAttribute("target");
				return document.getElementById(target);
			} else return this.lastElementChild.matches("[popover]") && this.lastElementChild;
		}
	};
	element("context", UIContext);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/menu.js
	var UIMenu$1 = class _UIMenu extends UIElement {
		boot() {
			this._focusable = new FocusableGroup(this, {
				wrap: false,
				ensureTabbable: false
			});
			on(this, "keydown", (e) => {
				if (["ArrowDown"].includes(e.key)) {
					e.target === this ? this._focusable.focusFirst() : this._focusable.focusNext();
					e.preventDefault();
					e.stopPropagation();
				} else if (["ArrowUp"].includes(e.key)) {
					e.target === this ? this._focusable.focusFirst() : this._focusable.focusPrev();
					e.preventDefault();
					e.stopPropagation();
				}
			});
			search(this, (query) => this._focusable.focusBySearch(query));
			if (this.hasAttribute("popover")) this.addEventListener("lofi-close-popovers", (e) => {
				if (this.hasAttribute("keep-open")) return;
				if (e.detail?.immediate) this.hidePopover();
				else setTimeout(() => this.hidePopover(), 50);
			});
			if (this.parentElement.localName === "ui-dropdown") {
				let dropdown = this.parentElement;
				on(dropdown.trigger(), "keydown", (e) => {
					if (e.key === "ArrowDown") {
						this.fromArrowDown = true;
						this.showPopover();
						e.preventDefault();
						e.stopPropagation();
					} else if (dropdown.hasAttribute("data-os-sidebar-group-dropdown") && ["Enter", " "].includes(e.key)) this.fromCollapsedSidebarGroupTrigger = !this.matches(":popover-open");
				});
			}
			setAttribute2(this, "role", "menu");
			setAttribute2(this, "tabindex", "-1");
		}
		mount() {
			this.initializeMenuItems();
			new MutationObserver((mutations) => {
				this.initializeMenuItems();
			}).observe(this, {
				childList: true,
				subtree: true
			});
		}
		onPopoverShow() {
			requestAnimationFrame(() => {
				let openedViaHover = this._openedViaHover;
				this._openedViaHover = false;
				if (openedViaHover) return;
				if (this.fromArrowDown || this.fromCollapsedSidebarGroupTrigger) {
					this._focusable.focusFirst();
					this.fromArrowDown = false;
					this.fromCollapsedSidebarGroupTrigger = false;
				} else this.focus();
			});
		}
		onPopoverHide() {
			this._focusable.wipeTabbables();
		}
		initializeMenuItems() {
			this.walker().each((el) => {
				if (el._disableable) return;
				initializeMenuItem(el);
			});
		}
		walker() {
			return walker(this, (el, { skip, reject }) => {
				if (el instanceof _UIMenu) return reject();
				if (el instanceof UISelect) return reject();
				if (!["a", "button"].includes(el.localName)) return skip();
			});
		}
	};
	var UISubmenu = class extends UIElement {
		boot() {}
	};
	var UIMenuCheckbox = class extends UIElement {
		boot() {
			let button = this;
			this._disableable = new Disableable(this);
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) setAttribute2(button, "aria-disabled", "true");
				else removeAttribute(button, "aria-disabled");
			});
			assignId(button, "menu-checkbox");
			setAttribute2(button, "role", "menuitemcheckbox");
			button._focusable = new Focusable(button, {
				disableable: this._disableable,
				hover: true,
				tabbableAttr: "data-active"
			});
			button._selectable = new Selectable(button, {
				toggleable: true,
				value: this.hasAttribute("value") ? this.getAttribute("value") : button.textContent.trim(),
				label: this.hasAttribute("label") ? this.getAttribute("label") : button.textContent.trim(),
				dataAttr: "data-checked",
				ariaAttr: "aria-checked",
				selectedInitially: this.hasAttribute("checked")
			});
			this._controllable = new Controllable(this);
			this._controllable.initial((initial) => initial && button._selectable.setState(initial));
			this._controllable.getter(() => button._selectable.getState());
			let detangled = detangle();
			this._controllable.setter(detangled((value3) => {
				this._selectable.setState(value3);
			}));
			this._selectable.onChange(detangled(() => {
				this._controllable.dispatch();
			}));
			on(button, "click", this._disableable.enabled(() => {
				if (!this.hasAttribute("keep-open")) this.dispatchEvent(new CustomEvent("lofi-close-popovers", { bubbles: true }));
				button._selectable.press();
			}));
			respondToKeyboardClick(button);
		}
	};
	var UIMenuRadio = class extends UIElement {
		boot() {
			let button = this;
			this._disableable = new Disableable(this);
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) setAttribute2(button, "aria-disabled", "true");
				else removeAttribute(button, "aria-disabled");
			});
			assignId(button, "menu-radio");
			setAttribute2(button, "role", "menuitemradio");
			button._focusable = new Focusable(button, {
				disableable: this._disableable,
				hover: true,
				tabbableAttr: "data-active"
			});
			button._selectable = new Selectable(button, {
				toggleable: false,
				value: this.hasAttribute("value") ? this.getAttribute("value") : button.textContent.trim(),
				label: this.hasAttribute("label") ? this.getAttribute("label") : button.textContent.trim(),
				dataAttr: "data-checked",
				ariaAttr: "aria-checked",
				selectedInitially: this.hasAttribute("checked")
			});
			on(button, "click", this._disableable.enabled(() => {
				if (!this.hasAttribute("keep-open")) this.dispatchEvent(new CustomEvent("lofi-close-popovers", { bubbles: true }));
				button._selectable.press();
			}));
			respondToKeyboardClick(button);
		}
	};
	var UIMenuRadioGroup = class extends UIElement {
		boot() {
			this._selectable = new SelectableGroup(this);
			this._controllable = new Controllable(this);
			setAttribute2(this, "role", "group");
			this._controllable.initial((initial) => initial && this._selectable.setState(initial));
			this._controllable.getter(() => this._selectable.getState());
			let detangled = detangle();
			this._controllable.setter(detangled((value3) => {
				this._selectable.setState(value3);
			}));
			this._selectable.onChange(detangled(() => {
				this._controllable.dispatch();
			}));
			on(this, "lofi-close-popovers", (e) => {
				if (this.hasAttribute("keep-open")) {
					e.preventDefault();
					e.stopPropagation();
				}
			});
		}
	};
	var UIMenuCheckboxGroup = class extends UIElement {
		boot() {
			this._selectable = new SelectableGroup(this, { multiple: true });
			this._controllable = new Controllable(this);
			setAttribute2(this, "role", "group");
			this._controllable.initial((initial) => initial && this._selectable.setState(initial));
			this._controllable.getter(() => this._selectable.getState());
			let detangled = detangle();
			this._controllable.setter(detangled((value3) => {
				this._selectable.setState(value3);
			}));
			this._selectable.onChange(detangled(() => {
				this._controllable.dispatch();
			}));
		}
	};
	element("menu", UIMenu$1);
	element("submenu", UISubmenu);
	element("menu-checkbox", UIMenuCheckbox);
	element("menu-radio", UIMenuRadio);
	element("menu-radio-group", UIMenuRadioGroup);
	element("menu-checkbox-group", UIMenuCheckboxGroup);
	function respondToKeyboardClick(el) {
		on(el, "keydown", (e) => {
			if (e.key === "Enter") {
				el.click();
				e.preventDefault();
				e.stopPropagation();
			}
		});
		on(el, "keydown", (e) => {
			if (e.key === " ") {
				e.preventDefault();
				e.stopPropagation();
			}
		});
		on(el, "keyup", (e) => {
			if (e.key === " ") {
				el.click();
				e.preventDefault();
				e.stopPropagation();
			}
		});
	}
	function initializeMenuItem(el) {
		let link = el.querySelector("a");
		let button = el;
		let submenu = el.parentElement.matches("ui-submenu") && el.parentElement.querySelector("ui-menu[popover]");
		let target = link || button;
		el._disableable = new Disableable(el);
		el._disableable.onInitAndChange((disabled) => {
			if (disabled) setAttribute2(target, "aria-disabled", "true");
			else removeAttribute(target, "aria-disabled");
		});
		assignId(target, "menu-item");
		setAttribute2(target, "role", "menuitem");
		target._focusable = new Focusable(target, {
			disableable: el._disableable,
			hover: true,
			tabbableAttr: "data-active"
		});
		if (!submenu) {
			let handler = el._disableable.enabled(() => {
				if (!el.hasAttribute("keep-open")) el.dispatchEvent(new CustomEvent("lofi-close-popovers", {
					bubbles: true,
					detail: { immediate: true }
				}));
			});
			on(el, "mouseup", handler);
			on(el, "click", (e) => !e.isTrusted && handler());
			respondToKeyboardClick(button);
		} else {
			submenu._popoverable = new Popoverable(submenu, { triggers: [button] });
			submenu._anchorable = new Anchorable(submenu, {
				reference: button,
				position: submenu.hasAttribute("position") ? submenu.getAttribute("position") : isRTL(submenu) ? "left start" : "right start",
				gap: submenu.hasAttribute("gap") ? submenu.getAttribute("gap") : "-5",
				crossAxis: true
			});
			button.addEventListener("click", el._disableable.enabled((e) => {
				submenu._popoverable.setState(true);
			}));
			let { clear } = interest(button, submenu, {
				gain() {
					submenu._popoverable.setState(true);
				},
				lose() {
					submenu._popoverable.setState(false);
				},
				focusable: false,
				useSafeArea: true
			});
			submenu._popoverable.onChange(() => {
				if (!submenu._popoverable.getState()) {
					clear();
					submenu._focusable.wipeTabbables();
				}
				submenu._popoverable.getState() ? submenu._anchorable.reposition() : submenu._anchorable.cleanup();
			});
			on(button, "keydown", (e) => {
				if (e.key === "Enter") {
					submenu._popoverable.setState(true);
					setTimeout(() => submenu._focusable.focusFirst());
				}
			});
			on(button, "keydown", (e) => {
				if (e.key === "ArrowRight") {
					submenu._popoverable.setState(true);
					setTimeout(() => submenu._focusable.focusFirst());
				}
			});
			on(submenu, "keydown", (e) => {
				if (e.key === "ArrowLeft") {
					submenu._popoverable.setState(false);
					button.focus();
					e.stopPropagation();
				}
			});
		}
	}
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/toolbar.js
	var UIToolbar = class _UIToolbar extends UIElement {
		mount() {
			this._focusable = new FocusableGroup(this, { wrap: true });
			this._disableable = new Disableable(this);
			let undoDisableds = [];
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) {
					setAttribute2(this, "aria-disabled", "true");
					this.walker().each((el) => {
						if (!el.hasAttribute("disabled")) {
							setAttribute2(el, "disabled", "true");
							setAttribute2(el, "aria-disabled", "true");
							undoDisableds.push(() => {
								removeAndReleaseAttribute(el, "disabled");
								removeAndReleaseAttribute(el, "aria-disabled");
							});
						}
					});
				} else {
					removeAndReleaseAttribute(this, "aria-disabled");
					undoDisableds.forEach((fn) => fn());
					undoDisableds = [];
				}
			});
			on(this, "keydown", (e) => {
				if (["ArrowRight"].includes(e.key)) {
					e.target === this ? this._focusable.focusFirst() : this._focusable.focusNext();
					e.preventDefault();
					e.stopPropagation();
				} else if (["ArrowLeft"].includes(e.key)) {
					e.target === this ? this._focusable.focusFirst() : this._focusable.focusPrev();
					e.preventDefault();
					e.stopPropagation();
				}
			});
			setAttribute2(this, "role", "toolbar");
			this.initializeToolbarItems();
		}
		initializeToolbarItems() {
			this.walker().each((el) => {
				if (el._disableable) return;
				initializeToolbarItem(el);
			});
		}
		walker() {
			return walker(this, (el, { skip, reject }) => {
				if (el instanceof _UIToolbar) return reject();
				if (el.hasAttribute("popover")) return reject();
				if (el instanceof UIMenu) return reject();
				if (el instanceof UIOptions) return reject();
				if (!["a", "button"].includes(el.localName)) return skip();
			});
		}
	};
	element("toolbar", UIToolbar);
	function initializeToolbarItem(el) {
		el._disableable = new Disableable(el);
		el._disabled = el.hasAttribute("disabled");
		let target = el.querySelector("a") || el;
		if (el._disabled) {
			setAttribute2(target, "disabled", "");
			setAttribute2(target, "aria-disabled", "true");
		}
		if (el._disabled) return;
		target._focusable = new Focusable(target, { disableable: el._disableable });
	}
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/tooltip.js
	var UITooltip = class extends UIElement {
		boot() {
			this.hasAttribute("label");
			let button = this.button();
			let overlay = this.overlay();
			if (!button) return console.warn("ui-tooltip: no trigger element found", this);
			else if (!overlay) return;
			overlay._popoverable = new Popoverable(overlay, { scope: "tooltip" });
			overlay._anchorable = new Anchorable(overlay, {
				reference: button,
				position: this.hasAttribute("position") ? this.getAttribute("position") : void 0,
				gap: this.hasAttribute("gap") ? this.getAttribute("gap") : void 0,
				offset: this.hasAttribute("offset") ? this.getAttribute("offset") : void 0
			});
			overlay._popoverable.onChange(() => {
				overlay._popoverable.getState() ? overlay._anchorable.reposition() : overlay._anchorable.cleanup();
			});
			this._disableable = new Disableable(this);
			new MutationObserver(() => {
				if (this.getAttribute("draggable") === "true") overlay._popoverable.setState(false);
			}).observe(this, { attributeFilter: ["draggable"] });
			let id = assignId(overlay, "tooltip");
			let interactive = this.hasAttribute("interactive");
			let wantsLabel = this.hasAttribute("label") || button.textContent.trim() === "";
			if (interactive) {
				setAttribute2(button, "aria-controls", id);
				setAttribute2(button, "aria-expanded", "false");
				overlay._popoverable.onChange(() => {
					overlay._popoverable.getState() ? setAttribute2(button, "aria-expanded", "true") : setAttribute2(button, "aria-expanded", "false");
				});
			} else {
				if (wantsLabel) setAttribute2(button, "aria-labelledby", id);
				else setAttribute2(button, "aria-describedby", id);
				setAttribute2(overlay, "aria-hidden", "true");
			}
			setAttribute2(overlay, "role", "tooltip");
		}
		mount() {
			if (!this._disableable) return;
			let overlay = this.overlay();
			let removeInterest;
			this._disableable.onInitAndChange((disabled) => {
				if (removeInterest) {
					removeInterest();
					removeInterest = null;
				}
				if (!disabled) removeInterest = interest(this, overlay, {
					gain() {
						overlay._popoverable.setState(true);
					},
					lose() {
						overlay._popoverable.setState(false);
					},
					focusable: true,
					useSafeArea: false
				}).remove;
			});
			this.onUnmount(() => {
				if (removeInterest) {
					removeInterest();
					removeInterest = null;
				}
			});
		}
		button() {
			return this.firstElementChild;
		}
		overlay() {
			return this.lastElementChild !== this.button() && this.lastElementChild.tagName !== "TEMPLATE" && this.lastElementChild;
		}
	};
	element("tooltip", UITooltip);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/sidebar.js
	var EVENTS = {
		STATE_CHANGED: "stateChanged",
		DESKTOP_COLLAPSED: "desktopCollapsed",
		DESKTOP_EXPANDED: "desktopExpanded",
		MOBILE_COLLAPSED: "mobileCollapsed",
		MOBILE_EXPANDED: "mobileExpanded",
		VIEWPORT_ENTER_MOBILE: "viewportEnterMobile",
		VIEWPORT_ENTER_DESKTOP: "viewportEnterDesktop"
	};
	var UISidebar = class extends UIElement {
		boot() {
			this.config = {
				breakpoint: this.hasAttribute("breakpoint") ? this.getAttribute("breakpoint") : 1024,
				collapsible: false,
				persist: this.hasAttribute("persist") ? ["false", "none"].includes(this.getAttribute("persist")) ? false : true : true,
				sticky: this.hasAttribute("sticky") ? true : false
			};
			this.observable = new Observable();
			this.state = {
				active: false,
				viewportDesktop: true,
				viewportMobile: false,
				collapsedMobile: true,
				collapsedDesktop: false
			};
			if (this.config.sticky) this.setStickyPositionStyles();
			if (this.hasAttribute("collapsible")) {
				let value3 = this.getAttribute("collapsible");
				if (value3 === "true") this.config.collapsible = true;
				else if (value3 === "false") this.config.collapsible = false;
				else if (value3 === "mobile") this.config.collapsible = "mobile";
			}
			if (this.config.persist && this.config.collapsible) this.state.collapsedDesktop = JSON.parse(localStorage.getItem("os-sidebar-collapsed-desktop"));
			this.removeAttribute("data-os-sidebar-cloak");
			this.observable.subscribe(EVENTS.VIEWPORT_ENTER_DESKTOP, () => {
				let reapplyTransition = setStyle(this, "transition", "none");
				setTimeout(reapplyTransition);
				this.state.viewportDesktop = true;
				this.state.viewportMobile = false;
				this.observable.notify(EVENTS.STATE_CHANGED);
			});
			this.observable.subscribe(EVENTS.VIEWPORT_ENTER_MOBILE, () => {
				let reapplyTransition = setStyle(this, "transition", "none");
				setTimeout(reapplyTransition);
				this.state.viewportDesktop = false;
				this.state.viewportMobile = true;
				this.observable.notify(EVENTS.STATE_CHANGED);
			});
			this.observable.subscribe(EVENTS.DESKTOP_COLLAPSED, () => {
				this.state.collapsedDesktop = true;
				this.observable.notify(EVENTS.STATE_CHANGED);
			});
			this.observable.subscribe(EVENTS.DESKTOP_EXPANDED, () => {
				this.state.collapsedDesktop = false;
				this.observable.notify(EVENTS.STATE_CHANGED);
			});
			this.observable.subscribe(EVENTS.MOBILE_COLLAPSED, () => {
				this.state.collapsedMobile = true;
				this.observable.notify(EVENTS.STATE_CHANGED);
			});
			this.observable.subscribe(EVENTS.MOBILE_EXPANDED, () => {
				this.state.collapsedMobile = false;
				this.observable.notify(EVENTS.STATE_CHANGED);
			});
			this.observable.subscribe(EVENTS.STATE_CHANGED, () => {
				if (this.config.persist) localStorage.setItem("os-sidebar-collapsed-desktop", JSON.stringify(this.state.collapsedDesktop));
				this.updateDataAttributes(this);
			});
			let viewportObserver = new ViewportResizeObserver(this.observable, this.config);
			this.onUnmount(() => viewportObserver.disconnect());
			let onSidebarToggle = () => {
				if (this.state.viewportDesktop) this.state.collapsedDesktop ? this.observable.notify(EVENTS.DESKTOP_EXPANDED) : this.observable.notify(EVENTS.DESKTOP_COLLAPSED);
				else this.state.collapsedMobile ? this.observable.notify(EVENTS.MOBILE_EXPANDED) : this.observable.notify(EVENTS.MOBILE_COLLAPSED);
			};
			document.addEventListener("os-sidebar-toggle", onSidebarToggle);
			this.onUnmount(() => document.removeEventListener("os-sidebar-toggle", onSidebarToggle));
			this.addEventListener("click", (e) => {
				if (!(e.target === this)) return;
				if (!this.state.collapsedDesktop) return;
				this.observable.notify(EVENTS.DESKTOP_EXPANDED);
			});
			this.addEventListener("mouseenter", () => this.refreshActiveState());
			this.addEventListener("mouseleave", () => this.refreshActiveState());
			this.addEventListener("focusin", () => this.refreshActiveState());
			this.addEventListener("focusout", () => queueMicrotask(() => this.refreshActiveState()));
			let groupDropdownObserver = new MutationObserver((mutations) => {
				if (!mutations.some((mutation) => mutation.target.matches?.("[data-os-sidebar-group-dropdown]"))) return;
				this.refreshActiveState();
			});
			groupDropdownObserver.observe(this, {
				attributes: true,
				attributeFilter: ["data-open"],
				subtree: true
			});
			this.onUnmount(() => groupDropdownObserver.disconnect());
		}
		setStickyPositionStyles() {
			let offsetTop = this.offsetTop;
			let pageScrollYValue = window.pageYOffset;
			if (pageScrollYValue > 0) {
				window.scrollTo(window.scrollX, 0);
				offsetTop = this.offsetTop;
				window.scrollTo(window.scrollX, pageScrollYValue);
			}
			this.style.position = "sticky";
			this.style.top = offsetTop + "px";
			this.style.maxHeight = `calc(100dvh - ${offsetTop}px)`;
		}
		refreshActiveState() {
			let active = !!(this.matches(":hover") || this.querySelector(":focus-visible") || this.querySelector("[data-os-sidebar-group-dropdown][data-open]"));
			if (this.state.active === active) return;
			this.state.active = active;
			this.observable.notify(EVENTS.STATE_CHANGED);
		}
		updateDataAttributes(el) {
			let isFullCollapsible = this.config.collapsible === true;
			if (isFullCollapsible) this.state.active ? setAttribute2(el, "data-os-sidebar-active", "") : removeAndReleaseAttribute(el, "data-os-sidebar-active");
			if (this.state.viewportDesktop) {
				removeAndReleaseAttribute(el, "data-os-sidebar-on-mobile");
				setAttribute2(el, "data-os-sidebar-on-desktop", "");
				removeAndReleaseAttribute(el, "data-os-sidebar-collapsed-mobile");
				if (this.state.collapsedDesktop) {
					if (isFullCollapsible) setAttribute2(el, "data-os-sidebar-collapsed-desktop", "");
				} else removeAndReleaseAttribute(el, "data-os-sidebar-collapsed-desktop");
			} else {
				removeAndReleaseAttribute(el, "data-os-sidebar-on-desktop");
				setAttribute2(el, "data-os-sidebar-on-mobile", "");
				removeAndReleaseAttribute(el, "data-os-sidebar-collapsed-desktop");
				if (this.state.collapsedMobile) setAttribute2(el, "data-os-sidebar-collapsed-mobile", "");
				else removeAndReleaseAttribute(el, "data-os-sidebar-collapsed-mobile");
			}
		}
	};
	var ViewportResizeObserver = class {
		constructor(observable, { breakpoint }) {
			this.observable = observable;
			this.breakpoint = breakpoint;
			this.watchForViewportChanges();
		}
		watchForViewportChanges() {
			let breakpoint = typeof this.breakpoint === "number" ? `${this.breakpoint}px` : this.breakpoint;
			this.viewport = matchMedia(`(min-width: ${breakpoint})`);
			this.onViewportChange = () => {
				this.viewport.matches ? this.observable.notify(EVENTS.VIEWPORT_ENTER_DESKTOP) : this.observable.notify(EVENTS.VIEWPORT_ENTER_MOBILE);
			};
			this.onViewportChange();
			this.viewport.addEventListener("change", this.onViewportChange);
		}
		disconnect() {
			this.viewport.removeEventListener("change", this.onViewportChange);
		}
	};
	var UISidebarToggle = class extends UIElement {
		mount() {
			let button = this.querySelector("button,ui-button");
			on(button || this, "click", () => {
				this.dispatchEvent(new CustomEvent("os-sidebar-toggle", { bubbles: true }));
			});
			queueMicrotask(() => {
				let sidebar = document.querySelector("ui-sidebar");
				sidebar.updateDataAttributes(this);
				sidebar.observable.subscribe(EVENTS.STATE_CHANGED, () => {
					sidebar.updateDataAttributes(this);
				});
			});
		}
	};
	element("sidebar", UISidebar);
	element("sidebar-toggle", UISidebarToggle);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/slider.js
	var UISlider = class extends UIElement {
		boot() {
			this.thumbs = this.querySelectorAll("[data-os-slider-thumb]");
			this.indicator = this.querySelector("[data-os-slider-indicator]");
			this.inputEls = this.querySelectorAll("input[type=\"range\"]");
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
					} else this.inputEls[0].value = value3;
					this.state.onChanges.forEach((i) => i(this.state.getValue()));
				},
				setValueInRange: (index, value3) => {
					let newValues = [...this.state.getValue()];
					newValues[index] = value3;
					this.state.setValue(newValues);
				},
				setValueFromString: (value3) => {
					if (this.config.range) this.state.setValue(value3.split(","));
					else this.state.setValue(value3);
				},
				increment: (index, increment) => {
					let values = this.state.getValueAsArray();
					if (this.config.range) {
						values[index] = this.constrainRangeValue(index, values[index] + increment);
						this.state.setValueInRange(index, values[index]);
					} else this.state.setValue(clamp2(values[index] + increment, this.config.min, this.config.max));
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
				if (disabled) this.inputEls.forEach((el) => el.setAttribute("disabled", ""));
				else this.inputEls.forEach((el) => el.removeAttribute("disabled"));
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
			if (this.hasAttribute("value")) this.state.setValueFromString(this.getAttribute("value"));
			else this.state.setValue(this.state.getValue());
		}
		setupExtraInputIfExists() {
			let extraInput = this.closest("ui-field")?.querySelector("[data-os-input] > input");
			if (!extraInput) return;
			this.extraInput = extraInput;
			this._disableable.onInitAndChange((disabled) => {
				if (disabled) setAttribute2(extraInput, "disabled", "");
				else removeAttribute(extraInput, "disabled");
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
					} else this.state.setValue(value3);
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
						default: return;
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
				if (Math.abs(e.clientX - thumbMidpoint) < thumbRect.width / 2) pointerOffset = e.clientX - thumbMidpoint;
				let initialValues = this.state.getValueAsArray();
				let lastValues = this.state.getValueAsArray();
				let setValue = (e2) => {
					let value3 = this.getValueFromX(e2.clientX - pointerOffset);
					if (this.config.range) value3 = this.constrainRangeValue(index, value3);
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
					if (initialValues.toString() !== lastValues.toString()) this.dispatchEvent(new Event("change", {
						bubbles: false,
						cancelable: true
					}));
				}, { once: true });
			});
		}
		registerMutationObserver() {
			new MutationObserver(() => {
				this.config.min = this.min();
				this.config.max = this.max();
				this.config.step = this.step();
				this.config.bigStep = this.bigStep();
				this.config.minStepsBetween = this.minStepsBetween();
				this.setNativeInputAttributes();
				this.repositionThumbsAndIndicator();
				this.repositionTicks();
			}).observe(this, {
				attributes: true,
				attributeFilter: [
					"min",
					"max",
					"step",
					"big-step",
					"min-steps-between"
				]
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
				for (let i = 0; i < values.length; i++) if (values[i] == tickValue) setAttribute2(tick, "data-current", "");
				else removeAttribute(tick, "data-current");
				if (tickValue >= activeRange[0] || tickValue <= activeRange[1]) setAttribute2(tick, "data-active", "");
				else removeAttribute(tick, "data-active");
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
					if (this.getBoundingClientRect().right - rect.right < rect.width / 4) continue;
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
			let trackPadding = this.thumbs[0].getBoundingClientRect().width / 2;
			return ((trackRect.width - trackPadding * 2) * fraction + trackPadding) / trackRect.width * 100;
		}
		getValueFromX(x) {
			let min2 = this.config.min;
			let max2 = this.config.max;
			let step = this.config.step;
			let trackRect = this.getBoundingClientRect();
			let trackPadding = this.thumbs[0].getBoundingClientRect().width / 2;
			let trackWidth = trackRect.width - trackPadding * 2;
			let rescaled = clamp2((x - (trackRect.left + trackPadding)) / trackWidth, 0, 1);
			rescaled = isRTL(this) ? 1 - rescaled : rescaled;
			let value3 = (max2 - min2) * rescaled + min2;
			value3 = roundValueToStep(value3, step, min2);
			value3 = clamp2(value3, min2, max2);
			return value3;
		}
		constrainRangeValue(index, value3) {
			let newValues = [...this.state.getValue()];
			newValues[index] = value3;
			if ((newValues[1] - newValues[0]) / this.config.step < this.config.minStepsBetween) return [newValues[1] - this.config.minStepsBetween * this.config.step, newValues[0] + this.config.minStepsBetween * this.config.step][index];
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
			if (!this.hasAttribute("step")) return 1;
			let step = parseFloat(this.getAttribute("step"));
			return step <= 0 || isNaN(step) ? 1 : step;
		}
		bigStep() {
			if (!this.hasAttribute("big-step")) return this.step();
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
		if (Math.abs(step) >= 1) precision = step.toString().split(".")[1]?.length || 0;
		else {
			let parts = step.toExponential().split("e-");
			let matissaDecimalPart = parts[0].split(".")[1];
			precision = (matissaDecimalPart ? matissaDecimalPart.length : 0) + parseInt(parts[1], 10);
		}
		let nearest = Math.round((value3 - min2) / step) * step + min2;
		return Number(nearest.toFixed(precision));
	}
	element("slider", UISlider);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/carousel.js
	var UICarousel = class extends UIElement {
		mount() {
			this.track = null;
			this.slides = [];
			this.indicators = this.indicators || [];
			this.state = {
				current: 0,
				atStart: true,
				atEnd: false
			};
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
			this.onUnmount(on(this.track, "scroll", () => this.queueStateUpdate(), { passive: true }).off);
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
			this.indicators = Array.from(/* @__PURE__ */ new Set([...this.indicators, ...this.querySelectorAll("ui-carousel-indicators")]));
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
			this.track.style.setProperty("--os-carousel-fade-left", isRTL(this) ? `max(${fade}, ${scrolled})` : `max(${fade}, ${remaining})`);
			this.track.style.setProperty("--os-carousel-fade-right", isRTL(this) ? `max(${fade}, ${remaining})` : `max(${fade}, ${scrolled})`);
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
			if (this.hasAttribute("name")) controls.push(...document.querySelectorAll(selector.split(",").map((selector2) => `${selector2.trim()}[name="${CSS.escape(this.getAttribute("name"))}"]`).join(", ")));
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
					let button = hydrate({ attrs: { "aria-label": this.carousel.getAttribute("advance") === "page" ? `Show page ${index + 1}` : `Show slide ${index + 1}` } });
					this.buttonCleanups.push(on(button, "click", () => {
						this.carousel.pauseAutoplay();
						this.carousel.goTo(target);
					}).off);
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
			if (this.hasAttribute("name")) return document.querySelector(`ui-carousel[name="${CSS.escape(this.getAttribute("name"))}"]`);
			return this.closest("ui-carousel");
		}
	};
	var UICarouselButton = class extends UIElement {
		mount() {
			this.onUnmount(on(this, "click", (event) => {
				let carousel = this.carousel();
				if (!carousel) return;
				carousel.pauseAutoplay();
				this.getAttribute("direction") === "previous" ? carousel.previous() : carousel.next();
				event.preventDefault();
			}).off);
		}
		carousel() {
			if (this.hasAttribute("name")) return document.querySelector(`ui-carousel[name="${CSS.escape(this.getAttribute("name"))}"]`);
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
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/color-picker.js
	var UIColorPicker = class extends UIControl {
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
				if (initial !== void 0 && initial !== null && initial !== "") this.setValue(initial, { dispatch: false });
				else this.setValue(this.getAttribute("value"), { dispatch: false });
			});
			this._controllable.getter(() => this.getValue());
			this._controllable.setter(detangled((value3) => {
				this.setValue(value3, { dispatch: false });
				if (this.isDeferred()) this.syncCommittedStateFromDraft();
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
			this.attributeObserver.observe(this, {
				attributes: true,
				attributeFilter: [
					"disabled",
					"format",
					"value"
				]
			});
			this.onUnmount(() => this.attributeObserver.disconnect());
		}
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
			return [
				"hexa",
				"rgba",
				"hsla"
			].includes(this.getFormat());
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
			if (value3 === void 0 || value3 === null || value3 === "") this.writeState(createEmptyState(), { dispatch });
			else {
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
			if (this.state.empty) nextState = {
				hsv: {
					h: clamp4(partial.h ?? 0, 0, 360),
					s: clamp4(partial.s ?? 0, 0, 100),
					v: clamp4(partial.v ?? 0, 0, 100)
				},
				alpha: clamp4(options.alpha ?? 1, 0, 1),
				empty: false
			};
			else nextState = {
				...this.state,
				hsv: {
					h: clamp4(partial.h ?? this.state.hsv.h, 0, 360),
					s: clamp4(partial.s ?? this.state.hsv.s, 0, 100),
					v: clamp4(partial.v ?? this.state.hsv.v, 0, 100)
				},
				alpha: clamp4(options.alpha ?? this.state.alpha, 0, 1),
				empty: false
			};
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
			this.updateColor({}, {
				...options,
				alpha: value3
			});
		}
		setSaturationValue(saturation, value3, options = {}) {
			this.updateColor({
				s: saturation,
				v: value3
			}, options);
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
				case "hexa": return `${rgbToHex(rgb.r, rgb.g, rgb.b)}${alphaToHex(alpha)}`;
				case "rgb": return `rgb(${rgb.r} ${rgb.g} ${rgb.b})`;
				case "rgba": return `rgb(${rgb.r} ${rgb.g} ${rgb.b} / ${formatAlpha(alpha)})`;
				case "hsl": return `hsl(${Math.round(hsl.h)} ${Math.round(hsl.s)}% ${Math.round(hsl.l)}%)`;
				case "hsla": return `hsl(${Math.round(hsl.h)} ${Math.round(hsl.s)}% ${Math.round(hsl.l)}% / ${formatAlpha(alpha)})`;
				default: return rgbToHex(rgb.r, rgb.g, rgb.b);
			}
		}
		syncValueAttribute() {
			if (this.committedState.empty || !this.committedState.value) removeAttribute(this, "value");
			else setAttribute2(this, "value", this.committedState.value);
		}
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
			if (this.buttonEl) on(this.buttonEl, "click", (event) => {
				if (event.target.closest("[aria-label=\"Clear color\"]")) return;
				if (this._disableable.isDisabled()) return;
				this.toggle();
			});
			if (this.swatchButton) on(this.swatchButton, "click", (event) => {
				if (this._disableable.isDisabled()) return;
				event.stopPropagation();
				this.toggle();
			});
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
				if (value3 === "") this.setValue(null);
				else if (!this.setValue(value3)) inputEl.value = this.getValue() || "";
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
					default: return;
				}
				this.setSaturationValue(s, v);
				event.preventDefault();
			});
		}
		setAreaFromPointer(event) {
			let rect = this.areaEl.getBoundingClientRect();
			let saturation = clamp4((isRTL(this) ? (rect.right - event.clientX) / rect.width : (event.clientX - rect.left) / rect.width) * 100, 0, 100);
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
			if (channel === "alpha") this.setAlpha(value3 / 100, { dispatch: false });
			else this.setHue(value3, { dispatch: false });
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
						default: return;
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
			if (this.isDeferred() && this.isMobileViewport()) this.commit();
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
						let result = await new EyeDropper().open();
						if (result.sRGBHex) this.setValue(result.sRGBHex);
					} catch {}
				});
			});
		}
		setupCopyButton() {
			this.copyButtons.forEach((button) => {
				on(button, "click", (event) => {
					event.stopPropagation();
					let value3 = this.getValue();
					if (value3 && navigator.clipboard?.writeText) navigator.clipboard.writeText(value3);
				});
			});
		}
		syncUI() {
			let committedValue = this.getValue();
			let draftValue = this.getDraftValue();
			this.previewEls.forEach((el) => {
				el.style.backgroundColor = committedValue ? this.getCheckerboardColor(this.committedState) : "transparent";
			});
			if (this.inputEl && document.activeElement !== this.inputEl) this.inputEl.value = committedValue || "";
			if (this.popoverInputEl && document.activeElement !== this.popoverInputEl) this.popoverInputEl.value = draftValue || "";
			this.syncTriggerValueText(committedValue);
			if (this.areaHue) this.areaHue.style.backgroundColor = this.getAreaColor(this.state);
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
				if (slider.getAttribute("data-channel") === "alpha") {
					slider.value = Math.round(this.state.alpha * 100);
					this.syncAlphaTrack(slider);
				} else slider.value = Math.round(this.state.hsv.h);
			});
		}
		syncAlphaTrack(slider) {
			let track = slider.querySelector("[data-os-color-picker-slider-track]");
			if (track) track.style.background = `linear-gradient(to right, transparent, ${this.getSolidColor(this.state)})`;
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
			if (this.swatchButton.dataset.baseLabel === void 0) this.swatchButton.dataset.baseLabel = this.swatchButton.getAttribute("aria-label") || "";
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
			if (this.querySelector("[data-os-color-picker-swatch]:focus")) return;
			let target = this.swatchEls.find((s) => s.getAttribute("aria-selected") === "true") || this.swatchEls[0];
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
			hsv: {
				h: 0,
				s: 0,
				v: 0
			},
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
		return `#${[
			r,
			g,
			b
		].map((component) => component.toString(16).padStart(2, "0")).join("")}`;
	}
	function normalizeColorInput(input) {
		let value3 = input.trim();
		if (/^[0-9a-fA-F]{3}$|^[0-9a-fA-F]{6}$|^[0-9a-fA-F]{8}$/.test(value3)) return `#${value3}`;
		if (/^\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}$/.test(value3)) return `rgb(${value3.replace(/,/g, " ").replace(/\s+/g, " ")})`;
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
		let delta = max2 - Math.min(r, g, b);
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
		if (h >= 0 && h < 60) [r, g, b] = [
			c,
			x,
			0
		];
		else if (h < 120) [r, g, b] = [
			x,
			c,
			0
		];
		else if (h < 180) [r, g, b] = [
			0,
			c,
			x
		];
		else if (h < 240) [r, g, b] = [
			0,
			x,
			c
		];
		else if (h < 300) [r, g, b] = [
			x,
			0,
			c
		];
		else [r, g, b] = [
			c,
			0,
			x
		];
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
			s = l > .5 ? d / (2 - max2 - min2) : d / (max2 + min2);
			switch (max2) {
				case r:
					h = (g - b) / d + (g < b ? 6 : 0);
					break;
				case g:
					h = (b - r) / d + 2;
					break;
				default: h = (r - g) / d + 4;
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
	function initPopover4(root, popover, dialogable, anchorable, onOpen = () => {}) {
		let refresh = () => {
			Array.from([root, popover]).forEach((el) => {
				dialogable.getState() ? setAttribute2(el, "data-open", "") : removeAttribute(el, "data-open");
			});
			if (dialogable.getState()) {
				anchorable.reposition();
				onOpen();
			} else anchorable.cleanup();
		};
		dialogable.onChange(() => refresh());
		refresh();
		dialogable.onChange(() => {
			if (dialogable.getState()) root.dispatchEvent(new Event("open", {
				bubbles: false,
				cancelable: false
			}));
			else root.dispatchEvent(new Event("close", {
				bubbles: false,
				cancelable: false
			}));
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
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/switch.js
	var UISwitch = class extends UIControl {
		boot() {
			let button = this;
			this._disableable = new Disableable(this);
			this._selectable = new Selectable(button, {
				toggleable: true,
				dataAttr: "data-checked",
				ariaAttr: "aria-checked",
				value: this.hasAttribute("value") ? this.getAttribute("value") : null,
				label: this.hasAttribute("label") ? this.getAttribute("label") : null,
				selectedInitially: this.hasAttribute("checked")
			});
			this._submittable = new Submittable(this, {
				name: this.getAttribute("name"),
				value: this.getAttribute("value") ?? "on",
				includeWhenEmpty: false,
				shouldUpdateValue: false
			});
			this.value = this._selectable.getValue();
			this._detangled = detangle();
			this._selectable.onChange(this._detangled(() => {
				this.dispatchEvent(new Event("input", {
					bubbles: false,
					cancelable: true
				}));
				this.dispatchEvent(new Event("change", {
					bubbles: false,
					cancelable: true
				}));
			}));
			setAttribute2(button, "role", "switch");
			this._selectable.onInitAndChange(() => {
				this._submittable.update(this._selectable.getState());
			});
			this._disableable.onInitAndChange((disabled) => {
				disabled ? removeAttribute(button, "tabindex", "0") : setAttribute2(button, "tabindex", "0");
			});
			on(button, "click", this._disableable.disabled((e) => {
				e.preventDefault();
				e.stopPropagation();
			}), { capture: true });
			on(button, "click", this._disableable.enabled((e) => {
				this._selectable.press();
			}));
			on(button, "keydown", this._disableable.enabled((e) => {
				if (e.key === "Enter") {
					this._selectable.press();
					e.preventDefault();
					e.stopPropagation();
				}
			}));
			on(button, "keydown", this._disableable.enabled((e) => {
				if (e.key === " ") {
					e.preventDefault();
					e.stopPropagation();
				}
			}));
			on(button, "keyup", this._disableable.enabled((e) => {
				if (e.key === " ") {
					this._selectable.press();
					e.preventDefault();
					e.stopPropagation();
				}
			}));
			respondToLabelClick2(button);
		}
		get checked() {
			return this._selectable.isSelected();
		}
		set checked(value3) {
			this._detangled(() => {
				value3 ? this._selectable.select() : this._selectable.deselect();
			})();
		}
	};
	function respondToLabelClick2(el) {
		el.closest("label")?.addEventListener("click", (e) => {
			if (!el.contains(e.target)) el.click();
		});
	}
	element("switch", UISwitch);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/field.js
	var UIField = class _UIField extends UIElement {
		mount() {
			this.control = this.fieldWalker().find((el) => this.isControl(el));
			if (!this.control) return;
		}
		associateLabelWithControl(control) {
			if (!control) return;
			if (!this.label) return;
			this.control = control;
			if (this.control.hasAttribute("aria-labelledby")) return;
			setAttribute2(this.elOrButton(this.control), "aria-labelledby", this.label.id);
			if (this.control && !(this.control instanceof UIControl) && this.hasAttribute("disabled")) this.control.setAttribute("disabled", "");
		}
		associateDescriptionWithControl(control) {
			if (!control) return;
			if (!this.description) return;
			this.control = control;
			if (this.control.hasAttribute("aria-describedby")) return;
			setAttribute2(this.elOrButton(this.control), "aria-describedby", this.description.id);
		}
		associateLabel(label) {
			this.label = label;
			on(label, "click", (e) => {
				if ([
					"a",
					"button",
					"ui-button"
				].includes(e.target.localName)) return;
				this.focusOrTogggle(this.control);
			});
			this.control && this.associateLabelWithControl(this.control);
		}
		associateDescription(description) {
			this.description = description;
			this.control && this.associateDescriptionWithControl(this.control);
		}
		fieldWalker() {
			return walker(this, (el, { skip, reject }) => {
				if (el instanceof _UIField && el !== this) return reject();
				if (el.parentElement.localName === "ui-editor" && el !== this) return reject();
			});
		}
		isControl(el) {
			if (el instanceof UIControl) return true;
			if (el.matches("input, textarea, select")) return true;
			return false;
		}
		focusOrTogggle(control) {
			if (!control) return;
			if (control.disabled || control.hasAttribute("disabled")) return;
			if (control.localName === "input" && ["checkbox", "radio"].includes(control.type) || [
				"ui-switch",
				"ui-radio",
				"ui-checkbox"
			].includes(control.localName)) {
				control.click();
				control.focus();
			} else if (control.localName === "input" && ["file"].includes(control.type)) control.click();
			else if ([
				"ui-select",
				"ui-date-picker",
				"ui-time-picker",
				"ui-otp",
				"ui-color-picker"
			].includes(control.localName)) control.trigger()?.focus();
			else if (["ui-editor"].includes(control.localName)) control.focus();
			else if (["ui-composer"].includes(control.localName)) control.focusInput();
			else control.focus();
		}
		elOrButton(el) {
			if (el instanceof UIElement && el.firstElementChild instanceof HTMLButtonElement) return el.firstElementChild;
			return el;
		}
	};
	var UILabel = class extends UIElement {
		mount() {
			assignId(this, "label");
			setAttribute2(this, "aria-hidden", "true");
			this.closest("ui-field")?.associateLabel(this);
		}
	};
	var UIDescription = class extends UIElement {
		mount() {
			assignId(this, "description");
			setAttribute2(this, "aria-hidden", "true");
			this.closest("ui-field")?.associateDescription(this);
		}
	};
	element("field", UIField);
	element("label", UILabel);
	element("description", UIDescription);
	var UILegend = class extends UIElement {
		mount() {
			let id = assignId(this, "legend");
			let fieldset = this.closest("fieldset");
			if (fieldset) setAttribute2(fieldset, "aria-labelledby", id);
		}
	};
	element("legend", UILegend);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/chart.js
	var isDefined = (value3) => value3 !== void 0 && value3 !== null;
	function generateChartObject(config) {
		let scaleFns = {
			"time": scaleTime,
			"linear": scaleLinear,
			"categorical": scaleCategorical
		};
		let dataMemoKey = 0;
		let dimensionsMemoKey = 0;
		let memoizedValues = {};
		let memoize = (name, key, fn) => {
			let memoKey = `${name}-${key}`;
			for (let existingKey in memoizedValues) if (existingKey.startsWith(`${name}-`) && !existingKey.endsWith(String(key))) delete memoizedValues[existingKey];
			if (!memoizedValues[memoKey]) memoizedValues[memoKey] = fn();
			return memoizedValues[memoKey];
		};
		let dimensions = {
			index: {
				key: config.dimensions.index.key,
				asArea: config.dimensions.index.asArea,
				get axis() {
					return chart.axes[chart.horizontal ? "y" : "x"];
				},
				get rawValues() {
					return memoize("index.rawValues", dataMemoKey, () => chart.data.map((d) => d[this.key]));
				},
				get type() {
					return memoize("index.type", dataMemoKey, () => {
						return this.axis.defaultScale || (this.rawValues.every((v) => isDateish(v)) ? "time" : "categorical");
					});
				},
				get values() {
					return memoize("index.values", dataMemoKey, () => this.rawValues.map((v) => {
						if (this.type === "time") return dateFromString(v);
						else if (this.type === "linear") return Number(v);
						else return v;
					}));
				},
				get points() {
					return memoize("index.points", dataMemoKey + dimensionsMemoKey, () => {
						return chart.data.map((datum) => {
							let { start, center, end, width } = this.axis.scale(datum[this.key], { area: this.asArea });
							return {
								start,
								center,
								end,
								width,
								datum
							};
						});
					});
				}
			},
			value: {
				keys: config.dimensions.value.keys,
				includeZero: config.dimensions.value.includeZero,
				get axis() {
					return chart.axes[chart.horizontal ? "x" : "y"];
				},
				get type() {
					return memoize("value.type", dataMemoKey, () => this.axis.defaultScale || "linear");
				},
				get values() {
					return memoize("value.values", dataMemoKey, () => {
						let groupsData = chart.data.flatMap((d) => chart.groupsMetadata.reduce((acc, groupMetadata) => {
							groupMetadata.children.forEach((itemMetadata) => {
								if (itemMetadata.type === "bar") {
									if (!itemMetadata.field) return;
									if (!isDefined(d[itemMetadata.field])) return;
									acc.push(d[itemMetadata.field]);
								}
								if (itemMetadata.type === "stack") {
									let gap = Number(itemMetadata.gap.replace("px", ""));
									let positiveValue = 0;
									let negativeValue = 0;
									let hasPositive = false;
									let hasNegative = false;
									itemMetadata.children.forEach((childMetadata) => {
										if (childMetadata.type === "bar") {
											if (!childMetadata.field) return;
											if (!isDefined(d[childMetadata.field])) return;
											let val = d[childMetadata.field];
											if (val >= 0) {
												if (hasPositive) positiveValue += gap;
												positiveValue += val;
												hasPositive = true;
											} else {
												if (hasNegative) negativeValue -= gap;
												negativeValue += val;
												hasNegative = true;
											}
										}
									});
									if (hasPositive) acc.push(positiveValue);
									if (hasNegative) acc.push(negativeValue);
								}
							});
							return acc;
						}, []));
						let stacksData = chart.data.flatMap((d) => chart.stacksMetadata.reduce((acc, stackMetadata) => {
							let gap = Number(stackMetadata.gap.replace("px", ""));
							let positiveValue = 0;
							let negativeValue = 0;
							let hasPositive = false;
							let hasNegative = false;
							stackMetadata.children.forEach((childMetadata) => {
								if (childMetadata.type === "bar") {
									if (!childMetadata.field) return;
									if (!isDefined(d[childMetadata.field])) return;
									let val = d[childMetadata.field];
									if (val >= 0) {
										if (hasPositive) positiveValue += gap;
										positiveValue += val;
										hasPositive = true;
									} else {
										if (hasNegative) negativeValue -= gap;
										negativeValue += val;
										hasNegative = true;
									}
								}
							});
							if (hasPositive) acc.push(positiveValue);
							if (hasNegative) acc.push(negativeValue);
							return acc;
						}, []));
						let data = chart.data.flatMap((d) => this.keys.reduce((acc, key) => {
							if (!isDefined(d[key])) return acc;
							acc.push(d[key]);
							return acc;
						}, []));
						return [
							...groupsData,
							...stacksData,
							...data,
							...this.includeZero ? [0] : []
						];
					});
				},
				zeroLine() {
					let position = this.axis.scale(0).center;
					if (this.axis.axis === "x") return {
						x1: position,
						x2: position,
						y1: chart.dimensions.index.axis.range[0],
						y2: chart.dimensions.index.axis.range[1]
					};
					return {
						x1: chart.dimensions.index.axis.range[0],
						x2: chart.dimensions.index.axis.range[1],
						y1: position,
						y2: position
					};
				}
			}
		};
		let chart = {
			horizontal: config.horizontal,
			locale: config.locale,
			data: [],
			stacksMetadata: config.stacksMetadata,
			groupsMetadata: config.groupsMetadata,
			width: config.width,
			height: config.height,
			inset: config.inset,
			dimensions,
			axes: {
				x: {
					axis: "x",
					format: config.axes.x.format,
					inset: {
						start: 0,
						end: 0
					},
					position: config.axes.x.position || "bottom",
					defaultScale: config.axes.x.scale,
					tickStart: config.axes.x.tickStart,
					tickEnd: config.axes.x.tickEnd,
					tickCount: config.axes.x.tickCount,
					tickStep: config.axes.x.tickStep,
					tickValues: config.axes.x.tickValues,
					tickSuffix: config.axes.x.tickSuffix,
					tickPrefix: config.axes.x.tickPrefix,
					get interval() {
						return memoize("x.interval", dataMemoKey, () => {
							return config.axes.x.interval || "auto";
						});
					},
					get dimension() {
						return chart.dimensions[chart.horizontal ? "value" : "index"];
					},
					get values() {
						return this.dimension.values;
					},
					get type() {
						return this.dimension.type;
					},
					get ticks() {
						return memoize("x.ticks", dataMemoKey, () => {
							let tickValues = generateTickValues(chart.data, this, generateDomain(this.type, this.values));
							let tickLabels = generateTickLabels(tickValues, this.type, this.format, this.tickSuffix, this.tickPrefix, this.interval, chart.locale);
							return tickValues.map((value3, index) => ({
								value: value3,
								label: tickLabels[index]
							}));
						});
					},
					get domain() {
						return memoize("x.domain", dataMemoKey, () => generateDomain(this.type, [...this.ticks.map((t) => t.value), ...this.values]));
					},
					get range() {
						return memoize("x.range", dimensionsMemoKey, () => [chart.inset.left + this.inset.start, chart.width - chart.inset.right - this.inset.end]);
					},
					get scale() {
						return memoize("x.scale", dimensionsMemoKey + dataMemoKey, () => scaleFns[this.type](this.domain, this.range, this.values));
					}
				},
				y: {
					axis: "y",
					format: config.axes.y.format,
					inset: {
						start: 0,
						end: 0
					},
					position: config.axes.y.position || "left",
					defaultScale: config.axes.y.scale,
					tickStart: config.axes.y.tickStart,
					tickEnd: config.axes.y.tickEnd,
					tickCount: config.axes.y.tickCount,
					tickStep: config.axes.y.tickStep,
					tickValues: config.axes.y.tickValues,
					tickSuffix: config.axes.y.tickSuffix,
					tickPrefix: config.axes.y.tickPrefix,
					get interval() {
						return memoize("y.interval", dataMemoKey, () => {
							return config.axes.y.interval || "auto";
						});
					},
					get dimension() {
						return chart.dimensions[chart.horizontal ? "index" : "value"];
					},
					get values() {
						return this.dimension.values;
					},
					get type() {
						return this.dimension.type;
					},
					get ticks() {
						return memoize("y.ticks", dataMemoKey, () => {
							let tickValues = generateTickValues(chart.data, this, generateDomain(this.type, this.values));
							let tickLabels = generateTickLabels(tickValues, this.type, this.format, this.tickSuffix, this.tickPrefix, this.interval, chart.locale);
							return tickValues.map((value3, index) => ({
								value: value3,
								label: tickLabels[index]
							}));
						});
					},
					get domain() {
						return memoize("y.domain", dataMemoKey, () => generateDomain(this.type, [...this.ticks.map((t) => t.value), ...this.values]));
					},
					get range() {
						return memoize("y.range", dimensionsMemoKey, () => chart.horizontal ? [chart.inset.top + this.inset.start, chart.height - chart.inset.bottom - this.inset.end] : [chart.height - chart.inset.bottom - this.inset.end, chart.inset.top + this.inset.start]);
					},
					get scale() {
						return memoize("y.scale", dimensionsMemoKey + dataMemoKey, () => scaleFns[this.type](this.domain, this.range, this.values));
					}
				}
			},
			get series() {
				return memoize("series", dataMemoKey + dimensionsMemoKey, () => this.dimensions.value.keys.reduce((acc, key) => {
					if (!chart.data.some((datum) => isDefined(datum[key]))) {
						console.warn(`ui-chart: series field "${key}" does not exist`);
						return acc;
					}
					let segments = [];
					let currentSegment = [];
					chart.data.forEach((datum) => {
						if (!isDefined(datum[key])) {
							if (currentSegment.length) segments.push(currentSegment);
							currentSegment = [];
							return;
						}
						let index = chart.dimensions.index.axis.scale(datum[chart.dimensions.index.key], { area: chart.dimensions.index.asArea }).center;
						let value3 = chart.dimensions.value.axis.scale(datum[key]).center;
						currentSegment.push({
							x: chart.horizontal ? value3 : index,
							y: chart.horizontal ? index : value3,
							datum
						});
					});
					if (currentSegment.length) segments.push(currentSegment);
					let points = segments.flat();
					acc[key] = {
						field: key,
						values: chart.data.map((d) => d[key]),
						bars({ barWidth, minHeight, radius }) {
							let { start: groupStart, center: groupCenter, width: fullGroupWidth } = chart.dimensions.index.axis.scale(chart.dimensions.index.axis.domain[0], { area: chart.dimensions.index.asArea });
							let barRadius = radius;
							if (barWidth.match(/^(\d+)%$/)) barWidth = fullGroupWidth * Number(barWidth.replace("%", "")) / 100;
							else barWidth = Number(barWidth.replace("px", ""));
							let groupStartOffset = (fullGroupWidth - barWidth) / 2;
							return chart.data.flatMap((d) => {
								let value3 = d[key];
								if (!isDefined(value3)) return [];
								let { start } = chart.dimensions.index.axis.scale(d[chart.dimensions.index.key], { area: chart.dimensions.index.asArea });
								let x, y, width, height;
								let radius2 = barRadius;
								if (chart.horizontal) {
									x = value3 >= 0 ? chart.dimensions.value.axis.scale(0).center : chart.dimensions.value.axis.scale(value3).center;
									y = start + groupStartOffset;
									width = value3 >= 0 ? chart.dimensions.value.axis.scale(value3).center - x : chart.dimensions.value.axis.scale(0).center - x;
									height = barWidth;
									if (width < minHeight) {
										if (value3 < 0) x -= minHeight - width;
										width = minHeight;
									}
								} else {
									x = start + groupStartOffset;
									y = value3 >= 0 ? chart.dimensions.value.axis.scale(value3).center : chart.dimensions.value.axis.scale(0).center;
									height = value3 >= 0 ? chart.dimensions.value.axis.scale(0).center - y : chart.dimensions.value.axis.scale(value3).center - y;
									width = barWidth;
									if (height < minHeight) {
										y -= minHeight - height;
										height = minHeight;
									}
								}
								radius2 = orientBarRadius(radius2, value3, chart.horizontal);
								return [{
									x,
									y,
									width,
									height,
									path: () => {
										return getBarPath(x, y, width, height, radius2);
									}
								}];
							});
						},
						linePath(curve = "smooth") {
							let createPath = {
								smooth: (points2) => smooth(points2, chart.horizontal ? "y" : "x"),
								none: noCurve
							}[curve];
							return this.segments.map((segment) => createPath(segment.map((point) => [point.x, point.y]))).filter(Boolean).join(" ");
						},
						areaPath(curve = "smooth") {
							let createPath = {
								smooth: (points2) => smooth(points2, chart.horizontal ? "y" : "x"),
								none: noCurve
							}[curve];
							return this.segments.map((segment) => {
								let linePath = createPath(segment.map((point) => [point.x, point.y]));
								if (!linePath) return "";
								let firstPoint = segment[0];
								let lastPoint = segment[segment.length - 1];
								let baseline = chart.dimensions.value.axis.scale(chart.dimensions.value.axis.domain[0]).center;
								return chart.horizontal ? `${linePath} L${baseline},${lastPoint.y} L${baseline},${firstPoint.y} Z` : `${linePath} L${lastPoint.x},${baseline} L${firstPoint.x},${baseline} Z`;
							}).filter(Boolean).join(" ");
						},
						points,
						segments
					};
					return acc;
				}, {}));
			},
			get stackedSeries() {
				return memoize("stackedSeries", dataMemoKey + dimensionsMemoKey, () => {
					let data = {};
					let filteredMetadata = [];
					return chart.stacksMetadata.map((stackMetadata) => {
						stackMetadata.children.forEach((childMetadata) => {
							if (childMetadata.type === "bar") {
								if (!childMetadata.field) return;
								if (!chart.data[0].hasOwnProperty(childMetadata.field)) {
									console.warn(`ui-chart: series field "${childMetadata.field}" does not exist`);
									return;
								}
								data[childMetadata.field] = chart.data.map((d) => d[childMetadata.field]);
								filteredMetadata.push(childMetadata);
							}
						});
						return { bars() {
							let { start: groupStart, center: groupCenter, width: fullGroupWidth } = chart.dimensions.index.axis.scale(chart.dimensions.index.axis.domain[0], { area: chart.dimensions.index.asArea });
							let stackWidth = stackMetadata.width ?? "90%";
							if (stackWidth.match(/^(\d+)%$/)) stackWidth = fullGroupWidth * Number(stackWidth.replace("%", "")) / 100;
							else stackWidth = Number(stackWidth.replace("px", ""));
							let stackStartOffset = (fullGroupWidth - stackWidth) / 2;
							let stackGap = Number(stackMetadata.gap.replace("px", ""));
							let previousPositiveHeight = [];
							let previousNegativeHeight = [];
							let stackRadius = stackMetadata.radius;
							let topBarField = [];
							let bottomBarField = [];
							if (stackRadius) filteredMetadata.forEach((itemMetadata) => {
								if (itemMetadata.type === "bar") data[itemMetadata.field].forEach((value3, index) => {
									if (value3 >= 0) {
										topBarField[index] = itemMetadata.field;
										if (bottomBarField[index] === void 0) bottomBarField[index] = itemMetadata.field;
									} else {
										bottomBarField[index] = itemMetadata.field;
										if (topBarField[index] === void 0) topBarField[index] = itemMetadata.field;
									}
								});
							});
							return filteredMetadata.reduce((acc, itemMetadata) => {
								if (itemMetadata.type === "bar") {
									let bars = [];
									data[itemMetadata.field].forEach((value3, index) => {
										if (previousPositiveHeight[index] === void 0) {
											previousPositiveHeight[index] = 0;
											previousNegativeHeight[index] = 0;
										}
										let { start } = chart.dimensions.index.axis.scale(chart.data[index][chart.dimensions.index.key], { area: chart.dimensions.index.asArea });
										let x, y, width, height;
										let minHeight = Number(itemMetadata.minHeight.replace("px", ""));
										let radius = itemMetadata.radius;
										if (chart.horizontal) {
											x = value3 >= 0 ? chart.dimensions.value.axis.scale(0).center : chart.dimensions.value.axis.scale(value3).center;
											y = start + stackStartOffset;
											width = value3 >= 0 ? chart.dimensions.value.axis.scale(value3).center - x : chart.dimensions.value.axis.scale(0).center - x;
											height = stackWidth;
										} else {
											x = start + stackStartOffset;
											y = value3 >= 0 ? chart.dimensions.value.axis.scale(value3).center : chart.dimensions.value.axis.scale(0).center;
											height = value3 >= 0 ? chart.dimensions.value.axis.scale(0).center - y : chart.dimensions.value.axis.scale(value3).center - y;
											width = stackWidth;
										}
										if (stackRadius) {
											let isTopBar = itemMetadata.field === topBarField[index];
											let isBottomBar = itemMetadata.field === bottomBarField[index];
											let visualRadius = {
												topLeft: 0,
												topRight: 0,
												bottomLeft: 0,
												bottomRight: 0
											};
											if (isTopBar) {
												visualRadius.topLeft = stackRadius.topLeft;
												visualRadius.topRight = stackRadius.topRight;
											}
											if (isBottomBar) {
												visualRadius.bottomLeft = stackRadius.bottomLeft;
												visualRadius.bottomRight = stackRadius.bottomRight;
											}
											if (value3 < 0) radius = {
												topLeft: visualRadius.bottomLeft,
												topRight: visualRadius.bottomRight,
												bottomLeft: visualRadius.topLeft,
												bottomRight: visualRadius.topRight
											};
											else radius = visualRadius;
										}
										if (chart.horizontal) {
											if (width < minHeight) {
												if (value3 < 0) x -= minHeight - width;
												width = minHeight;
											}
											x = value3 >= 0 ? x + previousPositiveHeight[index] : x - previousNegativeHeight[index];
										} else {
											y = value3 >= 0 ? y - previousPositiveHeight[index] : y + previousNegativeHeight[index];
											if (height < minHeight) {
												y -= minHeight - height;
												height = minHeight;
											}
										}
										radius = orientBarRadius(radius, value3, chart.horizontal);
										let bar = {
											x,
											y,
											width,
											height,
											path: () => {
												return getBarPath(x, y, width, height, radius);
											}
										};
										if (value3 >= 0) previousPositiveHeight[index] += (chart.horizontal ? width : height) + stackGap;
										else previousNegativeHeight[index] += (chart.horizontal ? width : height) + stackGap;
										bars.push(bar);
									});
									acc[itemMetadata.field] = bars;
								}
								return acc;
							}, {});
						} };
					});
				});
			},
			get groupedSeries() {
				return memoize("groupedSeries", dataMemoKey + dimensionsMemoKey, () => chart.groupsMetadata.map((groupMetadata) => {
					let data = {};
					let filteredMetadata = [];
					groupMetadata.children.forEach((itemMetadata) => {
						if (itemMetadata.type === "bar") {
							if (!itemMetadata.field) return;
							if (!chart.data[0].hasOwnProperty(itemMetadata.field)) {
								console.warn(`ui-chart: series field "${itemMetadata.field}" does not exist`);
								return;
							}
							data[itemMetadata.field] = chart.data.map((d) => d[itemMetadata.field]);
							filteredMetadata.push(itemMetadata);
						} else if (itemMetadata.type === "stack") {
							let childrenMetadata = [];
							itemMetadata.children.forEach((child) => {
								if (child.type === "bar") {
									if (!child.field) return;
									if (!chart.data[0].hasOwnProperty(child.field)) {
										console.warn(`ui-chart: series field "${child.field}" does not exist`);
										return;
									}
									data[child.field] = chart.data.map((d) => d[child.field]);
									childrenMetadata.push(child);
								}
							});
							if (childrenMetadata.length > 0) {
								let stackMetadata = {
									...itemMetadata,
									children: childrenMetadata
								};
								filteredMetadata.push(stackMetadata);
							}
						}
					});
					return {
						keys: filteredMetadata,
						values: data,
						bars() {
							let { start: groupStart, center: groupCenter, width: fullGroupWidth } = chart.dimensions.index.axis.scale(chart.dimensions.index.axis.domain[0], { area: chart.dimensions.index.asArea });
							let groupWidth = groupMetadata.width ?? "90%";
							if (groupWidth.match(/^(\d+)%$/)) groupWidth = fullGroupWidth * Number(groupWidth.replace("%", "")) / 100;
							else groupWidth = Number(groupWidth.replace("px", ""));
							let groupStartOffset = (fullGroupWidth - groupWidth) / 2;
							let gap = groupMetadata.gap ?? "2%";
							if (gap.match(/^(\d+)%$/)) gap = groupWidth * Number(gap.replace("%", "")) / 100;
							else gap = Number(gap.replace("px", ""));
							let countOfBars = filteredMetadata.length;
							let totalGap = gap * (countOfBars - 1);
							let countOfBarsWithoutWidth = countOfBars;
							let totalSpecifiedWidths = filteredMetadata.reduce((acc, itemMetadata) => {
								if (!itemMetadata.width) return acc;
								if (itemMetadata.width.match(/^(\d+)%$/)) acc += groupWidth * Number(itemMetadata.width.replace("%", "")) / 100;
								else acc += Number(itemMetadata.width.replace("px", ""));
								countOfBarsWithoutWidth--;
								return acc;
							}, 0);
							let barWidth = countOfBarsWithoutWidth > 0 ? (groupWidth - totalSpecifiedWidths - totalGap) / countOfBarsWithoutWidth : 0;
							if (barWidth === 0) groupStartOffset = (fullGroupWidth - (totalSpecifiedWidths + totalGap)) / 2;
							let itemOffset = groupStartOffset;
							return filteredMetadata.reduce((acc, itemMetadata, index) => {
								let width = barWidth;
								if (itemMetadata.width) {
									if (itemMetadata.width.match(/^(\d+)%$/)) width = groupWidth * Number(itemMetadata.width.replace("%", "")) / 100;
									else width = Number(itemMetadata.width.replace("px", ""));
								}
								if (index > 0) itemOffset += gap;
								if (itemMetadata.type === "bar") {
									let bars = [];
									data[itemMetadata.field].forEach((value3, index2) => {
										let { start: groupStart2 } = chart.dimensions.index.axis.scale(chart.data[index2][chart.dimensions.index.key], { area: chart.dimensions.index.asArea });
										let x, y, localWidth, height;
										let minHeight = Number(itemMetadata.minHeight.replace("px", ""));
										let radius = itemMetadata.radius;
										if (chart.horizontal) {
											x = value3 >= 0 ? chart.dimensions.value.axis.scale(0).center : chart.dimensions.value.axis.scale(value3).center;
											y = groupStart2 + itemOffset;
											localWidth = value3 >= 0 ? chart.dimensions.value.axis.scale(value3).center - x : chart.dimensions.value.axis.scale(0).center - x;
											height = width;
											if (localWidth < minHeight) {
												if (value3 < 0) x -= minHeight - localWidth;
												localWidth = minHeight;
											}
										} else {
											x = groupStart2 + itemOffset;
											y = value3 >= 0 ? chart.dimensions.value.axis.scale(value3).center : chart.dimensions.value.axis.scale(0).center;
											height = value3 >= 0 ? chart.dimensions.value.axis.scale(0).center - y : chart.dimensions.value.axis.scale(value3).center - y;
											localWidth = width;
											if (height < minHeight) {
												y -= minHeight - height;
												height = minHeight;
											}
										}
										radius = orientBarRadius(radius, value3, chart.horizontal);
										let bar = {
											x,
											y,
											width: localWidth,
											height,
											path: () => {
												return getBarPath(x, y, localWidth, height, radius);
											}
										};
										bars.push(bar);
									});
									acc[itemMetadata.field] = bars;
								}
								if (itemMetadata.type === "stack") {
									let stackGap = Number(itemMetadata.gap.replace("px", ""));
									let previousPositiveHeight = [];
									let previousNegativeHeight = [];
									let stackRadius = itemMetadata.radius;
									let topBarField = [];
									let bottomBarField = [];
									if (stackRadius) itemMetadata.children.forEach((child) => {
										if (child.type === "bar") data[child.field].forEach((value3, index2) => {
											if (value3 >= 0) {
												topBarField[index2] = child.field;
												if (bottomBarField[index2] === void 0) bottomBarField[index2] = child.field;
											} else {
												bottomBarField[index2] = child.field;
												if (topBarField[index2] === void 0) topBarField[index2] = child.field;
											}
										});
									});
									itemMetadata.children.forEach((child) => {
										if (child.type === "bar") {
											let bars = [];
											data[child.field].forEach((value3, index2) => {
												if (previousPositiveHeight[index2] === void 0) {
													previousPositiveHeight[index2] = 0;
													previousNegativeHeight[index2] = 0;
												}
												let { start } = chart.dimensions.index.axis.scale(chart.data[index2][chart.dimensions.index.key], { area: chart.dimensions.index.asArea });
												let x, y, localWidth, height;
												let minHeight = Number(child.minHeight.replace("px", ""));
												let radius = child.radius;
												if (chart.horizontal) {
													x = value3 >= 0 ? chart.dimensions.value.axis.scale(0).center : chart.dimensions.value.axis.scale(value3).center;
													y = start + itemOffset;
													localWidth = value3 >= 0 ? chart.dimensions.value.axis.scale(value3).center - x : chart.dimensions.value.axis.scale(0).center - x;
													height = width;
												} else {
													x = start + itemOffset;
													y = value3 >= 0 ? chart.dimensions.value.axis.scale(value3).center : chart.dimensions.value.axis.scale(0).center;
													height = value3 >= 0 ? chart.dimensions.value.axis.scale(0).center - y : chart.dimensions.value.axis.scale(value3).center - y;
													localWidth = width;
												}
												if (stackRadius) {
													let isTopBar = child.field === topBarField[index2];
													let isBottomBar = child.field === bottomBarField[index2];
													let visualRadius = {
														topLeft: 0,
														topRight: 0,
														bottomLeft: 0,
														bottomRight: 0
													};
													if (isTopBar) {
														visualRadius.topLeft = stackRadius.topLeft;
														visualRadius.topRight = stackRadius.topRight;
													}
													if (isBottomBar) {
														visualRadius.bottomLeft = stackRadius.bottomLeft;
														visualRadius.bottomRight = stackRadius.bottomRight;
													}
													if (value3 < 0) radius = {
														topLeft: visualRadius.bottomLeft,
														topRight: visualRadius.bottomRight,
														bottomLeft: visualRadius.topLeft,
														bottomRight: visualRadius.topRight
													};
													else radius = visualRadius;
												}
												if (chart.horizontal) {
													if (localWidth < minHeight) {
														if (value3 < 0) x -= minHeight - localWidth;
														localWidth = minHeight;
													}
													x = value3 >= 0 ? x + previousPositiveHeight[index2] : x - previousNegativeHeight[index2];
												} else {
													if (height < minHeight) {
														y -= minHeight - height;
														height = minHeight;
													}
													y = value3 >= 0 ? y - previousPositiveHeight[index2] : y + previousNegativeHeight[index2];
												}
												radius = orientBarRadius(radius, value3, chart.horizontal);
												let bar = {
													x,
													y,
													width: localWidth,
													height,
													path: () => {
														return getBarPath(x, y, localWidth, height, radius);
													}
												};
												if (value3 >= 0) previousPositiveHeight[index2] += (chart.horizontal ? localWidth : height) + stackGap;
												else previousNegativeHeight[index2] += (chart.horizontal ? localWidth : height) + stackGap;
												bars.push(bar);
											});
											acc[child.field] = bars;
										}
									});
								}
								itemOffset += width;
								return acc;
							}, {});
						}
					};
				}));
			},
			cursor(indexPoint, type = "line") {
				if (type === "area") {
					if (this.horizontal) return {
						x: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[0]).center,
						y: indexPoint.start,
						width: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[1]).center - this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[0]).center,
						height: indexPoint.width
					};
					return {
						x: indexPoint.start,
						y: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[1]).center,
						width: indexPoint.width,
						height: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[0]).center - this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[1]).center
					};
				}
				if (this.horizontal) {
					let y = indexPoint.center;
					return [{
						x: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[0]).center,
						y
					}, {
						x: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[1]).center,
						y
					}];
				}
				let x = indexPoint.center;
				return [{
					x,
					y: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[0]).center
				}, {
					x,
					y: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[1]).center
				}];
			},
			indexPosition(x, y) {
				return this.horizontal ? y : x;
			},
			closestIndexPoint(indexPosition) {
				return this.closestIndexPoints(indexPosition)[0];
			},
			closestIndexPoints(indexPosition) {
				return memoize("closestIndexPoints", dataMemoKey + dimensionsMemoKey + indexPosition, () => {
					let closestPoints = [];
					let minDistance = Infinity;
					let lastPoint = null;
					this.dimensions.index.points.forEach((point) => {
						let distance = Math.abs(point.center - indexPosition);
						if (distance < minDistance) {
							minDistance = distance;
							lastPoint = point;
						}
					});
					lastPoint && closestPoints.push(lastPoint);
					return closestPoints;
				});
			},
			updateDimensions(dimensions2, inset) {
				this.width = dimensions2.width;
				this.height = dimensions2.height;
				this.inset = inset;
				dimensionsMemoKey++;
			},
			updateData(rawData) {
				let data = JSON.parse(JSON.stringify(rawData));
				if (data[0] !== void 0 && typeof data[0] !== "object") data = data.map((d, i) => {
					return { value: d };
				});
				if (!data.every((d) => d.index)) data.forEach((d, i) => {
					d.index = i;
				});
				this.data = data;
				dataMemoKey++;
			}
		};
		chart.updateData(config.data);
		return chart;
	}
	function orientBarRadius(radius, value3, horizontal) {
		let oriented = { ...radius };
		if (value3 < 0) oriented = {
			topLeft: oriented.bottomLeft,
			topRight: oriented.bottomRight,
			bottomRight: oriented.topRight,
			bottomLeft: oriented.topLeft
		};
		if (horizontal) oriented = {
			topLeft: oriented.bottomLeft,
			topRight: oriented.topLeft,
			bottomRight: oriented.topRight,
			bottomLeft: oriented.bottomRight
		};
		return oriented;
	}
	function generateDomain(type, values) {
		let deduplicatedValues = [...new Set(values)];
		if (type === "time") return generateTimeDomain(deduplicatedValues);
		else if (type === "linear") {
			deduplicatedValues.sort((a, b) => Number(a) - Number(b));
			if (deduplicatedValues.length === 1) {
				if (deduplicatedValues[0] === 0) return [Number(deduplicatedValues[0]), 1];
				else if (deduplicatedValues[0] > 0) return [0, Number(deduplicatedValues[0])];
				else return [Number(deduplicatedValues[0]), 0];
			}
			return [Number(deduplicatedValues[0]), Number(deduplicatedValues[deduplicatedValues.length - 1])];
		} else return [deduplicatedValues[0], deduplicatedValues[deduplicatedValues.length - 1]];
	}
	function generateTickValues(data, axis, domain) {
		let tickValues = [];
		if (axis.type === "linear") {
			if (axis.tickValues) return axis.tickValues;
			let [minValue, maxValue] = domain;
			let targetCount = axis.tickCount ? Number(axis.tickCount) : 5;
			let tickStart = axis.tickStart === 0 || axis.tickStart === "0" ? 0 : axis.tickStart || "auto";
			let tickEnd = axis.tickEnd === 0 || axis.tickEnd === "0" ? 0 : axis.tickEnd || "auto";
			if (tickStart === "auto" && minValue > 0 && maxValue > 0) minValue = 0;
			else if (tickStart !== "min" && !isNaN(Number(tickStart))) minValue = Math.min(minValue, Number(tickStart));
			if (tickEnd !== "auto" && tickEnd !== "max" && !isNaN(Number(tickEnd))) maxValue = Number(tickEnd);
			let interval = Number(axis.interval);
			let niceStep;
			if (axis.interval === "auto" || Number.isNaN(interval)) {
				let rawStep = (maxValue - minValue) / (targetCount - 1);
				let magnitude = Math.pow(10, Math.floor(Math.log10(rawStep)));
				let normalizedStep = rawStep / magnitude;
				if (normalizedStep < 1.5) niceStep = 1;
				else if (normalizedStep < 3) niceStep = 2;
				else if (normalizedStep < 7) niceStep = 5;
				else niceStep = 10;
				niceStep *= magnitude;
			} else niceStep = interval;
			let start;
			if (tickStart === "auto") start = Math.floor(minValue / niceStep) * niceStep;
			else if (tickStart === "min") start = minValue;
			else start = Number(tickStart);
			let currentTick = start;
			while (currentTick <= maxValue + niceStep * .1) {
				if (currentTick >= minValue - niceStep * .1) tickValues.push(currentTick);
				currentTick += niceStep;
			}
			if (tickEnd === "max") {
				if (Math.abs(tickValues[tickValues.length - 1] - domain[1]) > niceStep * .1) tickValues.push(domain[1]);
			} else if (tickEnd !== "auto" && !isNaN(Number(tickEnd))) {
				const end = Number(tickEnd);
				const count = tickValues.length;
				tickValues = Array.from({ length: count }, (_, i) => start + (end - start) * (i / (count - 1)));
			}
			if (tickValues.length > targetCount * 2) {
				let step = Math.ceil(tickValues.length / targetCount);
				tickValues = tickValues.filter((_, i) => i % step === 0);
			}
		} else if (axis.type === "time") {
			let tickCount = axis.tickCount || data.length;
			let [_, ticks] = generateDateFormatAndTicks(domain[0], domain[1], tickCount, axis.interval, axis.dimension.asArea);
			return ticks;
		} else if (axis.type === "categorical") {
			data.forEach((datum) => {
				let value3 = datum[axis.dimension.key];
				tickValues.push(value3);
			});
			if (axis.tickCount && tickValues.length > axis.tickCount) {
				const step = Math.ceil(tickValues.length / axis.tickCount);
				tickValues = tickValues.filter((_, i) => i % step === 0);
			}
		}
		return tickValues;
	}
	function generateTickLabels(tickValues, type, format, tickSuffix, tickPrefix, interval, locale) {
		let labels = [];
		if (type === "time") {
			if (!format) [format] = generateDateFormatAndTicks(tickValues[0], tickValues[tickValues.length - 1], null, interval);
			format = {
				...format,
				timeZone: "UTC"
			};
			labels = tickValues.map((value3) => new Date(value3).toLocaleString(locale, format));
		} else if (type === "linear") {
			if (format) labels = tickValues.map((value3) => value3.toLocaleString(locale, format));
			else labels = tickValues.map((value3) => value3.toLocaleString(locale));
		} else labels = tickValues.map((value3) => value3 + "");
		if (tickPrefix) labels = labels.map((l) => tickPrefix + l);
		if (tickSuffix) labels = labels.map((l) => l + tickSuffix);
		return labels;
	}
	function scaleCategorical(domain, range, values) {
		const uniqueValues = [...new Set(values)];
		const count = uniqueValues.length;
		const indexMap = new Map(uniqueValues.map((category, index) => [category, index]));
		return function(value3, { area = false } = {}) {
			if (value3 === null || value3 === void 0) return;
			const index = indexMap.get(value3);
			if (index === void 0) return;
			if (area) {
				let step2 = (range[1] - range[0]) / (area ? count : count - 1);
				let start = range[0] + index * step2;
				return {
					start,
					center: start + step2 / 2,
					end: start + step2,
					width: step2
				};
			}
			if (count === 1) {
				let point2 = range[0] + (range[1] - range[0]) / 2;
				return {
					start: point2,
					center: point2,
					end: point2,
					width: 0
				};
			}
			let step = (range[1] - range[0]) / (count - 1);
			let point = range[0] + index * step;
			return {
				start: point,
				center: point,
				end: point,
				width: 0
			};
		};
	}
	function scaleLinear(domain, range, values) {
		const domainDiff = domain[1] - domain[0];
		const rangeDiff = range[1] - range[0];
		return function(value3) {
			let point = range[0] + (value3 - domain[0]) * (rangeDiff / domainDiff);
			return {
				start: point,
				center: point,
				end: point,
				width: 0
			};
		};
	}
	function smooth(points, direction = "x") {
		if (points.length < 2) return "";
		const sign = (x) => x < 0 ? -1 : 1;
		const getCoords = (point) => direction === "x" ? {
			x: point[0],
			y: point[1]
		} : {
			x: point[1],
			y: point[0]
		};
		const calculateSlope = (p0, p1, p2) => {
			const { x: x0, y: y0 } = getCoords(p0);
			const { x: x1, y: y1 } = getCoords(p1);
			const { x: x2, y: y2 } = getCoords(p2);
			const h0 = x1 - x0;
			const h1 = x2 - x1;
			const s0 = (y1 - y0) / (h0 || h1 < 0 && -0);
			const s1 = (y2 - y1) / (h1 || h0 < 0 && -0);
			const p = (s0 * h1 + s1 * h0) / (h0 + h1);
			return (sign(s0) + sign(s1)) * Math.min(Math.abs(s0), Math.abs(s1), .5 * Math.abs(p)) || 0;
		};
		const endpointSlope = (p0, p1, t) => {
			const { x: x0, y: y0 } = getCoords(p0);
			const { x: x1, y: y1 } = getCoords(p1);
			const h = x1 - x0;
			return h ? (3 * (y1 - y0) / h - t) / 2 : t;
		};
		let path = `M${points[0][0]},${points[0][1]}`;
		if (points.length === 2) return path + `L${points[1][0]},${points[1][1]}`;
		for (let i = 0; i < points.length - 2; i++) {
			const p0 = points[i];
			const p1 = points[i + 1];
			if (p0[0] === p1[0] && p0[1] === p1[1]) continue;
			const p2 = points[i + 2];
			const t1 = calculateSlope(p0, p1, p2);
			const { x: x0 } = getCoords(p0);
			const { x: x1 } = getCoords(p1);
			const delta = (x1 - x0) / 3;
			const slope = i === 0 ? endpointSlope(p0, p1, t1) : points[i - 1] ? calculateSlope(points[i - 1], p0, p1) : t1;
			const c1x = direction === "x" ? p0[0] + delta : p0[0] + delta * slope;
			const c1y = direction === "x" ? p0[1] + delta * slope : p0[1] + delta;
			const c2x = direction === "x" ? p1[0] - delta : p1[0] - delta * t1;
			const c2y = direction === "x" ? p1[1] - delta * t1 : p1[1] - delta;
			path += `C${c1x},${c1y} ${c2x},${c2y} ${p1[0]},${p1[1]}`;
		}
		const n = points.length;
		const last = points[n - 1];
		const secondLast = points[n - 2];
		if (!(last[0] === secondLast[0] && last[1] === secondLast[1])) {
			const t0 = calculateSlope(points[n - 3], secondLast, last);
			const { x: x0 } = getCoords(secondLast);
			const { x: x1 } = getCoords(last);
			const delta = (x1 - x0) / 3;
			const endSlope = endpointSlope(secondLast, last, t0);
			const c1x = direction === "x" ? secondLast[0] + delta : secondLast[0] + delta * t0;
			const c1y = direction === "x" ? secondLast[1] + delta * t0 : secondLast[1] + delta;
			const c2x = direction === "x" ? last[0] - delta : last[0] - delta * endSlope;
			const c2y = direction === "x" ? last[1] - delta * endSlope : last[1] - delta;
			path += `C${c1x},${c1y} ${c2x},${c2y} ${last[0]},${last[1]}`;
		}
		return path;
	}
	function simpleCurve(points, smoothing = .05) {
		if (!points || points.length < 2) return "";
		let path = `M ${points[0][0]} ${points[0][1]}`;
		for (let i = 1; i < points.length; i++) {
			const current = points[i];
			const previous = points[i - 1];
			const prevPoint = points[i - 2] || previous;
			const nextPoint = points[i + 1] || current;
			const cp1x = previous[0] + (nextPoint[0] - prevPoint[0]) * smoothing;
			const cp1y = previous[1] + (nextPoint[1] - prevPoint[1]) * smoothing;
			const cp2x = current[0] - (nextPoint[0] - prevPoint[0]) * smoothing;
			const cp2y = current[1] - (nextPoint[1] - prevPoint[1]) * smoothing;
			path += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${current[0]} ${current[1]}`;
		}
		return path;
	}
	function noCurve(points) {
		return simpleCurve(points, 0);
	}
	function getBarPath(x, y, width, height, radius = null) {
		let topLeftRadius = radius?.topLeft ?? 0;
		let topRightRadius = radius?.topRight ?? 0;
		let bottomRightRadius = radius?.bottomRight ?? 0;
		let bottomLeftRadius = radius?.bottomLeft ?? 0;
		let maxRadius = Math.min(Math.abs(width) / 2, Math.abs(height) / 2);
		topLeftRadius = Math.min(topLeftRadius, maxRadius);
		topRightRadius = Math.min(topRightRadius, maxRadius);
		bottomRightRadius = Math.min(bottomRightRadius, maxRadius);
		bottomLeftRadius = Math.min(bottomLeftRadius, maxRadius);
		if (topLeftRadius === 0 && topRightRadius === 0 && bottomRightRadius === 0 && bottomLeftRadius === 0) return `M${x},${y} h${width} v${height} h-${width} v-${height}`;
		let topY = y;
		let bottomY = y + height;
		let topLeftRounded = topLeftRadius > 0;
		let topRightRounded = topRightRadius > 0;
		let bottomRightRounded = bottomRightRadius > 0;
		let bottomLeftRounded = bottomLeftRadius > 0;
		let leftX = x;
		let rightX = x + width;
		let path = "";
		if (topLeftRounded) {
			path += `M${leftX},${topY + topLeftRadius}`;
			path += `A${topLeftRadius},${topLeftRadius} 0 0 1 ${leftX + topLeftRadius},${topY}`;
		} else path += `M${leftX},${topY}`;
		if (topRightRounded) {
			path += `H${rightX - topRightRadius}`;
			path += `A${topRightRadius},${topRightRadius} 0 0 1 ${rightX},${topY + topRightRadius}`;
		} else path += `H${rightX}`;
		if (bottomRightRounded) {
			path += `V${bottomY - bottomRightRadius}`;
			path += `A${bottomRightRadius},${bottomRightRadius} 0 0 1 ${rightX - bottomRightRadius},${bottomY}`;
		} else path += `V${bottomY}`;
		if (bottomLeftRounded) {
			path += `H${leftX + bottomLeftRadius}`;
			path += `A${bottomLeftRadius},${bottomLeftRadius} 0 0 1 ${leftX},${bottomY - bottomLeftRadius}`;
		} else path += `H${leftX}`;
		path += `V${topY + (topLeftRounded ? topLeftRadius : 0)}`;
		path += `Z`;
		return path;
	}
	function isDateish(value3) {
		return value3 instanceof Date || typeof value3 === "string" && value3.includes("-") && !isNaN(Date.parse(value3));
	}
	function generateTimeDomain(values) {
		values.sort((a, b) => {
			if (typeof a === "string") return a.localeCompare(b);
			return a - b;
		});
		return [dateFromString(values[0]), dateFromString(values[values.length - 1])];
	}
	function scaleTime(domain, range, values) {
		let minValue = new Date(domain[0]).getTime();
		let domainDiff = new Date(domain[1]).getTime() - minValue;
		let rangeDiff = range[1] - range[0];
		const count = (values ? [...new Set(values)] : []).length;
		return function(value3, { area = false } = {}) {
			if (!value3) return void 0;
			const dateValue = dateFromString(value3);
			if (isNaN(dateValue.getTime())) return void 0;
			if (domainDiff === 0) {
				let center = range[0] + rangeDiff / 2;
				if (area) return {
					start: range[0],
					center,
					end: range[1],
					width: rangeDiff / count
				};
				return {
					start: center,
					center,
					end: center,
					width: 0
				};
			}
			const percent = (dateValue.getTime() - minValue) / domainDiff;
			if (area) {
				let index = Math.floor(percent * count);
				index = Math.min(Math.max(0, index), count - 1);
				let step = rangeDiff / count;
				let start = range[0] + index * step;
				return {
					start,
					center: start + step / 2,
					end: start + step,
					width: step
				};
			}
			let point = range[0] + percent * rangeDiff;
			return {
				start: point,
				center: point,
				end: point,
				width: 0
			};
		};
	}
	function generateDateFormatAndTicks(start, end, tickCount, interval, asArea = false) {
		let startDate = start;
		let endDate = end;
		const diffMinutes = (endDate - startDate) / 1e3 / 60;
		const diffHours = diffMinutes / 60;
		const diffDays = diffHours / 24;
		const diffMonths = diffDays / 30;
		const diffYears = diffDays / 365;
		let format;
		let incrementFn;
		let firstTick;
		switch (interval) {
			case "second":
				format = {
					hour: "numeric",
					minute: "numeric",
					second: "numeric"
				};
				incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds() + 1));
				firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours(), startDate.getUTCMinutes(), startDate.getUTCSeconds()));
				break;
			case "minute":
				format = {
					hour: "numeric",
					minute: "numeric"
				};
				incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes() + 1));
				firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours(), startDate.getUTCMinutes()));
				break;
			case "hour":
				format = {
					hour: "numeric",
					minute: "numeric"
				};
				incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours() + 1));
				firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours()));
				break;
			case "day":
				format = { day: "numeric" };
				incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + 1));
				firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate()));
				break;
			case "week":
				format = {
					day: "numeric",
					month: "short"
				};
				incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + 7));
				firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate()));
				break;
			case "month":
				format = {
					month: "short",
					day: "numeric"
				};
				incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + 1, 1));
				firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), 1));
				break;
			case "year":
				format = { year: "numeric" };
				incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear() + 1, 0, 1));
				firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), 0, 1));
		}
		if (incrementFn) {} else if (diffHours < 1) {
			format = {
				hour: "numeric",
				minute: "numeric"
			};
			let hourStep = tickCount ? Math.max(1, Math.floor(diffMinutes / (tickCount - 1))) : 1;
			incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes() + hourStep));
			firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours()));
		} else if (diffDays < 1) {
			format = { hour: "numeric" };
			let hourStep = tickCount ? Math.max(1, Math.floor(diffHours / (tickCount - 1))) : 1;
			incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours() + hourStep));
			firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), 0));
		} else if (diffDays <= 7) {
			format = {
				weekday: "short",
				hour: "numeric"
			};
			let hourStep = tickCount ? Math.max(1, Math.floor(diffHours / (tickCount - 1))) : 6;
			incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours() + hourStep));
			firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), 0));
		} else if (diffDays <= 30) {
			format = {
				month: "short",
				day: "numeric"
			};
			let dateStep = tickCount ? Math.max(1, Math.floor(diffDays / (tickCount - 1))) : 1;
			incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + dateStep));
			firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate()));
		} else if (diffMonths <= 6) {
			format = {
				month: "short",
				day: "numeric"
			};
			let dateStep = tickCount ? Math.max(1, Math.floor(diffDays / (tickCount - 1))) : 7;
			incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + dateStep));
			firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate()));
		} else if (diffYears < 2) {
			format = { month: "short" };
			let monthStep = tickCount ? Math.max(1, Math.floor(diffMonths / (tickCount - 1))) : 1;
			incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + monthStep, 1));
			firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth()));
		} else {
			format = { year: "numeric" };
			let yearStep = tickCount ? Math.max(1, Math.floor(diffYears / (tickCount - 1))) : 1;
			incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear() + yearStep, 0, 1));
			firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), 0, 1));
		}
		let startingTick = startDate;
		if (asArea) {
			let nextTick = incrementFn(firstTick);
			while (nextTick <= startDate) {
				firstTick = nextTick;
				nextTick = incrementFn(firstTick);
			}
			startingTick = firstTick;
		}
		let ticks = [startingTick];
		let currentTick = startingTick;
		while (currentTick <= endDate) {
			currentTick = incrementFn(currentTick);
			if (currentTick <= endDate) ticks.push(new Date(currentTick.getTime()));
		}
		return [format, ticks];
	}
	function dateFromString(value3) {
		if (value3 instanceof Date) return value3;
		let { year, month, day, hour, minute, second, millisecond } = parseDateString(value3);
		return new Date(Date.UTC(year, month - 1, day, hour, minute, second, millisecond));
	}
	function parseDateString(dateStr) {
		const match = dateStr.match(/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?(?:T(\d{2}):(\d{2}):(\d{2})(?:\.(\d+))?(Z)?)?$/);
		if (!match) throw new Error("Invalid date format");
		return {
			year: match[1] ? parseInt(match[1], 10) : null,
			month: match[2] ? parseInt(match[2], 10) : null,
			day: match[3] ? parseInt(match[3], 10) : null,
			hour: match[4] ? parseInt(match[4], 10) : null,
			minute: match[5] ? parseInt(match[5], 10) : null,
			second: match[6] ? parseInt(match[6], 10) : null,
			millisecond: match[7] ? parseInt(match[7].padEnd(3, "0"), 10) : null,
			utc: !!match[8]
		};
	}
	var TAU = Math.PI * 2;
	var START_ANGLE = -Math.PI / 2;
	var FULL_CIRCLE_EPSILON = 1e-9;
	var PIE_PALETTE = [
		"blue",
		"violet",
		"emerald",
		"amber",
		"rose",
		"cyan",
		"fuchsia",
		"lime",
		"orange",
		"teal",
		"indigo",
		"pink",
		"sky",
		"green",
		"yellow",
		"red",
		"purple"
	];
	function generatePieSectors(data, { field = "value", labelField = "index", colorAssignments = /* @__PURE__ */ new Map() } = {}) {
		let { items, issues } = normalizePieData(data, {
			field,
			labelField
		});
		return {
			sectors: partitionPie(resolvePieColors(items, colorAssignments)),
			issues
		};
	}
	function normalizePieData(data, { field = "value", labelField = "index" } = {}) {
		let issues = /* @__PURE__ */ new Set();
		let items = [];
		data.forEach((datum, index) => {
			let raw = datum[field];
			if (raw === void 0 || raw === null) return;
			let value3 = parseValue(raw);
			if (!Number.isFinite(value3)) return issues.add("invalid");
			if (value3 < 0) return issues.add("negative");
			if (value3 === 0) return;
			let color = datum.color === void 0 || datum.color === null ? null : String(datum.color);
			if (color !== null && !PIE_PALETTE.includes(color)) {
				issues.add("invalid-color");
				color = null;
			}
			items.push({
				key: resolveKey(datum, labelField, index),
				datum,
				label: datum[labelField],
				value: value3,
				index,
				color
			});
		});
		if (data.length > 0 && data.every((datum) => datum[field] === void 0)) issues.add("missing-field");
		if (new Set(items.map((item) => String(item.key))).size !== items.length) issues.add("duplicate-key");
		return {
			items,
			issues: [...issues]
		};
	}
	function resolvePieColors(items, assignments = /* @__PURE__ */ new Map(), palette = PIE_PALETTE) {
		let keys = [...new Set(items.filter((item) => !item.color).map((item) => String(item.key)))];
		let taken = new Set(keys.filter((key) => assignments.has(key)).map((key) => assignments.get(key)));
		keys.filter((key) => !assignments.has(key)).sort().forEach((key) => {
			let slot = hashString(key) % palette.length;
			if (taken.size < palette.length) while (taken.has(palette[slot])) slot = (slot + 1) % palette.length;
			assignments.set(key, palette[slot]);
			taken.add(palette[slot]);
		});
		return items.map((item) => ({
			...item,
			color: item.color ?? assignments.get(String(item.key))
		}));
	}
	function partitionPie(items) {
		let total = items.reduce((sum, item) => sum + item.value, 0);
		if (!(total > 0)) return [];
		let angle = START_ANGLE;
		return items.map((item) => {
			let startAngle = angle;
			angle += item.value / total * TAU;
			return {
				...item,
				total,
				percentage: item.value / total,
				startAngle,
				endAngle: angle
			};
		});
	}
	function resolvePieLayout({ width, height, inset = {}, innerRadius = 0, separatorWidth = 0 }) {
		let left = inset.left ?? 0;
		let right = inset.right ?? 0;
		let top = inset.top ?? 0;
		let bottom = inset.bottom ?? 0;
		let availableWidth = Math.max(0, width - left - right);
		let availableHeight = Math.max(0, height - top - bottom);
		let outerRadius = Math.max(0, Math.min(availableWidth, availableHeight) / 2 - separatorWidth / 2);
		let resolvedInnerRadius = parseInnerRadius(innerRadius, outerRadius);
		resolvedInnerRadius = Math.min(Math.max(0, resolvedInnerRadius), Math.max(0, outerRadius - 1));
		return {
			cx: left + availableWidth / 2,
			cy: top + availableHeight / 2,
			outerRadius,
			innerRadius: resolvedInnerRadius
		};
	}
	function getSectorPath({ cx, cy, innerRadius = 0, outerRadius, startAngle, endAngle, cornerRadius = 0 }) {
		let r0 = Math.max(0, innerRadius);
		let r1 = outerRadius;
		let a0 = startAngle;
		let a1 = endAngle;
		let da = a1 - a0;
		if (!(r1 > 0) || !(da > 0)) return "";
		let point = (radius, angle) => `${cx + Math.cos(angle) * radius},${cy + Math.sin(angle) * radius}`;
		let arc = (radius, largeArc, sweep, to) => `A${radius},${radius} 0 ${largeArc} ${sweep} ${to}`;
		if (da >= TAU - FULL_CIRCLE_EPSILON) {
			let opposite = a0 + Math.PI;
			let outer = `M${point(r1, a0)} ${arc(r1, 1, 1, point(r1, opposite))} ${arc(r1, 1, 1, point(r1, a0))} Z`;
			if (r0 <= 0) return outer;
			return `${outer} M${point(r0, a0)} ${arc(r0, 1, 0, point(r0, opposite))} ${arc(r0, 1, 0, point(r0, a0))} Z`;
		}
		let { outer: rc1, inner: rc0 } = clampCornerRadius(cornerRadius, r0, r1, da);
		let parts = [];
		if (rc1 > 0) {
			let rho1 = r1 - rc1;
			let delta1 = Math.min(Math.asin(rc1 / rho1), da / 2);
			let foot1 = rho1 * Math.cos(delta1);
			parts.push(`M${point(foot1, a0)}`);
			parts.push(arc(rc1, 0, 1, point(r1, a0 + delta1)));
			parts.push(arc(r1, da - 2 * delta1 > Math.PI ? 1 : 0, 1, point(r1, a1 - delta1)));
			parts.push(arc(rc1, 0, 1, point(foot1, a1)));
		} else {
			parts.push(`M${point(r1, a0)}`);
			parts.push(arc(r1, da > Math.PI ? 1 : 0, 1, point(r1, a1)));
		}
		if (r0 <= 0) parts.push(`L${cx},${cy}`);
		else if (rc0 > 0) {
			let rho0 = r0 + rc0;
			let delta0 = Math.min(Math.asin(rc0 / rho0), da / 2);
			let foot0 = rho0 * Math.cos(delta0);
			parts.push(`L${point(foot0, a1)}`);
			parts.push(arc(rc0, 0, 1, point(r0, a1 - delta0)));
			parts.push(arc(r0, da - 2 * delta0 > Math.PI ? 1 : 0, 0, point(r0, a0 + delta0)));
			parts.push(arc(rc0, 0, 1, point(foot0, a0)));
		} else {
			parts.push(`L${point(r0, a1)}`);
			parts.push(arc(r0, da > Math.PI ? 1 : 0, 0, point(r0, a0)));
		}
		parts.push("Z");
		return parts.join(" ");
	}
	function clampCornerRadius(cornerRadius, r0, r1, da) {
		let rc = Math.min(Math.max(0, Number(cornerRadius) || 0), (r1 - r0) / 2);
		let outer = rc;
		let inner = r0 > 0 ? rc : 0;
		if (rc > 0 && da < Math.PI) {
			let s = Math.sin(da / 2);
			outer = Math.min(outer, r1 * s / (1 + s));
			if (s < 1) inner = Math.min(inner, r0 * s / (1 - s));
		}
		return {
			outer: outer > 1e-6 ? outer : 0,
			inner: inner > 1e-6 ? inner : 0
		};
	}
	function parseValue(raw) {
		if (typeof raw === "number") return raw;
		if (typeof raw === "string" && raw.trim() !== "") return Number(raw);
		return NaN;
	}
	function parseInnerRadius(value3, outerRadius) {
		if (typeof value3 === "number") return value3;
		let string = String(value3 ?? "").trim();
		if (string.endsWith("%")) return outerRadius * (Number(string.slice(0, -1)) || 0) / 100;
		return Number(string.replace(/px$/, "")) || 0;
	}
	function resolveKey(datum, labelField, index) {
		if (datum.id !== void 0 && datum.id !== null) return datum.id;
		if (datum[labelField] !== void 0 && datum[labelField] !== null) return datum[labelField];
		return index;
	}
	function hashString(string) {
		let hash = 2166136261;
		for (let i = 0; i < string.length; i++) {
			hash ^= string.charCodeAt(i);
			hash = Math.imul(hash, 16777619);
		}
		return hash >>> 0;
	}
	var UIChart = class extends HTMLElement {
		constructor() {
			super();
			this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
			let svgTemplate = this.querySelector("template[name=\"svg\"]");
			let svg = hydrateTemplate(svgTemplate);
			svgTemplate.after(svg);
			this.init(svgTemplate, svg);
		}
		init(svgTemplate, svg) {
			if (!this._initialized) {
				this._data = this.hasAttribute("value") ? JSON.parse(this.getAttribute("value")) : [];
				this._observable = new Observable();
				this._selectable = {
					getState: () => this._data,
					setState: (value3) => {
						this._data = value3;
						this._observable.notify("data", this._data);
					}
				};
				this._controllable = new Controllable(this);
				this._controllable.initial((initial) => initial && this._selectable.setState(initial));
				this._controllable.getter(() => this._selectable.getState());
				this._controllable.setter((value3) => {
					this._selectable.setState(value3);
				});
				this._pieColorAssignments = /* @__PURE__ */ new Map();
				this._initialized = true;
			}
			let horizontal = this.hasAttribute("horizontal");
			let locale = this.hasAttribute("locale") ? this.getAttribute("locale") : getLocale();
			let hasBars = false;
			let hasLines = false;
			let hasAreas = false;
			let hasPoints = false;
			let templates = {
				svg: svgTemplate,
				bars: {},
				lines: {},
				areas: {},
				points: {},
				groups: [],
				stacks: [],
				cursor: svgTemplate.content.querySelector("template[name=\"cursor\"]"),
				zeroLine: svgTemplate.content.querySelector("template[name=\"zero-line\"]"),
				pie: svgTemplate.content.querySelector("template[name=\"pie\"]"),
				axes: {
					x: {
						template: svgTemplate.content.querySelector("template[name=\"axis\"][axis=\"x\"]"),
						axisLine: svgTemplate.content.querySelector("template[name=\"axis\"][axis=\"x\"] template[name=\"axis-line\"]"),
						gridLine: svgTemplate.content.querySelector("template[name=\"axis\"][axis=\"x\"] template[name=\"grid-line\"]"),
						tickMark: svgTemplate.content.querySelector("template[name=\"axis\"][axis=\"x\"] template[name=\"tick-mark\"]"),
						tickLabel: svgTemplate.content.querySelector("template[name=\"axis\"][axis=\"x\"] template[name=\"tick-label\"]")
					},
					y: {
						template: svgTemplate.content.querySelector("template[name=\"axis\"][axis=\"y\"]"),
						axisLine: svgTemplate.content.querySelector("template[name=\"axis\"][axis=\"y\"] template[name=\"axis-line\"]"),
						gridLine: svgTemplate.content.querySelector("template[name=\"axis\"][axis=\"y\"] template[name=\"grid-line\"]"),
						tickMark: svgTemplate.content.querySelector("template[name=\"axis\"][axis=\"y\"] template[name=\"tick-mark\"]"),
						tickLabel: svgTemplate.content.querySelector("template[name=\"axis\"][axis=\"y\"] template[name=\"tick-label\"]")
					}
				},
				tooltip: this.querySelector("template[name=\"tooltip\"]"),
				summary: this.querySelector("template[name=\"summary\"]")
			};
			svgTemplate.content.querySelectorAll("template[name=\"group\"]").forEach((template) => {
				templates.groups.push(template);
			});
			svgTemplate.content.querySelectorAll("template[name=\"stack\"]:not(template[name=\"group\"] template[name=\"stack\"])").forEach((template) => {
				templates.stacks.push(template);
			});
			svgTemplate.content.querySelectorAll("template[name=\"bar\"]:not(template[name=\"stack\"] template[name=\"bar\"]):not(template[name=\"group\"] template[name=\"bar\"])").forEach((template) => {
				templates.bars[template.getAttribute("field")] = template;
				hasBars = true;
			});
			svgTemplate.content.querySelectorAll("template[name=\"line\"]").forEach((template) => {
				templates.lines[template.getAttribute("field")] = template;
				hasLines = true;
			});
			svgTemplate.content.querySelectorAll("template[name=\"area\"]").forEach((template) => {
				templates.areas[template.getAttribute("field")] = template;
				hasAreas = true;
			});
			svgTemplate.content.querySelectorAll("template[name=\"point\"]").forEach((template) => {
				templates.points[template.getAttribute("field")] = template;
				hasPoints = true;
			});
			let repositionCursor = () => {};
			let repositionTooltip = () => {};
			let updateSummary = () => {};
			let activatePoint = () => {};
			let cursor = null;
			let cursorType = "line";
			let overlay = null;
			let tooltip = null;
			let summary = null;
			let stacksMetadata = [];
			svgTemplate.content.querySelectorAll("template[name=\"stack\"]:not(template[name=\"group\"] template[name=\"stack\"])").forEach((stackTemplate) => {
				let stackMetadata = {
					width: stackTemplate.getAttribute("width") ?? "90%",
					gap: stackTemplate.getAttribute("gap") ?? "0",
					radius: stackTemplate.hasAttribute("radius") ? parseRadius(stackTemplate.getAttribute("radius")) : null,
					children: []
				};
				stackTemplate.querySelectorAll("template[name=\"bar\"]").forEach((barTemplate) => {
					let barMetadata = {
						type: "bar",
						field: barTemplate.getAttribute("field"),
						width: barTemplate.getAttribute("width"),
						minHeight: barTemplate.getAttribute("min-height") ?? "0",
						radius: parseRadius(barTemplate.getAttribute("radius") ?? "0")
					};
					stackMetadata.children.push(barMetadata);
					hasBars = true;
				});
				stacksMetadata.push(stackMetadata);
			});
			let groupsMetadata = [];
			svgTemplate.content.querySelectorAll("template[name=\"group\"]").forEach((groupTemplate) => {
				let groupMetadata = {
					width: groupTemplate.getAttribute("width") ?? "90%",
					gap: groupTemplate.getAttribute("gap") ?? "2%",
					children: []
				};
				groupTemplate.querySelectorAll("template[name=\"stack\"], template[name=\"bar\"]:not(template[name=\"stack\"] template[name=\"bar\"])").forEach((itemTemplate) => {
					let itemMetadata = {};
					if (itemTemplate.getAttribute("name") === "stack") {
						itemMetadata = {
							type: "stack",
							width: itemTemplate.getAttribute("width"),
							gap: itemTemplate.getAttribute("gap") ?? "0",
							radius: itemTemplate.hasAttribute("radius") ? parseRadius(itemTemplate.getAttribute("radius")) : null,
							children: []
						};
						itemTemplate.querySelectorAll("template[name=\"bar\"]").forEach((barTemplate) => {
							let barMetadata = {
								type: "bar",
								field: barTemplate.getAttribute("field"),
								width: barTemplate.getAttribute("width"),
								minHeight: barTemplate.getAttribute("min-height") ?? "0",
								radius: parseRadius(barTemplate.getAttribute("radius") ?? "0")
							};
							itemMetadata.children.push(barMetadata);
							hasBars = true;
						});
					} else if (itemTemplate.getAttribute("name") === "bar") {
						itemMetadata = {
							type: "bar",
							field: itemTemplate.getAttribute("field"),
							width: itemTemplate.getAttribute("width"),
							minHeight: itemTemplate.getAttribute("min-height") ?? "0",
							radius: parseRadius(itemTemplate.getAttribute("radius") ?? "4 0")
						};
						hasBars = true;
					}
					groupMetadata.children.push(itemMetadata);
				});
				groupsMetadata.push(groupMetadata);
			});
			let hasCartesianMarks = hasBars || hasLines || hasAreas || hasPoints;
			let requiresMultipleDataPoints = hasCartesianMarks ? hasLines || hasAreas : true;
			let pieTemplates = svgTemplate.content.querySelectorAll("template[name=\"pie\"]");
			let hasPie = pieTemplates.length > 0;
			let hasCartesianChrome = !!(templates.cursor || templates.zeroLine || templates.axes.x.template || templates.axes.y.template);
			let pieConflict = null;
			if (hasPie && (hasCartesianMarks || hasCartesianChrome)) pieConflict = "ui-chart: a pie cannot be combined with bar, line, area, point, axis, cursor, or zero-line marks, so nothing was rendered.";
			else if (pieTemplates.length > 1) pieConflict = "ui-chart: only one pie is allowed per chart, so nothing was rendered.";
			let pieMetadata = null;
			if (hasPie) pieMetadata = {
				field: templates.pie.getAttribute("field") || "value",
				labelField: templates.pie.getAttribute("label-field") || "index",
				innerRadius: templates.pie.getAttribute("inner-radius") ?? "0",
				radius: Number(templates.pie.getAttribute("radius")) || 0,
				separatorWidth: Number(templates.pie.firstElementChild?.getAttribute("stroke-width")) || 0
			};
			overlay = document.createElementNS("http://www.w3.org/2000/svg", "rect");
			overlay.setAttribute("data-overlay", "");
			overlay.setAttribute("fill", "none");
			overlay.setAttribute("pointer-events", "all");
			if (hasPie) overlay.setAttribute("pointer-events", "none");
			overlay.addEventListener("mousemove", throttle(function(event) {
				repositionCursor(event);
				repositionTooltip(event);
				updateSummary(event);
				activatePoint(event);
			}, 1));
			overlay.addEventListener("mouseleave", () => {
				repositionCursor(null);
				repositionTooltip(null);
				updateSummary(null);
				activatePoint(null);
			});
			svg.appendChild(overlay);
			if (templates.cursor) {
				cursor = hydrateSvgTemplate(templates.cursor);
				cursor.setAttribute("data-cursor", "");
				svg.appendChild(cursor);
			}
			let gutter = {
				left: 8,
				right: 8,
				top: 8,
				bottom: 8
			};
			if (templates.svg?.hasAttribute("gutter")) {
				let values = templates.svg?.getAttribute("gutter").split(" ").map((i) => i.replace("px", "")).map(Number);
				if (values.length === 1) {
					gutter.top = values[0];
					gutter.right = values[0];
					gutter.bottom = values[0];
					gutter.left = values[0];
				} else if (values.length === 2) {
					gutter.top = values[0];
					gutter.right = values[1];
					gutter.bottom = values[0];
					gutter.left = values[1];
				} else if (values.length === 3) {
					gutter.top = values[0];
					gutter.right = values[1];
					gutter.bottom = values[2];
					gutter.left = values[1];
				} else if (values.length === 4) {
					gutter.top = values[0];
					gutter.right = values[1];
					gutter.bottom = values[2];
					gutter.left = values[3];
				}
			}
			let [indexKey, valueKeys] = discoverDimensions(svg, hasBars, horizontal);
			let svgRect = svg.parentElement.getBoundingClientRect();
			if (svgRect.width === 0 || svgRect.height === 0) {
				let visibilityObserver = new ResizeObserver((entries) => {
					let entry = entries[0];
					if (entry.contentRect.width > 0 && entry.contentRect.height > 0) {
						visibilityObserver.disconnect();
						this.init(svgTemplate, svg);
					}
				});
				visibilityObserver.observe(svg.parentElement);
				return;
			}
			if (pieConflict) console.warn(pieConflict);
			if (templates.tooltip) {
				tooltip = hydrateTemplate(templates.tooltip);
				templates.tooltip.after(tooltip);
			}
			if (templates.summary) {
				summary = hydrateTemplate(templates.summary);
				templates.summary.after(summary);
			}
			let chart = generateChartObject({
				horizontal,
				locale,
				data: this._data,
				stacksMetadata,
				groupsMetadata,
				width: svgRect.width,
				height: svgRect.height,
				inset: {
					left: 0,
					right: 0,
					top: 0,
					bottom: 0
				},
				dimensions: {
					index: {
						key: indexKey,
						asArea: hasBars || templates.cursor?.innerHTML?.match(/type="([^"]+)"/)?.[1] === "area"
					},
					value: {
						keys: valueKeys,
						includeZero: hasBars
					}
				},
				axes: {
					x: {
						format: templates.axes.x.template?.hasAttribute("format") ? JSON.parse(templates.axes.x.template?.getAttribute("format")) : null,
						scale: templates.axes.x.template?.getAttribute("scale"),
						interval: templates.axes.x.template?.getAttribute("interval"),
						position: templates.axes.x.template?.getAttribute("position"),
						tickCount: templates.axes.x.template?.getAttribute("tick-count") || templates.axes.x.tickLabel?.getAttribute("target-tick-count"),
						tickStart: templates.axes.x.template?.getAttribute("tick-start"),
						tickEnd: templates.axes.x.template?.getAttribute("tick-end"),
						tickStep: templates.axes.x.template?.getAttribute("tick-step") ? Number(templates.axes.x.template?.getAttribute("tick-step")) : null,
						tickSuffix: templates.axes.x.template?.getAttribute("tick-suffix"),
						tickPrefix: templates.axes.x.template?.getAttribute("tick-prefix"),
						tickValues: templates.axes.x.template?.hasAttribute("tick-values") ? JSON.parse(templates.axes.x.template?.getAttribute("tick-values")) : null
					},
					y: {
						format: templates.axes.y.template?.hasAttribute("format") ? JSON.parse(templates.axes.y.template?.getAttribute("format")) : null,
						scale: templates.axes.y.template?.getAttribute("scale"),
						interval: templates.axes.y.template?.getAttribute("interval"),
						position: templates.axes.y.template?.getAttribute("position"),
						tickCount: templates.axes.y.template?.getAttribute("tick-count") || templates.axes.y.tickLabel?.getAttribute("target-tick-count"),
						tickStart: templates.axes.y.template?.getAttribute("tick-start"),
						tickEnd: templates.axes.y.template?.getAttribute("tick-end"),
						tickStep: templates.axes.y.template?.getAttribute("tick-step") ? Number(templates.axes.y.template?.getAttribute("tick-step")) : null,
						tickSuffix: templates.axes.y.template?.getAttribute("tick-suffix"),
						tickPrefix: templates.axes.y.template?.getAttribute("tick-prefix"),
						tickValues: templates.axes.y.template?.hasAttribute("tick-values") ? JSON.parse(templates.axes.y.template?.getAttribute("tick-values")) : null
					}
				}
			});
			let renderTooltipContent = (datum) => {
				let tooltipClone = templates.tooltip.content.cloneNode(true).firstElementChild;
				tooltipClone.querySelectorAll("slot").forEach((slot) => {
					let field = slot.getAttribute("field");
					if (field) {
						let format = slot.hasAttribute("format") ? JSON.parse(slot.getAttribute("format")) : null;
						let value3 = datum[field];
						if (format === null) return slot.textContent = value3;
						format = {
							...format,
							timeZone: "UTC"
						};
						if (isNumeric(value3)) value3 = Number(value3).toLocaleString(chart.locale, format);
						else if (isDateish2(value3)) value3 = new Date(value3).toLocaleDateString(chart.locale, format);
						slot.textContent = value3;
					}
				});
				tooltip.innerHTML = tooltipClone.innerHTML;
			};
			let positionTooltip = (anchorX, anchorY) => {
				let tooltipRect = tooltip.getBoundingClientRect();
				let svgRect2 = svg.getBoundingClientRect();
				let rightSpace = svgRect2.width - (anchorX + tooltipRect.width + 15);
				let bottomSpace = svgRect2.height - (anchorY + tooltipRect.height + 15);
				let xOffset = rightSpace < 0 ? anchorX - tooltipRect.width - 15 : anchorX + 15;
				let yOffset = bottomSpace < 0 ? anchorY - tooltipRect.height - 15 : anchorY + 15;
				let originX = 0;
				let originY = 0;
				if (tooltip.offsetParent) {
					let parentRect = tooltip.offsetParent.getBoundingClientRect();
					originX = svgRect2.left - (parentRect.left + tooltip.offsetParent.clientLeft) - tooltip.offsetLeft;
					originY = svgRect2.top - (parentRect.top + tooltip.offsetParent.clientTop) - tooltip.offsetTop;
				}
				tooltip.style.transform = `translate(${originX + xOffset}px, ${originY + yOffset}px)`;
			};
			let renderSummaryContent = (datum, useFallback) => {
				let summaryClone = templates.summary.content.cloneNode(true).firstElementChild;
				summaryClone.querySelectorAll("slot").forEach((slot) => {
					let field = slot.getAttribute("field");
					if (field) {
						let format = slot.hasAttribute("format") ? JSON.parse(slot.getAttribute("format")) : {};
						format = {
							...format,
							timeZone: "UTC"
						};
						let value3 = slot.hasAttribute("fallback") && useFallback ? slot.getAttribute("fallback") : datum[field];
						if (isNumeric(value3)) value3 = Number(value3).toLocaleString(chart.locale, format);
						else if (isDateish2(value3)) value3 = new Date(value3).toLocaleString(chart.locale, format);
						slot.textContent = value3;
					}
				});
				summary.innerHTML = summaryClone.innerHTML;
			};
			let orderMarks = () => {
				svg.appendChild(overlay);
				svg.querySelectorAll("[data-grid-line-group]").forEach((i) => overlay.before(i));
				svg.querySelectorAll("[data-axis-line]").forEach((i) => overlay.before(i));
				if (cursorType === "area") svg.querySelectorAll("[data-cursor]").forEach((i) => overlay.before(i));
				let groupIndex = 0;
				let stackIndex = 0;
				svgTemplate.content.querySelectorAll("template[name=\"group\"], template[name=\"stack\"]:not(template[name=\"group\"] template[name=\"stack\"]), template[name=\"bar\"]:not(template[name=\"stack\"] template[name=\"bar\"]):not(template[name=\"group\"] template[name=\"bar\"]), template[name=\"line\"], template[name=\"area\"], template[name=\"point\"], template[name=\"pie\"]").forEach((template) => {
					let name = template.getAttribute("name");
					let field = template.getAttribute("field");
					let el;
					if (name === "group") {
						el = svg.querySelector(`[data-group][data-group-index="${groupIndex}"]`);
						groupIndex++;
					} else if (name === "stack") {
						el = svg.querySelector(`[data-stack][data-stack-index="${stackIndex}"]`);
						stackIndex++;
					} else if (name === "bar") el = svg.querySelector(`[data-bar-group][data-series="${field}"]`);
					else if (name === "point") el = svg.querySelector(`[data-point-group][data-series="${field}"]`);
					else if (name === "pie") el = svg.querySelector(`[data-pie-group][data-series="${field}"]`);
					else el = svg.querySelector(`[data-${name}][data-series="${field}"]`);
					if (el) overlay.before(el);
				});
				svg.querySelectorAll("[data-zero-line]").forEach((i) => overlay.before(i));
				if (cursorType === "line") svg.querySelectorAll("[data-cursor]").forEach((i) => overlay.before(i));
			};
			let insetForOverflow = () => {
				let overflow = getSvgOverflow(svg);
				adjustOverflowForPendingTickRotation(svg, chart, gutter, overflow);
				chart.updateDimensions({
					width: chart.width,
					height: chart.height
				}, {
					left: gutter.left + overflow.left,
					right: gutter.right + overflow.right,
					top: gutter.top + overflow.top,
					bottom: gutter.bottom + overflow.bottom
				});
			};
			let pieSectors = [];
			let updatePieSectors = () => {
				if (!pieMetadata || pieConflict) return;
				let { sectors, issues } = generatePieSectors(chart.data, {
					field: pieMetadata.field,
					labelField: pieMetadata.labelField,
					colorAssignments: this._pieColorAssignments
				});
				pieSectors = sectors;
				issues.forEach((issue) => console.warn(pieWarning(issue, pieMetadata.field)));
			};
			updatePieSectors();
			let renderDefaultPieSummary = () => {
				let lastSector = pieSectors[pieSectors.length - 1];
				if (lastSector) renderSummaryContent(lastSector.datum, true);
			};
			let activateSector = (event, sector) => {
				svg.querySelectorAll("[data-sector]").forEach((i) => {
					removeAttribute(i, "data-active");
					removeAttribute(i, "data-inactive");
				});
				if (event === null) {
					if (tooltip) removeAttribute(tooltip, "data-active");
					if (summary) renderDefaultPieSummary();
					return;
				}
				svg.querySelectorAll("[data-sector]").forEach((i) => {
					setAttribute2(i, i === event.currentTarget ? "data-active" : "data-inactive", "");
				});
				if (tooltip) {
					let svgRect2 = svg.getBoundingClientRect();
					setAttribute2(tooltip, "data-active", "");
					tooltip.style.setProperty("--os-chart-color", pieFill(sector));
					positionTooltip(event.clientX - svgRect2.left, event.clientY - svgRect2.top);
					renderTooltipContent(sector.datum);
				}
				if (summary) renderSummaryContent(sector.datum, false);
			};
			let redrawPie = (checkOverflow = true) => {
				svg.querySelector("[data-pie-group]")?.remove();
				if (pieConflict || pieSectors.length === 0) return;
				let layout = resolvePieLayout({
					width: chart.width,
					height: chart.height,
					inset: chart.inset,
					innerRadius: pieMetadata.innerRadius,
					separatorWidth: pieMetadata.separatorWidth
				});
				let pieGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
				pieGroupEl.setAttribute("data-pie-group", "");
				pieGroupEl.setAttribute("data-series", pieMetadata.field);
				pieSectors.forEach((sector) => {
					let sectorEl = hydrateSvgTemplate(templates.pie);
					sectorEl.setAttribute("data-sector", "");
					sectorEl.setAttribute("data-series", pieMetadata.field);
					sectorEl.setAttribute("data-key", sector.key);
					sectorEl.setAttribute("d", getSectorPath({
						...layout,
						startAngle: sector.startAngle,
						endAngle: sector.endAngle,
						cornerRadius: pieMetadata.radius
					}));
					sectorEl.style.fill = pieFill(sector);
					sectorEl.addEventListener("mousemove", throttle((event) => activateSector(event, sector), 1));
					sectorEl.addEventListener("mouseleave", () => activateSector(null));
					pieGroupEl.appendChild(sectorEl);
				});
				svg.appendChild(pieGroupEl);
				if (summary) renderDefaultPieSummary();
				orderMarks();
				if (checkOverflow) {
					insetForOverflow();
					redrawPie(false);
				}
			};
			let redraw = (checkOverflow = true) => {
				setAttribute2(svg, "viewBox", `0 0 ${chart.width} ${chart.height}`);
				if (hasPie) return redrawPie(checkOverflow);
				if (chart.data.length === 0) return;
				if (chart.data.length === 1 && requiresMultipleDataPoints) {
					console.warn("ui-chart: chart only has one data point so charts with lines or areas cannot be rendered.");
					return;
				}
				if (chart.data[0][chart.dimensions.index.key] === void 0) {
					console.warn(`ui-chart: axis field "${chart.dimensions.index.key}" does not exist`);
					return;
				}
				Object.entries(chart.series).forEach(([field, series]) => {
					let barGroupEl = svg.querySelector(`[data-bar-group][data-series="${field}"]`);
					let template = templates.bars[field];
					if (template) {
						let barWidth = template.hasAttribute("width") ? template.getAttribute("width") : "90%";
						let radius = parseRadius(template.hasAttribute("radius") ? template.getAttribute("radius") : "8 0");
						let minHeight = template.hasAttribute("min-height") ? Number(template.getAttribute("min-height")) : 0;
						barGroupEl?.remove();
						barGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
						barGroupEl.setAttribute("data-bar-group", "");
						barGroupEl.setAttribute("data-series", field);
						series.bars({
							barWidth,
							minHeight,
							radius
						}).forEach((bar) => {
							let barEl = hydrateSvgTemplate(template);
							barEl.setAttribute("data-bar", "");
							barEl.setAttribute("data-series", field);
							barEl.setAttribute("d", bar.path(radius));
							barGroupEl.appendChild(barEl);
						});
						svg.appendChild(barGroupEl);
					}
				});
				Object.entries(chart.stackedSeries).forEach(([index, series]) => {
					let stackEl = svg.querySelector(`[data-stack][data-stack-index="${index}"]`);
					let stackTemplate = templates.stacks[index];
					if (!stackTemplate) return;
					stackEl?.remove();
					stackEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
					stackEl.setAttribute("data-stack", "");
					stackEl.setAttribute("data-stack-index", index);
					let stackTemplates = { bars: {} };
					stackTemplate.querySelectorAll("template[name=\"bar\"]").forEach((template) => {
						stackTemplates.bars[template.getAttribute("field")] = template;
					});
					Object.entries(series.bars()).forEach(([field, bars]) => {
						let barTemplate = stackTemplates.bars[field];
						let stackBarGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
						stackBarGroupEl.setAttribute("data-bar-group", "");
						stackBarGroupEl.setAttribute("data-series", field);
						bars.forEach((bar) => {
							let barEl = hydrateSvgTemplate(barTemplate);
							barEl.setAttribute("data-bar", "");
							barEl.setAttribute("data-series", field);
							barEl.setAttribute("d", bar.path());
							stackBarGroupEl.appendChild(barEl);
						});
						stackEl.appendChild(stackBarGroupEl);
					});
					svg.appendChild(stackEl);
				});
				Object.entries(chart.groupedSeries).forEach(([index, series]) => {
					let groupEl = svg.querySelector(`[data-group][data-group-index="${index}"]`);
					let groupTemplate = templates.groups[index];
					if (!groupTemplate) return;
					groupEl?.remove();
					groupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
					groupEl.setAttribute("data-group", "");
					groupEl.setAttribute("data-group-index", index);
					let groupTemplates = { bars: {} };
					groupTemplate.querySelectorAll("template[name=\"bar\"]").forEach((template) => {
						groupTemplates.bars[template.getAttribute("field")] = template;
					});
					Object.entries(series.bars()).forEach(([field, bars]) => {
						let barTemplate = groupTemplates.bars[field];
						let groupBarGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
						groupBarGroupEl.setAttribute("data-bar-group", "");
						groupBarGroupEl.setAttribute("data-series", field);
						bars.forEach((bar) => {
							let barEl = hydrateSvgTemplate(barTemplate);
							barEl.setAttribute("data-bar", "");
							barEl.setAttribute("data-series", field);
							barEl.setAttribute("d", bar.path());
							groupBarGroupEl.appendChild(barEl);
						});
						groupEl.appendChild(groupBarGroupEl);
					});
					svg.appendChild(groupEl);
				});
				Object.entries(chart.series).forEach(([field, series]) => {
					let line = svg.querySelector(`[data-line][data-series="${field}"]`);
					let template = templates.lines[field];
					if (template) {
						let curve = template.getAttribute("curve") || "smooth";
						if (!line) {
							let lineEl = hydrateSvgTemplate(template);
							lineEl.setAttribute("data-line", "");
							lineEl.setAttribute("data-series", field);
							lineEl.setAttribute("d", series.linePath(curve));
							svg.appendChild(lineEl);
						} else line.setAttribute("d", series.linePath(curve));
					}
				});
				Object.entries(chart.series).forEach(([field, series]) => {
					let area = svg.querySelector(`[data-area][data-series="${field}"]`);
					let template = templates.areas[field];
					if (template) {
						let curve = template.getAttribute("curve") || "smooth";
						if (!area) {
							let areaEl = hydrateSvgTemplate(template);
							areaEl.setAttribute("data-area", "");
							areaEl.setAttribute("data-series", field);
							areaEl.setAttribute("d", series.areaPath(curve));
							svg.appendChild(areaEl);
						} else area.setAttribute("d", series.areaPath(curve));
					}
				});
				Object.entries(chart.series).forEach(([field, series]) => {
					let pointGroupEl = svg.querySelector(`[data-point-group][data-series="${field}"]`);
					let template = templates.points[field];
					if (template) {
						svg.querySelector(`[data-point-group][data-series="${field}"]`)?.remove();
						pointGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
						pointGroupEl.setAttribute("data-point-group", "");
						pointGroupEl.setAttribute("data-series", field);
						series.points.forEach((point) => {
							if (!isFinite(point.x) || !isFinite(point.y)) return;
							let pointEl = hydrateSvgTemplate(template);
							pointEl.setAttribute("data-point", "");
							pointEl.setAttribute("data-series", field);
							pointEl.setAttribute("cx", point.x);
							pointEl.setAttribute("cy", point.y);
							pointGroupEl.appendChild(pointEl);
						});
						svg.appendChild(pointGroupEl);
					}
				});
				activatePoint = (event) => {
					svg.querySelectorAll("[data-point-group]").forEach((pointGroupEl) => {
						pointGroupEl.querySelectorAll("[data-point]").forEach((i) => i.removeAttribute("data-active"));
						if (event) {
							let mouseX = event.clientX - svg.getBoundingClientRect().left;
							let mouseY = event.clientY - svg.getBoundingClientRect().top;
							if (mouseX >= chart.inset.left && mouseX <= chart.width - chart.inset.right && mouseY >= chart.inset.top && mouseY <= chart.height - chart.inset.bottom) chart.closestIndexPoints(chart.indexPosition(mouseX, mouseY)).forEach((point) => {
								let index = chart.data.indexOf(point.datum);
								pointGroupEl.querySelectorAll("[data-point]")[index]?.setAttribute("data-active", "");
							});
						} else pointGroupEl.querySelectorAll("[data-point]").forEach((i) => i.removeAttribute("data-active"));
					});
				};
				if (templates.axes.x.template) {
					if (templates.axes.x.axisLine) {
						svg.querySelector("[data-axis-line][data-axis=\"x\"]")?.remove();
						let axisLineEl = hydrateSvgTemplate(templates.axes.x.axisLine);
						axisLineEl.setAttribute("data-axis-line", "");
						axisLineEl.setAttribute("data-axis", "x");
						axisLineEl.setAttribute("x1", chart.axes.x.range[0]);
						axisLineEl.setAttribute("x2", chart.axes.x.range[1]);
						let y = chart.axes.x.position === "bottom" ? Math.max(...chart.axes.y.range) : Math.min(...chart.axes.y.range);
						axisLineEl.setAttribute("y1", y);
						axisLineEl.setAttribute("y2", y);
						svg.appendChild(axisLineEl);
					}
					if (templates.axes.x.gridLine) {
						svg.querySelector("[data-grid-line-group][data-axis=\"x\"]")?.remove();
						let gridLineGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
						gridLineGroupEl.setAttribute("data-grid-line-group", "");
						gridLineGroupEl.setAttribute("data-axis", "x");
						chart.axes.x.ticks.forEach(({ value: value3 }) => {
							let gridLineEl = hydrateSvgTemplate(templates.axes.x.gridLine);
							gridLineEl.setAttribute("data-grid-line", "");
							gridLineEl.setAttribute("data-axis", "x");
							gridLineEl.setAttribute("x1", chart.axes.x.scale(value3, { area: chart.axes.x.dimension.asArea }).center);
							gridLineEl.setAttribute("x2", chart.axes.x.scale(value3, { area: chart.axes.x.dimension.asArea }).center);
							gridLineEl.setAttribute("y1", chart.inset.top);
							gridLineEl.setAttribute("y2", chart.height - chart.inset.bottom);
							gridLineGroupEl.appendChild(gridLineEl);
						});
						svg.appendChild(gridLineGroupEl);
					}
					if (templates.axes.x.tickMark) {
						svg.querySelector("[data-tick-mark-group][data-axis=\"x\"]")?.remove();
						let tickMarkGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
						tickMarkGroupEl.setAttribute("data-tick-mark-group", "");
						tickMarkGroupEl.setAttribute("data-axis", "x");
						chart.axes.x.ticks.forEach(({ value: value3, label }) => {
							let position = {
								x: chart.axes.x.scale(value3, { area: chart.axes.x.dimension.asArea }).center,
								y: chart.axes.x.position === "bottom" ? Math.max(...chart.axes.y.range) : Math.min(...chart.axes.y.range)
							};
							let tickMarkEl = hydrateSvgTemplate(templates.axes.x.tickMark);
							tickMarkEl.setAttribute("data-tick-mark", "");
							tickMarkEl.setAttribute("data-axis", "x");
							tickMarkEl.setAttribute("transform", `translate(${position.x}, ${position.y})`);
							tickMarkGroupEl.appendChild(tickMarkEl);
						});
						svg.appendChild(tickMarkGroupEl);
					}
					if (templates.axes.x.tickLabel) {
						svg.querySelector("[data-tick-label-group][data-axis=\"x\"]")?.remove();
						let tickLabelGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
						tickLabelGroupEl.setAttribute("data-tick-label-group", "");
						tickLabelGroupEl.setAttribute("data-axis", "x");
						chart.axes.x.ticks.forEach(({ value: value3, label }) => {
							let position = {
								x: chart.axes.x.scale(value3, { area: chart.axes.x.dimension.asArea }).center,
								y: chart.axes.x.position === "bottom" ? Math.max(...chart.axes.y.range) : Math.min(...chart.axes.y.range)
							};
							let tickLabelEl = hydrateSvgTemplate(templates.axes.x.tickLabel);
							tickLabelEl.querySelectorAll("slot").forEach((i) => i.replaceWith(document.createTextNode(label)));
							tickLabelEl.setAttribute("data-tick-label", "");
							tickLabelEl.setAttribute("data-axis", "x");
							tickLabelEl.setAttribute("transform", `translate(${position.x}, ${position.y})`);
							tickLabelGroupEl.appendChild(tickLabelEl);
						});
						svg.appendChild(tickLabelGroupEl);
						handleTickOverflow(tickLabelGroupEl, chart.axes.x);
					}
				}
				if (templates.axes.y.template) {
					if (templates.axes.y.axisLine) {
						svg.querySelector("[data-axis-line][data-axis=\"y\"]")?.remove();
						let axisLineEl = hydrateSvgTemplate(templates.axes.y.axisLine);
						axisLineEl.setAttribute("data-axis-line", "");
						axisLineEl.setAttribute("data-axis", "y");
						axisLineEl.setAttribute("x1", chart.axes.x.range[chart.axes.y.position === "left" ? 0 : 1]);
						axisLineEl.setAttribute("x2", chart.axes.x.range[chart.axes.y.position === "left" ? 0 : 1]);
						axisLineEl.setAttribute("y1", chart.axes.y.range[0]);
						axisLineEl.setAttribute("y2", chart.axes.y.range[1]);
						svg.appendChild(axisLineEl);
					}
					if (templates.axes.y.gridLine) {
						svg.querySelector("[data-grid-line-group][data-axis=\"y\"]")?.remove();
						let gridLineGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
						gridLineGroupEl.setAttribute("data-grid-line-group", "");
						gridLineGroupEl.setAttribute("data-axis", "y");
						chart.axes.y.ticks.forEach(({ value: value3, label }) => {
							let position = {
								x: chart.axes.x.range[0],
								y: chart.axes.y.scale(value3, { area: chart.axes.y.dimension.asArea }).center
							};
							let gridLineEl = hydrateSvgTemplate(templates.axes.y.gridLine);
							gridLineEl.setAttribute("data-grid-line", "");
							gridLineEl.setAttribute("data-axis", "y");
							gridLineEl.setAttribute("x1", chart.inset.left);
							gridLineEl.setAttribute("x2", chart.width - chart.inset.right);
							gridLineEl.setAttribute("y1", position.y);
							gridLineEl.setAttribute("y2", position.y);
							gridLineGroupEl.appendChild(gridLineEl);
						});
						svg.appendChild(gridLineGroupEl);
					}
					if (templates.axes.y.tickMark) {
						svg.querySelector("[data-tick-mark-group][data-axis=\"y\"]")?.remove();
						let tickMarkGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
						tickMarkGroupEl.setAttribute("data-tick-mark-group", "");
						tickMarkGroupEl.setAttribute("data-axis", "y");
						chart.axes.y.ticks.forEach(({ value: value3, label }) => {
							let position = {
								x: chart.axes.x.range[chart.axes.y.position === "left" ? 0 : 1],
								y: chart.axes.y.scale(value3, { area: chart.axes.y.dimension.asArea }).center
							};
							let tickMarkEl = hydrateSvgTemplate(templates.axes.y.tickMark);
							tickMarkEl.setAttribute("data-tick-mark", "");
							tickMarkEl.setAttribute("data-axis", "y");
							tickMarkEl.setAttribute("transform", `translate(${position.x}, ${position.y})`);
							tickMarkGroupEl.appendChild(tickMarkEl);
						});
						svg.appendChild(tickMarkGroupEl);
					}
					if (templates.axes.y.tickLabel) {
						svg.querySelector("[data-tick-label-group][data-axis=\"y\"]")?.remove();
						let tickLabelGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
						tickLabelGroupEl.setAttribute("data-tick-label-group", "");
						tickLabelGroupEl.setAttribute("data-axis", "y");
						chart.axes.y.ticks.forEach(({ value: value3, label }) => {
							let position = {
								x: chart.axes.x.range[chart.axes.y.position === "left" ? 0 : 1],
								y: chart.axes.y.scale(value3, { area: chart.axes.y.dimension.asArea }).center
							};
							let tickLabelEl = hydrateSvgTemplate(templates.axes.y.tickLabel);
							tickLabelEl.querySelectorAll("slot").forEach((i) => i.replaceWith(document.createTextNode(label)));
							tickLabelEl.setAttribute("data-tick-label", "");
							tickLabelEl.setAttribute("data-axis", "y");
							tickLabelEl.setAttribute("transform", `translate(${position.x}, ${position.y})`);
							tickLabelGroupEl.appendChild(tickLabelEl);
						});
						svg.appendChild(tickLabelGroupEl);
						handleTickOverflow(tickLabelGroupEl, chart.axes.y);
					}
				}
				svg.querySelector("[data-zero-line]")?.remove();
				if (templates.zeroLine && chart.dimensions.value.axis.domain[0] < 0 && chart.dimensions.value.axis.domain[1] > 0) {
					let zeroLineEl = hydrateSvgTemplate(templates.zeroLine);
					zeroLineEl.setAttribute("data-zero-line", "");
					zeroLineEl.setAttribute("data-axis", chart.dimensions.value.axis.axis);
					let { x1, x2, y1, y2 } = chart.dimensions.value.zeroLine();
					zeroLineEl.setAttribute("x1", x1);
					zeroLineEl.setAttribute("x2", x2);
					zeroLineEl.setAttribute("y1", y1);
					zeroLineEl.setAttribute("y2", y2);
					svg.appendChild(zeroLineEl);
				}
				if (overlay) {
					overlay.setAttribute("width", chart.width);
					overlay.setAttribute("height", chart.height);
					overlay.setAttribute("x", 0);
					overlay.setAttribute("y", 0);
				}
				if (cursor) {
					cursorType = cursor.getAttribute("type") ?? "line";
					let renderCursor = (closestIndexPoint) => {
						if (cursorType === "area") {
							cursor.setAttribute("opacity", "0.1");
							let { x, y, width, height } = chart.cursor(closestIndexPoint, "area");
							let radius = parseRadius(cursor.getAttribute("radius") ?? "0");
							cursor.setAttribute("fill", "currentColor");
							cursor.setAttribute("stroke", "none");
							cursor.setAttribute("d", getBarPath(x, y, width, height, radius));
							return;
						}
						cursor.setAttribute("opacity", "1");
						let [start, end] = chart.cursor(closestIndexPoint);
						cursor.setAttribute("d", `M ${start.x} ${start.y} L ${end.x} ${end.y}`);
					};
					repositionCursor = (event) => {
						if (event) {
							let svgRect2 = svg.getBoundingClientRect();
							let mouseX = event.clientX - svgRect2.left;
							let mouseY = event.clientY - svgRect2.top;
							if (mouseX >= chart.inset.left && mouseX <= chart.width - chart.inset.right && mouseY >= chart.inset.top && mouseY <= chart.height - chart.inset.bottom) {
								let closestIndexPoint = chart.closestIndexPoint(chart.indexPosition(mouseX, mouseY));
								renderCursor(closestIndexPoint);
							} else cursor.setAttribute("opacity", "0");
						} else cursor.setAttribute("opacity", "0");
					};
				}
				if (tooltip) repositionTooltip = (event) => {
					if (event === null) removeAttribute(tooltip, "data-active");
					else {
						let mouseX = event.clientX - svg.getBoundingClientRect().left;
						let mouseY = event.clientY - svg.getBoundingClientRect().top;
						if (mouseX >= chart.inset.left && mouseX <= chart.width - chart.inset.right && mouseY >= chart.inset.top && mouseY <= chart.height - chart.inset.bottom) {
							let closestIndexPoint = chart.closestIndexPoint(chart.indexPosition(mouseX, mouseY));
							if (closestIndexPoint) {
								setAttribute2(tooltip, "data-active", "");
								let anchorX = chart.horizontal ? mouseX : closestIndexPoint.center;
								let anchorY = chart.horizontal ? closestIndexPoint.center : mouseY;
								positionTooltip(anchorX, anchorY);
								renderTooltipContent(closestIndexPoint.datum);
							} else removeAttribute(tooltip, "data-active");
						} else removeAttribute(tooltip, "data-active");
					}
				};
				if (summary) {
					updateSummary = (event) => {
						let closestIndexPoint = null;
						if (event !== null) {
							let mouseX = event.clientX - svg.getBoundingClientRect().left;
							let mouseY = event.clientY - svg.getBoundingClientRect().top;
							if (mouseX >= chart.inset.left && mouseX <= chart.width - chart.inset.right && mouseY >= chart.inset.top && mouseY <= chart.height - chart.inset.bottom) closestIndexPoint = chart.closestIndexPoint(chart.indexPosition(mouseX, mouseY));
						}
						closestIndexPoint = closestIndexPoint || chart.closestIndexPoint(chart.dimensions.index.axis.range[1]);
						if (closestIndexPoint) renderSummaryContent(closestIndexPoint.datum, event === null);
					};
					updateSummary(null);
				}
				orderMarks();
				if (checkOverflow) {
					insetForOverflow();
					redraw(false);
				}
			};
			this._observable.subscribe("resize", () => {
				let svgRect2 = svg.parentElement.getBoundingClientRect();
				if (svgRect2.width === 0 || svgRect2.height === 0) return;
				chart.updateDimensions({
					width: svgRect2.width,
					height: svgRect2.height
				}, {
					left: 0,
					right: 0,
					top: 0,
					bottom: 0
				});
				redraw();
				if (cursor) cursor.setAttribute("opacity", "0");
			});
			this._observable.subscribe("data", () => {
				chart.updateData(this._data);
				updatePieSectors();
				chart.updateDimensions({
					width: chart.width,
					height: chart.height
				}, {
					left: 0,
					right: 0,
					top: 0,
					bottom: 0
				});
				redraw();
			});
			new ResizeObserver(() => {
				this._observable.notify("resize");
			}).observe(this);
			new MutationObserver((mutations) => {
				mutations.forEach((mutation) => {
					if (mutation.attributeName !== "value") return;
					this._selectable.setState(this.hasAttribute("value") ? JSON.parse(this.getAttribute("value")) : []);
				});
			}).observe(this, {
				attributes: true,
				attributeFilter: ["value"]
			});
		}
	};
	customElements.define("ui-chart", UIChart);
	function discoverDimensions(svg, hasBars = false, horizontal = false) {
		let indexKey = null;
		let valueKeys = [];
		svg.querySelectorAll("template[name=\"line\"], template[name=\"area\"], template[name=\"point\"], template[name=\"pie\"], template[name=\"bar\"]:not(template[name=\"stack\"] template[name=\"bar\"]):not(template[name=\"group\"] template[name=\"bar\"])").forEach((template) => {
			valueKeys.push(template.getAttribute("field") || "value");
		});
		svg.querySelectorAll(`template[name="axis"][axis="${horizontal ? "y" : "x"}"]`).forEach((template) => {
			indexKey = template.getAttribute("field") || "index";
		});
		svg.querySelectorAll("template[name=\"pie\"][label-field]").forEach((template) => {
			indexKey = template.getAttribute("label-field");
		});
		valueKeys = Array.from(new Set(valueKeys));
		if (indexKey === null) indexKey = "index";
		if (valueKeys.length === 0 && !hasBars) valueKeys = ["value"];
		return [indexKey, valueKeys];
	}
	function pieFill(sector) {
		return `var(--color-${sector.color}-500)`;
	}
	function pieWarning(issue, field) {
		return {
			"missing-field": `ui-chart: series field "${field}" does not exist`,
			"invalid": "ui-chart: pie values must be finite numbers, so non-numeric values were omitted.",
			"negative": "ui-chart: pie values must be positive, so negative values were omitted.",
			"duplicate-key": "ui-chart: multiple pie sectors resolved to the same identity (id or label field), so their colors and updates may be ambiguous.",
			"invalid-color": `ui-chart: pie "color" values must be one of ${PIE_PALETTE.join(", ")}, so unsupported colors fell back to the default palette.`
		}[issue];
	}
	function hydrateSvgTemplate(template) {
		let svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
		svg.setAttribute("xmlns", "http://www.w3.org/2000/svg");
		svg.setAttribute("version", "1.1");
		svg.innerHTML = template.innerHTML;
		return svg.firstElementChild;
	}
	function adjustOverflowForPendingTickRotation(svg, chart, gutter, overflow) {
		let tickLabelGroupEl = svg.querySelector("[data-tick-label-group][data-axis=\"x\"]");
		if (!tickLabelGroupEl) return;
		if (chart.axes.x.type !== "categorical") return;
		if (hasOverlappingTicks(tickLabelGroupEl, chart.axes.x)) return;
		let widthReduction = gutter.left + overflow.left + gutter.right + overflow.right - (chart.inset.left + chart.inset.right);
		if (widthReduction <= 0) return;
		let children = Array.from(tickLabelGroupEl.children).filter((child) => child.style.display !== "none");
		for (let i = 0; i < children.length - 1; i++) {
			let rect = children[i].getBoundingClientRect();
			if (children[i + 1].getBoundingClientRect().left - rect.right - widthReduction / Math.max(1, children.length - 1) < 20) {
				let undo = rotateTickLabels(tickLabelGroupEl);
				let rotatedOverflow = getSvgOverflow(svg);
				overflow.bottom = Math.max(overflow.bottom, rotatedOverflow.bottom);
				undo();
				return;
			}
		}
	}
	function handleTickOverflow(tickLabelGroupEl, axis) {
		if (!hasOverlappingTicks(tickLabelGroupEl, axis)) return;
		if (axis.axis === "x" && axis.type === "categorical") rotateTickLabels(tickLabelGroupEl);
		else if (axis.axis === "x" && axis.type === "time") {
			let undo = hideEveryOtherTickLabel(tickLabelGroupEl);
			if (!hasOverlappingTicks(tickLabelGroupEl, axis)) return;
			undo();
			onlyShowFirstAndLastTickLabel(tickLabelGroupEl);
		}
	}
	function hasOverlappingTicks(tickLabelGroupEl, axis) {
		let children = Array.from(tickLabelGroupEl.children);
		children = children.filter((child) => {
			return child.style.display != "none";
		});
		for (let i = 0; i < children.length; i++) {
			let tickLabelEl = children[i];
			let nextTickLabelEl = children[i + 1];
			if (!nextTickLabelEl) break;
			let tickLabelRect = tickLabelEl.getBoundingClientRect();
			let nextTickLabelRect = nextTickLabelEl.getBoundingClientRect();
			let overlapMargin = 20;
			if (axis.axis === "y") {
				if (tickLabelRect.bottom + overlapMargin < nextTickLabelRect.top) return true;
			} else if (tickLabelRect.right + overlapMargin > nextTickLabelRect.left) return true;
		}
		return false;
	}
	function rotateTickLabels(tickLabelGroupEl) {
		let undos = [];
		Array.from(tickLabelGroupEl.children).forEach((child, index) => {
			child.matches("text") || child.querySelector("text");
			Array.from(tickLabelGroupEl.children).forEach((child2, index2) => {
				let textNode2 = child2.matches("text") ? child2 : child2.querySelector("text");
				if (textNode2) {
					textNode2.style.transform = "rotate(-45deg)";
					textNode2.style.textAnchor = "end";
					undos.push(() => {
						textNode2.style.transform = null;
						textNode2.style.textAnchor = null;
					});
				}
			});
		});
		return () => undos.forEach((undo) => undo());
	}
	function hideEveryOtherTickLabel(tickLabelGroupEl) {
		let children = Array.from(tickLabelGroupEl.children);
		children.forEach((child, index) => {
			if (index % 2 === 1) child.style.display = "none";
		});
		return () => children.forEach((child, index) => {
			child.style.display = "inline";
		});
	}
	function onlyShowFirstAndLastTickLabel(tickLabelGroupEl) {
		let children = Array.from(tickLabelGroupEl.children);
		children.forEach((child, index) => {
			child.style.display = "none";
		});
		let firstChild = children[0];
		let lastChild = children[children.length - 1];
		let firstTextNode = firstChild.matches("text") ? firstChild : firstChild.querySelector("text");
		let lastTextNode = lastChild.matches("text") ? lastChild : lastChild.querySelector("text");
		firstChild.style.display = "inline";
		firstTextNode.style.textAnchor = "start";
		lastChild.style.display = "inline";
		lastTextNode.style.textAnchor = "end";
		return () => children.forEach((child, index) => {
			child.style.display = "inline";
		});
	}
	function isNumeric(value3) {
		return typeof value3 === "number" || typeof value3 === "string" && !isNaN(Number(value3));
	}
	function isDateish2(value3) {
		return value3 instanceof Date || typeof value3 === "string" && !isNaN(Date.parse(value3));
	}
	function getSvgOverflow(svg) {
		let svgRect = svg.getBoundingClientRect();
		let contentRect = {
			top: Infinity,
			right: -Infinity,
			bottom: -Infinity,
			left: Infinity
		};
		Array.from(svg.children).forEach((el) => {
			if (el.hasAttribute("data-overlay")) return;
			if (el.hasAttribute("data-cursor")) return;
			if (el.style.display === "none") return;
			let rect = el.getBoundingClientRect();
			if (!rect.width && !rect.height) return;
			contentRect.top = Math.min(contentRect.top, rect.top);
			contentRect.right = Math.max(contentRect.right, rect.right);
			contentRect.bottom = Math.max(contentRect.bottom, rect.bottom);
			contentRect.left = Math.min(contentRect.left, rect.left);
		});
		return {
			top: Math.max(0, svgRect.top - contentRect.top),
			right: Math.max(0, contentRect.right - svgRect.right),
			bottom: Math.max(0, contentRect.bottom - svgRect.bottom),
			left: Math.max(0, svgRect.left - contentRect.left)
		};
	}
	function parseRadius(radiusString) {
		if (!radiusString || radiusString.trim() === "") throw new Error("Radius string cannot be empty");
		let radiusValues = radiusString.trim().split(/\s+/).map((val) => {
			let num = val.replace(/px/, "");
			return Number(num) || 0;
		});
		if (radiusValues.length === 0 || radiusValues.every((v) => isNaN(v))) throw new Error(`Invalid radius string: "${radiusString}"`);
		let radius = {
			topLeft: 0,
			topRight: 0,
			bottomRight: 0,
			bottomLeft: 0
		};
		if (radiusValues.length === 1) {
			radius.topLeft = radiusValues[0];
			radius.topRight = radiusValues[0];
			radius.bottomRight = radiusValues[0];
			radius.bottomLeft = radiusValues[0];
		} else if (radiusValues.length === 2) {
			radius.topLeft = radiusValues[0];
			radius.topRight = radiusValues[0];
			radius.bottomRight = radiusValues[1];
			radius.bottomLeft = radiusValues[1];
		} else if (radiusValues.length === 4) {
			radius.topLeft = radiusValues[0];
			radius.topRight = radiusValues[1];
			radius.bottomRight = radiusValues[2];
			radius.bottomLeft = radiusValues[3];
		} else throw new Error(`Invalid radius string: "${radiusString}". Must be 1, 2, or 4 numbers`);
		return radius;
	}
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/button.js
	var UIButton = class extends UIElement {
		boot() {
			setAttribute2(this, "role", "button");
			this._disableable = new Disableable(this);
			this._disableable.onInitAndChange((disabled) => {
				disabled ? removeAttribute(this, "tabindex", "0") : setAttribute2(this, "tabindex", "0");
			});
			this._buttonType = this.getAttribute("type") || "button";
		}
		mount() {
			on(this, "keydown", (e) => {
				if (e.key === "Enter") {
					e.preventDefault();
					this.disabled || this.click();
				}
				if (e.key === " ") e.preventDefault();
			});
			on(this, "keyup", (e) => {
				if (e.key === " ") {
					e.preventDefault();
					e.stopPropagation();
					this.disabled || this.click();
				}
			});
			on(this, "click", () => {
				if (this.disabled) return;
				if (this._buttonType === "submit") this._handleSubmit();
				else if (this._buttonType === "reset") this._handleReset();
			});
		}
		_handleSubmit() {
			let form = this.closest("form");
			if (form) form.requestSubmit();
		}
		_handleReset() {
			let form = this.closest("form");
			if (form) form.reset();
		}
	};
	element("button", UIButton);
	var UIClose = class extends UIElement {
		mount() {
			this.onUnmount(on(this, "click", (event) => {
				if (!event.target.closest("button,ui-button")) return;
				(closest(this, (el) => !!el._closeable)?._closeable)?.close();
			}).off);
		}
	};
	element("close", UIClose);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/toast.js
	var UIToastGroup = class extends UIElement {
		toasts = [];
		initiallyExpanded = false;
		expanded = false;
		hovered = false;
		mount() {
			this.initiallyExpanded = this.hasAttribute("expanded");
			let position = this.getAttribute("position") || "bottom right";
			this.verticalPosition = position.match(/\b(top|bottom)\b/)?.[0] || "bottom";
			this.horizontalPosition = position.match(/\b(left|start|center|right|end)\b/)?.[0] || "right";
			if (this.horizontalPosition === "left") this.horizontalPosition = "start";
			else if (this.horizontalPosition === "right") this.horizontalPosition = "end";
			setAttribute2(this, "role", "status");
			let onVisibilityChange = () => this.syncTimeouts();
			document.addEventListener("visibilitychange", onVisibilityChange);
			this.onUnmount(() => document.removeEventListener("visibilitychange", onVisibilityChange));
		}
		showToast(options = {}) {
			let toastContainer = this.querySelector("ui-toast");
			if (!toastContainer) return console.warn("ui-toast-group: no ui-toast element found", this);
			if (this.matches(":popover-open")) this.hidePopover();
			this.showPopover();
			let duration = Number(options.duration === void 0 ? 5e3 : options.duration);
			let template = toastContainer.prepareToastTemplate(options);
			let toast = {
				timeout: null,
				el: template,
				height: null,
				contentEl: template.firstElementChild,
				contentHeight: null
			};
			let show = () => {
				this.animateIn(toast);
				this.updateList();
			};
			let hide3 = () => {
				this.animateOut(toast);
				this.updateList();
			};
			this.toasts.unshift(toast);
			this.appendChild(template);
			show();
			template.hideToast = hide3;
			let toastTimeout = duration !== 0 && timeout(() => {
				toast.timeout = null;
				hide3();
			}, duration);
			if (toastTimeout) {
				toast.timeout = toastTimeout;
				this.syncTimeouts();
			}
			template.destroyToast = () => {
				if (toast.timeout) {
					toast.timeout.cancel();
					toast.timeout = null;
				}
				let index = this.toasts.indexOf(toast);
				if (index > -1) this.toasts.splice(index, 1);
				template.remove();
				if (this.toasts.length === 0) {
					this.expanded = false;
					this.hovered = false;
				}
				this.updateList();
			};
			template.addEventListener("mouseenter", () => {
				this.hovered = true;
				this.syncTimeouts();
				if (!this.initiallyExpanded) {
					this.expanded = true;
					this.updateList();
				}
			});
			template.addEventListener("mouseleave", () => {
				this.hovered = false;
				this.syncTimeouts();
				if (!this.initiallyExpanded) {
					this.expanded = false;
					this.updateList();
				}
			});
			template._closeable = new Closeable(template);
			template._closeable.onClose(() => template.destroyToast());
		}
		pauseTimeouts() {
			for (let toast of this.toasts) toast.timeout?.pause();
		}
		resumeTimeouts() {
			for (let toast of this.toasts) toast.timeout?.resume();
		}
		syncTimeouts() {
			document.hidden || this.hovered ? this.pauseTimeouts() : this.resumeTimeouts();
		}
		animateIn(toast) {
			let translateY = 1.5 * ((this.verticalPosition === "top" ? "bottom" : "top") === "top" ? 1 : -1);
			toast.contentEl.style.opacity = 0;
			toast.contentEl.style.transform = `translateY(${translateY}rem)`;
			toast.el.style.zIndex = 5001;
			toast.contentEl.offsetHeight;
			toast.contentHeight = toast.contentEl.offsetHeight;
			toast.height = toast.el.offsetHeight;
			toast.contentEl.style.opacity = 100;
			toast.contentEl.style.transform = `translateY(0)`;
		}
		animateOut(toast) {
			let translateY = .5 * (this.verticalPosition === "top" ? 1 : -1);
			toast.contentEl.style.transform = `translateY(${translateY}rem)`;
			toast.contentEl.style.opacity = 0;
			let index = this.toasts.indexOf(toast);
			if (index > -1) this.toasts.splice(index, 1);
			let cleanup = () => {
				toast.el.remove();
				if (this.toasts.length === 0) {
					this.expanded = false;
					this.hovered = false;
				}
			};
			if (toast.contentEl.getAnimations().length) toast.contentEl.addEventListener("transitionend", () => {
				cleanup();
			}, { once: true });
			else cleanup();
		}
		updateList() {
			this.initiallyExpanded || this.expanded ? this.expand() : this.collapse();
		}
		expand() {
			let runningHeight = 0;
			for (let toast of this.toasts) {
				let verticalPositionInt = this.verticalPosition === "top" ? 1 : -1;
				toast.el.style.transform = `translateY(${verticalPositionInt * runningHeight}px)`;
				toast.el.style.opacity = 100;
				toast.contentEl.style.height = `${toast.contentHeight}px`;
				toast.contentEl.style.overflow = "auto";
				runningHeight += toast.height;
			}
		}
		collapse() {
			let i = 0;
			let frontToastHeight = this.toasts[0]?.contentHeight || 0;
			for (let toast of this.toasts) {
				let numberOfToastsToShow = 3;
				let scale = 0;
				let translateY = 10 * (this.verticalPosition === "top" ? 1 : -1) * i;
				toast.el.style.zIndex = 5e3 - i;
				if (i >= numberOfToastsToShow) {
					scale = .9;
					toast.el.style.opacity = 0;
				} else {
					scale = 1 - .05 * i;
					toast.el.style.opacity = 100;
				}
				toast.contentEl.style.height = `${frontToastHeight}px`;
				toast.contentEl.style.overflow = "hidden";
				toast.el.style.transform = `scaleX(${scale}) translateY(${translateY}px)`;
				i++;
			}
		}
	};
	var UIToast = class extends UIElement {
		toastTimeout = null;
		hovered = false;
		mount() {
			if (!this.closest("ui-toast-group")) {
				setAttribute2(this, "role", "status");
				let onKeydown = (e) => {
					if (e.key === "Escape") this.hideToast();
				};
				document.addEventListener("keydown", onKeydown);
				let onVisibilityChange = () => this.syncTimeout();
				document.addEventListener("visibilitychange", onVisibilityChange);
				this.onUnmount(() => {
					document.removeEventListener("keydown", onKeydown);
					document.removeEventListener("visibilitychange", onVisibilityChange);
				});
				this.defaultPosition = this.getAttribute("position") || "bottom end";
			}
		}
		showToast(options = {}) {
			let existingToast = this.template().nextElementSibling;
			if (existingToast) existingToast.destroyToast();
			let duration = Number(options.duration === void 0 ? 5e3 : options.duration);
			let position = options.dataset?.position ?? this.defaultPosition;
			if (options.dataset?.position !== void 0) delete options.dataset.position;
			this.setAttribute("position", position);
			let template = this.prepareToastTemplate(options);
			let show = () => {
				this.showPopover();
				template.classList.add("showing");
			};
			let hide3 = () => {
				template._hiding = true;
				template.classList.remove("showing");
				if (template.getAnimations().length) template.addEventListener("transitionend", () => {
					template.remove();
					this.hidePopover();
				}, { once: true });
				else {
					template.remove();
					this.hidePopover();
				}
			};
			this.appendChild(template);
			show();
			template.hideToast = hide3;
			this.toastTimeout = duration !== 0 ? timeout(() => {
				this.toastTimeout = null;
				hide3();
			}, duration) : null;
			template.destroyToast = () => {
				if (!template.isConnected) return;
				this.toastTimeout?.cancel();
				this.toastTimeout = null;
				this.hovered = false;
				template.remove();
				this.hidePopover();
			};
			template.addEventListener("mouseenter", () => {
				this.hovered = true;
				this.syncTimeout();
			});
			template.addEventListener("mouseleave", () => {
				this.hovered = false;
				this.syncTimeout();
			});
			this.syncTimeout();
			template._closeable = new Closeable(template);
			template._closeable.onClose(() => template.destroyToast());
		}
		hideToast() {
			let toast = this.template().nextElementSibling;
			toast && toast.destroyToast();
		}
		syncTimeout() {
			if (!this.toastTimeout) return;
			document.hidden || this.hovered ? this.toastTimeout.pause() : this.toastTimeout.resume();
		}
		template() {
			return this.querySelector("template");
		}
		prepareToastTemplate(options) {
			let slots = options.slots || {};
			let dataset = options.dataset || {};
			let link = options.link || null;
			let action = options.action || null;
			let templateEl = this.template();
			if (!templateEl) return console.warn("ui-toast: no template element found", this);
			let template = templateEl.content.cloneNode(true).firstElementChild;
			template.setAttribute("aria-atomic", "true");
			Object.entries(slots).forEach(([key, value3]) => {
				if ([
					null,
					void 0,
					false
				].includes(value3)) return;
				template.querySelectorAll(`slot[name="${key}"]`).forEach((i) => {
					i.replaceWith(document.createTextNode(value3));
				});
			});
			Object.entries(dataset).forEach(([key, value3]) => {
				template.dataset[key] = value3;
			});
			this.hydrateLinkTemplate(template, link);
			this.hydrateAction(template, action);
			template.querySelectorAll("slot").forEach((slot) => slot.remove());
			return template;
		}
		hydrateLinkTemplate(template, link) {
			let linkTemplate = template.querySelector("template[name=\"link\"]");
			if (!linkTemplate) return;
			if (!link) {
				linkTemplate.remove();
				return;
			}
			let linkEl = linkTemplate.content.cloneNode(true).firstElementChild;
			Object.entries(link).forEach(([key, value3]) => {
				if ([
					null,
					void 0,
					false
				].includes(value3)) return;
				if (["label", "text"].includes(key)) return;
				if (key === "navigate" && value3) return linkEl.setAttribute("wire:navigate", "");
				if (![
					"href",
					"target",
					"rel",
					"download"
				].includes(key)) return;
				if (key === "download" && value3 === true) return linkEl.setAttribute(key, "");
				linkEl.setAttribute(key, value3);
			});
			linkEl.querySelectorAll("slot[name=\"text\"]").forEach((slot) => slot.replaceWith(document.createTextNode(link.label ?? link.text ?? "")));
			linkTemplate.replaceWith(linkEl);
		}
		hydrateAction(template, action) {
			let actionTemplate = template.querySelector("template[name=\"action\"]");
			if (!actionTemplate) return;
			if (!action?.label || !action.href && !action.event && typeof action.onClick !== "function") {
				actionTemplate.remove();
				return;
			}
			let container = actionTemplate.content.cloneNode(true).firstElementChild;
			let button = container.querySelector("[data-os-toast-action-button]");
			let link = container.querySelector("[data-os-toast-action-link]");
			let actionEl = action.href ? link : button;
			let unusedEl = action.href ? button : link;
			let labelEl = actionEl?.querySelector("[data-os-toast-action-label]");
			if (!actionEl || !labelEl) {
				actionTemplate.remove();
				return;
			}
			unusedEl?.remove();
			labelEl.textContent = action.label;
			actionTemplate.replaceWith(container);
			if (action.href) {
				this.hydrateActionLink(actionEl, action);
				actionEl.addEventListener("click", () => {
					setTimeout(() => template._closeable?.close());
				});
				return;
			}
			actionEl.addEventListener("click", () => {
				if (typeof action.onClick === "function") action.onClick();
				else if (action.event) {
					if (action.dismiss !== false) this.dismissAfterLoading(actionEl, template);
					actionEl.dispatchEvent(new CustomEvent(action.event, {
						bubbles: true,
						composed: true,
						detail: action.params || {}
					}));
				}
				if (!action.event && action.dismiss !== false) template._closeable?.close();
			});
		}
		hydrateActionLink(linkEl, action) {
			Object.entries(action).forEach(([key, value3]) => {
				if ([
					null,
					void 0,
					false
				].includes(value3)) return;
				if (key === "navigate" && value3) return linkEl.setAttribute("wire:navigate", "");
				if (![
					"href",
					"target",
					"rel",
					"download"
				].includes(key)) return;
				if (key === "download" && value3 === true) return linkEl.setAttribute(key, "");
				linkEl.setAttribute(key, value3);
			});
		}
		dismissAfterLoading(actionEl, template) {
			let loadingStarted = actionEl.hasAttribute("data-loading");
			let observer = new MutationObserver(() => {
				if (actionEl.hasAttribute("data-loading")) {
					loadingStarted = true;
					return;
				}
				if (loadingStarted) {
					observer.disconnect();
					template._closeable?.close();
				}
			});
			observer.observe(actionEl, {
				attributes: true,
				attributeFilter: ["data-loading"]
			});
			setTimeout(() => {
				if (loadingStarted || actionEl.hasAttribute("data-loading")) return;
				observer.disconnect();
				template._closeable?.close();
			}, 150);
		}
	};
	element("toast-group", UIToastGroup);
	element("toast", UIToast);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/radio.js
	var UIRadioGroup = class _UIRadioGroup extends UIControl {
		boot() {
			this._disableable = new Disableable(this);
			this._disableable.onInitAndChange((disabled) => {
				this.walker().each((el) => {
					el.disabled = disabled;
				});
			});
			this._selectable = new SelectableGroup(this);
			this._controllable = new Controllable(this, {
				disabled: this._disabled,
				bubbles: true
			});
			this._focusable = new FocusableGroup(this, { wrap: true });
			this._submittable = new Submittable(this, {
				name: this.getAttribute("name"),
				value: this._selectable.getState(),
				includeWhenEmpty: false
			});
			this._controllable.initial((initial) => initial && this._selectable.setState(initial));
			this._controllable.getter(() => this._selectable.getState());
			this._detangled = detangle();
			this._controllable.setter(this._detangled((value3) => {
				this._selectable.setState(value3);
			}));
			this._selectable.onChange(this._detangled(() => {
				this._controllable.dispatch();
			}));
			this._selectable.onInitAndChange(() => {
				this._submittable.update(this._selectable.getState());
			});
			on(this, "keydown", (e) => {
				if (["ArrowDown", "ArrowRight"].includes(e.key)) {
					this._focusable.focusNext();
					e.preventDefault();
					e.stopPropagation();
				} else if (["ArrowUp", "ArrowLeft"].includes(e.key)) {
					this._focusable.focusPrev();
					e.preventDefault();
					e.stopPropagation();
				}
			});
			setAttribute2(this, "role", "radiogroup");
			queueMicrotask(() => {
				this._submittable.update(this._selectable.getState());
			});
		}
		walker() {
			return walker(this, (el, { skip, reject }) => {
				if (el instanceof _UIRadioGroup) return reject();
				if (!(el.localName === "ui-radio")) return skip();
			});
		}
	};
	var UIRadio = class extends UIControl {
		boot() {
			let button = this;
			this._disableable = new Disableable(this);
			this._selectable = new Selectable(button, {
				value: this.hasAttribute("value") ? this.getAttribute("value") : Math.random().toString(36).substring(2, 10),
				label: this.hasAttribute("label") ? this.getAttribute("label") : null,
				selectedInitially: this.hasAttribute("checked"),
				dataAttr: "data-checked",
				ariaAttr: "aria-checked"
			});
			this._selectable.onChange(() => {
				if (this._selectable.isSelected()) this._focusable.focus(false);
			});
			this._disableable.onChange((disabled) => {
				if (disabled) this._focusable.untabbable();
				else this._selectable.isSelected() && this._focusable.tabbable();
			});
			setAttribute2(button, "role", "radio");
			this._focusable = new Focusable(button, {
				disableable: this._disableable,
				tabbableAttr: "data-active"
			});
			on(button, "click", this._disableable.disabled((e) => {
				e.preventDefault();
				e.stopPropagation();
			}), { capture: true });
			on(button, "click", this._disableable.enabled((e) => {
				this._selectable.press();
			}));
			on(button, "keydown", this._disableable.enabled((e) => {
				if (e.key === "Enter") this.closest("form")?.requestSubmit();
			}));
			on(button, "keydown", this._disableable.enabled((e) => {
				if (e.key === " ") {
					e.preventDefault();
					e.stopPropagation();
				}
			}));
			on(button, "keyup", this._disableable.enabled((e) => {
				if (e.key === " ") {
					this._selectable.press();
					e.preventDefault();
					e.stopPropagation();
				}
			}));
			respondToLabelClick3(button);
			on(button, "focus", (e) => {
				isUsingKeyboard() && this._selectable.select();
			});
		}
		get value() {
			return this._selectable?.getValue();
		}
		set value(val) {
			if (!this._selectable) return;
			this._selectable.value = val + "";
		}
		get checked() {
			return this._selectable.isSelected();
		}
		set checked(value3) {
			(this.closest("ui-radio-group")?._detangled || (() => {}))(() => {
				value3 && this._selectable.select();
			})();
		}
	};
	function respondToLabelClick3(el) {
		el.closest("label")?.addEventListener("click", (e) => {
			if (!el.contains(e.target)) el.click();
		});
	}
	element("radio-group", UIRadioGroup);
	element("radio", UIRadio);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/table.js
	var UITableScrollArea = class extends UIElement {
		boot() {
			on(this, "scroll", (e) => {
				this.updateAttributes();
			}, { passive: true });
			new ResizeObserver(() => {
				this.updateAttributes();
			}).observe(this);
		}
		mount() {
			queueMicrotask(() => {
				this.updateAttributes();
			});
		}
		updateAttributes() {
			if (this.scrollLeft > 12) setAttribute2(this, "data-scrolled-right", "");
			else removeAttribute(this, "data-scrolled-right");
			if (this.scrollWidth - this.clientWidth - this.scrollLeft > 12) setAttribute2(this, "data-scrolled-left", "");
			else removeAttribute(this, "data-scrolled-left");
		}
	};
	element("table-scroll-area", UITableScrollArea);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/tabs.js
	var UITabGroup = class _UITabGroup extends UIElement {
		boot() {
			this._disabled = this.hasAttribute("disabled");
		}
		mount() {
			this.walkPanels((el) => initializePanel(el));
			new MutationObserver((mutations) => {
				this.walkPanels((el) => initializePanel(el));
			}).observe(this, { childList: true });
		}
		showPanel(name) {
			this.walkPanels((el) => {
				if (el.getAttribute("name") === name) el.show();
				else el.hide();
			});
		}
		selectPanel(name) {
			for (let tabs of this.querySelectorAll("ui-tabs")) {
				if (tabs.closest("ui-tab-group") !== this) continue;
				tabs._selectableGroup?.setState(name);
			}
		}
		getPanel(name) {
			return this.walkPanels((el, bail) => {
				if (el.getAttribute("name") === name) bail(el);
			});
		}
		walkPanels(callback) {
			let bailed = false;
			let bailedReturn;
			for (let child of this.children) {
				if (child instanceof _UITabGroup) continue;
				if (child instanceof UITabs) continue;
				callback(child, (forward) => {
					bailed = true;
					bailedReturn = forward;
				});
				if (bailed) break;
			}
			return bailedReturn;
		}
	};
	var UITabsScrollArea = class extends UIElement {
		boot() {
			on(this, "scroll", (e) => {
				this.updateScrollVariable();
			}, { passive: true });
			new ResizeObserver(() => {
				this.updateScrollVariable();
			}).observe(this);
		}
		updateScrollVariable() {
			let percentage = Math.abs(this.scrollLeft) / (this.scrollWidth - this.clientWidth) * 100;
			this.style.setProperty("--os-scroll-percentage", percentage + "%");
		}
	};
	var UITabs = class _UITabs extends UIControl {
		boot() {
			this._focusable = new FocusableGroup(this, { wrap: true });
			this._selectableGroup = new SelectableGroup(this);
			this._controllable = new Controllable(this, { disabled: this._disabled });
			this._controllable.initial((initial) => initial && this._selectableGroup.setState(initial));
			this._controllable.getter(() => this._selectableGroup.getState());
			let detangled = detangle();
			this._controllable.setter(detangled((value3) => {
				this._selectableGroup.setState(value3);
			}));
			this._selectableGroup.onChange(detangled(() => {
				this._controllable.dispatch();
			}));
			on(this, "keydown", (e) => {
				if (["ArrowDown", "ArrowRight"].includes(e.key)) {
					this._focusable.focusNext();
					e.preventDefault();
					e.stopPropagation();
				} else if (["ArrowUp", "ArrowLeft"].includes(e.key)) {
					this._focusable.focusPrev();
					e.preventDefault();
					e.stopPropagation();
				}
			});
			setAttribute2(this, "role", "tablist");
		}
		mount() {
			this.initializeTabs();
			this._focusable.ensureTabbable();
			if (!this._selectableGroup.getState()) this._selectableGroup.selectFirst();
			new MutationObserver((mutations) => {
				this.initializeTabs();
				let selected = this._selectableGroup.selected();
				selected.el.closest("ui-tab-group").showPanel(selected.value);
			}).observe(this, { childList: true });
		}
		initializeTabs() {
			this.walker().each((el) => {
				if (el._initialized) return;
				if (el._disableable) return;
				if (el.hasAttribute("action")) return;
				initializeTab(el);
				el._initialized = true;
			});
		}
		walker() {
			return walker(this, (el, { skip, reject }) => {
				if (el instanceof UITabGroup) return reject();
				if (el instanceof _UITabs) return reject();
				if (!["a", "button"].includes(el.localName)) return skip();
			});
		}
	};
	function initializeTab(el) {
		el._disableable = new Disableable(el);
		let link = el.matches("a") ? el : null;
		let target = link || el;
		el._disableable.onInitAndChange((disabled) => {
			if (disabled) setAttribute2(target, "aria-disabled", "true");
			else removeAttribute(target, "aria-disabled");
		});
		assignId(target, "tab");
		setAttribute2(target, "role", "tab");
		target._focusable = new Focusable(target, {
			disableable: el._disableable,
			tabbableAttr: "data-active"
		});
		if (!link) {
			let panel = el.getAttribute("name");
			el._selectable = new Selectable(el, {
				value: panel || Math.random().toString(36).substring(2, 10),
				label: el.hasAttribute("label") ? el.getAttribute("label") : el.textContent.trim(),
				selectedInitially: el.hasAttribute("selected"),
				toggleable: false
			});
			on(el, "click", () => el._selectable.press());
			el._selectable.onInitAndChange(() => {
				let panelEl = el.closest("ui-tab-group")?.getPanel(panel);
				el._selectable.getState() ? panelEl?.show() : panelEl?.hide();
				if (el._selectable.getState()) el._focusable.focus(false);
			});
			let pointerdownTimeout = null;
			if (!el.closest("ui-tab-group")?.hasAttribute("manual")) {
				on(el, "pointerdown", () => {
					clearTimeout(pointerdownTimeout);
					pointerdownTimeout = setTimeout(() => pointerdownTimeout = null);
				});
				on(el, "focus", () => {
					if (!pointerdownTimeout) el._selectable.select();
				});
			}
			queueMicrotask(() => {
				let container = el.closest("ui-tab-group");
				if (!container) return;
				let panelEl = container.getPanel(panel);
				if (!panelEl) throw new Error("Could not find panel...");
				setAttribute2(el, "aria-controls", panelEl.id);
				setAttribute2(panelEl, "aria-labelledby", el.id);
			});
		}
	}
	function initializePanel(el) {
		if (el._initialized) return;
		assignId(el, "tab-panel");
		setAttribute2(el, "role", "tabpanel");
		el.hasAttribute("tabindex") || setAttribute2(el, "tabindex", "-1");
		el.show = () => {
			setAttribute2(el, "data-selected", "");
			setAttribute2(el, "tabindex", "0");
			removeAttribute(el, "hidden");
		};
		el.hide = () => {
			removeAttribute(el, "data-selected");
			setAttribute2(el, "tabindex", "-1");
			setAttribute2(el, "hidden", el.closest("ui-tab-group")?.hasAttribute("findable") ? "until-found" : "");
		};
		on(el, "beforematch", () => {
			let group = el.closest("ui-tab-group");
			if (group?.hasAttribute("findable")) group.selectPanel(el.getAttribute("name"));
		});
		el._initialized = true;
	}
	element("tab-group", UITabGroup);
	element("tabs-scroll-area", UITabsScrollArea);
	element("tabs", UITabs);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/components/otp.js
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
					for (let i = 0; i < this.length; i++) this.inputEls[i].value = cleaned[i] || "";
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
			if (this.hasAttribute("value")) this.state.setValue(this.getAttribute("value"));
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
				if (value3.length === this.length && this.config.autoSubmit) this.closest("form")?.requestSubmit();
				if (Array.from(this.inputEls).findIndex((input) => input === document.activeElement) > this.nextIndex()) this.focusIndex(this.nextIndex());
			});
			this._disableable = new Disableable(this);
			this._disableable.onInitAndChange((disabled) => {
				for (let inputEl of this.inputEls) if (disabled) setAttribute2(inputEl, "disabled", disabled);
				else removeAttribute(inputEl, "disabled");
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
					if (value3.length > 1 && prevInsertedChar) value3 = e.target.value = prevInsertedChar;
					if (value3.length > 1) {
						this.state.setValue(value3);
						this.focusIndex(this.nextIndex());
						e.preventDefault();
						return;
					}
					if (value3 == "") {
						this.state.reapply();
						if (i < this.nextIndex()) this.focusIndex(i);
						else this.focusPrev(i);
						return;
					}
					if (this.invalidCharacters().test(value3)) {
						e.target.value = prevValue;
						this.focusIndex(i);
						return;
					}
					if (this.config.mode !== "numeric") e.target.value = value3.toUpperCase();
					this.inputEls[i].blur();
					this.state.notify();
					this.focusNext(i);
				});
				on(this.inputEls[i], "keydown", (e) => {
					if ([
						"Backspace",
						"Delete",
						"Clear"
					].includes(e.key)) {
						if (e.target.value === "") requestAnimationFrame(() => this.focusPrev(i));
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
					if (document.activeElement === this.inputEls[index]) requestAnimationFrame(() => this.inputEls[index].setSelectionRange(0, 1));
					else this.focusIndex(index);
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
			if (document.activeElement === input) input.blur();
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
			for (let i = 0; i < this.length; i++) if (i === active) setAttribute2(this.inputEls[i], "tabindex", "0");
			else setAttribute2(this.inputEls[i], "tabindex", "-1");
		}
		invalidCharacters() {
			switch (this.config.mode) {
				case "numeric": return /[^\d]/g;
				case "alpha": return /[^a-zA-Z]/g;
				case "alphanumeric": return /[^a-zA-Z0-9]/g;
				default: throw new Error(`Unknown OTP input mode "${this.config.mode}"`);
			}
		}
	};
	element("otp", UIOTP);
	//#endregion
	//#region packages/novay/minios/resources/js/ui/alpine/index.js
	function setupAlpineOS(AlpineInstance) {
		let Alpine = AlpineInstance || window.Alpine;
		if (!Alpine || Alpine._os_registered) return;
		Alpine._os_registered = true;
		let applyAppearance = window.OS?.applyAppearance;
		if (!applyAppearance) applyAppearance = (appearance) => {
			let applyDark = () => document.documentElement.classList.add("dark");
			let applyLight = () => document.documentElement.classList.remove("dark");
			if (appearance === "system") {
				let media = window.matchMedia("(prefers-color-scheme: dark)");
				window.localStorage.removeItem("os.appearance");
				media.matches ? applyDark() : applyLight();
			} else if (appearance === "dark") {
				window.localStorage.setItem("os.appearance", "dark");
				applyDark();
			} else if (appearance === "light") {
				window.localStorage.setItem("os.appearance", "light");
				applyLight();
			}
		};
		let os = Alpine.reactive({
			toast(...params) {
				let detail = {
					slots: {},
					dataset: {}
				};
				if (typeof params[0] === "string") detail.slots.text = params.shift();
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
				return { close() {
					document.dispatchEvent(new CustomEvent("modal-close", { detail: {} }));
				} };
			},
			appearance: window.localStorage.getItem("os.appearance") || "system",
			systemAppearanceChanged: 1,
			get dark() {
				JSON.stringify(os.systemAppearanceChanged);
				if (os.appearance === "system") return window.matchMedia("(prefers-color-scheme: dark)").matches;
				else return os.appearance === "dark";
			},
			set dark(value3) {
				if (value3 === this.dark) return;
				if (value3) os.appearance = "dark";
				else os.appearance = "light";
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
		window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", () => {
			os.systemAppearanceChanged++;
			applyAppearance(os.appearance);
		});
		Alpine.data("osInputClearable", () => ({ clear() {
			let input = this.$el.closest("[data-os-input]")?.querySelector("input");
			if (!input) return;
			input.value = "";
			input.dispatchEvent(new Event("input", { bubbles: false }));
			input.dispatchEvent(new Event("change", { bubbles: false }));
			input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
			input.focus();
		} }));
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
				new MutationObserver(() => {
					let type = this.open ? "text" : "password";
					if (input.getAttribute("type") === type) return;
					input.setAttribute("type", type);
				}).observe(input, {
					attributes: true,
					attributeFilter: ["type"]
				});
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
					if (scope && event.detail.scope === scope) this.$el.showModal();
					else if (!event.detail.scope) this.$el.showModal();
				}
			},
			handleClose(event) {
				if (!event.detail.name) this.$el.close();
				else if (event.detail.name === name) {
					if (scope && event.detail.scope === scope) this.$el.close();
					else if (!event.detail.scope) this.$el.close();
				}
			}
		}));
		Alpine.data("osCommandInputClearable", () => ({ clear() {
			let input = this.$el.closest("[data-os-command-input]")?.querySelector("input");
			if (!input) return;
			input.value = "";
			input.dispatchEvent(new Event("input", { bubbles: false }));
			input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
			input.focus();
		} }));
		Alpine.data("osSelectSearchClearable", () => ({ clear() {
			let input = this.$el.closest("[data-os-select-search]")?.querySelector("input");
			if (!input) return;
			input.value = "";
			input.dispatchEvent(new Event("input", { bubbles: false }));
			input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
			input.focus();
		} }));
		Alpine.data("osPillboxSearchClearable", () => ({ clear() {
			let input = this.$el.closest("[data-os-pillbox-search]")?.querySelector("input");
			if (!input) return;
			input.value = "";
			input.dispatchEvent(new Event("input", { bubbles: false }));
			input.dispatchEvent(new CustomEvent("clear", { bubbles: false }));
			input.focus();
		} }));
	}
	if (typeof window !== "undefined") {
		if (window.Alpine) setupAlpineOS(window.Alpine);
		else document.addEventListener("alpine:init", () => setupAlpineOS(window.Alpine));
	}
	//#endregion
	//#region packages/novay/minios/resources/js/ui/index.js
	if (typeof isSupported2 === "function" && typeof isPolyfilled === "function") {
		if (!isSupported2() && !isPolyfilled()) apply2();
	}
	//#endregion
})();
