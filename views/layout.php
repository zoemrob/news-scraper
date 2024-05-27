<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Associated Press News Insights</title>
        <link rel="stylesheet" href="/public/home.css">
        <script src="/public/home.js"></script>
    </head>
    <body>
        <div>
            <header>
                <h1>Associated Press News Insights</h1>
            </header>
        </div>
        <div class="layout">
            <div class="subheader">
                <button id="updateInsights" type="button">Update Insights</button>
            </div>
            <div class="content">
                <div class="left"><?= $leftContent ?? '' ?></div>
                <div class="right"><?= $rightContent ?? '' ?></div>
            </div>
        </div>
    </body>
</html>