// IIFE to provide scoping for js
(function() {
    const baseUrl = 'http://localhost';
    const insightsPath = '/insights';
    const articlePath = '/insights/article';

    // global state for charts, simple application
    let charts = [];

    // once page loads, attempt to mount event listeners
    document.addEventListener('DOMContentLoaded', setup);

    // Mounts event listeners, sets up bar charts
    function setup() {
        document.getElementById('updateInsights').addEventListener('click', updateInsights);
        mountTableListener();
        setupBarCharts();
    }

    // Event handler for "Update Insights" button.
    // Updates table and chart with new relevant data if available.
    async function updateInsights() {
        const results = await get(insightsPath);
        const { chartLeft, chartRight, tableHtml } = await results.json();

        updateInsightsButton('Update Insights');
        resetSubheader();
        updateTableHtml(tableHtml);
        updateBarCharts(chartLeft, chartRight);
    }

    // Mounts event listener to table
    function mountTableListener() {
        const table = document.querySelector('table.articles');
        if (table) {
            table.addEventListener('click', tableListener);
        }
    }

    // Removes event listener from table to free memory (possibly unneeded, but good practice to remove listeners)
    function unmountTableListener() {
        const table = document.querySelector('table.articles');
        if (table) {
            table.removeEventListener('click', tableListener);
        }
    }

    /**
     * Event handler for Article Insights "Show" button
     * Attempts to retrieve chart data for the selected article and update relevant UI
     *
     * @param target {EventTarget} the clicked element
     */
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

    /**
     * Updates "Update Insights" button text
     * @param text {String} text to update
     */
    function updateInsightsButton(text) {
        document.getElementById('updateInsights').innerText = text;
    }

    /**
     * Updates subheader to reflect accurate title and link
     * @param headline {String} headline text
     * @param url {String} url href
     */
    function updateSubheader(headline, url) {
        const subheaderLink = document.querySelector('h2 a');
        subheaderLink.setAttribute('href', url);
        subheaderLink.innerText = headline;
    }

    /**
     * Updates subheader back to default
     */
    function resetSubheader() {
        updateSubheader('Associated Press News: www.apnews.com', 'https://www.apnews.com');
    }

    /**
     * Retrieves relevant data for an Article, if available. Updates charts to reflect article insights.
     */
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

    /**
     * Sets innerHTML of article headlines table
     * @param tableHtml {String}
     */
    function updateTableHtml(tableHtml) {
        unmountTableListener();
        document.querySelector('.bottom').innerHTML = tableHtml;
        mountTableListener();
    }

    /**
     * Rebuilds charts from data attribute on the canvases.
     */
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

    /**
     * Creates a new chart.js Chart, stores in charts state
     * @param mountElement {HTMLElement}
     * @param data {Object} to initialize chart
     */
    function buildBarChart(mountElement, data) {
        charts.push(new Chart(mountElement, data));
    }

    /**
     * Rebuilds chart html element
     * @param toElement {HTMLElement} to attach wrapper and canvas to
     */
    function mountChartScaffold(toElement) {
        const canvas = document.createElement('canvas');
        const canvasWrapper = document.createElement('div');

        canvasWrapper.classList.add('canvasWrapper');
        canvasWrapper.appendChild(canvas);
        toElement.appendChild(canvasWrapper);
        toElement.classList.remove('empty');

        return toElement.querySelector('canvas');
    }

    /**
     * Accepts new chart data and updates it accordingly
     * @param chartLeftData {Object|Boolean} if false, will not update
     * @param chartRightData {Object|Boolean} if false, will not update
     */
    function updateBarCharts(chartLeftData, chartRightData) {
        if (chartLeftData) {
            let leftChart = document.querySelector('.left .canvasWrapper canvas');
            if (!leftChart) {
                leftChart = mountChartScaffold(document.querySelector('.left'), chartLeftData);
            }
            leftChart.dataset.config = JSON.stringify(chartLeftData);
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

    /**
     * Destroys chart within selector
     * @param selector {String} CSS Selector
     */
    function destroyChart(selector) {
        const element = document.querySelector(selector);
        element.classList.add('empty');
        element.innerHTML = '';
    }

    /**
     * Displays empty message if no insights within keyword occurrence threshold.
     * @param message {String}
     */
    function emptyResultForArticle(message) {
        destroyChart('.left');
        destroyChart('.right');
        const rightContainer = document.querySelector('.right');
        rightContainer.innerText = message;
        rightContainer.classList.remove('empty');
    }

    /**
     * Makes request to PHP backend
     * @param path {String} request path
     * @param params {Object} request params, (unused)
     *
     * @return {Promise<Response>}
     */
    async function get(path, params = {}) {
        const queryParams = new URLSearchParams(params).toString();

        return await fetch(`${baseUrl}${path}${queryParams ? '?' + queryParams : ''}`);
    }
})();