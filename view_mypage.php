<?php
// セッション開始
session_start();

// DB接続情報
const DB_HOST = 'mysql:dbname=board;host=127.0.0.1;charset=utf8';
const DB_USER = 'root';
const DB_PASSWORD = '';

// ユーザーIDを取得し、データベースからユーザー情報を取得
$uid = $_SESSION['uid'];

// DB接続
$pdo = new PDO(DB_HOST, DB_USER, DB_PASSWORD, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$sql = 'SELECT * FROM account WHERE UID = :uid';
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':uid', $uid);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>マイページ</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <h1>マイページ</h1>
    <p>姓: <?php echo htmlspecialchars($user['LastName']); ?></p>
    <p>姓（カナ）: <?php echo htmlspecialchars($user['LastNameKana']); ?></p>
    <p>名: <?php echo htmlspecialchars($user['FirstName']); ?></p>
    <p>名（カナ）: <?php echo htmlspecialchars($user['FirstNameKana']); ?></p>
    <p>ユーザー名: <?php echo htmlspecialchars($user['UserName']); ?></p>
    <p>生年月日: <?php echo htmlspecialchars($user['Birthdate']); ?></p>
    <p>電話番号: <?php echo htmlspecialchars($user['Phone']); ?></p>
    <p>メールアドレス: <?php echo htmlspecialchars($user['Email']); ?></p>
    <p><a href="mypage.php">編集ページに移動する</a></p>
    <p><a href="board.php">掲示板に戻る</a></p>
</body>
</html>
