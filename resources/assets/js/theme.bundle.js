/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/scss/theme.scss":
/*!*****************************!*\
  !*** ./src/scss/theme.scss ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/js/autosize.js":
/*!****************************!*\
  !*** ./src/js/autosize.js ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var autosize__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! autosize */ "./node_modules/autosize/dist/autosize.esm.js");
//
// autosize.js
// Dashkit module
//



const toggles = document.querySelectorAll('[data-autosize]');

toggles.forEach((toggle) => {
  (0,autosize__WEBPACK_IMPORTED_MODULE_0__["default"])(toggle);
});

// Make available globally
window.autosize = autosize__WEBPACK_IMPORTED_MODULE_0__["default"];


/***/ }),

/***/ "./src/js/bootstrap.js":
/*!*****************************!*\
  !*** ./src/js/bootstrap.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");


// Make available globally
window.Alert = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Alert;
window.Button = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Button;
window.Carousel = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Carousel;
window.Collapse = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Collapse;
window.Dropdown = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Dropdown;
window.Modal = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Modal;
window.Offcanvas = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Offcanvas;
window.Popover = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Popover;
window.ScrollSpy = bootstrap__WEBPACK_IMPORTED_MODULE_0__.ScrollSpy;
window.Tab = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Tab;
window.Toast = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Toast;
window.Tooltip = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Tooltip;


/***/ }),

/***/ "./src/js/chart.js":
/*!*************************!*\
  !*** ./src/js/chart.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var chart_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! chart.js */ "./node_modules/chart.js/dist/chart.js");
/* harmony import */ var _helpers__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./helpers */ "./src/js/helpers/index.js");
//
// chart.js
// Theme module
//





chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.register(
  chart_js__WEBPACK_IMPORTED_MODULE_1__.ArcElement,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.BarController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.BarElement,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.BubbleController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.CategoryScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Decimation,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.DoughnutController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Filler,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Legend,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.LinearScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.LineController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.LineElement,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.LogarithmicScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.PieController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.PointElement,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.PolarAreaController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.RadarController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.RadialLinearScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.ScatterController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.TimeScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.TimeSeriesScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Title,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Tooltip
);

