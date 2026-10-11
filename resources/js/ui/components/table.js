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
        setAttribute2(this, "data-scrolled-right", "");
      } else {
        removeAttribute(this, "data-scrolled-right");
      }
      if (this.scrollWidth - this.clientWidth - this.scrollLeft > 12) {
        setAttribute2(this, "data-scrolled-left", "");
      } else {
        removeAttribute(this, "data-scrolled-left");
      }
    }
  };
  element("table-scroll-area", UITableScrollArea);

