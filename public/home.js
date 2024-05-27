// IIFE to provide scoping for js
(function() {
    const baseUrl = 'http://localhost';
    const insightsPath = '/insights';
    const articlePath = '/insights/article';

    document.addEventListener('DOMContentLoaded', setup());

    function setup() {
        document.getElementById('updateInsights').addEventListener('click', updateInsights);
        mountTableListener();
        setupBarCharts();
    }

    function updateInsights() {
        unmountTableListener();
        const results = get(insightsPath);
        // modify table
        mountTableListener();
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

    function setupBarCharts() {
        document.querySelectorAll('.canvasWrapper').forEach(canvasWrapper => {
            const canvas = canvasWrapper.querySelector('canvas');
            if (canvas) {
                console.log(canvas.dataset);
                const chartData = JSON.parse(canvas.dataset.config);
                buildBarChart(canvas, chartData);
            }
        });
    }

    function buildBarChart(mountElement, data) {
        new Chart(mountElement, data)
    }

    async function get(path, params = {}) {
        const queryParams = new URLSearchParams(params).toString();

        return await fetch(`${baseUrl}${path}${queryParams ? '?' + queryParams : ''}`);
    }
})();