const colors = {
  gray: {
    300: (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-gray-300'),
    600: (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-gray-600'),
    700: (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-gray-700'),
    800: (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-gray-800'),
    900: (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-gray-900'),
  },
  primary: {
    100: (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-primary-100'),
    300: (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-primary-300'),
    700: (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-primary-700'),
  },
  black: (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-dark'),
  white: (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-white'),
  transparent: 'transparent',
};

const fonts = {
  base: 'Cerebri Sans',
};

const toggles = document.querySelectorAll('[data-toggle="chart"]');
const legends = document.querySelectorAll('[data-toggle="legend"]');

//
// Functions
//

function globalOptions() {
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.responsive = true;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.maintainAspectRatio = false;

  // Default
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.color = (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-default-color');
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.font.family = fonts.base;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.font.size = 13;

  // Layout
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.layout.padding = 0;

  // Legend
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.plugins.legend.display = false;

  // Point
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.point.radius = 0;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.point.backgroundColor = colors.primary[700];

  // Line
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.line.tension = 0.4;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.line.borderWidth = 3;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.line.borderColor = colors.primary[700];
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.line.backgroundColor = colors.transparent;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.line.borderCapStyle = 'rounded';

  // Bar
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.bar.backgroundColor = colors.primary[700];
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.bar.borderWidth = 0;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.bar.borderRadius = 10;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.bar.borderSkipped = false;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.datasets.bar.maxBarThickness = 10;

  // Arc
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.arc.backgroundColor = colors.primary[700];
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.arc.borderColor = (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-arc-border-color');
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.arc.borderWidth = 4;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.elements.arc.hoverBorderColor = (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-arc-hover-border-color');

  // Tooltips
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.plugins.tooltip.enabled = false;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.plugins.tooltip.mode = 'index';
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.plugins.tooltip.intersect = false;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.plugins.tooltip.external = externalTooltipHandler;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.plugins.tooltip.callbacks.label = externalTooltipLabelCallback;

  // Doughnut
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.datasets.doughnut.cutout = '83%';

  // yAxis
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.linear.border = { ...chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.linear.border, ...{ display: false, dash: [2], dashOffset: [2] } };
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.linear.grid = {
    ...chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.linear.grid,
    ...{ color: (0,_helpers__WEBPACK_IMPORTED_MODULE_0__.getCSSVariableValue)('--bs-chart-grid-line-color'), drawTicks: false },
  };

  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.linear.ticks.beginAtZero = true;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.linear.ticks.padding = 10;
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.linear.ticks.stepSize = 10;

  // xAxis
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.category.border = { ...chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.category.border, ...{ display: false } };
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.category.grid = { ...chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.category.grid, ...{ display: false, drawTicks: false, drawOnChartArea: false } };
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.defaults.scales.category.ticks.padding = 20;
}

function getOrCreateTooltip(chart) {
  let tooltipEl = chart.canvas.parentNode.querySelector('div');

  if (!tooltipEl) {
    tooltipEl = document.createElement('div');
    tooltipEl.setAttribute('id', 'chart-tooltip');
    tooltipEl.setAttribute('role', 'tooltip');
    tooltipEl.classList.add('popover', 'bs-popover-top');

    const arrowEl = document.createElement('div');
    arrowEl.classList.add('popover-arrow', 'translate-middle-x');

    const contentEl = document.createElement('div');
    contentEl.classList.add('popover-content');

    tooltipEl.appendChild(arrowEl);
    tooltipEl.appendChild(contentEl);
    chart.canvas.parentNode.appendChild(tooltipEl);
  }

  return tooltipEl;
}

function externalTooltipHandler(context) {
  // Tooltip Element
  const { chart, tooltip } = context;
  const tooltipEl = getOrCreateTooltip(chart);

  // Hide if no tooltip
  if (tooltip.opacity === 0) {
    tooltipEl.style.visibility = 'hidden';
    return;
  }

  // Set Text
  if (tooltip.body) {
    const titleLines = tooltip.title || [];
    const bodyLines = tooltip.body.map((b) => b.lines);

    const headEl = document.createElement('div');
    titleLines.forEach((title) => {
      const headingEl = document.createElement('h3');
      headingEl.classList.add('popover-header', 'text-center', 'text-nowrap');

      const text = document.createTextNode(title);

      headingEl.appendChild(text);
      headEl.appendChild(headingEl);
    });

    const bodyEl = document.createElement('div');
    bodyLines.forEach((body, i) => {
      const colors = tooltip.labelColors[i];

      const indicatorEl = document.createElement('span');
      indicatorEl.classList.add('popover-body-indicator');
      indicatorEl.style.backgroundColor =
        chart.config.type === 'line' && colors.borderColor !== 'rgba(0,0,0,0.1)' ? colors.borderColor : colors.backgroundColor;

      const contentEl = document.createElement('div');
      contentEl.classList.add('popover-body', 'd-flex', 'align-items-center', 'text-nowrap');
      contentEl.classList.add(bodyLines.length > 1 ? 'justify-content-left' : 'justify-content-center');

      const text = document.createTextNode(body);

      contentEl.appendChild(indicatorEl);
      contentEl.appendChild(text);
      bodyEl.appendChild(contentEl);
    });

    const rootEl = tooltipEl.querySelector('.popover-content');

    // Remove old children
    while (rootEl.firstChild) {
      rootEl.firstChild.remove();
    }

    // Add new children
    rootEl.appendChild(headEl);
    rootEl.appendChild(bodyEl);
  }

  const { offsetLeft: positionX, offsetTop: positionY } = chart.canvas;

  // Display, position, and set styles for font
  tooltipEl.style.visibility = 'visible';
  tooltipEl.style.left = positionX + tooltip.caretX + 'px';
  tooltipEl.style.top = positionY + tooltip.caretY + 'px';
  tooltipEl.style.transform = 'translateX(-50%) translateY(-100%) translateY(-1rem)';
}

function externalTooltipLabelCallback(ctx) {
  let content = '';
  const scale = ctx.chart.scales[ctx.dataset.yAxisID || 'y'];
  if (scale) {
    const label = ctx.chart.tooltip.dataPoints.length > 1 ? ' ' + ctx.dataset.label + ' ' : ' ';
    content = label + scale.options.ticks.callback.apply(scale, [ctx.parsed.y, 0, []]);
  } else {
    const callbacks = ctx.chart.options.plugins.tooltip.callbacks;
    const before = callbacks.beforeLabel() || '';
    const after = callbacks.afterLabel() || '';
    content = before + ctx.formattedValue + after;
  }

  return content;
}

function toggleDataset(toggle) {
  const id = toggle.dataset.target;
  const action = toggle.dataset.action;
  const index = parseInt(toggle.dataset.dataset);

  const chart = document.querySelector(id);
  const chartInstance = chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.getChart(chart);

  // Action: Toggle
  if (action === 'toggle') {
    const datasets = chartInstance.data.datasets;

    const activeDataset = datasets.filter(function (dataset) {
      return !dataset.hidden;
    })[0];

    let backupDataset = datasets.filter(function (dataset) {
      return dataset.order === 1000;
    })[0];

    // Backup active dataset
    if (!backupDataset) {
      backupDataset = {};

      for (const prop in activeDataset) {
        if (prop !== '_meta') {
          backupDataset[prop] = activeDataset[prop];
        }
      }

      backupDataset.order = 1000;
      backupDataset.hidden = true;

      // Push to the dataset list
      datasets.push(backupDataset);
    }

    // Toggle dataset
    const sourceDataset = datasets[index] === activeDataset ? backupDataset : datasets[index];

    for (const prop in activeDataset) {
      if (prop !== '_meta') {
        activeDataset[prop] = sourceDataset[prop];
      }
    }

    // Update chart
    chartInstance.update();
  }

  // Action: Add
  if (action === 'add') {
    const dataset = chartInstance.data.datasets[index];
    const isHidden = dataset.hidden;

    // Toggle dataset
    dataset.hidden = !isHidden;
  }

  // Update chart
  chartInstance.update();
}

function toggleLegend(legend) {
  const chart = chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.getChart(legend);
  const legendEl = document.createElement('div');

  chart.legend.legendItems?.forEach((item) => {
    legendEl.innerHTML += `<span class="chart-legend-item"><span class="chart-legend-indicator" style="background-color: ${item.fillStyle}"></span>${item.text}</span>`;
  });

  const id = legend.dataset.target;
  const container = document.querySelector(id);

  container.appendChild(legendEl);
}

//
// Events
//

// Global options
globalOptions();

// Toggle dataset
toggles.forEach(function (toggle) {
  const event = toggle.dataset.trigger;

  toggle.addEventListener(event, function () {
    toggleDataset(toggle);
  });
});

// // Toggle legend
document.addEventListener('DOMContentLoaded', function () {
  legends.forEach(function (legend) {
    toggleLegend(legend);
  });
});

// Make available globally
window.Chart = chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart;


/***/ }),

/***/ "./src/js/checklist.js":
/*!*****************************!*\
  !*** ./src/js/checklist.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _shopify_draggable__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shopify/draggable */ "./node_modules/@shopify/draggable/lib/draggable.bundle.js");
/* harmony import */ var _shopify_draggable__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_shopify_draggable__WEBPACK_IMPORTED_MODULE_0__);
//
// checklist.js
// Dashkit module
//



const checklists = document.querySelectorAll('.checklist');

if (checklists) {
  new _shopify_draggable__WEBPACK_IMPORTED_MODULE_0__.Sortable(checklists, {
    draggable: '.form-check',
    handle: '.form-check-label',
    mirror: {
      constrainDimensions: true
    }
  });
}

/***/ }),

/***/ "./src/js/choices.js":
/*!***************************!*\
  !*** ./src/js/choices.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var choices_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! choices.js */ "./node_modules/choices.js/public/assets/scripts/choices.js");
/* harmony import */ var choices_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(choices_js__WEBPACK_IMPORTED_MODULE_0__);
//
// choices.js
// Theme module
//



const toggles = document.querySelectorAll('[data-choices]');

toggles.forEach((toggle) => {
  const elementOptions = toggle.dataset.choices ? JSON.parse(toggle.dataset.choices) : {};

  const defaultOptions = {
    classNames: {
      containerInner: toggle.className,
      input: 'form-control',
      inputCloned: 'form-control-sm',
      listDropdown: 'dropdown-menu',
      itemChoice: 'dropdown-item',
      activeState: 'show',
      selectedState: 'active',
    },
    shouldSort: false,
    callbackOnCreateTemplates: function (template) {
      return {
        choice: (classNames, data) => {
          const classes = `${classNames.item} ${classNames.itemChoice} ${
            data.disabled ? classNames.itemDisabled : classNames.itemSelectable
          }`;
          const disabled = data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable';
          const role = data.groupId > 0 ? 'role="treeitem"' : 'role="option"';
          const selectText = this.config.itemSelectText;

          const label =
            data.customProperties && data.customProperties.avatarSrc
              ? `
            <div class="avatar avatar-xs me-3">
              <img class="avatar-img rounded-circle" src="${data.customProperties.avatarSrc}" alt="${data.label}" >
            </div> ${data.label}
          `
              : data.label;

          return template(`
            <div class="${classes}" data-select-text="${selectText}" data-choice ${disabled} data-id="${data.id}" data-value="${data.value}" ${role}>
              ${label}
            </div>
          `);
        },
      };
    },
  };

  const options = {
    ...elementOptions,
    ...defaultOptions,
  };

  new (choices_js__WEBPACK_IMPORTED_MODULE_0___default())(toggle, options);
});

// Make available globally
window.Choices = (choices_js__WEBPACK_IMPORTED_MODULE_0___default());


/***/ }),

/***/ "./src/js/dropzone.js":
/*!****************************!*\
  !*** ./src/js/dropzone.js ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var dropzone__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! dropzone */ "./node_modules/dropzone/dist/dropzone.js");
/* harmony import */ var dropzone__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(dropzone__WEBPACK_IMPORTED_MODULE_0__);
//
// dropzone.js
// Theme module
//



(dropzone__WEBPACK_IMPORTED_MODULE_0___default().autoDiscover) = false;
(dropzone__WEBPACK_IMPORTED_MODULE_0___default().thumbnailWidth) = null;
(dropzone__WEBPACK_IMPORTED_MODULE_0___default().thumbnailHeight) = null;

const toggles = document.querySelectorAll('[data-dropzone]');

toggles.forEach((toggle) => {
  let currentFile = undefined;

  const elementOptions = toggle.dataset.dropzone ? JSON.parse(toggle.dataset.dropzone) : {};

  const defaultOptions = {
    previewsContainer: toggle.querySelector('.dz-preview'),
    previewTemplate: toggle.querySelector('.dz-preview').innerHTML,
    init: function () {
      this.on('addedfile', function (file) {
        const maxFiles = elementOptions.maxFiles;

        if (maxFiles == 1 && currentFile) {
          this.removeFile(currentFile);
        }

        currentFile = file;
      });
    },
  };

  const options = {
    ...elementOptions,
    ...defaultOptions,
  };

  // Clear preview
  toggle.querySelector('.dz-preview').innerHTML = '';

  // Init dropzone
  new (dropzone__WEBPACK_IMPORTED_MODULE_0___default())(toggle, options);
});

// Make available globally
window.Dropzone = (dropzone__WEBPACK_IMPORTED_MODULE_0___default());


/***/ }),

/***/ "./src/js/flatpickr.js":
/*!*****************************!*\
  !*** ./src/js/flatpickr.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flatpickr__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flatpickr */ "./node_modules/flatpickr/dist/esm/index.js");
