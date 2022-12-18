//
// chart.js
// Theme module
//

import {
  ArcElement,
  BarController,
  BarElement,
  BubbleController,
  CategoryScale,
  Chart,
  Decimation,
  DoughnutController,
  Filler,
  Legend,
  LineController,
  LineElement,
  LinearScale,
  LogarithmicScale,
  PieController,
  PointElement,
  PolarAreaController,
  RadarController,
  RadialLinearScale,
  ScatterController,
  TimeScale,
  TimeSeriesScale,
  Title,
  Tooltip,
} from 'chart.js';

import { getCSSVariableValue } from './helpers';

Chart.register(
  ArcElement,
  BarController,
  BarElement,
  BubbleController,
  CategoryScale,
  Decimation,
  DoughnutController,
  Filler,
  Legend,
  LinearScale,
  LineController,
  LineElement,
  LogarithmicScale,
  PieController,
  PointElement,
  PolarAreaController,
  RadarController,
  RadialLinearScale,
  ScatterController,
  TimeScale,
  TimeSeriesScale,
  Title,
  Tooltip
);

const colors = {
  gray: {
    300: getCSSVariableValue('--bs-chart-gray-300'),
    600: getCSSVariableValue('--bs-chart-gray-600'),
    700: getCSSVariableValue('--bs-chart-gray-700'),
    800: getCSSVariableValue('--bs-chart-gray-800'),
    900: getCSSVariableValue('--bs-chart-gray-900'),
  },
  primary: {
    100: getCSSVariableValue('--bs-chart-primary-100'),
    300: getCSSVariableValue('--bs-chart-primary-300'),
    700: getCSSVariableValue('--bs-chart-primary-700'),
  },
  black: getCSSVariableValue('--bs-dark'),
  white: getCSSVariableValue('--bs-white'),
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
  Chart.defaults.responsive = true;
  Chart.defaults.maintainAspectRatio = false;

  // Default
  Chart.defaults.color = getCSSVariableValue('--bs-chart-default-color');
  Chart.defaults.font.family = fonts.base;
  Chart.defaults.font.size = 13;

  // Layout
  Chart.defaults.layout.padding = 0;

  // Legend
  Chart.defaults.plugins.legend.display = false;

  // Point
  Chart.defaults.elements.point.radius = 0;
  Chart.defaults.elements.point.backgroundColor = colors.primary[700];

  // Line
  Chart.defaults.elements.line.tension = 0.4;
  Chart.defaults.elements.line.borderWidth = 3;
  Chart.defaults.elements.line.borderColor = colors.primary[700];
  Chart.defaults.elements.line.backgroundColor = colors.transparent;
  Chart.defaults.elements.line.borderCapStyle = 'rounded';

  // Bar
  Chart.defaults.elements.bar.backgroundColor = colors.primary[700];
  Chart.defaults.elements.bar.borderWidth = 0;
  Chart.defaults.elements.bar.borderRadius = 10;
  Chart.defaults.elements.bar.borderSkipped = false;
  Chart.defaults.datasets.bar.maxBarThickness = 10;

  // Arc
  Chart.defaults.elements.arc.backgroundColor = colors.primary[700];
  Chart.defaults.elements.arc.borderColor = getCSSVariableValue('--bs-chart-arc-border-color');
  Chart.defaults.elements.arc.borderWidth = 4;
  Chart.defaults.elements.arc.hoverBorderColor = getCSSVariableValue('--bs-chart-arc-hover-border-color');

  // Tooltips
  Chart.defaults.plugins.tooltip.enabled = false;
  Chart.defaults.plugins.tooltip.mode = 'index';
  Chart.defaults.plugins.tooltip.intersect = false;
  Chart.defaults.plugins.tooltip.external = externalTooltipHandler;
  Chart.defaults.plugins.tooltip.callbacks.label = externalTooltipLabelCallback;

  // Doughnut
  Chart.defaults.datasets.doughnut.cutout = '83%';

  // yAxis
  Chart.defaults.scales.linear.border = { ...Chart.defaults.scales.linear.border, ...{ display: false, dash: [2], dashOffset: [2] } };
  Chart.defaults.scales.linear.grid = {
    ...Chart.defaults.scales.linear.grid,
    ...{ color: getCSSVariableValue('--bs-chart-grid-line-color'), drawTicks: false },
  };

  Chart.defaults.scales.linear.ticks.beginAtZero = true;
  Chart.defaults.scales.linear.ticks.padding = 10;
  Chart.defaults.scales.linear.ticks.stepSize = 10;

  // xAxis
  Chart.defaults.scales.category.border = { ...Chart.defaults.scales.category.border, ...{ display: false } };
  Chart.defaults.scales.category.grid = { ...Chart.defaults.scales.category.grid, ...{ display: false, drawTicks: false, drawOnChartArea: false } };
  Chart.defaults.scales.category.ticks.padding = 20;
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
  const chartInstance = Chart.getChart(chart);

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
  const chart = Chart.getChart(legend);
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
window.Chart = Chart;
