  // js/sidebar.js
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
      if (this.config.sticky) {
        this.setStickyPositionStyles();
      }
      if (this.hasAttribute("collapsible")) {
        let value3 = this.getAttribute("collapsible");
        if (value3 === "true") {
          this.config.collapsible = true;
        } else if (value3 === "false") {
          this.config.collapsible = false;
        } else if (value3 === "mobile") {
          this.config.collapsible = "mobile";
        }
      }
      if (this.config.persist && this.config.collapsible) {
        this.state.collapsedDesktop = JSON.parse(localStorage.getItem("os-sidebar-collapsed-desktop"));
      }
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
        if (this.config.persist) {
          localStorage.setItem("os-sidebar-collapsed-desktop", JSON.stringify(this.state.collapsedDesktop));
        }
        this.updateDataAttributes(this);
      });
      let viewportObserver = new ViewportResizeObserver(this.observable, this.config);
      this.onUnmount(() => viewportObserver.disconnect());
      let onSidebarToggle = () => {
        if (this.state.viewportDesktop) {
          this.state.collapsedDesktop ? this.observable.notify(EVENTS.DESKTOP_EXPANDED) : this.observable.notify(EVENTS.DESKTOP_COLLAPSED);
        } else {
          this.state.collapsedMobile ? this.observable.notify(EVENTS.MOBILE_EXPANDED) : this.observable.notify(EVENTS.MOBILE_COLLAPSED);
        }
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
      if (isFullCollapsible) {
        this.state.active ? setAttribute2(el, "data-os-sidebar-active", "") : removeAndReleaseAttribute(el, "data-os-sidebar-active");
      }
      if (this.state.viewportDesktop) {
        removeAndReleaseAttribute(el, "data-os-sidebar-on-mobile");
        setAttribute2(el, "data-os-sidebar-on-desktop", "");
        removeAndReleaseAttribute(el, "data-os-sidebar-collapsed-mobile");
        if (this.state.collapsedDesktop) {
          if (isFullCollapsible) {
            setAttribute2(el, "data-os-sidebar-collapsed-desktop", "");
          }
        } else {
          removeAndReleaseAttribute(el, "data-os-sidebar-collapsed-desktop");
        }
      } else {
        removeAndReleaseAttribute(el, "data-os-sidebar-on-desktop");
        setAttribute2(el, "data-os-sidebar-on-mobile", "");
        removeAndReleaseAttribute(el, "data-os-sidebar-collapsed-desktop");
        if (this.state.collapsedMobile) {
          setAttribute2(el, "data-os-sidebar-collapsed-mobile", "");
        } else {
          removeAndReleaseAttribute(el, "data-os-sidebar-collapsed-mobile");
        }
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