//
// flatpickr.js
// Theme module
//



const toggles = document.querySelectorAll('[data-flatpickr]');

toggles.forEach((toggle) => {
  const options = toggle.dataset.flatpickr ? JSON.parse(toggle.dataset.flatpickr) : {};

  (0,flatpickr__WEBPACK_IMPORTED_MODULE_0__["default"])(toggle, options);
});

// Make available globally
window.flatpickr = flatpickr__WEBPACK_IMPORTED_MODULE_0__["default"];


/***/ }),

/***/ "./src/js/helpers/getCSSVariableValue.js":
/*!***********************************************!*\
  !*** ./src/js/helpers/getCSSVariableValue.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ getCSSVariableValue)
/* harmony export */ });
function getCSSVariableValue(variable) {
  return getComputedStyle(document.documentElement).getPropertyValue(variable);
}


/***/ }),

/***/ "./src/js/helpers/index.js":
/*!*********************************!*\
  !*** ./src/js/helpers/index.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "getCSSVariableValue": () => (/* reexport safe */ _getCSSVariableValue__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _getCSSVariableValue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./getCSSVariableValue */ "./src/js/helpers/getCSSVariableValue.js");





/***/ }),

/***/ "./src/js/highlight.js":
/*!*****************************!*\
  !*** ./src/js/highlight.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var highlight_js_lib_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! highlight.js/lib/core */ "./node_modules/highlight.js/lib/core.js");
