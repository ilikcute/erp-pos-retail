import { ref, onMounted, onUnmounted, watch } from "vue";
import Chart from "chart.js/auto";

export function useChart(canvasRef, options = {}) {
    const chart = ref(null);

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: "top",
            },
            title: {
                display: false,
            },
        },
    };

    const createChart = (config) => {
        if (!canvasRef.value) return;

        if (chart.value) {
            chart.value.destroy();
        }

        chart.value = new Chart(canvasRef.value, {
            ...config,
            options: {
                ...defaultOptions,
                ...config.options,
            },
        });
    };

    const updateChart = (data, options = {}) => {
        if (!chart.value) return;

        chart.value.data = data;
        if (Object.keys(options).length > 0) {
            Object.assign(chart.value.options, options);
        }
        chart.value.update();
    };

    const destroyChart = () => {
        if (chart.value) {
            chart.value.destroy();
            chart.value = null;
        }
    };

    onUnmounted(() => {
        destroyChart();
    });

    return {
        chart,
        createChart,
        updateChart,
        destroyChart,
    };
}

// Chart preset templates
export const chartPresets = {
    lineChart: (labels, datasets) => ({
        type: "line",
        data: { labels, datasets },
        options: {
            responsive: true,
            plugins: { legend: { position: "top" } },
            scales: {
                y: { beginAtZero: true },
            },
        },
    }),

    barChart: (labels, datasets) => ({
        type: "bar",
        data: { labels, datasets },
        options: {
            responsive: true,
            plugins: { legend: { position: "top" } },
            scales: {
                y: { beginAtZero: true },
            },
        },
    }),

    pieChart: (labels, dataset) => ({
        type: "pie",
        data: {
            labels,
            datasets: [dataset],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: "right" } },
        },
    }),

    doughnutChart: (labels, dataset) => ({
        type: "doughnut",
        data: {
            labels,
            datasets: [dataset],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: "bottom" } },
        },
    }),

    areaChart: (labels, datasets) => ({
        type: "line",
        data: { labels, datasets },
        options: {
            responsive: true,
            fill: true,
            plugins: { legend: { position: "top" } },
            scales: {
                y: { beginAtZero: true, stacked: false },
            },
        },
    }),

    mixedChart: (labels, datasets) => ({
        type: "bar",
        data: { labels, datasets },
        options: {
            responsive: true,
            plugins: { legend: { position: "top" } },
            scales: {
                y: { beginAtZero: true },
            },
        },
    }),
};

// Default colors for charts
export const chartColors = {
    primary: "rgb(59, 130, 246)", // blue-500
    success: "rgb(34, 197, 94)", // green-500
    danger: "rgb(239, 68, 68)", // red-500
    warning: "rgb(245, 158, 11)", // amber-500
    info: "rgb(59, 130, 246)", // blue-500
    secondary: "rgb(107, 114, 128)", // gray-500
    light: "rgb(243, 244, 246)", // gray-100
    dark: "rgb(17, 24, 39)", // gray-900
};

export const getChartDataset = (
    label,
    data,
    color = chartColors.primary,
    options = {},
) => ({
    label,
    data,
    backgroundColor: Array.isArray(data)
        ? `rgba(${hexToRgb(color)}, 0.1)`
        : color,
    borderColor: color,
    borderWidth: 2,
    tension: 0.4,
    fill: options.fill || false,
    ...options,
});

function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result
        ? `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}`
        : "59, 130, 246";
}
