<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ホーム画面</title>
    <link rel="stylesheet" href="board_style.css"> <!-- CSSファイルを読み込む -->
</head>
<body>
    <header>
        <h1>CampusCodeLink</h1>
    </header>
    <nav>
        <div class="logo">CampusCodeLink</div>
        <div class="nav-links">
            <a href="#">ホーム</a>
            <a href="#">後で見る</a>
            <a href="#">通知</a>
            <a href="#">参加プロジェクト</a>
            <a href="#">アカウント</a>
        </div>
    </nav>

    <section class="container">
        <!-- 募集中プロジェクト -->
        <div class="project-section">
            <h2>募集中プロジェクト</h2>
            <!-- 外部のfooter.phpファイルを組み込む -->
             <?php require 'board.php'; ?>
        </div>

        <!-- 参加プロジェクト -->
        <div class="sidebar">
            <h2>参加プロジェクト</h2>
            <div class="project-card">
                <h3>プロジェクト名</h3>
                <p>進捗状況<br>貢献度<br>使用言語</p>
            </div>
            <div class="project-card">
                <h3>プロジェクト名</h3>
                <p>進捗状況<br>貢献度<br>使用言語</p>
            </div>
            <div class="project-card">
                <h3>プロジェクト名</h3>
                <p>進捗状況<br>貢献度<br>使用言語</p>
            </div>
        </div>

        <!-- 通知 -->
        <div class="notifications">
            <h2>最新のお知らせ</h2>
            <p>運営からのメッセージ、通知</p>
        </div>
    </section>

    <!-- プラスボタン -->
    <div class="add-button">+</div>
</body>
</html>