/* harmony import */ var highlight_js_lib_core__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(highlight_js_lib_core__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var highlight_js_lib_languages_javascript__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! highlight.js/lib/languages/javascript */ "./node_modules/highlight.js/lib/languages/javascript.js");
/* harmony import */ var highlight_js_lib_languages_javascript__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(highlight_js_lib_languages_javascript__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var highlight_js_lib_languages_xml__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! highlight.js/lib/languages/xml */ "./node_modules/highlight.js/lib/languages/xml.js");
/* harmony import */ var highlight_js_lib_languages_xml__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(highlight_js_lib_languages_xml__WEBPACK_IMPORTED_MODULE_2__);
//
// highlight.js
// Dashkit module
//





const highlights = document.querySelectorAll('.highlight');

// Only register the languages we need to reduce the download footprint
highlight_js_lib_core__WEBPACK_IMPORTED_MODULE_0___default().registerLanguage('xml', (highlight_js_lib_languages_xml__WEBPACK_IMPORTED_MODULE_2___default()));
highlight_js_lib_core__WEBPACK_IMPORTED_MODULE_0___default().registerLanguage('javascript', (highlight_js_lib_languages_javascript__WEBPACK_IMPORTED_MODULE_1___default()));

highlights.forEach((highlight) => {
  highlight_js_lib_core__WEBPACK_IMPORTED_MODULE_0___default().highlightBlock(highlight);
});

// Make available globally
window.hljs = (highlight_js_lib_core__WEBPACK_IMPORTED_MODULE_0___default());


/***/ }),

/***/ "./src/js/inputmask.js":
/*!*****************************!*\
  !*** ./src/js/inputmask.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var inputmask__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! inputmask */ "./node_modules/inputmask/dist/inputmask.js");
/* harmony import */ var inputmask__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(inputmask__WEBPACK_IMPORTED_MODULE_0__);
//
// inputmask.js
// Theme module
//



const toggles = document.querySelectorAll('[data-inputmask]');

const options = {
  rightAlign: false,
};

inputmask__WEBPACK_IMPORTED_MODULE_0___default()(options).mask(toggles);

// Make available globally
window.Inputmask = (inputmask__WEBPACK_IMPORTED_MODULE_0___default());


/***/ }),

/***/ "./src/js/kanban.js":
/*!**************************!*\
  !*** ./src/js/kanban.js ***!
  \**************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _shopify_draggable__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shopify/draggable */ "./node_modules/@shopify/draggable/lib/draggable.bundle.js");
/* harmony import */ var _shopify_draggable__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_shopify_draggable__WEBPACK_IMPORTED_MODULE_0__);
//
// kanban.js
// Dashkit module
//



const categories = document.querySelectorAll('.kanban-category');
const links = document.querySelectorAll('.kanban-add-link');
const forms = document.querySelectorAll('.kanban-add-form');

function toggleItems(el) {
  const parent = el.closest('.kanban-add');
  const card = parent.querySelector('.card');
  const link = parent.querySelector('.kanban-add-link');
  const form = parent.querySelector('.kanban-add-form');

  link.classList.toggle('d-none');
  form.classList.toggle('d-none');

  if (card && card.classList.contains('card-sm')) {
    if (card.classList.contains('card-flush')) {
      card.classList.remove('card-flush');
    } else {
      card.classList.add('card-flush');
    }
  }
}

if (categories) {
  new _shopify_draggable__WEBPACK_IMPORTED_MODULE_0__.Sortable(categories, {
    draggable: '.kanban-item',
    mirror: {
      constrainDimensions: true,
    },
  });
}

links.forEach((link) => {
  link.addEventListener('click', () => {
    toggleItems(link);
  });
});

forms.forEach((form) => {
  form.addEventListener('reset', function () {
    toggleItems(form);
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
  });
});

// Make available globally
window.Sortable = _shopify_draggable__WEBPACK_IMPORTED_MODULE_0__.Sortable;


/***/ }),

/***/ "./src/js/list.js":
/*!************************!*\
  !*** ./src/js/list.js ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var list_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! list.js */ "./node_modules/list.js/src/index.js");
