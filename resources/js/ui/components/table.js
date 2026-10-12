  // js/table.js
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
      if (this.scrollLeft > 12) {
        this.setAttribute("data-scrolled-right", "");
      } else {
        this.removeAttribute("data-scrolled-right");
      }
      if (this.scrollWidth - this.clientWidth - this.scrollLeft > 12) {
        this.setAttribute("data-scrolled-left", "");
      } else {
        this.removeAttribute("data-scrolled-left");
      }
    }
  };
  element("table-scroll-area", UITableScrollArea);

