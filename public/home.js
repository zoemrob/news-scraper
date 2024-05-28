// IIFE to provide scoping for js
(function() {
    const baseUrl = 'http://localhost';
    const insightsPath = '/insights';
    const articlePath = '/insights/article';

    // global state for charts, simple application
    let charts = [];

    document.addEventListener('DOMContentLoaded', setup());

    function setup() {
        document.getElementById('updateInsights').addEventListener('click', updateInsights);
        mountTableListener();
        setupBarCharts();
    }

    async function updateInsights() {
        const results = await get(insightsPath);
        const { chartLeft, chartRight, tableHtml } = await results.json();
        console.log(chartLeft);
        console.log(chartRight);
        updateTableHtml(tableHtml);
        updateBarCharts(chartLeft, chartRight);
    }

    function mountTableListener() {
        const table = document.querySelector('table.articles');
        if (table) {
            table.addEventListener('click', tableListener);
        }
    }

    function unmountTableListener() {
        const table = document.querySelector('table.articles');
        if (table) {
            table.removeEventListener('click', tableListener);
        }
    }

    function tableListener({target}) {
        if (!target.classList.contains('articleButton')) return;

        const tr = target.closest('tr');
        const referenceId = tr.dataset.referenceId;
        // need to modify the router to accept id, or use query param as workaround
        //        get(articlePath)
    }

    function updateTableHtml(tableHtml) {
        unmountTableListener();
        document.querySelector('.bottom').innerHTML = tableHtml;
        mountTableListener();
    }

    function setupBarCharts() {
        if (charts.length !== 0) {
            charts.forEach(chart => chart.destroy());
        }

        document.querySelectorAll('.canvasWrapper').forEach(canvasWrapper => {
            const canvas = canvasWrapper.querySelector('canvas');
            if (canvas) {
                const chartData = JSON.parse(canvas.dataset.config);
                buildBarChart(canvas, chartData);
            }
        });
    }

    function buildBarChart(mountElement, data) {
        charts.push(new Chart(mountElement, data));
    }

    function mountChartScaffold(toElement, chartData) {
        const canvas = document.createElement('canvas');
        const canvasWrapper = document.createElement('div');

        canvasWrapper.classList.add('canvasWrapper');
        canvasWrapper.appendChild(canvas);
        toElement.appendChild(canvasWrapper);

        return toElement.querySelector('canvas');
    }

    function updateBarCharts(chartLeftData, chartRightData) {
        if (chartLeftData) {
            let leftChart = document.querySelector('.left .canvasWrapper canvas');
            if (!leftChart) {
                leftChart = mountChartScaffold(document.querySelector('.left'), chartLeftData);
            }
            leftChart.dataset.config =JSON.stringify(chartLeftData);
        }

        if (chartRightData) {
            let rightChart = document.querySelector('.right .canvasWrapper canvas');
            if (!rightChart) {
                rightChart = mountChartScaffold(document.querySelector('.right'), chartRightData);
            }
            rightChart.dataset.config = JSON.stringify(chartRightData);
        }

        setupBarCharts();
    }

    async function get(path, params = {}) {
        const queryParams = new URLSearchParams(params).toString();

        return await fetch(`${baseUrl}${path}${queryParams ? '?' + queryParams : ''}`);
    }
})();