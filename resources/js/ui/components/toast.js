  // js/toast.js
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
      if (this.horizontalPosition === "left") {
        this.horizontalPosition = "start";
      } else if (this.horizontalPosition === "right") {
        this.horizontalPosition = "end";
      }
      setAttribute2(this, "role", "status");
      let onVisibilityChange = () => this.syncTimeouts();
      document.addEventListener("visibilitychange", onVisibilityChange);
      this.onUnmount(() => document.removeEventListener("visibilitychange", onVisibilityChange));
    }
    showToast(options = {}) {
      let toastContainer = this.querySelector("ui-toast");
      if (!toastContainer) {
        return console.warn("ui-toast-group: no ui-toast element found", this);
      }
      if (this.matches(":popover-open")) {
        this.hidePopover();
      }
      this.showPopover();
      let duration = Number(options.duration === void 0 ? 5e3 : options.duration);
      let template = toastContainer.prepareToastTemplate(options);
      let toastEl = template.firstElementChild;
      let toast = {
        timeout: null,
        el: template,
        height: null,
        contentEl: toastEl,
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
        if (index > -1) {
          this.toasts.splice(index, 1);
        }
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
      for (let toast of this.toasts) {
        toast.timeout?.pause();
      }
    }
    resumeTimeouts() {
      for (let toast of this.toasts) {
        toast.timeout?.resume();
      }
    }
    syncTimeouts() {
      document.hidden || this.hovered ? this.pauseTimeouts() : this.resumeTimeouts();
    }
    animateIn(toast) {
      let reverseVerticalPosition = this.verticalPosition === "top" ? "bottom" : "top";
      let verticalPositionInt = reverseVerticalPosition === "top" ? 1 : -1;
      let translateY = 1.5 * verticalPositionInt;
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
      let verticalPositionInt = this.verticalPosition === "top" ? 1 : -1;
      let translateY = 0.5 * verticalPositionInt;
      toast.contentEl.style.transform = `translateY(${translateY}rem)`;
      toast.contentEl.style.opacity = 0;
      let index = this.toasts.indexOf(toast);
      if (index > -1) {
        this.toasts.splice(index, 1);
      }
      let cleanup = () => {
        toast.el.remove();
        if (this.toasts.length === 0) {
          this.expanded = false;
          this.hovered = false;
        }
      };
      if (toast.contentEl.getAnimations().length) {
        toast.contentEl.addEventListener("transitionend", () => {
          cleanup();
        }, { once: true });
      } else {
        cleanup();
      }
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
      let frontToast = this.toasts[0];
      let frontToastHeight = frontToast?.contentHeight || 0;
      for (let toast of this.toasts) {
        let numberOfToastsToShow = 3;
        let scale = 0;
        let verticalPositionInt = this.verticalPosition === "top" ? 1 : -1;
        let translateY = 10 * verticalPositionInt * i;
        toast.el.style.zIndex = 5e3 - i;
        if (i >= numberOfToastsToShow) {
          scale = 1 - 0.05 * (numberOfToastsToShow - 1);
          toast.el.style.opacity = 0;
        } else {
          scale = 1 - 0.05 * i;
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
          if (e.key === "Escape") {
            this.hideToast();
          }
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
      if (options.dataset?.position !== void 0) {
        delete options.dataset.position;
      }
      this.setAttribute("position", position);
      let template = this.prepareToastTemplate(options);
      let show = () => {
        this.showPopover();
        template.classList.add("showing");
      };
      let hide3 = () => {
        template._hiding = true;
        template.classList.remove("showing");
        if (template.getAnimations().length) {
          template.addEventListener("transitionend", () => {
            template.remove();
            this.hidePopover();
          }, { once: true });
        } else {
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
      if (!templateEl) {
        return console.warn("ui-toast: no template element found", this);
      }
      let template = templateEl.content.cloneNode(true).firstElementChild;
      template.setAttribute("aria-atomic", "true");
      Object.entries(slots).forEach(([key, value3]) => {
        if ([null, void 0, false].includes(value3)) return;
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
      let linkTemplate = template.querySelector('template[name="link"]');
      if (!linkTemplate) return;
      if (!link) {
        linkTemplate.remove();
        return;
      }
      let linkEl = linkTemplate.content.cloneNode(true).firstElementChild;
      Object.entries(link).forEach(([key, value3]) => {
        if ([null, void 0, false].includes(value3)) return;
        if (["label", "text"].includes(key)) return;
        if (key === "navigate" && value3) {
          return linkEl.setAttribute("wire:navigate", "");
        }
        if (!["href", "target", "rel", "download"].includes(key)) return;
        if (key === "download" && value3 === true) {
          return linkEl.setAttribute(key, "");
        }
        linkEl.setAttribute(key, value3);
      });
      linkEl.querySelectorAll('slot[name="text"]').forEach(
        (slot) => slot.replaceWith(document.createTextNode(link.label ?? link.text ?? ""))
      );
      linkTemplate.replaceWith(linkEl);
    }
    hydrateAction(template, action) {
      let actionTemplate = template.querySelector('template[name="action"]');
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
        if (typeof action.onClick === "function") {
          action.onClick();
        } else if (action.event) {
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
        if ([null, void 0, false].includes(value3)) return;
        if (key === "navigate" && value3) {
          return linkEl.setAttribute("wire:navigate", "");
        }
        if (!["href", "target", "rel", "download"].includes(key)) return;
        if (key === "download" && value3 === true) {
          return linkEl.setAttribute(key, "");
        }
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

