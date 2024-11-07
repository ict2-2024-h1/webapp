<?php
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.gc_divisor', 1);
session_start();

// DB接続情報
const DB_HOST = 'mysql:dbname=board;host=127.0.0.1;charset=utf8';
const DB_USER = 'root';
const DB_PASSWORD = '';

if (!isset($_SESSION['uid'])) {
    echo 'ログインしてください。';
    exit();
}

$uid = $_SESSION['uid'];
$uname=$_SESSION['username'];// 追加 ID値を渡す

// post_id が設定されているか確認
if (!isset($_POST['post_id'])) {
    echo '投稿が指定されていません。';
    exit();
}

$post_id = $_POST['post_id'];

// 承認待ちの参加者を取得
try {
    $pdo = new PDO(DB_HOST, DB_USER, DB_PASSWORD, [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    $sql = 'SELECT participants_wait FROM board_info WHERE id = :post_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();

    $result = $stmt->fetch();

    $sql = 'SELECT participants FROM board_info WHERE id = :post_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();

    $rresult = $stmt->fetch();
    if (!isset($rresult['participants'])) {
        echo '参加者情報が見つかりませんでした。';
        exit();
    }

    if ($result) {
        $participants_wait = explode(',', $result['participants_wait']);
    } else {
        echo '指定された投稿は存在しません。';
        exit();
    }
} catch (PDOException $e) {
    echo '接続失敗: ' . $e->getMessage();
    exit();
}

// フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    foreach ($participants_wait as $participant_uid) {
        if (isset($_POST['approve_' . $participant_uid])) {
            print_r($result); 
            // 参加者を承認
            $current_participants = explode(',', $rresult['participants'] ?? '');

// 承認された参加者UIDを最後に追加
            $current_participants[] = $participant_uid;

// 最終的なparticipants文字列を作成
            $participants = implode(',', $current_participants);

            $participants_wait = array_diff($participants_wait, [$participant_uid]);
        } elseif (isset($_POST['disapprove_' . $participant_uid])) {
            $current_participants = explode(',', $rresult['participants'] ?? '');
            $participants = implode(',', $current_participants);
            $participants_wait = array_diff($participants_wait, [$participant_uid]);
        }
    }

    // データベースを更新
    $sql = 'UPDATE board_info SET participants = :participants, participants_wait = :participants_wait WHERE id = :post_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':participants', $participants, PDO::PARAM_STR);
    $stmt->bindValue(':participants_wait', implode(',', $participants_wait), PDO::PARAM_STR);
    $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    
    echo '参加者の管理が完了しました。';
    header('refresh: 3; url=board.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>参加者管理</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <h1>参加者管理</h1>
    <form action="#" method="post">
        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_id); ?>">
        <ul>
            <?php foreach ($participants_wait as $participant_uid) : ?>
                <li>
                    <span>UID: <?php echo htmlspecialchars($participant_uid); ?></span>
                    <button type="submit" name="approve_<?php echo htmlspecialchars($participant_uid); ?>">承認</button>
                    <button type="submit" name="disapprove_<?php echo htmlspecialchars($participant_uid); ?>">不承認</button>
                </li>
            <?php endforeach; ?>
        </ul>
        <input type="hidden" name="action" value="manage_participants">
    </form>
    <form action="board.php" method="post">
            <button type="submit" name="update_btn">掲示板に戻る</button>
            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
    </form>
</body>
</html>
