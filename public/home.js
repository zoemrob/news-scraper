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

        updateInsightsButton('Update Insights');
        resetSubheader();
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
        const headline = tr.querySelector('td:first-child').innerText;
        const url = tr.querySelector('td:nth-child(2)').innerText;
        updateSubheader(headline, url);
        updateInsightsButton('Back to Insights');
        updateArticleInsights(referenceId);
    }

    function updateInsightsButton(text) {
        document.getElementById('updateInsights').innerText = text;
    }

    function updateSubheader(headline, url) {
        const subheaderLink = document.querySelector('h2 a');
        subheaderLink.setAttribute('href', url);
        subheaderLink.innerText = headline;
    }

    function resetSubheader() {
        updateSubheader('Associated Press News: www.apnews.com', 'https://www.apnews.com');
    }

    async function updateArticleInsights(referenceId) {
        const response = await get(`${articlePath}/${referenceId}`);
        const { chartRight } = await response.json();

        if (typeof chartRight === "string") {
            emptyResultForArticle(chartRight);
            return;
        }

        destroyChart('.left');
        updateBarCharts(false, chartRight);
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
        toElement.classList.remove('empty');

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

    function destroyChart(selector) {
        const element = document.querySelector(selector);
        element.classList.add('empty');
        element.innerHTML = '';
    }

    function emptyResultForArticle(message) {
        destroyChart('.left');
        destroyChart('.right');
        const rightContainer = document.querySelector('.right');
        rightContainer.innerText = message;
        rightContainer.classList.remove('empty');
    }

    async function get(path, params = {}) {
        const queryParams = new URLSearchParams(params).toString();

        return await fetch(`${baseUrl}${path}${queryParams ? '?' + queryParams : ''}`);
    }
})();