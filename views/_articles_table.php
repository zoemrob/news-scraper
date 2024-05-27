<table class="articles">
    <thead>
        <tr>
            <th>Article Headline</th>
            <th>Canonical URL</th>
            <th>Description (if present)</th>
            <th>Article Insights</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach(APNewsScraper::articleData() as $articleDatum): ?>
            <tr data-reference-id="<?= $articleDatum[APNewsScraper::ARTICLE_REFERENCE_ID] ?>">
                <td><?= $articleDatum[APNewsScraper::ARTICLE_HEADER] ?></td>
                <td><a href="<?= $articleDatum[APNewsScraper::ARTICLE_URL] ?>"><?= $articleDatum[APNewsScraper::ARTICLE_URL] ?></a></td>
                <td><?= $articleDatum[APNewsScraper::ARTICLE_DESCRIPTION] ?></td>
                <td><button class="articleButton">Show</button></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>