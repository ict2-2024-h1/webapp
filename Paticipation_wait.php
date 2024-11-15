<?php
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.gc_divisor', 1);
session_start();
/**
* DB接続情報
*/
const DB_HOST = 'mysql:dbname=board;host=127.0.0.1;charset=utf8';
const DB_USER = 'root';
const DB_PASSWORD = '';

$cont_id = $_SESSION['uid'];  // ユーザーIDをセッションから取得
if (isset($_POST['apply_btn'])) {
    $post_id = $_POST['post_id'];
    $user_id = $cont_id; // 現在のユーザーID
    try {
        $pdo = new PDO(DB_HOST, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        echo 'データベース接続に失敗しました: ' . $e->getMessage();
        exit();
    }

    // 既存の参加待機リストを取得
    $sql = "SELECT participants_wait FROM board_info WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id]);
    $row = $stmt->fetch();
    $current_waiting_list = $row['participants_wait'];

    // UIDを追加（カンマ区切りで追加）
    if ($current_waiting_list) {
        $updated_waiting_list = $current_waiting_list . ',' . $user_id;
    } else {
        $updated_waiting_list = $user_id;
    }

    // participants_waitに更新
    $sql = "UPDATE board_info SET participants_wait = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$updated_waiting_list, $post_id]);

    echo "参加申請が送信されました。3秒後に自動で掲示板TOPへ戻ります。";
    header('refresh: 3; url=board.php');
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