/* harmony import */ var list_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(list_js__WEBPACK_IMPORTED_MODULE_0__);
//
// list.js
// Theme module
//



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (function () {
  const lists = document.querySelectorAll('[data-list]');
  const sorts = document.querySelectorAll('[data-sort]');

  function init(list) {
    const listAlert = list.querySelector('.list-alert');
    const listAlertCount = list.querySelector('.list-alert-count');
    const listAlertClose = list.querySelector('.list-alert .btn-close');
    const listCheckboxes = list.querySelectorAll('.list-checkbox');
    const listCheckboxAll = list.querySelector('.list-checkbox-all');
    const listPagination = list.querySelectorAll('.list-pagination');
    const listPaginationPrev = list.querySelector('.list-pagination-prev');
    const listPaginationNext = list.querySelector('.list-pagination-next');
    const listOptions = list.dataset.list && JSON.parse(list.dataset.list);

    const defaultOptions = {
      listClass: 'list',
      searchClass: 'list-search',
      sortClass: 'list-sort',
    };

    // Merge options
    const options = Object.assign(defaultOptions, listOptions);

    // Init
    const listObj = new (list_js__WEBPACK_IMPORTED_MODULE_0___default())(list, options);

    // Pagination
    if (listPagination) {
      [].forEach.call(listPagination, function (pagination) {
        pagination.addEventListener('click', function (e) {
          e.preventDefault();
        });
      });
    }

    // Pagination (next)
    if (listPaginationNext) {
      listPaginationNext.addEventListener('click', function (e) {
        e.preventDefault();

        const nextItem = parseInt(listObj.i) + parseInt(listObj.page);

        if (nextItem <= listObj.size()) {
          listObj.show(nextItem, listObj.page);
        }
      });
    }

    // Pagination (prev)
    if (listPaginationPrev) {
      listPaginationPrev.addEventListener('click', function (e) {
        e.preventDefault();

        const prevItem = parseInt(listObj.i) - parseInt(listObj.page);

        if (prevItem > 0) {
          listObj.show(prevItem, listObj.page);
        }
      });
    }

    // Checkboxes
    if (listCheckboxes) {
      [].forEach.call(listCheckboxes, function (checkbox) {
        checkbox.addEventListener('change', function () {
          countCheckboxes(listCheckboxes, listAlert, listAlertCount);

          if (listCheckboxAll) {
            listCheckboxAll.checked = false;
          }
        });
      });
    }

    // Checkbox
    if (listCheckboxAll) {
      listCheckboxAll.addEventListener('change', function () {
        [].forEach.call(listCheckboxes, function (checkbox) {
          checkbox.checked = listCheckboxAll.checked;
        });

        countCheckboxes(listCheckboxes, listAlert, listAlertCount);
      });
    }

    // Alert
    if (listAlertClose) {
      listAlertClose.addEventListener('click', function (e) {
        e.preventDefault();

        if (listCheckboxAll) {
          listCheckboxAll.checked = false;
        }

        [].forEach.call(listCheckboxes, function (checkbox) {
          checkbox.checked = false;
        });

        countCheckboxes(listCheckboxes, listAlert, listAlertCount);
      });
    }
  }

  function countCheckboxes(listCheckboxes, listAlert, listAlertCount) {
    const checked = [].slice.call(listCheckboxes).filter(function (checkbox) {
      return checkbox.checked;
    });

    if (listAlert) {
      checked.length ? listAlert.classList.add('show') : listAlert.classList.remove('show');
      listAlertCount.innerHTML = checked.length;
    }
  }

  if (typeof (list_js__WEBPACK_IMPORTED_MODULE_0___default()) !== 'undefined' && lists) {
    [].forEach.call(lists, function (list) {
      init(list);
    });
  }

  if (typeof (list_js__WEBPACK_IMPORTED_MODULE_0___default()) !== 'undefined' && sorts) {
    [].forEach.call(sorts, function (sort) {
      sort.addEventListener('click', function (e) {
        e.preventDefault();
      });
    });
  }
}());

// Make available globally
window.List = (list_js__WEBPACK_IMPORTED_MODULE_0___default());


/***/ }),

/***/ "./src/js/map.js":
/*!***********************!*\
  !*** ./src/js/map.js ***!
  \***********************/
/***/ (() => {

//
// map.js
// Theme module
//

const maps = document.querySelectorAll('[data-map]');
const accessToken = 'pk.eyJ1IjoiZ29vZHRoZW1lcyIsImEiOiJjanU5eHR4N2cybDU5NGVwOHZwNGprb3E0In0.msdw9q16dh8v4azJXUdiXg';

if (typeof mapboxgl !== 'undefined') {
  maps.forEach(map => {
    const elementOptions = map.dataset.map ? JSON.parse(map.dataset.map) : {};

    const defaultOptions = {
      container: map,
      style: 'mapbox://styles/mapbox/light-v9',
      scrollZoom: false,
      interactive: false
    }

    const options = {
      ...elementOptions,
      ...defaultOptions
    };

    // Get access token
    mapboxgl.accessToken = accessToken;

    // Init map
    new mapboxgl.Map(options);
  })
}


/***/ }),

/***/ "./src/js/navbar-collapse.js":
/*!***********************************!*\
  !*** ./src/js/navbar-collapse.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
//
// navbar.js
// Theme module
//



const collapses = document.querySelectorAll('.navbar-nav .collapse');

collapses.forEach(collapse => {

  // Init collapses
  const collapseInstance = new bootstrap__WEBPACK_IMPORTED_MODULE_0__.Collapse(collapse, {
    toggle: false
  });

  // Hide sibling collapses on `show.bs.collapse`
  collapse.addEventListener('show.bs.collapse', (e) => {
    e.stopPropagation();

    const closestCollapse = collapse.parentElement.closest('.collapse');
    const siblingCollapses = closestCollapse.querySelectorAll('.collapse');

    siblingCollapses.forEach(siblingCollapse => {
      const siblingCollapseInstance = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Collapse.getInstance(siblingCollapse);

      if (siblingCollapseInstance === collapseInstance) {
        return;
      }

      siblingCollapseInstance.hide(); 
    });
  });

  // Hide nested collapses on `hide.bs.collapse`
  collapse.addEventListener('hide.bs.collapse', (e) => {
    e.stopPropagation();

    const childCollapses = collapse.querySelectorAll('.collapse');

    childCollapses.forEach(childCollapse => {
      const childCollapseInstance = bootstrap__WEBPACK_IMPORTED_MODULE_0__.Collapse.getInstance(childCollapse);

      childCollapseInstance.hide();
    });
  });
});

/***/ }),

