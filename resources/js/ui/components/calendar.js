// js/calendar/date.js
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
    let weeksBetween = Math.floor((targetThursday.getTime() - firstThursday.getTime()) / (7 * 24 * 60 * 60 * 1e3));
    return weeksBetween + 1;
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
    return [this.getYear(), this.getPaddedMonth(), this.getPaddedDay()].join("-");
  }
  toParts() {
    return [this.getYear(), this.getMonth(), this.getDay()];
  }
  toDate() {
    return new Date(this.getYear(), this.getMonth() - 1, this.getDay());
  }
  toFormattedString(locale, options) {
    return this.toDate().toLocaleDateString(locale, options);
  }
};

// js/calendar/render.js
function renderMonth(monthEl, config, viewState, metadata) {
  let headingTemplate = monthEl.querySelector('template[name="heading"]');
  let weekdayTemplate = monthEl.querySelector('template[name="weekday"]');
  let weekTemplate = monthEl.querySelector('template[name="week"]');
  headingTemplate && renderHeading(headingTemplate, config, viewState);
  weekdayTemplate && renderWeekdays(weekdayTemplate, config);
  weekTemplate && renderDates(weekTemplate, config, viewState, metadata);
}
function renderHeading(template, config, viewState) {
  renderTemplate2(template, (hydrate) => {
    return hydrate({
      slots: {
        default: new Intl.DateTimeFormat(config.locale, {
          month: template.hasAttribute("display") ? template.getAttribute("display") : "long",
          year: "numeric",
          timeZone: "UTC"
        }).format(new DateValue(viewState.year, viewState.month).getDate())
      }
    });
  });
}
function renderWeekdays(template, config) {
  let format = (date) => {
    if (template.hasAttribute("display")) {
      return new Intl.DateTimeFormat(config.locale, { weekday: template.getAttribute("display") }).format(date);
    }
    let safeTwoCharLocales = ["en", "es", "de", "fr", "it", "pt"];
    if (safeTwoCharLocales.includes(config.locale.split("-")[0])) {
      let formatter2 = new Intl.DateTimeFormat(config.locale, { weekday: "short" });
      return formatter2.format(date).slice(0, 2);
    }
    let formatter = new Intl.DateTimeFormat(config.locale, { weekday: "narrow" });
    return formatter.format(date);
  };
  let weekdays = Array.from({ length: 7 }, (_, idx) => {
    let adjustedIdx = (idx + config.startDay) % 7;
    let date = new Date(2024, 0, adjustedIdx + 7);
    return format(date);
  });
  renderTemplate2(template, (hydrate) => {
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
  let totalCells = firstDay + totalDays;
  let remainingDays = (7 - totalCells % 7) % 7;
  let [nextYear, nextMonth] = new DateValue(year, month).addMonths(1).toParts();
  for (let i = 1; i <= remainingDays; i++) {
    displayDates.push(0);
    actualDates.push(new DateValue(nextYear, nextMonth, i).toIsoDateString());
  }
  if (config.fixedWeeks) {
    let currentWeeks = Math.ceil(displayDates.length / 7);
    let targetWeeks = config.fixedWeeks;
    let weeksDiff = targetWeeks - currentWeeks;
    if (weeksDiff > 0) {
      for (let i = 0; i < weeksDiff * 7; i++) {
        displayDates.push(0);
        actualDates.push(DateValue.fromIsoDateString(actualDates[actualDates.length - 1]).addDays(1).toIsoDateString());
      }
    } else if (weeksDiff < 0) {
      displayDates.length = targetWeeks * 7;
      actualDates.length = targetWeeks * 7;
    }
  }
  let splitIntoWeeks = (arr) => Array.from(
    { length: Math.ceil(arr.length / 7) },
    (_, idx) => arr.slice(idx * 7, (idx + 1) * 7)
  );
  let displayWeeks = splitIntoWeeks(displayDates);
  let actualWeeks = splitIntoWeeks(actualDates);
  renderTemplate2(template, (hydrate) => {
    return displayWeeks.map((week, weekIdx) => {
      let weekEl = hydrate();
      let actualWeekDates = actualWeeks[weekIdx];
      let numberTemplate = weekEl.querySelector('template[name="number"]');
      if (numberTemplate) {
        let fourthDayIdx = (4 - config.startDay + 7) % 7;
        let weekNumber = DateValue.fromIsoDateString(actualWeekDates[fourthDayIdx]).getWeekNumber();
        renderTemplate2(numberTemplate, (hydrate2) => {
          return hydrate2({ slots: { default: weekNumber } });
        });
      }
      renderTemplate2(weekEl.querySelector('template[name="day"]'), (hydrate2) => {
        return week.map((day, dayIdx) => {
          if (day === 0) {
            let weekDate = DateValue.fromIsoDateString(actualWeekDates[dayIdx]);
            let dayNumber = weekDate.getDay();
            return hydrate2({
              slots: { default: dayNumber },
              attrs: { "data-date": actualWeekDates[dayIdx], "data-outside": "" }
            });
          }
          let date = DateValue.fromIsoDateString(actualWeekDates[dayIdx]);
          let dayEl = hydrate2({
            slots: { default: day },
            attrs: { "data-date": actualWeekDates[dayIdx] }
          });
          let subtext = metadata.subtext(date);
          let details = metadata.details(date);
          let variant = metadata.variant(date);
          if (![false, null, void 0].includes(variant)) {
            dayEl.dataset.dateVariant = variant;
          }
          if (![false, null, void 0].includes(subtext)) {
            let template2 = dayEl.querySelector('template[name="subtext"]');
            template2 && renderTemplate2(template2, (hydrate3) => hydrate3({ slots: { default: subtext } }));
          }
          if (![false, null, void 0].includes(details)) {
            let template2 = dayEl.querySelector('template[name="details"]');
            template2 && renderTemplate2(template2, (hydrate3) => hydrate3({ slots: { default: details } }));
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
      let isCurrentMonth = DateValue.today().getMonth() === month && DateValue.today().getYear() === year;
      return isCurrentMonth ? DateValue.today() : new DateValue(year, month);
    });
  };
  let tabbable = getFirstDateToMakeTabbable(viewState.month, viewState.year);
  el.querySelectorAll("[data-date]").forEach((cell) => {
    setAttribute2(cell, "role", "gridcell");
    let button = cell.querySelector("button,ui-button");
    let date = dateFromCell(cell);
    if (!date) return;
    button ? setAttribute2(button, "aria-label", date.toFormattedString(config.locale, { day: "numeric", month: "long", year: "numeric", weekday: "long" })) : setAttribute2(cell, "aria-label", date.toFormattedString(config.locale, { day: "numeric", month: "long", year: "numeric", weekday: "long" }));
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
    if (DateValue.today().isSameDay(date)) {
      setAttribute2(cell, "data-today", "");
    } else {
      removeAttribute(cell, "data-today", "");
    }
    selectable.attributes(cell, date);
    if (date.isSameDay(tabbable)) {
      button && setAttribute2(button, "tabindex", "0");
    } else {
      button && setAttribute2(button, "tabindex", "-1");
    }
  });
}
function renderTemplate2(template, callback) {
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
      let slotNodes = key === "default" ? clone.querySelectorAll("slot:not([name])") : clone.querySelectorAll(`slot[name="${key}"]`);
      slotNodes.forEach((i) => i.replaceWith(
        typeof value3 === "string" ? document.createTextNode(value3) : value3
      ));
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


// js/calendar/observable.js
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

// js/calendar/selectable.js
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
    if (!date) {
      this.active = null;
    } else {
      this.active = date;
    }
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
    if (config.mode === CalendarModes.MULTIPLE) {
      let dates = value3 ? value3.map((i) => DateValue.fromIsoDateString(i)) : [];
      return new MultipleSelection2(dates);
    } else if (config.mode === CalendarModes.RANGE) {
      let { start, end, preset } = value3 || {};
      if (preset && presets[preset] && preset !== "custom") {
        return new PresetRangeSelection(preset, config);
      }
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
    if (config.mode === CalendarModes.MULTIPLE) {
      let dates = value3 ? value3.split(",").map((i) => i.trim()).map((i) => DateValue.fromIsoDateString(i)) : [];
      return new MultipleSelection2(dates);
    } else if (config.mode === CalendarModes.RANGE) {
      if (!value3) return new EmptyRangeSelection();
      let [start, end] = value3.split("/").map((i) => i.trim());
      if (!end && start && presets[start] && start !== "custom") {
        return new PresetRangeSelection(start, config);
      }
      start = DateValue.fromIsoDateString(start);
      if (!end) {
        return new PartialRangeSelection(start);
      }
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
    this._blockSync = () => {
    };
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
  contains() {
  }
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
  display() {
  }
  value() {
  }
  activate() {
  }
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
    return this._date.toFormattedString(locale, { day: "numeric", month: "short", year: "numeric" });
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
    let containsDate = this._dates.some((i) => i.isSameDay(date));
    if (containsDate) return new _MultipleSelection(this._dates.filter((i) => !i.isSameDay(date)));
    return new _MultipleSelection([...this._dates, date]);
  }
  contains(date) {
    return this._dates.some((i) => i.isSameDay(date));
  }
  lowerBound(fallback) {
    let sortedDates = this._dates.sort((a, b) => a.isBefore(b) ? -1 : 1);
    return sortedDates[0] || value2(fallback);
  }
  upperBound(fallback) {
    let sortedDates = this._dates.sort((a, b) => a.isBefore(b) ? -1 : 1);
    return sortedDates[sortedDates.length - 1] || value2(fallback);
  }
  display(locale) {
    return this._dates.map((i) => i.toFormattedString(locale, { day: "numeric", month: "short", year: "numeric" })).join(", ");
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
    if (date.isSameDay(this._start) && config.minRange > 1) {
      return new EmptyRangeSelection();
    }
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
    if (date.isAfter(this._start)) {
      return !nextUnavailable || date.isBefore(nextUnavailable);
    }
    if (date.isBefore(this._start)) {
      return !prevUnavailable || date.isAfter(prevUnavailable);
    }
    return true;
  }
  contains(date) {
    return date.isSameDay(this._start);
  }
  lowerBound() {
    return this._start;
  }
  display(locale) {
    return new Intl.DateTimeFormat(locale, { day: "numeric", month: "short", year: "numeric", timeZone: "UTC" }).format(this._start.getDate()) + " -";
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
    return new Intl.DateTimeFormat(locale, { day: "numeric", month: "short", year: "numeric", timeZone: "UTC" }).formatRange(this._start.getDate(), this._end.getDate()).replaceAll("\u2009", " ");
  }
  value() {
    return { start: this._start.toIsoDateString(), end: this._end.toIsoDateString() };
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
    return new Intl.DateTimeFormat(locale, { day: "numeric", month: "short", year: "numeric", timeZone: "UTC" }).formatRange(this._start.getDate(), this._end.getDate()).replaceAll("\u2009", " ");
  }
  value() {
    return { start: this._start.toIsoDateString(), end: this._end.toIsoDateString(), preset: this._preset };
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
    let end = start.incrementDays(6);
    return [start, end];
  },
  lastWeek: (config) => {
    let start = DateValue.today().incrementDays(config.startDay - (DateValue.today().getDayOfWeek() + 7));
    let end = start.incrementDays(6);
    return [start, end];
  },
  last7Days: () => {
    let end = DateValue.today();
    let start = DateValue.today().incrementDays(-6);
    return [start, end];
  },
  thisMonth: () => {
    let start = DateValue.fromParts(DateValue.today().getYear(), DateValue.today().getMonth(), 1);
    let end = DateValue.fromParts(DateValue.today().getYear(), DateValue.today().getMonth(), DateValue.today().getDaysInMonth());
    return [start, end];
  },
  lastMonth: () => {
    let month = DateValue.today().getMonth() - 1;
    let year = DateValue.today().getYear();
    if (month < 1) {
      month += 12;
      year--;
    }
    let start = DateValue.fromParts(year, month, 1);
    let end = DateValue.fromParts(year, month, start.getDaysInMonth());
    return [start, end];
  },
  thisQuarter: () => {
    let quarter = Math.floor((DateValue.today().getMonth() - 1) / 3);
    let startMonth = quarter * 3 + 1;
    let endMonth = startMonth + 2;
    let start = DateValue.fromParts(DateValue.today().getYear(), startMonth, 1);
    let endMonthStart = DateValue.fromParts(DateValue.today().getYear(), endMonth, 1);
    let end = DateValue.fromParts(DateValue.today().getYear(), endMonth, endMonthStart.getDaysInMonth());
    return [start, end];
  },
  lastQuarter: () => {
    let quarter = Math.floor((DateValue.today().getMonth() - 4) / 3);
    let startMonth = quarter * 3 + 1;
    let endMonth = startMonth + 2;
    let year = DateValue.today().getYear();
    if (startMonth < 1) {
      startMonth += 12;
      endMonth += 12;
      year--;
    }
    let start = DateValue.fromParts(year, startMonth, 1);
    let endMonthStart = DateValue.fromParts(year, endMonth, 1);
    let end = DateValue.fromParts(year, endMonth, endMonthStart.getDaysInMonth());
    return [start, end];
  },
  thisYear: () => {
    let start = DateValue.fromParts(DateValue.today().getYear(), 1, 1);
    let end = DateValue.fromParts(DateValue.today().getYear(), 12, 31);
    return [start, end];
  },
  lastYear: () => {
    let year = DateValue.today().getYear() - 1;
    let start = DateValue.fromParts(year, 1, 1);
    let end = DateValue.fromParts(year, 12, 31);
    return [start, end];
  },
  last14Days: () => {
    let end = DateValue.today();
    let start = DateValue.today().incrementDays(-13);
    return [start, end];
  },
  last30Days: () => {
    let end = DateValue.today();
    let start = DateValue.today().incrementDays(-29);
    return [start, end];
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
    let start = DateValue.fromParts(year, month, day);
    return [start, end];
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
    let start = DateValue.fromParts(year, month, day);
    return [start, end];
  },
  yearToDate: () => {
    let start = DateValue.fromParts(DateValue.today().getYear(), 1, 1);
    let end = DateValue.today();
    return [start, end];
  },
  tomorrow: () => {
    let date = DateValue.today().incrementDays(1);
    return [date, date];
  },
  nextWeek: (config) => {
    let start = DateValue.today().incrementDays(7 + config.startDay - DateValue.today().getDayOfWeek());
    let end = start.incrementDays(6);
    return [start, end];
  },
  next7Days: () => {
    let start = DateValue.today();
    let end = DateValue.today().incrementDays(6);
    return [start, end];
  },
  nextMonth: () => {
    let month = DateValue.today().getMonth() + 1;
    let year = DateValue.today().getYear();
    if (month > 12) {
      month -= 12;
      year++;
    }
    let start = DateValue.fromParts(year, month, 1);
    let end = DateValue.fromParts(year, month, start.getDaysInMonth());
    return [start, end];
  },
  nextQuarter: () => {
    let quarter = Math.floor((DateValue.today().getMonth() - 1) / 3) + 1;
    let startMonth = quarter * 3 + 1;
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
    let end = DateValue.fromParts(year, endMonth, endMonthStart.getDaysInMonth());
    return [start, end];
  },
  nextYear: () => {
    let year = DateValue.today().getYear() + 1;
    let start = DateValue.fromParts(year, 1, 1);
    let end = DateValue.fromParts(year, 12, 31);
    return [start, end];
  },
  next14Days: () => {
    let start = DateValue.today();
    let end = DateValue.today().incrementDays(13);
    return [start, end];
  },
  next30Days: () => {
    let start = DateValue.today();
    let end = DateValue.today().incrementDays(29);
    return [start, end];
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
    let end = DateValue.fromParts(year, month, day).incrementDays(-1);
    return [start, end];
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
    let end = DateValue.fromParts(year, month, day).incrementDays(-1);
    return [start, end];
  },
  allTime: (config) => {
    if (!config.min) throw new Error("Min date is required for allTime preset");
    let start = config.min;
    let end = DateValue.today();
    return [start, end];
  }
};

// js/calendar/view.js
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
    if (this.isWithinMinAndMax(month, this.year)) {
      this.month = month;
    }
    this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
  }
  setYear(year) {
    if (!year) return;
    let month = this.month;
    if (!this.isWithinMinAndMax(month, year)) {
      if (this.config.min && DateValue.fromParts(year, month).isBefore(DateValue.firstDayOfMonth(this.config.min))) {
        month = this.config.min.getMonth();
      } else if (this.config.max && DateValue.fromParts(year, month).isAfter(this.config.max)) {
        month = this.config.max.getMonth();
      }
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

// js/calendar/validator.js
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

// js/calendar/meta.js
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
    this.metadata = { ...this.metadata, ...object };
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

// js/calendar/index.js
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
      // Newer browsers use getWeekInfo() but have removed weekInfo (from Chrome 143+), older browsers use weekInfo, so the startDay calculation needs to account for that...
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
    this._disableable = new Disableable(this, {
      disableWithParent: false
    });
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
      this.dispatchEvent(new Event("select", { bubbles: false, cancelable: true }));
      this._controllable.dispatch();
    }));
    this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
      this._submittable.update(this.selectable.getValue());
    });
    this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
      this.anchorSelection(elDrivingConfig);
    });
    this.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
      this.dispatchEvent(new Event("navigate", { bubbles: false, cancelable: true }));
    });
    queueMicrotask(() => {
      this.closest("ui-date-picker")?.bootWithCalendar(this);
    });
    let observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (!["unavailable", "max", "min"].includes(mutation.attributeName)) return;
        this.config.unavailable = this.getUnavailableDates(elDrivingConfig);
        this.config.min = this.getMinDate(elDrivingConfig);
        this.config.max = this.getMaxDate(elDrivingConfig);
        this.validator.unavailable = this.config.unavailable;
        this.validator.min = this.config.min;
        this.validator.max = this.config.max;
        this.observable.notify(ObservableTrigger2.VIEW_CHANGE);
      });
    });
    observer.observe(elDrivingConfig, { attributes: true });
  }
  getOpenTo(el) {
    if (!el.hasAttribute("open-to")) return;
    let openTo = el.getAttribute("open-to");
    openTo = openTo === "today" ? DateValue.today() : DateValue.fromIsoDateString(openTo);
    if (!this.validator.isBetweenMinMax(openTo)) {
      return this.config.min;
    }
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
    let isFocusedOnADateButton = this.contains(focusedEl) && focusedEl.closest("[data-date]") !== null;
    if (isFocusedOnADateButton) return;
    if (elDrivingConfig.hasAttribute("force-open-to")) return;
    this.viewState.setDate(
      this.selectable.lowerBound(DateValue.today())
    );
  }
  appendMetadata(object) {
    this.metadata.append(object);
  }
  resetMetadata(object = {}) {
    this.metadata.reset(object);
  }
  visibleDateRange() {
    return [
      DateValue.fromParts(this.viewState.year, this.viewState.month, 1).toIsoDateString(),
      DateValue.fromParts(this.viewState.year, this.viewState.month + 1, 0).toIsoDateString()
    ];
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
      if (calendar.viewState.canNavigatePrevious() && !this._disableable.isDisabled()) {
        removeAttribute(this, "disabled");
      } else {
        setAttribute2(this, "disabled", "");
      }
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
      if (calendar.viewState.canNavigateNext() && !this._disableable.isDisabled()) {
        removeAttribute(this, "disabled");
      } else {
        setAttribute2(this, "disabled", "");
      }
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
        if (disabled) {
          setAttribute2(select, "disabled", "");
        } else {
          removeAttribute(select, "disabled");
        }
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
    let months = Array.from({ length: 12 }, (_, idx) => idx + 1);
    let renderableMonths = months.map((month) => {
      let firstDayOfMonth = new DateValue(this.viewState.year, month);
      let lastDayOfMonth = new DateValue(this.viewState.year, month + 1, 0);
      if (!this.validator.isBetweenMinMax(firstDayOfMonth) && !this.validator.isBetweenMinMax(lastDayOfMonth)) {
        return null;
      }
      return {
        month,
        label: new Intl.DateTimeFormat(this.config.locale, { month: display, timeZone: "UTC" }).format(new DateValue(2024, month).getDate())
      };
    }).filter(Boolean);
    renderTemplate2(select.querySelector("template"), (hydrate) => {
      if (renderableMonths.length === 0) {
        let month = this.viewState.month;
        let label = new Intl.DateTimeFormat(this.config.locale, { month: display, timeZone: "UTC" }).format(new DateValue(2024, month).getDate());
        let option = hydrate({ slots: { default: label } });
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
        if (disabled) {
          setAttribute2(select, "disabled", "");
        } else {
          removeAttribute(select, "disabled");
        }
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
    let years = Array.from(
      { length: this.numberOfPastYears + this.numberOfFutureYears + 1 },
      (_, i) => currentYear - this.numberOfPastYears + i
    );
    let renderableYears = years.map((year) => {
      let yearStart = new DateValue(year, 1);
      let yearEnd = new DateValue(year, 12, 31);
      if (this.config.min && yearEnd.isBefore(this.config.min)) return null;
      if (this.config.max && yearStart.isAfter(this.config.max)) return null;
      return year;
    }).filter(Boolean);
    renderTemplate2(select.querySelector("template"), (hydrate) => {
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
    let template = this.querySelector('template:not([name]), template[name="month"]');
    renderTemplate2(template, (hydrate) => {
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
    if (calendar.hasAttribute("static")) {
      this.behavior = "none";
    }
    initFauxButton(this, () => this._disableable.isDisabled(), () => this.navigateAndSelectToday());
    this._disableable.onInitAndChange((disabled) => {
      if (disabled) {
        setAttribute2(this, "disabled", "");
      } else {
        removeAttribute(this, "disabled");
      }
    });
    let template = this.querySelector("template");
    template && renderTemplate2(template, (hydrate) => {
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
    let shouldSelect = this.behavior === "select" || this.behavior === "auto" && isViewingCurrentMonth();
    if (shouldSelect) {
      if (calendar.config.mode === CalendarModes.RANGE) {
        calendar.selectable.setValue({ start: DateValue.today().toIsoDateString(), end: DateValue.today().toIsoDateString() });
      } else if (calendar.config.mode === CalendarModes.MULTIPLE) {
        calendar.selectable.setValue([DateValue.today().toIsoDateString()]);
      } else {
        calendar.selectable.setValue(DateValue.today().toIsoDateString());
      }
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
        } else if (selectable instanceof HTMLSelectElement) {
          return selectable.querySelector(`option[value="${preset}"]`)?.textContent;
        }
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
          if (start && end) this.selectable.setValue({ start, end });
        } else {
          this.selectable.setValue({ preset: selectable.value });
        }
      }));
      this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
        let value3 = this.selectable.getValue();
        selectable.value = value3?.preset || "custom";
      });
      this._disableable.onInitAndChange((disabled) => {
        if (disabled) {
          setAttribute2(selectable, "disabled", "");
        } else {
          removeAttribute(selectable, "disabled");
        }
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
    if (this.config.mode === CalendarModes.RANGE) {
      syncInputStateWithRangeSelectionState(firstInput, secondInput, calendar);
    } else {
      syncInputStateWithSingleSelectionState(firstInput, calendar);
    }
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
        if (e.shiftKey) {
          viewState.previousMonth();
        } else {
          viewState.previousMonth();
        }
        break;
      case "PageDown":
        if (e.shiftKey) {
          viewState.nextMonth();
        } else {
          viewState.nextMonth();
        }
        break;
      default:
        handled = false;
    }
    if (handled) {
      e.preventDefault();
      e.stopPropagation();
      let cell2 = Array.from(el.querySelectorAll("[data-date]")).find((cell3) => dateFromCell2(cell3).isSameDay(focusedDate));
      if (!cell2 && direction) {
        if (direction === "forward") {
          viewState.nextMonth();
        } else {
          viewState.previousMonth();
        }
        cell2 = Array.from(el.querySelectorAll("[data-date]")).find((cell3) => dateFromCell2(cell3).isSameDay(focusedDate));
      }
      cell2?.querySelector("button,ui-button")?.focus();
    }
  });
  return { enable() {
    blocked = false;
  }, disable() {
    blocked = true;
  } };
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
    let selected = subject.selectable.getValue();
    input.value = selected || "";
  });
  subject.observable.subscribe(ObservableTrigger2.SELECTION, sync);
  sync();
  input.addEventListener("change", detangled(() => {
    let date = input.valueAsDate;
    if (date) {
      subject.selectable.setValue(
        DateValue.fromLocalDate(date).toIsoDateString()
      );
      subject.viewState.setDate(DateValue.fromLocalDate(date));
    } else {
      subject.selectable.setValue("");
    }
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
    if (start && end) {
      subject.selectable.setValue(
        { start: DateValue.fromLocalDate(start).toIsoDateString(), end: DateValue.fromLocalDate(end).toIsoDateString() }
      );
    } else if (start) {
      subject.selectable.setValue(
        { start: DateValue.fromLocalDate(start).toIsoDateString(), end: null }
      );
    } else {
      subject.selectable.setValue(
        { start: null, end: null }
      );
    }
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

// js/calendar/trigger.js
var UIDatePickerTrigger = class extends UIControl {
  boot() {
    this.picker = this.closest("ui-date-picker");
    this._disableable = new Disableable(this);
    let disableClick = (e) => {
      e.preventDefault();
      e.stopImmediatePropagation();
    };
    this._disableable.onInitAndChange((disabled) => {
      if (disabled) {
        this.addEventListener("click", disableClick, true);
      } else {
        this.removeEventListener("click", disableClick, true);
      }
    });
    this.dayInput = this.querySelector("[data-os-day-input]");
    this.monthInput = this.querySelector("[data-os-month-input]");
    this.yearInput = this.querySelector("[data-os-year-input]");
    if (!this.dayInput || !this.monthInput || !this.yearInput) return;
    let locale = this.picker?.getAttribute("locale") || navigator.language;
    let { order: inputOrder, separator, placeholders } = getLocaleInfo(locale);
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
      while (container.firstChild) {
        container.removeChild(container.firstChild);
      }
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
        if (e.key === "ArrowRight" && i < this.inputs.length - 1) {
          return this.inputs[i + 1].focus();
        }
        if (e.key === "ArrowLeft" && i > 0) {
          return this.inputs[i - 1].focus();
        }
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
      if (pickerLabelledBy && triggerLabelledBy && pickerLabelledBy !== triggerLabelledBy) {
        setAttribute2(this, "aria-labelledby", `${pickerLabelledBy} ${triggerLabelledBy}`);
      }
    });
    this.picker.observable.subscribe(ObservableTrigger2.SELECTION, () => {
      this.updateInputsFromSelectable(this.picker.selectable);
    });
    this.updateInputsFromSelectable(this.picker.selectable);
    this.addEventListener("mousedown", (event) => {
      if (!event.target.closest("input")) {
        event.preventDefault();
      }
    });
    if (!this.hasAttribute("data-bare")) {
      this.addEventListener("click", () => {
        this.picker._dialogable.toggle();
      });
    }
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
      this.picker._rangeTriggerDates = this.picker._rangeTriggerDates || { start: null, end: null };
      this.picker._rangeTriggerDates[this.rangeRole] = isoDate;
      let { start, end } = this.picker._rangeTriggerDates;
      if (start && end) {
        this.picker.selectable.setValue({ start, end });
      } else if (start) {
        this.picker.selectable.setValue({ start, end: null });
      } else {
        this.picker.selectable.setValue({ start: null, end: null });
      }
    } else {
      this.picker.selectable.setValue(isoDate);
    }
    this.picker.viewState.setDate(DateValue.fromIsoDateString(isoDate));
  }
  updateInputsFromSelectable(selectable) {
    let value3 = selectable.getValue();
    if (this.rangeRole && value3 && typeof value3 === "object") {
      this.picker._rangeTriggerDates = this.picker._rangeTriggerDates || { start: null, end: null };
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
    if (day > max2) {
      this.dayInput.value = max2.toString().padStart(2, "0");
    }
  }
  expandTwoDigitYear(year) {
    let rangeEnd = DateValue.today().getYear() + 20;
    let century = Math.floor(rangeEnd / 100) * 100;
    let expandedYear = century + year;
    return expandedYear > rangeEnd ? expandedYear - 100 : expandedYear;
  }
  initTwoDigitInputListeners(input, { max: max2, advanceThreshold, handleSecondDigit, onValueChange }) {
    let isEditing = false;
    input.addEventListener("keydown", (e) => {
      if (e.key === "Backspace") {
        if (input.value === "" || input.value === input.placeholder) {
          let idx = this.inputs.indexOf(input);
          if (idx > 0) {
            this.inputs[idx - 1].focus();
          }
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
        if (isNaN(number) || number >= maxVal) {
          number = 1;
        } else {
          number++;
        }
      } else if (e.key === "ArrowDown") {
        if (isNaN(number) || number === 1) {
          number = maxVal;
        } else {
          number--;
        }
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
      let existing = parseInt(input.value);
      let completed = handleSecondDigit(existing, digit);
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
      if (input.value === "00") {
        input.value = "01";
      }
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
          if (idx > 0) {
            this.inputs[idx - 1].focus();
          }
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
        if (isNaN(number)) {
          number = (/* @__PURE__ */ new Date()).getFullYear();
        } else {
          number++;
        }
      } else if (e.key === "ArrowDown") {
        if (isNaN(number)) {
          number = (/* @__PURE__ */ new Date()).getFullYear();
        } else if (number > 1) {
          number--;
        }
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
        if (nextInput) {
          nextInput.focus();
        } else {
          highlightInputContents2(this.yearInput);
        }
        return;
      }
      this.yearInput.value = buffer.padStart(4, "0");
      highlightInputContents2(this.yearInput);
    });
    this.yearInput.addEventListener("blur", () => {
      if (buffer.length > 0 && buffer.length <= 2) {
        this.yearInput.value = this.expandTwoDigitYear(parseInt(buffer)).toString();
      } else if (buffer.length > 0) {
        this.yearInput.value = buffer.padStart(4, "0");
      }
      buffer = "";
      if (this.yearInput.value === "0000") {
        this.yearInput.value = "0001";
      }
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
        if (input === this.yearInput && value3.length <= 2) {
          number = this.expandTwoDigitYear(number);
        }
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
  if (isoMatch) {
    return {
      year: parseInt(isoMatch[1]),
      month: parseInt(isoMatch[2]),
      day: parseInt(isoMatch[3])
    };
  }
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
    let formatter = new Intl.DateTimeFormat(locale, {
      year: "numeric",
      month: "2-digit",
      day: "2-digit"
    });
    let parts = formatter.formatToParts(new Date(2024, 2, 15));
    let order = parts.filter((p) => ["day", "month", "year"].includes(p.type)).map((p) => p.type);
    let literal = parts.find((p) => p.type === "literal");
    let separator = literal?.value?.trim() || "/";
    let placeholders = { day: "dd", month: "mm", year: "yyyy" };
    parts.forEach((p) => {
      if (p.type === "day") placeholders.day = p.value.replace(/\d/g, "d");
      if (p.type === "month") placeholders.month = p.value.replace(/\d/g, "m");
      if (p.type === "year") placeholders.year = p.value.replace(/\d/g, "y");
    });
    return { order, separator, placeholders };
  } catch {
    return {
      order: ["day", "month", "year"],
      separator: "/",
      placeholders: { day: "dd", month: "mm", year: "yyyy" }
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

// js/calendar/picker.js
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
    if (dateFieldEl) {
      setAttribute2(this.triggerEl, "role", "group");
    } else {
      setAttribute2(this.triggerEl, "role", "combobox");
    }
    setAttribute2(this.triggerEl, "aria-controls", calendarId);
    this._disableable.onInitAndChange((disabled) => {
      if (disabled) setAttribute2(this.triggerEl, "disabled", "");
      else removeAttribute(this.triggerEl, "disabled");
    });
    if (dropdownEnabled) {
      this._dialogable = new Dialogable(popoverEl, {
        clickOutside: !this.hasAttribute("disable-click-outside"),
        triggers: dateFieldEl ? [buttonEl].filter(Boolean) : [buttonEl, inputEl, secondInputEl].filter(Boolean)
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
      if (!dateFieldEl) {
        linkExpandedStateToPopover3(this.triggerEl, this._dialogable);
      }
      preventScrollWhenPopoverIsOpen3(this, this._dialogable);
      if (!dateFieldEl) {
        controlPopoverWithKeyboard2(this.triggerEl, this._dialogable);
      }
      handlePopoverClosing3(this, this.calendar.config.mode, this.observable, this.selectable, this._dialogable);
      let bareTriggerEls = Array.from(popoverEl.querySelectorAll("ui-date-picker-trigger[data-bare]"));
      if (bareTriggerEls.length === 2 && isRange) {
        bareTriggerEls[0].connectToPicker(this, "start");
        bareTriggerEls[1].connectToPicker(this, "end");
      } else if (bareTriggerEls.length === 1) {
        bareTriggerEls[0].connectToPicker(this);
      }
    }
    if (dateFieldEl && secondDateFieldEl && isRange) {
      dateFieldEl.connectToPicker(this, "start");
      secondDateFieldEl.connectToPicker(this, "end");
    } else if (dateFieldEl) {
      dateFieldEl.connectToPicker(this);
    } else if (isRange && popoverEl && inputEl && secondInputEl) {
      highlightInputContentsWhenFocused2(inputEl);
      highlightInputContentsWhenFocused2(secondInputEl);
      syncInputStateWithRangeSelectionState(inputEl, secondInputEl, this);
    } else if (popoverEl && inputEl) {
      let input = inputEl;
      highlightInputContentsWhenFocused2(input);
      syncInputStateWithSingleSelectionState(input, this);
    } else if (popoverEl) {
      let button = buttonEl;
      togglePopoverWithMouse2(button, this._dialogable);
    }
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
      this.dispatchEvent(new Event("select", { bubbles: false, cancelable: true }));
      this._controllable.dispatch();
    }));
    this.observable.subscribe(ObservableTrigger2.SELECTION, () => {
      this._submittable.update(this.selectable.getValue());
    });
    this.observable.subscribe(ObservableTrigger2.VIEW_CHANGE, () => {
      this.dispatchEvent(new Event("navigate", { bubbles: false, cancelable: true }));
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
    if (!this.querySelector('template[name="date"]')) {
      let template = document.createElement("template");
      template.setAttribute("name", "date");
      template.innerHTML = "<div><slot></slot></div>";
      this.appendChild(template);
    }
    this.templates = {
      placeholder: this.querySelector('template[name="placeholder"]'),
      date: this.querySelector('template[name="date"]')
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
      let { cleanup } = renderTemplate2(this.templates.date, (hydrate) => {
        return hydrate({ slots: { default: picker.selectable.display(this.picker.calendar.config.locale) } });
      });
      this.templates.date.clearDate = cleanup;
    } else {
      if (!this.templates.placeholder) return;
      let { cleanup } = renderTemplate2(this.templates.placeholder, (hydrate) => {
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
    if (dialogable.getState()) {
      root.dispatchEvent(new Event("open", {
        bubbles: false,
        cancelable: false
      }));
    } else {
      root.dispatchEvent(new Event("close", {
        bubbles: false,
        cancelable: false
      }));
    }
  });
}
function controlPopoverWithKeyboard2(button, dialogable) {
  on(button, "keydown", (e) => {
    if (!["ArrowDown", "ArrowUp", "Escape"].includes(e.key)) return;
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
      if (dialogable.getState()) {
        dialogable.setState(false);
      }
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
    if (selectable.hasSelection()) {
      dialogable.setState(false);
    }
  });
}

