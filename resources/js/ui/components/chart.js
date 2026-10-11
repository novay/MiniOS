  // js/chart/chart.js
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
      for (let existingKey in memoizedValues) {
        if (existingKey.startsWith(`${name}-`) && !existingKey.endsWith(String(key))) {
          delete memoizedValues[existingKey];
        }
      }
      if (!memoizedValues[memoKey]) {
        memoizedValues[memoKey] = fn();
      }
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
            if (this.type === "time") {
              return dateFromString(v);
            } else if (this.type === "linear") {
              return Number(v);
            } else {
              return v;
            }
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
              if (!isDefined(d[key])) {
                return acc;
              }
              acc.push(d[key]);
              return acc;
            }, []));
            return [...groupsData, ...stacksData, ...data, ...this.includeZero ? [0] : []];
          });
        },
        zeroLine() {
          let position = this.axis.scale(0).center;
          if (this.axis.axis === "x") {
            return {
              x1: position,
              x2: position,
              y1: chart.dimensions.index.axis.range[0],
              y2: chart.dimensions.index.axis.range[1]
            };
          }
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
          inset: { start: 0, end: 0 },
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
              return tickValues.map((value3, index) => ({ value: value3, label: tickLabels[index] }));
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
          inset: { start: 0, end: 0 },
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
              return tickValues.map((value3, index) => ({ value: value3, label: tickLabels[index] }));
            });
          },
          get domain() {
            return memoize("y.domain", dataMemoKey, () => generateDomain(this.type, [...this.ticks.map((t) => t.value), ...this.values]));
          },
          get range() {
            return memoize(
              "y.range",
              dimensionsMemoKey,
              () => chart.horizontal ? [chart.inset.top + this.inset.start, chart.height - chart.inset.bottom - this.inset.end] : [chart.height - chart.inset.bottom - this.inset.end, chart.inset.top + this.inset.start]
            );
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
          let series = {
            field: key,
            values: chart.data.map((d) => d[key]),
            bars({ barWidth, minHeight, radius }) {
              let {
                start: groupStart,
                center: groupCenter,
                width: fullGroupWidth
              } = chart.dimensions.index.axis.scale(chart.dimensions.index.axis.domain[0], { area: chart.dimensions.index.asArea });
              let barRadius = radius;
              if (barWidth.match(/^(\d+)%$/)) {
                barWidth = fullGroupWidth * Number(barWidth.replace("%", "")) / 100;
              } else {
                barWidth = Number(barWidth.replace("px", ""));
              }
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
          acc[key] = series;
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
            let series = {
              bars() {
                let {
                  start: groupStart,
                  center: groupCenter,
                  width: fullGroupWidth
                } = chart.dimensions.index.axis.scale(chart.dimensions.index.axis.domain[0], { area: chart.dimensions.index.asArea });
                let stackWidth = stackMetadata.width ?? "90%";
                if (stackWidth.match(/^(\d+)%$/)) {
                  stackWidth = fullGroupWidth * Number(stackWidth.replace("%", "")) / 100;
                } else {
                  stackWidth = Number(stackWidth.replace("px", ""));
                }
                let stackStartOffset = (fullGroupWidth - stackWidth) / 2;
                let stackGap = Number(stackMetadata.gap.replace("px", ""));
                let previousPositiveHeight = [];
                let previousNegativeHeight = [];
                let stackRadius = stackMetadata.radius;
                let topBarField = [];
                let bottomBarField = [];
                if (stackRadius) {
                  filteredMetadata.forEach((itemMetadata) => {
                    if (itemMetadata.type === "bar") {
                      data[itemMetadata.field].forEach((value3, index) => {
                        if (value3 >= 0) {
                          topBarField[index] = itemMetadata.field;
                          if (bottomBarField[index] === void 0) {
                            bottomBarField[index] = itemMetadata.field;
                          }
                        } else {
                          bottomBarField[index] = itemMetadata.field;
                          if (topBarField[index] === void 0) {
                            topBarField[index] = itemMetadata.field;
                          }
                        }
                      });
                    }
                  });
                }
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
                        let visualRadius = { topLeft: 0, topRight: 0, bottomLeft: 0, bottomRight: 0 };
                        if (isTopBar) {
                          visualRadius.topLeft = stackRadius.topLeft;
                          visualRadius.topRight = stackRadius.topRight;
                        }
                        if (isBottomBar) {
                          visualRadius.bottomLeft = stackRadius.bottomLeft;
                          visualRadius.bottomRight = stackRadius.bottomRight;
                        }
                        if (value3 < 0) {
                          radius = {
                            topLeft: visualRadius.bottomLeft,
                            topRight: visualRadius.bottomRight,
                            bottomLeft: visualRadius.topLeft,
                            bottomRight: visualRadius.topRight
                          };
                        } else {
                          radius = visualRadius;
                        }
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
                      if (value3 >= 0) {
                        previousPositiveHeight[index] += (chart.horizontal ? width : height) + stackGap;
                      } else {
                        previousNegativeHeight[index] += (chart.horizontal ? width : height) + stackGap;
                      }
                      bars.push(bar);
                    });
                    acc[itemMetadata.field] = bars;
                  }
                  return acc;
                }, {});
              }
            };
            return series;
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
          let series = {
            keys: filteredMetadata,
            // @todo: Review whether these are needed...
            values: data,
            // @todo: Review whether these are needed...
            bars() {
              let {
                start: groupStart,
                center: groupCenter,
                width: fullGroupWidth
              } = chart.dimensions.index.axis.scale(chart.dimensions.index.axis.domain[0], { area: chart.dimensions.index.asArea });
              let groupWidth = groupMetadata.width ?? "90%";
              if (groupWidth.match(/^(\d+)%$/)) {
                groupWidth = fullGroupWidth * Number(groupWidth.replace("%", "")) / 100;
              } else {
                groupWidth = Number(groupWidth.replace("px", ""));
              }
              let groupStartOffset = (fullGroupWidth - groupWidth) / 2;
              let gap = groupMetadata.gap ?? "2%";
              if (gap.match(/^(\d+)%$/)) {
                gap = groupWidth * Number(gap.replace("%", "")) / 100;
              } else {
                gap = Number(gap.replace("px", ""));
              }
              let countOfBars = filteredMetadata.length;
              let totalGap = gap * (countOfBars - 1);
              let countOfBarsWithoutWidth = countOfBars;
              let totalSpecifiedWidths = filteredMetadata.reduce((acc, itemMetadata) => {
                if (!itemMetadata.width) return acc;
                if (itemMetadata.width.match(/^(\d+)%$/)) {
                  acc += groupWidth * Number(itemMetadata.width.replace("%", "")) / 100;
                } else {
                  acc += Number(itemMetadata.width.replace("px", ""));
                }
                countOfBarsWithoutWidth--;
                return acc;
              }, 0);
              let barWidth = countOfBarsWithoutWidth > 0 ? (groupWidth - totalSpecifiedWidths - totalGap) / countOfBarsWithoutWidth : 0;
              if (barWidth === 0) {
                groupStartOffset = (fullGroupWidth - (totalSpecifiedWidths + totalGap)) / 2;
              }
              let itemOffset = groupStartOffset;
              return filteredMetadata.reduce((acc, itemMetadata, index) => {
                let width = barWidth;
                if (itemMetadata.width) {
                  if (itemMetadata.width.match(/^(\d+)%$/)) {
                    width = groupWidth * Number(itemMetadata.width.replace("%", "")) / 100;
                  } else {
                    width = Number(itemMetadata.width.replace("px", ""));
                  }
                }
                if (index > 0) {
                  itemOffset += gap;
                }
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
                  let stack = [];
                  let stackGap = Number(itemMetadata.gap.replace("px", ""));
                  let previousPositiveHeight = [];
                  let previousNegativeHeight = [];
                  let stackRadius = itemMetadata.radius;
                  let topBarField = [];
                  let bottomBarField = [];
                  if (stackRadius) {
                    itemMetadata.children.forEach((child) => {
                      if (child.type === "bar") {
                        data[child.field].forEach((value3, index2) => {
                          if (value3 >= 0) {
                            topBarField[index2] = child.field;
                            if (bottomBarField[index2] === void 0) {
                              bottomBarField[index2] = child.field;
                            }
                          } else {
                            bottomBarField[index2] = child.field;
                            if (topBarField[index2] === void 0) {
                              topBarField[index2] = child.field;
                            }
                          }
                        });
                      }
                    });
                  }
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
                          let visualRadius = { topLeft: 0, topRight: 0, bottomLeft: 0, bottomRight: 0 };
                          if (isTopBar) {
                            visualRadius.topLeft = stackRadius.topLeft;
                            visualRadius.topRight = stackRadius.topRight;
                          }
                          if (isBottomBar) {
                            visualRadius.bottomLeft = stackRadius.bottomLeft;
                            visualRadius.bottomRight = stackRadius.bottomRight;
                          }
                          if (value3 < 0) {
                            radius = {
                              topLeft: visualRadius.bottomLeft,
                              topRight: visualRadius.bottomRight,
                              bottomLeft: visualRadius.topLeft,
                              bottomRight: visualRadius.topRight
                            };
                          } else {
                            radius = visualRadius;
                          }
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
                        if (value3 >= 0) {
                          previousPositiveHeight[index2] += (chart.horizontal ? localWidth : height) + stackGap;
                        } else {
                          previousNegativeHeight[index2] += (chart.horizontal ? localWidth : height) + stackGap;
                        }
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
          return series;
        }));
      },
      cursor(indexPoint, type = "line") {
        if (type === "area") {
          if (this.horizontal) {
            return {
              x: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[0]).center,
              y: indexPoint.start,
              width: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[1]).center - this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[0]).center,
              height: indexPoint.width
            };
          }
          return {
            x: indexPoint.start,
            y: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[1]).center,
            width: indexPoint.width,
            height: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[0]).center - this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[1]).center
          };
        }
        if (this.horizontal) {
          let y = indexPoint.center;
          return [
            { x: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[0]).center, y },
            { x: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[1]).center, y }
          ];
        }
        let x = indexPoint.center;
        return [
          { x, y: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[0]).center },
          { x, y: this.dimensions.value.axis.scale(this.dimensions.value.axis.domain[1]).center }
        ];
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
        if (data[0] !== void 0 && typeof data[0] !== "object") {
          data = data.map((d, i) => {
            return {
              value: d
            };
          });
        }
        if (!data.every((d) => d.index)) {
          data.forEach((d, i) => {
            d.index = i;
          });
        }
        this.data = data;
        dataMemoKey++;
      }
    };
    chart.updateData(config.data);
    return chart;
  }
  function orientBarRadius(radius, value3, horizontal) {
    let oriented = { ...radius };
    if (value3 < 0) {
      oriented = {
        topLeft: oriented.bottomLeft,
        topRight: oriented.bottomRight,
        bottomRight: oriented.topRight,
        bottomLeft: oriented.topLeft
      };
    }
    if (horizontal) {
      oriented = {
        topLeft: oriented.bottomLeft,
        topRight: oriented.topLeft,
        bottomRight: oriented.topRight,
        bottomLeft: oriented.bottomRight
      };
    }
    return oriented;
  }
  function generateDomain(type, values) {
    let deduplicatedValues = [...new Set(values)];
    if (type === "time") {
      return generateTimeDomain(deduplicatedValues);
    } else if (type === "linear") {
      deduplicatedValues.sort((a, b) => Number(a) - Number(b));
      if (deduplicatedValues.length === 1) {
        if (deduplicatedValues[0] === 0) {
          return [
            Number(deduplicatedValues[0]),
            1
          ];
        } else if (deduplicatedValues[0] > 0) {
          return [
            0,
            Number(deduplicatedValues[0])
          ];
        } else {
          return [
            Number(deduplicatedValues[0]),
            0
          ];
        }
      }
      return [Number(deduplicatedValues[0]), Number(deduplicatedValues[deduplicatedValues.length - 1])];
    } else {
      return [
        deduplicatedValues[0],
        deduplicatedValues[deduplicatedValues.length - 1]
      ];
    }
  }
  function generateTickValues(data, axis, domain) {
    let tickValues = [];
    if (axis.type === "linear") {
      if (axis.tickValues) {
        return axis.tickValues;
      }
      let [minValue, maxValue] = domain;
      let targetCount = axis.tickCount ? Number(axis.tickCount) : 5;
      let tickStart = axis.tickStart === 0 || axis.tickStart === "0" ? 0 : axis.tickStart || "auto";
      let tickEnd = axis.tickEnd === 0 || axis.tickEnd === "0" ? 0 : axis.tickEnd || "auto";
      if (tickStart === "auto" && minValue > 0 && maxValue > 0) {
        minValue = 0;
      } else if (tickStart !== "min" && !isNaN(Number(tickStart))) {
        minValue = Math.min(minValue, Number(tickStart));
      }
      if (tickEnd !== "auto" && tickEnd !== "max" && !isNaN(Number(tickEnd))) {
        maxValue = Number(tickEnd);
      }
      let interval = Number(axis.interval);
      let niceStep;
      if (axis.interval === "auto" || Number.isNaN(interval)) {
        let range = maxValue - minValue;
        let rawStep = range / (targetCount - 1);
        let magnitude = Math.pow(10, Math.floor(Math.log10(rawStep)));
        let normalizedStep = rawStep / magnitude;
        if (normalizedStep < 1.5) niceStep = 1;
        else if (normalizedStep < 3) niceStep = 2;
        else if (normalizedStep < 7) niceStep = 5;
        else niceStep = 10;
        niceStep *= magnitude;
      } else {
        niceStep = interval;
      }
      let start;
      if (tickStart === "auto") {
        start = Math.floor(minValue / niceStep) * niceStep;
      } else if (tickStart === "min") {
        start = minValue;
      } else {
        start = Number(tickStart);
      }
      let currentTick = start;
      while (currentTick <= maxValue + niceStep * 0.1) {
        if (currentTick >= minValue - niceStep * 0.1) {
          tickValues.push(currentTick);
        }
        currentTick += niceStep;
      }
      if (tickEnd === "max") {
        if (Math.abs(tickValues[tickValues.length - 1] - domain[1]) > niceStep * 0.1) {
          tickValues.push(domain[1]);
        }
      } else if (tickEnd !== "auto" && !isNaN(Number(tickEnd))) {
        const end = Number(tickEnd);
        const count = tickValues.length;
        tickValues = Array.from(
          { length: count },
          (_, i) => start + (end - start) * (i / (count - 1))
        );
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
      if (!format) {
        [format] = generateDateFormatAndTicks(tickValues[0], tickValues[tickValues.length - 1], null, interval);
      }
      format = { ...format, timeZone: "UTC" };
      labels = tickValues.map((value3) => new Date(value3).toLocaleString(locale, format));
    } else if (type === "linear") {
      if (format) labels = tickValues.map((value3) => value3.toLocaleString(locale, format));
      else labels = tickValues.map((value3) => value3.toLocaleString(locale));
    } else {
      labels = tickValues.map((value3) => value3 + "");
    }
    if (tickPrefix) labels = labels.map((l) => tickPrefix + l);
    if (tickSuffix) labels = labels.map((l) => l + tickSuffix);
    return labels;
  }
  function scaleCategorical(domain, range, values) {
    const uniqueValues = [...new Set(values)];
    const count = uniqueValues.length;
    const indexMap = new Map(
      uniqueValues.map((category, index) => [category, index])
    );
    return function(value3, { area = false } = {}) {
      if (value3 === null || value3 === void 0) {
        return void 0;
      }
      const index = indexMap.get(value3);
      if (index === void 0) {
        return void 0;
      }
      if (area) {
        let step2 = (range[1] - range[0]) / (area ? count : count - 1);
        let start = range[0] + index * step2;
        let center = start + step2 / 2;
        let end = start + step2;
        return { start, center, end, width: step2 };
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
    const getCoords = (point) => direction === "x" ? { x: point[0], y: point[1] } : { x: point[1], y: point[0] };
    const calculateSlope = (p0, p1, p2) => {
      const { x: x0, y: y0 } = getCoords(p0);
      const { x: x1, y: y1 } = getCoords(p1);
      const { x: x2, y: y2 } = getCoords(p2);
      const h0 = x1 - x0;
      const h1 = x2 - x1;
      const s0 = (y1 - y0) / (h0 || h1 < 0 && -0);
      const s1 = (y2 - y1) / (h1 || h0 < 0 && -0);
      const p = (s0 * h1 + s1 * h0) / (h0 + h1);
      return (sign(s0) + sign(s1)) * Math.min(Math.abs(s0), Math.abs(s1), 0.5 * Math.abs(p)) || 0;
    };
    const endpointSlope = (p0, p1, t) => {
      const { x: x0, y: y0 } = getCoords(p0);
      const { x: x1, y: y1 } = getCoords(p1);
      const h = x1 - x0;
      return h ? (3 * (y1 - y0) / h - t) / 2 : t;
    };
    let path = `M${points[0][0]},${points[0][1]}`;
    if (points.length === 2) {
      return path + `L${points[1][0]},${points[1][1]}`;
    }
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
  function simpleCurve(points, smoothing = 0.05) {
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
    if (topLeftRadius === 0 && topRightRadius === 0 && bottomRightRadius === 0 && bottomLeftRadius === 0) {
      return `M${x},${y} h${width} v${height} h-${width} v-${height}`;
    }
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
    } else {
      path += `M${leftX},${topY}`;
    }
    if (topRightRounded) {
      path += `H${rightX - topRightRadius}`;
      path += `A${topRightRadius},${topRightRadius} 0 0 1 ${rightX},${topY + topRightRadius}`;
    } else {
      path += `H${rightX}`;
    }
    if (bottomRightRounded) {
      path += `V${bottomY - bottomRightRadius}`;
      path += `A${bottomRightRadius},${bottomRightRadius} 0 0 1 ${rightX - bottomRightRadius},${bottomY}`;
    } else {
      path += `V${bottomY}`;
    }
    if (bottomLeftRounded) {
      path += `H${leftX + bottomLeftRadius}`;
      path += `A${bottomLeftRadius},${bottomLeftRadius} 0 0 1 ${leftX},${bottomY - bottomLeftRadius}`;
    } else {
      path += `H${leftX}`;
    }
    path += `V${topY + (topLeftRounded ? topLeftRadius : 0)}`;
    path += `Z`;
    return path;
  }
  function isDateish(value3) {
    return value3 instanceof Date || typeof value3 === "string" && value3.includes("-") && !isNaN(Date.parse(value3));
  }
  function generateTimeDomain(values) {
    values.sort((a, b) => {
      if (typeof a === "string") {
        return a.localeCompare(b);
      }
      return a - b;
    });
    return [
      dateFromString(values[0]),
      dateFromString(values[values.length - 1])
    ];
  }
  function scaleTime(domain, range, values) {
    let minValue = new Date(domain[0]).getTime();
    let maxValue = new Date(domain[1]).getTime();
    let domainDiff = maxValue - minValue;
    let rangeDiff = range[1] - range[0];
    const uniqueValues = values ? [...new Set(values)] : [];
    const count = uniqueValues.length;
    return function(value3, { area = false } = {}) {
      if (!value3) return void 0;
      const dateValue = dateFromString(value3);
      if (isNaN(dateValue.getTime())) return void 0;
      if (domainDiff === 0) {
        let center = range[0] + rangeDiff / 2;
        if (area) {
          return { start: range[0], center, end: range[1], width: rangeDiff / count };
        }
        return { start: center, center, end: center, width: 0 };
      }
      const percent = (dateValue.getTime() - minValue) / domainDiff;
      if (area) {
        let index = Math.floor(percent * count);
        index = Math.min(Math.max(0, index), count - 1);
        let step = rangeDiff / count;
        let start = range[0] + index * step;
        let center = start + step / 2;
        let end = start + step;
        return { start, center, end, width: step };
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
    const diffMs = endDate - startDate;
    const diffSeconds = diffMs / 1e3;
    const diffMinutes = diffSeconds / 60;
    const diffHours = diffMinutes / 60;
    const diffDays = diffHours / 24;
    const diffMonths = diffDays / 30;
    const diffYears = diffDays / 365;
    let format;
    let incrementFn;
    let firstTick;
    switch (interval) {
      case "second":
        format = { hour: "numeric", minute: "numeric", second: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds() + 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours(), startDate.getUTCMinutes(), startDate.getUTCSeconds()));
        break;
      case "minute":
        format = { hour: "numeric", minute: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes() + 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours(), startDate.getUTCMinutes()));
        break;
      case "hour":
        format = { hour: "numeric", minute: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours() + 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours()));
        break;
      case "day":
        format = { day: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate()));
        break;
      case "week":
        format = { day: "numeric", month: "short" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + 7));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate()));
        break;
      case "month":
        format = { month: "short", day: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + 1, 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), 1));
        break;
      case "year":
        format = { year: "numeric" };
        incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear() + 1, 0, 1));
        firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), 0, 1));
        break;
      default:
    }
    if (incrementFn) {
    } else if (diffHours < 1) {
      format = { hour: "numeric", minute: "numeric" };
      let hourStep = tickCount ? Math.max(1, Math.floor(diffMinutes / (tickCount - 1))) : 1;
      incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes() + hourStep));
      firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), startDate.getUTCHours()));
    } else if (diffDays < 1) {
      format = { hour: "numeric" };
      let hourStep = tickCount ? Math.max(1, Math.floor(diffHours / (tickCount - 1))) : 1;
      incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours() + hourStep));
      firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), 0));
    } else if (diffDays <= 7) {
      format = { weekday: "short", hour: "numeric" };
      let hourStep = tickCount ? Math.max(1, Math.floor(diffHours / (tickCount - 1))) : 6;
      incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours() + hourStep));
      firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate(), 0));
    } else if (diffDays <= 30) {
      format = { month: "short", day: "numeric" };
      let dateStep = tickCount ? Math.max(1, Math.floor(diffDays / (tickCount - 1))) : 1;
      incrementFn = (date) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate() + dateStep));
      firstTick = new Date(Date.UTC(startDate.getUTCFullYear(), startDate.getUTCMonth(), startDate.getUTCDate()));
    } else if (diffMonths <= 6) {
      format = { month: "short", day: "numeric" };
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
      if (currentTick <= endDate) {
        ticks.push(new Date(currentTick.getTime()));
      }
    }
    return [format, ticks];
  }
  function dateFromString(value3) {
    if (value3 instanceof Date) return value3;
    let {
      year,
      month,
      day,
      hour,
      minute,
      second,
      millisecond
    } = parseDateString(value3);
    return new Date(Date.UTC(year, month - 1, day, hour, minute, second, millisecond));
  }
  function parseDateString(dateStr) {
    const match = dateStr.match(/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?(?:T(\d{2}):(\d{2}):(\d{2})(?:\.(\d+))?(Z)?)?$/);
    if (!match) {
      throw new Error("Invalid date format");
    }
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

  // js/chart/pie.js
  var TAU = Math.PI * 2;
  var START_ANGLE = -Math.PI / 2;
  var FULL_CIRCLE_EPSILON = 1e-9;
  var PIE_PALETTE = ["blue", "violet", "emerald", "amber", "rose", "cyan", "fuchsia", "lime", "orange", "teal", "indigo", "pink", "sky", "green", "yellow", "red", "purple"];
  function generatePieSectors(data, { field = "value", labelField = "index", colorAssignments = /* @__PURE__ */ new Map() } = {}) {
    let { items, issues } = normalizePieData(data, { field, labelField });
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
    return { items, issues: [...issues] };
  }
  function resolvePieColors(items, assignments = /* @__PURE__ */ new Map(), palette = PIE_PALETTE) {
    let keys = [...new Set(items.filter((item) => !item.color).map((item) => String(item.key)))];
    let taken = new Set(keys.filter((key) => assignments.has(key)).map((key) => assignments.get(key)));
    keys.filter((key) => !assignments.has(key)).sort().forEach((key) => {
      let slot = hashString(key) % palette.length;
      if (taken.size < palette.length) {
        while (taken.has(palette[slot])) slot = (slot + 1) % palette.length;
      }
      assignments.set(key, palette[slot]);
      taken.add(palette[slot]);
    });
    return items.map((item) => ({ ...item, color: item.color ?? assignments.get(String(item.key)) }));
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
    if (r0 <= 0) {
      parts.push(`L${cx},${cy}`);
    } else if (rc0 > 0) {
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

  // js/chart/index.js
  var UIChart = class extends HTMLElement {
    constructor() {
      super();
      this.querySelectorAll("[data-appended]").forEach((el) => el.remove());
      let svgTemplate = this.querySelector('template[name="svg"]');
      let svg = hydrateTemplate(svgTemplate);
      svgTemplate.after(svg);
      this.init(svgTemplate, svg);
    }
    init(svgTemplate, svg) {
      if (!this._initialized) {
        this._data = this.hasAttribute("value") ? JSON.parse(this.getAttribute("value")) : [];
        this._observable = new Observable();
        this._selectable = { getState: () => this._data, setState: (value3) => {
          this._data = value3;
          this._observable.notify("data", this._data);
        } };
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
        cursor: svgTemplate.content.querySelector('template[name="cursor"]'),
        zeroLine: svgTemplate.content.querySelector('template[name="zero-line"]'),
        pie: svgTemplate.content.querySelector('template[name="pie"]'),
        axes: {
          x: {
            template: svgTemplate.content.querySelector('template[name="axis"][axis="x"]'),
            axisLine: svgTemplate.content.querySelector('template[name="axis"][axis="x"] template[name="axis-line"]'),
            gridLine: svgTemplate.content.querySelector('template[name="axis"][axis="x"] template[name="grid-line"]'),
            tickMark: svgTemplate.content.querySelector('template[name="axis"][axis="x"] template[name="tick-mark"]'),
            tickLabel: svgTemplate.content.querySelector('template[name="axis"][axis="x"] template[name="tick-label"]')
          },
          y: {
            template: svgTemplate.content.querySelector('template[name="axis"][axis="y"]'),
            axisLine: svgTemplate.content.querySelector('template[name="axis"][axis="y"] template[name="axis-line"]'),
            gridLine: svgTemplate.content.querySelector('template[name="axis"][axis="y"] template[name="grid-line"]'),
            tickMark: svgTemplate.content.querySelector('template[name="axis"][axis="y"] template[name="tick-mark"]'),
            tickLabel: svgTemplate.content.querySelector('template[name="axis"][axis="y"] template[name="tick-label"]')
          }
        },
        tooltip: this.querySelector('template[name="tooltip"]'),
        summary: this.querySelector('template[name="summary"]')
      };
      svgTemplate.content.querySelectorAll('template[name="group"]').forEach((template) => {
        templates.groups.push(template);
      });
      svgTemplate.content.querySelectorAll('template[name="stack"]:not(template[name="group"] template[name="stack"])').forEach((template) => {
        templates.stacks.push(template);
      });
      svgTemplate.content.querySelectorAll('template[name="bar"]:not(template[name="stack"] template[name="bar"]):not(template[name="group"] template[name="bar"])').forEach((template) => {
        templates.bars[template.getAttribute("field")] = template;
        hasBars = true;
      });
      svgTemplate.content.querySelectorAll('template[name="line"]').forEach((template) => {
        templates.lines[template.getAttribute("field")] = template;
        hasLines = true;
      });
      svgTemplate.content.querySelectorAll('template[name="area"]').forEach((template) => {
        templates.areas[template.getAttribute("field")] = template;
        hasAreas = true;
      });
      svgTemplate.content.querySelectorAll('template[name="point"]').forEach((template) => {
        templates.points[template.getAttribute("field")] = template;
        hasPoints = true;
      });
      let repositionCursor = () => {
      };
      let repositionTooltip = () => {
      };
      let updateSummary = () => {
      };
      let activatePoint = () => {
      };
      let cursor = null;
      let cursorType = "line";
      let overlay = null;
      let tooltip = null;
      let summary = null;
      let stacksMetadata = [];
      svgTemplate.content.querySelectorAll('template[name="stack"]:not(template[name="group"] template[name="stack"])').forEach((stackTemplate) => {
        let stackMetadata = {
          width: stackTemplate.getAttribute("width") ?? "90%",
          gap: stackTemplate.getAttribute("gap") ?? "0",
          radius: stackTemplate.hasAttribute("radius") ? parseRadius(stackTemplate.getAttribute("radius")) : null,
          children: []
        };
        stackTemplate.querySelectorAll('template[name="bar"]').forEach((barTemplate) => {
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
      svgTemplate.content.querySelectorAll('template[name="group"]').forEach((groupTemplate) => {
        let groupMetadata = {
          width: groupTemplate.getAttribute("width") ?? "90%",
          gap: groupTemplate.getAttribute("gap") ?? "2%",
          children: []
        };
        groupTemplate.querySelectorAll('template[name="stack"], template[name="bar"]:not(template[name="stack"] template[name="bar"])').forEach((itemTemplate) => {
          let itemMetadata = {};
          if (itemTemplate.getAttribute("name") === "stack") {
            itemMetadata = {
              type: "stack",
              width: itemTemplate.getAttribute("width"),
              gap: itemTemplate.getAttribute("gap") ?? "0",
              radius: itemTemplate.hasAttribute("radius") ? parseRadius(itemTemplate.getAttribute("radius")) : null,
              children: []
            };
            itemTemplate.querySelectorAll('template[name="bar"]').forEach((barTemplate) => {
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
      let pieTemplates = svgTemplate.content.querySelectorAll('template[name="pie"]');
      let hasPie = pieTemplates.length > 0;
      let hasCartesianChrome = !!(templates.cursor || templates.zeroLine || templates.axes.x.template || templates.axes.y.template);
      let pieConflict = null;
      if (hasPie && (hasCartesianMarks || hasCartesianChrome)) {
        pieConflict = "ui-chart: a pie cannot be combined with bar, line, area, point, axis, cursor, or zero-line marks, so nothing was rendered.";
      } else if (pieTemplates.length > 1) {
        pieConflict = "ui-chart: only one pie is allowed per chart, so nothing was rendered.";
      }
      let pieMetadata = null;
      if (hasPie) {
        pieMetadata = {
          field: templates.pie.getAttribute("field") || "value",
          labelField: templates.pie.getAttribute("label-field") || "index",
          innerRadius: templates.pie.getAttribute("inner-radius") ?? "0",
          radius: Number(templates.pie.getAttribute("radius")) || 0,
          separatorWidth: Number(templates.pie.firstElementChild?.getAttribute("stroke-width")) || 0
        };
      }
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
      let gutter = { left: 8, right: 8, top: 8, bottom: 8 };
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
            format = { ...format, timeZone: "UTC" };
            if (isNumeric(value3)) {
              value3 = Number(value3).toLocaleString(chart.locale, format);
            } else if (isDateish2(value3)) {
              value3 = new Date(value3).toLocaleDateString(chart.locale, format);
            }
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
            format = { ...format, timeZone: "UTC" };
            let value3 = slot.hasAttribute("fallback") && useFallback ? slot.getAttribute("fallback") : datum[field];
            if (isNumeric(value3)) {
              value3 = Number(value3).toLocaleString(chart.locale, format);
            } else if (isDateish2(value3)) {
              value3 = new Date(value3).toLocaleString(chart.locale, format);
            }
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
        svgTemplate.content.querySelectorAll('template[name="group"], template[name="stack"]:not(template[name="group"] template[name="stack"]), template[name="bar"]:not(template[name="stack"] template[name="bar"]):not(template[name="group"] template[name="bar"]), template[name="line"], template[name="area"], template[name="point"], template[name="pie"]').forEach((template) => {
          let name = template.getAttribute("name");
          let field = template.getAttribute("field");
          let el;
          if (name === "group") {
            el = svg.querySelector(`[data-group][data-group-index="${groupIndex}"]`);
            groupIndex++;
          } else if (name === "stack") {
            el = svg.querySelector(`[data-stack][data-stack-index="${stackIndex}"]`);
            stackIndex++;
          } else if (name === "bar") {
            el = svg.querySelector(`[data-bar-group][data-series="${field}"]`);
          } else if (name === "point") {
            el = svg.querySelector(`[data-point-group][data-series="${field}"]`);
          } else if (name === "pie") {
            el = svg.querySelector(`[data-pie-group][data-series="${field}"]`);
          } else {
            el = svg.querySelector(`[data-${name}][data-series="${field}"]`);
          }
          if (el) overlay.before(el);
        });
        svg.querySelectorAll("[data-zero-line]").forEach((i) => overlay.before(i));
        if (cursorType === "line") svg.querySelectorAll("[data-cursor]").forEach((i) => overlay.before(i));
      };
      let insetForOverflow = () => {
        let overflow = getSvgOverflow(svg);
        adjustOverflowForPendingTickRotation(svg, chart, gutter, overflow);
        chart.updateDimensions({ width: chart.width, height: chart.height }, {
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
          sectorEl.setAttribute("d", getSectorPath({ ...layout, startAngle: sector.startAngle, endAngle: sector.endAngle, cornerRadius: pieMetadata.radius }));
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
        if (chart.data.length === 0) {
          return;
        }
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
          let stackTemplates = {
            bars: {}
          };
          stackTemplate.querySelectorAll('template[name="bar"]').forEach((template) => {
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
          let groupTemplates = {
            bars: {}
          };
          groupTemplate.querySelectorAll('template[name="bar"]').forEach((template) => {
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
            } else {
              line.setAttribute("d", series.linePath(curve));
            }
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
            } else {
              area.setAttribute("d", series.areaPath(curve));
            }
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
              if (mouseX >= chart.inset.left && mouseX <= chart.width - chart.inset.right && mouseY >= chart.inset.top && mouseY <= chart.height - chart.inset.bottom) {
                let closestPoints = chart.closestIndexPoints(chart.indexPosition(mouseX, mouseY));
                closestPoints.forEach((point) => {
                  let index = chart.data.indexOf(point.datum);
                  pointGroupEl.querySelectorAll("[data-point]")[index]?.setAttribute("data-active", "");
                });
              }
            } else {
              pointGroupEl.querySelectorAll("[data-point]").forEach((i) => i.removeAttribute("data-active"));
            }
          });
        };
        if (templates.axes.x.template) {
          if (templates.axes.x.axisLine) {
            svg.querySelector('[data-axis-line][data-axis="x"]')?.remove();
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
            svg.querySelector('[data-grid-line-group][data-axis="x"]')?.remove();
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
            svg.querySelector('[data-tick-mark-group][data-axis="x"]')?.remove();
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
            svg.querySelector('[data-tick-label-group][data-axis="x"]')?.remove();
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
            svg.querySelector('[data-axis-line][data-axis="y"]')?.remove();
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
            svg.querySelector('[data-grid-line-group][data-axis="y"]')?.remove();
            let gridLineGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            gridLineGroupEl.setAttribute("data-grid-line-group", "");
            gridLineGroupEl.setAttribute("data-axis", "y");
            chart.axes.y.ticks.forEach(({ value: value3, label }) => {
              let position = { x: chart.axes.x.range[0], y: chart.axes.y.scale(value3, { area: chart.axes.y.dimension.asArea }).center };
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
            svg.querySelector('[data-tick-mark-group][data-axis="y"]')?.remove();
            let tickMarkGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            tickMarkGroupEl.setAttribute("data-tick-mark-group", "");
            tickMarkGroupEl.setAttribute("data-axis", "y");
            chart.axes.y.ticks.forEach(({ value: value3, label }) => {
              let position = { x: chart.axes.x.range[chart.axes.y.position === "left" ? 0 : 1], y: chart.axes.y.scale(value3, { area: chart.axes.y.dimension.asArea }).center };
              let tickMarkEl = hydrateSvgTemplate(templates.axes.y.tickMark);
              tickMarkEl.setAttribute("data-tick-mark", "");
              tickMarkEl.setAttribute("data-axis", "y");
              tickMarkEl.setAttribute("transform", `translate(${position.x}, ${position.y})`);
              tickMarkGroupEl.appendChild(tickMarkEl);
            });
            svg.appendChild(tickMarkGroupEl);
          }
          if (templates.axes.y.tickLabel) {
            svg.querySelector('[data-tick-label-group][data-axis="y"]')?.remove();
            let tickLabelGroupEl = document.createElementNS("http://www.w3.org/2000/svg", "g");
            tickLabelGroupEl.setAttribute("data-tick-label-group", "");
            tickLabelGroupEl.setAttribute("data-axis", "y");
            chart.axes.y.ticks.forEach(({ value: value3, label }) => {
              let position = { x: chart.axes.x.range[chart.axes.y.position === "left" ? 0 : 1], y: chart.axes.y.scale(value3, { area: chart.axes.y.dimension.asArea }).center };
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
              } else {
                cursor.setAttribute("opacity", "0");
              }
            } else {
              cursor.setAttribute("opacity", "0");
            }
          };
        }
        if (tooltip) {
          repositionTooltip = (event) => {
            if (event === null) {
              removeAttribute(tooltip, "data-active");
            } else {
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
                } else {
                  removeAttribute(tooltip, "data-active");
                }
              } else {
                removeAttribute(tooltip, "data-active");
              }
            }
          };
        }
        if (summary) {
          updateSummary = (event) => {
            let closestIndexPoint = null;
            if (event !== null) {
              let mouseX = event.clientX - svg.getBoundingClientRect().left;
              let mouseY = event.clientY - svg.getBoundingClientRect().top;
              if (mouseX >= chart.inset.left && mouseX <= chart.width - chart.inset.right && mouseY >= chart.inset.top && mouseY <= chart.height - chart.inset.bottom) {
                closestIndexPoint = chart.closestIndexPoint(chart.indexPosition(mouseX, mouseY));
              }
            }
            closestIndexPoint = closestIndexPoint || chart.closestIndexPoint(chart.dimensions.index.axis.range[1]);
            if (closestIndexPoint) {
              renderSummaryContent(closestIndexPoint.datum, event === null);
            }
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
        if (cursor) {
          cursor.setAttribute("opacity", "0");
        }
      });
      this._observable.subscribe("data", () => {
        chart.updateData(this._data);
        updatePieSectors();
        chart.updateDimensions({ width: chart.width, height: chart.height }, {
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
      }).observe(this, { attributes: true, attributeFilter: ["value"] });
    }
  };
  customElements.define("ui-chart", UIChart);
  function discoverDimensions(svg, hasBars = false, horizontal = false) {
    let indexKey = null;
    let valueKeys = [];
    svg.querySelectorAll('template[name="line"], template[name="area"], template[name="point"], template[name="pie"], template[name="bar"]:not(template[name="stack"] template[name="bar"]):not(template[name="group"] template[name="bar"])').forEach((template) => {
      valueKeys.push(template.getAttribute("field") || "value");
    });
    svg.querySelectorAll(`template[name="axis"][axis="${horizontal ? "y" : "x"}"]`).forEach((template) => {
      indexKey = template.getAttribute("field") || "index";
    });
    svg.querySelectorAll('template[name="pie"][label-field]').forEach((template) => {
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
    let tickLabelGroupEl = svg.querySelector('[data-tick-label-group][data-axis="x"]');
    if (!tickLabelGroupEl) return;
    if (chart.axes.x.type !== "categorical") return;
    if (hasOverlappingTicks(tickLabelGroupEl, chart.axes.x)) return;
    let widthReduction = gutter.left + overflow.left + gutter.right + overflow.right - (chart.inset.left + chart.inset.right);
    if (widthReduction <= 0) return;
    let children = Array.from(tickLabelGroupEl.children).filter((child) => child.style.display !== "none");
    for (let i = 0; i < children.length - 1; i++) {
      let rect = children[i].getBoundingClientRect();
      let nextRect = children[i + 1].getBoundingClientRect();
      let adjustedGap = nextRect.left - rect.right - widthReduction / Math.max(1, children.length - 1);
      if (adjustedGap < 20) {
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
    if (axis.axis === "x" && axis.type === "categorical") {
      rotateTickLabels(tickLabelGroupEl);
    } else if (axis.axis === "x" && axis.type === "time") {
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
        if (tickLabelRect.bottom + overlapMargin < nextTickLabelRect.top) {
          return true;
        }
      } else {
        if (tickLabelRect.right + overlapMargin > nextTickLabelRect.left) {
          return true;
        }
      }
    }
    return false;
  }
  function rotateTickLabels(tickLabelGroupEl) {
    let undos = [];
    Array.from(tickLabelGroupEl.children).forEach((child, index) => {
      let textNode = child.matches("text") ? child : child.querySelector("text");
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
    let contentRect = { top: Infinity, right: -Infinity, bottom: -Infinity, left: Infinity };
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
    if (!radiusString || radiusString.trim() === "") {
      throw new Error("Radius string cannot be empty");
    }
    let radiusValues = radiusString.trim().split(/\s+/).map((val) => {
      let num = val.replace(/px/, "");
      return Number(num) || 0;
    });
    if (radiusValues.length === 0 || radiusValues.every((v) => isNaN(v))) {
      throw new Error(`Invalid radius string: "${radiusString}"`);
    }
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
    } else {
      throw new Error(`Invalid radius string: "${radiusString}". Must be 1, 2, or 4 numbers`);
    }
    return radius;
  }

