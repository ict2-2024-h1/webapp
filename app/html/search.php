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

/**
 * ユーザーID（UID）をセッションから取得
 */
$uid = $_SESSION['uid']; // ユーザーIDをセッションから取得

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

    // 正しいテーブル名を使用した検索クエリ
    $sql = "SELECT * FROM board_info WHERE comment LIKE :keyword";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':keyword', $search_keyword, PDO::PARAM_STR);

    // クエリ実行
    $stmt->execute();
    $post_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 投稿一覧取得処理
 */
try {
    $sql = 'SELECT * FROM board_info ORDER BY id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $post_list = $stmt->fetchAll(PDO::FETCH_ASSOC);  // 投稿データを配列として取得
} catch (PDOException $e) {
    echo '接続失敗: ' . $e->getMessage();
    exit();
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
    <h1>掲示板アプリ</h1>
    <!-- 投稿フォーム -->
    <section class="post-form">
        <form action="#" method="post">
            <div class="post-form__flex">
                <div>
                    <label>
                        <p>タイトル（※最大30文字）</p>
                        <input type="text" name="post_title" value="<?php if (isset($_POST['post_title'])) echo htmlspecialchars($_POST['post_title'], ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>
            </div>
            <button class="btn--mg-c" type="submit" name="post_btn" value="post_btn">検索</button>
        </form>
    </section>
    <hr>
    
    <!-- 投稿一覧 -->
    <section class="post-list">
        <?php if (count($post_list) === 0) : ?>
            <p class="no-post-msg">現在、投稿はありません。</p>
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
                    <!-- 過去に更新されていたら更新日時も表示 -->
                    <?php if ($post_item['created_at'] < $post_item['updated_at']) : ?>
                    <span class="post-datetime post-datetime__updated">更新日時：<?php echo htmlspecialchars($post_item['updated_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                    
                    <!-- 自分の投稿の場合、編集・削除ボタンを表示 -->
                    <?php if ($post_item['contributor_id'] === $uid) : ?>
                    <div class="btn-flex">
                        <form action="update-edit.php" method="post">
                            <button type="submit" name="update_btn">編集</button>
                            <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_item['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        </form>
                        <form action="delete-confirm.php" method="post">
                            <button type="submit" name="delete_btn">削除</button>
                            <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_item['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        </form>
                    </div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</body>
</html>