/***/ "./src/js/navbar-dropdown.js":
/*!***********************************!*\
  !*** ./src/js/navbar-dropdown.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
//
// navbar-dropdown.js
//



const selectors = '.navbar .dropup, .navbar .dropend, .navbar .dropdown, .navbar .dropstart';
const dropdowns = document.querySelectorAll(selectors);
const NAVBAR_BREAKPOINT = 767;

if (window.innerWidth > NAVBAR_BREAKPOINT) {
  dropdowns.forEach((dropdown) => {
    const toggle = dropdown.querySelector('[data-bs-toggle="dropdown"]');
    const instance = new bootstrap__WEBPACK_IMPORTED_MODULE_0__.Dropdown(toggle);

    dropdown.addEventListener('mouseenter', () => {
      instance.show();
    });

    dropdown.addEventListener('mouseleave', () => {
      instance.hide();
    });
  });
}


/***/ }),

/***/ "./src/js/popover.js":
/*!***************************!*\
  !*** ./src/js/popover.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
//
// popover.js
// Theme module
//



const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');

popovers.forEach(popover => {
  new bootstrap__WEBPACK_IMPORTED_MODULE_0__.Popover(popover);
});

/***/ }),

/***/ "./src/js/quill.js":
/*!*************************!*\
  !*** ./src/js/quill.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var quill__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! quill */ "./node_modules/quill/dist/quill.js");
/* harmony import */ var quill__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(quill__WEBPACK_IMPORTED_MODULE_0__);
//
// quill.js
// Theme module
//



const toggles = document.querySelectorAll('[data-quill]');

toggles.forEach((toggle) => {
  const elementOptions = toggle.dataset.quill ? JSON.parse(toggle.dataset.quill) : {};

  const defaultOptions = {
    modules: {
      toolbar: [
        ['bold', 'italic'],
        ['link', 'blockquote', 'code', 'image'],
        [
          {
            list: 'ordered',
          },
          {
            list: 'bullet',
          },
        ],
      ],
    },
    theme: 'snow',
  };

  const options = {
    ...elementOptions,
    ...defaultOptions,
  };

  new (quill__WEBPACK_IMPORTED_MODULE_0___default())(toggle, options);
});

// Make available globally
window.Quill = (quill__WEBPACK_IMPORTED_MODULE_0___default());


/***/ }),

/***/ "./src/js/theme.js":
/*!*************************!*\
  !*** ./src/js/theme.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
/* harmony import */ var _autosize__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./autosize */ "./src/js/autosize.js");
/* harmony import */ var _bootstrap__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./bootstrap */ "./src/js/bootstrap.js");
/* harmony import */ var _checklist__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./checklist */ "./src/js/checklist.js");
/* harmony import */ var _choices__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./choices */ "./src/js/choices.js");
/* harmony import */ var _dropzone__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./dropzone */ "./src/js/dropzone.js");
/* harmony import */ var _flatpickr__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./flatpickr */ "./src/js/flatpickr.js");
/* harmony import */ var _highlight__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./highlight */ "./src/js/highlight.js");
/* harmony import */ var _inputmask__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./inputmask */ "./src/js/inputmask.js");
/* harmony import */ var _kanban__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./kanban */ "./src/js/kanban.js");
/* harmony import */ var _list__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./list */ "./src/js/list.js");
/* harmony import */ var _map__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./map */ "./src/js/map.js");
/* harmony import */ var _map__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(_map__WEBPACK_IMPORTED_MODULE_11__);
/* harmony import */ var _navbar_collapse__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./navbar-collapse */ "./src/js/navbar-collapse.js");
/* harmony import */ var _navbar_dropdown__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./navbar-dropdown */ "./src/js/navbar-dropdown.js");
/* harmony import */ var _popover__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./popover */ "./src/js/popover.js");
/* harmony import */ var _tooltip__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ./tooltip */ "./src/js/tooltip.js");
/* harmony import */ var _quill__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! ./quill */ "./src/js/quill.js");
/* harmony import */ var _wizard__WEBPACK_IMPORTED_MODULE_17__ = __webpack_require__(/*! ./wizard */ "./src/js/wizard.js");
/* harmony import */ var _user__WEBPACK_IMPORTED_MODULE_18__ = __webpack_require__(/*! ./user */ "./src/js/user.js");
// Vendor


// Theme

















// User



/***/ }),

/***/ "./src/js/tooltip.js":
/*!***************************!*\
  !*** ./src/js/tooltip.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
//
// popover.js
// Theme module
//



const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');

tooltips.forEach(tooltip => {
  new bootstrap__WEBPACK_IMPORTED_MODULE_0__.Tooltip(tooltip);
});

/***/ }),

/***/ "./src/js/user.js":
/*!************************!*\
  !*** ./src/js/user.js ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _chart__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./chart */ "./src/js/chart.js");
/* harmony import */ var chart_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! chart.js */ "./node_modules/chart.js/dist/chart.js");
//
// user.js
// Use this to write your custom JS
//





chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart.register(
  chart_js__WEBPACK_IMPORTED_MODULE_1__.ArcElement,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.BarController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.BarElement,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.BubbleController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.CategoryScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Decimation,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.DoughnutController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Filler,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Legend,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.LinearScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.LineController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.LineElement,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.LogarithmicScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.PieController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.PointElement,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.PolarAreaController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.RadarController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.RadialLinearScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.ScatterController,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.TimeScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.TimeSeriesScale,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Title,
  chart_js__WEBPACK_IMPORTED_MODULE_1__.Tooltip
);

// Audience chart

const audienceChart = document.getElementById('audienceChart');

