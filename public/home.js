// IIFE to provide scoping for js
(function() {
    const baseUrl = 'http://localhost';
    const insightsPath = '/insights';
    const articlePath = '/insights/article';

    document.addEventListener('DOMContentLoaded', addListeners);

    function addListeners() {
        document.getElementById('updateInsights').addEventListener('click', updateInsights);
        mountTableListener();
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
        console.log(`clicked tr ${referenceId}`);
        // need to modify the router to accept id, or use query param as workaround
//        get(articlePath)
    }

    async function get(path, params = {}) {
        const queryParams = new URLSearchParams(params).toString();

        return await fetch(`${baseUrl}${path}${queryParams ? '?' + queryParams : ''}`);
    }
})();