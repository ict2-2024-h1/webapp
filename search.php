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
$cont_id=$uid;
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : ''; // ユーザータイプをセッションから取得
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
if (isset($_POST['search_category_btn'])) {
    $search_keyword = '%' . $_POST['post_title'] . '%';  // ワイルドカードを使って部分一致検索

    // 正しいテーブル名を使用した検索クエリ
    $sql = "SELECT * FROM board_info WHERE category LIKE :keyword";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':keyword', $search_keyword, PDO::PARAM_STR);

    // クエリ実行
    $stmt->execute();
    $post_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if (isset($_POST['search_language_btn'])) {
    $search_keyword = '%' . $_POST['post_title'] . '%';  // ワイルドカードを使って部分一致検索

    // 正しいテーブル名を使用した検索クエリ
    $sql = "SELECT * FROM board_info WHERE language LIKE :keyword";
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
    <link rel="stylesheet" href="./board_style.css">
</head>
<body>
    <h1>掲示板アプリ</h1>
    <!-- 投稿フォーム -->
    <section class="post-form">
        <form action="#" method="post">
            <div class="search_category_flex">
                <div>
                    <label>
                        <p>カテゴリ検索</p>
                        <input type="text" name="post__title" value="<?php if (isset($_POST['post_title'])) echo htmlspecialchars($_POST['post_title'], ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>
            </div>
            <button class="btn--mg-c" type="submit" name="search_category_btn" value="search_category_btn">検索</button>
        </form>
        <form action="#" method="post">
            <div class="search_language">
                <div>
                    <label>
                        <p>使用言語検索</p>
                        <input type="text" name="post_title" value="<?php if (isset($_POST['post_title'])) echo htmlspecialchars($_POST['post_title'], ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>
            </div>
            <button class="btn--mg-c" type="submit" name="search_language_btn" value="search_language_btn">検索</button>
        </form>
        <form action="#" method="post">
            <div class="post-form__flex">
                <div>
                    <label>
                        <p>投稿内容検索</p>
                        <input type="text" name="post__title" value="<?php if (isset($_POST['post_title'])) echo htmlspecialchars($_POST['post_title'], ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>
            </div>
            <button class="btn--mg-c" type="submit" name="post_btn" value="post_btn">検索</button>
        </form>
        <form action="board.php" method="post">
            <button type="submit" name="update_btn">掲示板に戻る</button>
            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
        </form>
    </section>
    <hr>
    
    <!-- 投稿一覧 -->
    <section class="post-list">
    <div class="post-list-container">
                <?php if (count($post_list) === 0) : ?>
                    <!-- 投稿が無いときはメッセージを表示する -->
                    <p class="no-post-msg">現在、投稿はありません。</p>
                <?php else : ?>
                    <ul>
                        <!-- 投稿情報の出力 -->
                        <?php foreach ($post_list as $post_item) : ?>
                            <li>
                                    <!-- 投稿ID -->
                                <span>ID：<?php echo $post_item['id']; ?></span>
                                <!-- 投稿タイトル -->
                                <span><?php echo $post_item['title']; ?></span>
                                <!-- カテゴリ -->
                                <span>カテゴリ：<?php echo $post_item['category']; ?></span>
                                <!-- 使用言語 -->
                                <span>使用言語：<?php echo $post_item['language']; ?></span>
                                <!-- 募集人数 -->
                                <span>募集人数：<?php echo $post_item['recruitment_count']; ?>名</span>
                                <!-- 投稿者ID -->
                                <?php
                                // contributor_uidが設定されているかチェック
                                $contributor_id = isset($post_item['contributor_id']) ? $post_item['contributor_id'] : null;

                                // contributor_uidが存在する場合にのみ、usernameを取得
                                
                                $username = '不明なユーザー';
                                if ($contributor_id) {
                                // データベース接続
                                // SQLクエリでcontributor_uidを参照し、accountテーブルからusernameを取得
                                    $stmt = $pdo->prepare("SELECT username FROM account WHERE uid = :contributor_id");
                                    $stmt->bindParam(':contributor_id', $contributor_id);
                                    $stmt->execute();
    
                                // 結果を取得し、存在する場合は$usernameにセット
                                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                    
                                    if ($result) {
                                        $username = $result['username'];
                                    }
                                }
                                ?>
                                <span>／投稿者：</span>
                                <form action="companyprofile.php" method="post">
                                    <button type="submit" name="studentprof_btn" value="studentprof_btn"><?php echo $username; ?></button>
                                    <input type="hidden" name="user_id" value="<?php echo $post_item['contributor_id']; ?>">
                                </form>
                                
                                <!-- 投稿内容 -->
                                <p class="p-pre"><?php echo $post_item['comment']; ?></p>
                                <!-- 投稿日時 -->
                                <span class="post-datetime">投稿日時：<?php echo $post_item['created_at']; ?></span>
                                <!-- 更新日時 -->
                                <?php if ($post_item['created_at'] < $post_item['updated_at']) : ?>
                                    <span class="post-datetime post-datetime__updated">更新日時：<?php echo $post_item['updated_at']; ?></span>
                                    <?php endif; ?>

                                <?php if (strpos($user_type,"company" ) === false) : ?>
                                <!-- 自分の投稿内容かつセッションが有効な間は編集・削除が可能 -->
                                    <?php if (strpos($post_item['participants'], $cont_id) !== false) : ?>
                                    <form action="Paticipation.php" method="post">
                                        <button type="submit" name="participate_btn">詳細へ移動</button>
                                        <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
                                    </form>
                                    <?php endif; ?>
                                    <?php if (strpos($post_item['participants'], $cont_id) === false) : ?>
                                    <?php if (strpos($post_item['participants_wait'], $cont_id) === false) : ?>
                                        <form action="Paticipation_wait.php" method="post">
                                            <button type="submit" name="apply_btn">参加申請</button>
                                            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
                                        </form>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($post_item['contributor_id'] === $cont_id) : ?>
                                    <div class="btn-flex">
                                        <form action="update-edit.php" method="post">
                                            <button type="submit" name="update_btn">編集</button>
                                            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
                                        </form>
                                        <form action="delete-confirm.php" method="post">
                                            <button type="submit" name="delete_btn">削除</button>
                                            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
                                        </form>
                                        <form action="Paticipation_edit.php" method="post">
                                            <button type="submit" name="confirm_btn">参加者管理</button>
                                            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['id']) && ($_SESSION['id'] == $post_item['id'])): ?>
                                    <p class='updated-post'>更新しました</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
    </section>
</body>
</html>