if (audienceChart) {
  new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(audienceChart, {
    type: 'line',
    options: {
      scales: {
        yAxisOne: {
          display: 'auto',
          grid: {
            color: '#283E59',
          },
          ticks: {
            callback: function (value) {
              return value + 'k';
            },
          },
        },
        yAxisTwo: {
          display: 'auto',
          grid: {
            color: '#283E59',
          },
          ticks: {
            callback: function (value) {
              return value + '%';
            },
          },
        },
      },
    },
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [
        {
          label: 'Customers',
          data: [0, 10, 5, 15, 10, 20, 15, 25, 20, 30, 25, 40],
          yAxisID: 'yAxisOne',
        },
        {
          label: 'Sessions',
          data: [50, 75, 35, 25, 55, 87, 67, 53, 25, 80, 87, 45],
          yAxisID: 'yAxisOne',
          hidden: true,
        },
        {
          label: 'Conversion',
          data: [40, 57, 25, 50, 57, 32, 46, 28, 59, 34, 52, 48],
          yAxisID: 'yAxisTwo',
          hidden: true,
        },
      ],
    },
  });
}

// Convertions chart

const conversionsChart = document.getElementById('conversionsChart');

if (conversionsChart) {
  new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(conversionsChart, {
    type: 'bar',
    options: {
      scales: {
        y: {
          ticks: {
            callback: function (val) {
              return val + '%';
            },
          },
        },
      },
    },
    data: {
      labels: [
        'Oct 1',
        'Oct 2',
        'Oct 3',
        'Oct 4',
        'Oct 5',
        'Oct 6',
        'Oct 7',
        'Oct 8',
        'Oct 9',
        'Oct 10',
        'Oct 11',
        'Oct 12',
      ],
      datasets: [
        {
          label: '2020',
          data: [25, 20, 30, 22, 17, 10, 18, 26, 28, 26, 20, 32],
        },
        {
          label: '2019',
          data: [15, 10, 20, 12, 7, 0, 8, 16, 18, 16, 10, 22],
          backgroundColor: '#d2ddec',
          hidden: true,
        },
      ],
    },
  });
}

// Traffic chart

const trafficChart = document.getElementById('trafficChart');

if (trafficChart) {
  new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(trafficChart, {
    type: 'doughnut',
    options: {
      plugins: {
        tooltip: {
          callbacks: {
            afterLabel: function () {
              return '%';
            },
          },
        },
      },
    },
    data: {
      labels: ['Direct', 'Organic', 'Referral'],
      datasets: [
        {
          data: [60, 25, 15],
          backgroundColor: ['#2C7BE5', '#A6C5F7', '#D2DDEC'],
        },
        {
          data: [15, 45, 20],
          backgroundColor: ['#2C7BE5', '#A6C5F7', '#D2DDEC'],
          hidden: true,
        },
      ],
    },
  });
}

// Traffic chart (alt)

const trafficChartAlt = document.getElementById('trafficChartAlt');

if (trafficChartAlt) {
  new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(trafficChartAlt, {
    type: 'doughnut',
    options: {
      plugins: {
        tooltip: {
          callbacks: {
            afterLabel: function () {
              return '%';
            },
          },
        },
      },
    },
    data: {
      labels: ['Direct', 'Organic', 'Referral'],
      datasets: [
        {
          data: [60, 25, 15],
          backgroundColor: ['#2C7BE5', '#A6C5F7', '#D2DDEC'],
        },
        {
          data: [15, 45, 20],
          backgroundColor: ['#2C7BE5', '#A6C5F7', '#D2DDEC'],
          hidden: true,
        },
      ],
    },
  });
}

// Sales chart

const salesChart = document.getElementById('salesChart');

if (salesChart) {
  new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(salesChart, {
    type: 'line',
    options: {
      scales: {
        y: {
          ticks: {
            callback: function (value) {
              return '$' + value + 'k';
            },
          },
        },
      },
    },
    data: {
      labels: ['Oct 1', 'Oct 3', 'Oct 6', 'Oct 9', 'Oct 12', 'Oct 5', 'Oct 18', 'Oct 21', 'Oct 24', 'Oct 27', 'Oct 30'],
      datasets: [
        {
          label: 'All',
          data: [0, 10, 5, 15, 10, 20, 15, 25, 20, 30, 25],
        },
        {
          label: 'Direct',
          data: [7, 40, 12, 27, 34, 17, 19, 30, 28, 32, 24],
          hidden: true,
        },
        {
          label: 'Organic',
          data: [2, 12, 35, 25, 36, 25, 34, 16, 4, 14, 15],
          hidden: true,
        },
      ],
    },
  });
}

// Orders chart

const ordersChart = document.getElementById('ordersChart');

if (ordersChart) {
  new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(ordersChart, {
    type: 'bar',
    options: {
      scales: {
        y: {
          ticks: {
            callback: function (value) {
              return '$' + value + 'k';
            },
          },
        },
      },
    },
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [
        {
          label: 'Sales',
          data: [25, 20, 30, 22, 17, 10, 18, 26, 28, 26, 20, 32],
        },
        {
          label: 'Affiliate',
          data: [15, 10, 20, 12, 7, 0, 8, 16, 18, 16, 10, 22],
          backgroundColor: '#d2ddec',
          hidden: true,
        },
      ],
    },
  });
}

// Earnings chart

const earningsChart = document.getElementById('earningsChart');

