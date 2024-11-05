<?php
// セッション開始（必要なら）
session_start();

// データベース接続情報
const DB_HOST = 'mysql:dbname=board;host=127.0.0.1;charset=utf8';
const DB_USER = 'root';
const DB_PASSWORD = '';
$post_detail = null; // 投稿の詳細を格納する変数
$uid=$_SESSION['uid'];// 追加 ID値を渡す
// 参加ボタンが押されたか確認
if (isset($_POST['update_btn']) && isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];  // post_id を取得し整数に変換
    $userid=$uid;
    try {
        // データベースに接続
        $pdo = new PDO(DB_HOST, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $sql_post_detail = "SELECT * FROM board_info WHERE id = :post_id";
        $stmt = $pdo->prepare($sql_post_detail);
        $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->execute();
        $post_detail = $stmt->fetch(PDO::FETCH_ASSOC); // 投稿の詳細を取得
        // テーブル名をpost_idに基づいて動的に設定（例: table_123）
        $table_name = "table_" . $post_id;

        // テーブルが存在するか確認し、存在しなければ作成
        $sql_create_table = "
            CREATE TABLE IF NOT EXISTS $table_name (
                id INT AUTO_INCREMENT PRIMARY KEY,
                contributor_id VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        $pdo->exec($sql_create_table);

        // 参加後にその掲示板に投稿するフォームを表示
        if (isset($_POST['message']) && !empty($_POST['message'])) {
            // メッセージが送信された場合、そのメッセージをテーブルに挿入
            $sql_insert_message = "INSERT INTO $table_name (contributor_id, message) VALUES (:contributor_id, :message)";
            $stmt = $pdo->prepare($sql_insert_message);
            $stmt->bindValue(':contributor_id', $userid);  // ユーザーID（セッションから）
            $stmt->bindValue(':message', $_POST['message'], PDO::PARAM_STR);  // メッセージ
            $stmt->execute();
        }

        // 既存のメッセージを取得して表示
        $sql_select_messages = "SELECT * FROM $table_name ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql_select_messages);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo 'エラー: ' . $e->getMessage();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>参加掲示板</title>
</head>
<body>
    <h1>投稿 ID <?php echo htmlspecialchars($post_id, ENT_QUOTES, 'UTF-8'); ?> の掲示板</h1>
    <?php if ($post_detail) : ?>
        <h2>投稿の詳細</h2>
        <p><strong>タイトル:</strong> <?php echo htmlspecialchars($post_detail['title'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>投稿内容:</strong> <?php echo nl2br(htmlspecialchars($post_detail['comment'], ENT_QUOTES, 'UTF-8')); ?></p>
        <p><strong>投稿者ID:</strong> <?php echo htmlspecialchars($post_detail['contributor_id'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>投稿日:</strong> <?php echo htmlspecialchars($post_detail['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php else : ?>
        <p>該当する投稿は存在しません。</p>
    <?php endif; ?>
    <!-- メッセージ投稿フォーム -->
    <form action="#" method="post">
        <textarea name="message" placeholder="メッセージを入力" rows="5" cols="40"></textarea>
        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_id, ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" name="update_btn">メッセージ送信</button>
    </form>
    <form action="board.php" method="post">
            <button type="submit" name="update_btn">掲示板に戻る</button>
            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
    </form>

    <!-- メッセージ一覧 -->
    <h2>メッセージ一覧</h2>
    <?php if (!empty($messages)) : ?>
        <ul>
            <?php foreach ($messages as $message) : ?>
                <li>
                    <strong><?php echo htmlspecialchars($message['contributor_id'], ENT_QUOTES, 'UTF-8'); ?>:</strong>
                    <?php echo nl2br(htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8')); ?>
                    <small>（<?php echo htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8'); ?>）</small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>メッセージはまだありません。</p>
    <?php endif; ?>

</body>
</html>
