// IIFE to provide scoping for js
(function() {
    const baseUrl = 'http://localhost';
    const insightsPath = '/insights';

    document.addEventListener('DOMContentLoaded', addListeners);

    function addListeners() {
        document.getElementById('updateInsights').addEventListener('click', updateInsights);
    }

    function updateInsights() {
        get(insightsPath);
    }

    async function get(path, params = {}) {
        const queryParams = new URLSearchParams(params).toString();

        return await fetch(`${baseUrl}${path}${queryParams ? '?' + queryParams : ''}`);
    }
})();