if (earningsChart) {
  new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(earningsChart, {
    type: 'bar',
    options: {
      scales: {
        yAxisOne: {
          display: 'auto',
          ticks: {
            callback: function (value) {
              return '$' + value + 'k';
            },
          },
        },
        yAxisTwo: {
          display: 'auto',
          ticks: {
            callback: function (value) {
              return value + 'k';
            },
          },
        },
        yAxisThree: {
          display: 'auto',
          ticks: {
            callback: function (value) {
              return value + '%';
            },
          },
        },
      },
    },
    data: {
      labels: [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec',
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
      ],
      datasets: [
        {
          label: 'Earnings',
          data: [18, 10, 5, 15, 10, 20, 15, 25, 20, 26, 25, 29, 18, 10, 5, 15, 10, 20],
          yAxisID: 'yAxisOne',
        },
        {
          label: 'Sessions',
          data: [50, 75, 35, 25, 55, 87, 67, 53, 25, 80, 87, 45, 50, 75, 35, 25, 55, 19],
          yAxisID: 'yAxisTwo',
          hidden: true,
        },
        {
          label: 'Bounce',
          data: [40, 57, 25, 50, 57, 32, 46, 28, 59, 34, 52, 48, 40, 57, 25, 50, 57, 29],
          yAxisID: 'yAxisThree',
          hidden: true,
        },
      ],
    },
  });
}

// Weekly hours chart

const weeklyHoursChart = document.getElementById('weeklyHoursChart');

if (weeklyHoursChart) {
  new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(weeklyHoursChart, {
    type: 'bar',
    options: {
      scales: {
        y: {
          ticks: {
            callback: function (value) {
              return value + 'hrs';
            },
          },
        },
      },
    },
    data: {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      datasets: [
        {
          data: [21, 12, 28, 15, 5, 12, 17, 2],
        },
      ],
    },
  });
}

// Overview chart

const overviewChart = document.getElementById('overviewChart');

if (overviewChart) {
  new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(overviewChart, {
    type: 'line',
    options: {
      scales: {
        yAxisOne: {
          display: 'auto',
          ticks: {
            callback: function (value) {
              return '$' + value + 'k';
            },
          },
        },
        yAxisTwo: {
          display: 'auto',
          ticks: {
            callback: function (value) {
              return value + 'hrs';
            },
          },
        },
      },
    },
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [
        {
          label: 'Earned',
          data: [0, 10, 5, 15, 10, 20, 15, 25, 20, 30, 25, 40],
          yAxisID: 'yAxisOne',
        },
        {
          label: 'Hours Worked',
          data: [7, 35, 12, 27, 34, 17, 19, 30, 28, 32, 24, 39],
          yAxisID: 'yAxisTwo',
          hidden: true,
        },
      ],
    },
  });
}

// Sparkline chart

const sparklineChart = document.getElementById('sparklineChart');

if (sparklineChart) {
  new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(sparklineChart, {
    type: 'line',
    options: {
      scales: {
        y: {
          display: false,
        },
        x: {
          display: false,
        },
      },
      elements: {
        line: {
          borderWidth: 2,
        },
        point: {
          hoverRadius: 0,
        },
      },
      plugins: {
        tooltip: {
          external: () => false,
        },
      },
    },
    data: {
      labels: new Array(12).fill('Label'),
      datasets: [
        {
          data: [0, 15, 10, 25, 30, 15, 40, 50, 80, 60, 55, 65],
        },
      ],
    },
  });
}

// Sparkline chart (gray)

const sparklineCharts = document.querySelectorAll(
  '#sparklineChartSocialOne, #sparklineChartSocialTwo, #sparklineChartSocialThree, #sparklineChartSocialFour'
);

if (sparklineCharts) {
  [].forEach.call(sparklineCharts, function (chart) {
    new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(chart, {
      type: 'line',
      options: {
        scales: {
          y: {
            display: false,
          },
          x: {
            display: false,
          },
        },
        elements: {
          line: {
            borderWidth: 2,
            borderColor: '#D2DDEC',
          },
          point: {
            hoverRadius: 0,
          },
        },
        plugins: {
          tooltip: {
            external: () => false,
          },
        },
      },
      data: {
        labels: new Array(12).fill('Label'),
        datasets: [
          {
            data: [0, 15, 10, 25, 30, 15, 40, 50, 80, 60, 55, 65],
          },
        ],
      },
    });
  });
}

// Feed chart

const feedChart = document.getElementById('feedChart');

if (feedChart) {
  new chart_js__WEBPACK_IMPORTED_MODULE_1__.Chart(feedChart, {
    type: 'bar',
    options: {
      scales: {
        y: {
          ticks: {
            callback: function (value) {
              return '$' + value + 'k';
            },
          },
        },
      },
    },
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [
        {
          label: 'Sales',
          data: [25, 20, 30, 22, 17, 10, 18, 26, 28, 26, 20, 32],
        },
        {
          label: 'Affiliate',
          data: [15, 10, 20, 12, 7, 0, 8, 16, 18, 16, 10, 22],
          backgroundColor: '#d2ddec',
          hidden: true,
        },
      ],
    },
  });
}


/***/ }),

/***/ "./src/js/wizard.js":
/*!**************************!*\
  !*** ./src/js/wizard.js ***!
  \**************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var bootstrap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");
//
// wizard.js
// Dashkit module
//



const toggles = document.querySelectorAll('[data-toggle="wizard"]');

toggles.forEach((toggle) => {
  const tab = new bootstrap__WEBPACK_IMPORTED_MODULE_0__.Tab(toggle);
  const panes = toggle.closest('.tab-content').querySelectorAll('.tab-pane');

  toggle.addEventListener('click', function (e) {
    e.preventDefault();

    // Hide all tabs
    panes.forEach((tab) => {
      tab.classList.remove('active');
    });

    // Toggle new tab
    tab.show();

    // Remove active state
    toggle.classList.remove('active');
  });
});


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"theme": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkdashkit"] = self["webpackChunkdashkit"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["vendor"], () => (__webpack_require__("./src/js/theme.js")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["vendor"], () => (__webpack_require__("./src/scss/theme.scss")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=theme.bundle.js.map