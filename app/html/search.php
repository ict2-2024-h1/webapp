<?php
/**
 * セッション開始
 * セッションの保存期間を1800秒に指定　※任意の秒数へ変更可能
 */
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.gc_divisor', 1);
session_start();

/**
 * DB接続情報
 */
const DB_HOST = 'mysql:dbname=board;host=127.0.0.1;charset=utf8';
const DB_USER = 'root';
const DB_PASSWORD = '';

try {
    // データベースに接続
    $pdo = new PDO(DB_HOST, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'DB接続エラー: ' . $e->getMessage();
    exit;
}

// 検索結果を格納する配列
$post_list = [];

// 検索ボタンが押されたときの処理
if (isset($_POST['post_btn'])) {
    $search_keyword = '%' . $_POST['post_title'] . '%';  // ワイルドカードを使って部分一致検索

    // 検索クエリ
    $sql = "SELECT * FROM board_info WHERE comment LIKE :keyword";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':keyword', $search_keyword, PDO::PARAM_STR);

    // クエリ実行
    $stmt->execute();
    $post_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>掲示板アプリ</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <h1>コメント検索</h1>

    <!-- 検索フォーム -->
    <section class="post-form">
        <form action="#" method="post">
            <div class="post-form__flex">
                <div>
                    <label>
                        <p>検索キーワード</p>
                        <input type="text" name="post_title" value="<?php if (isset($_POST['post_title'])) echo htmlspecialchars($_POST['post_title'], ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>
            </div>
            <button class="btn--mg-c" type="submit" name="post_btn" value="post_btn">検索</button>
        </form>
    </section>
    <hr>

    <!-- 検索結果表示 -->
    <section class="post-list">
        <?php if (count($post_list) === 0) : ?>
            <p class="no-post-msg">一致するコメントは見つかりませんでした。</p>
        <?php else : ?>
            <ul>
                <?php foreach ($post_list as $post_item) : ?>
                <li>
                    <!-- 投稿ID -->
                    <span>ID：<?php echo htmlspecialchars($post_item['id'], ENT_QUOTES, 'UTF-8'); ?>　</span>
                    <!-- 投稿タイトル -->
                    <span><?php echo htmlspecialchars($post_item['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <!-- 投稿者ID -->
                    <span>／投稿者：<?php echo htmlspecialchars($post_item['contributor_id'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <!-- 投稿内容 -->
                    <p class="p-pre"><?php echo htmlspecialchars($post_item['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <!-- 投稿日時 -->
                    <span class="post-datetime">投稿日時：<?php echo htmlspecialchars($post_item['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</body>
</html>