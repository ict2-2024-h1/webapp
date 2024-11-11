<?php
session_start();
require_once('dbconnect.php'); // DB接続

// ユーザーIDを取得し、データベースからユーザー情報を取得
$uid = $_SESSION['uid'];

// DB接続
    $pdo = new PDO(DB_HOST, DB_USER, DB_PASSWORD, [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    

/// ユーザー情報の取得
$sql = 'SELECT * FROM account WHERE UID = :uid';
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':uid', $uid);
$stmt->execute();
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ</title>
    <link rel="stylesheet" href="mypage_style.css">
</head>
<body>
    <div class="container">
        <h1>プロフィール</h1>

        <div class="profile-info">
            <div class="profile-item">
                <label></label>
                <?php if ($profile['ProfilePic']) : ?>
                    <img src="<?php echo htmlspecialchars($profile['ProfilePic']); ?>" alt="プロフィール画像" class="profile-pic">
                <?php else : ?>
                    <p>画像は設定されていません。</p>
                <?php endif; ?>
            </div>
            <div class="profile-item">
                <label>ユーザー名:</label>
                <p><?php echo htmlspecialchars($profile['UserName']); ?></p>
            </div>
            <div class="profile-item">
                <label>自己紹介:</label>
                <p><?php echo nl2br(htmlspecialchars($profile['Bio'])); ?></p>
            </div>
            <div class="profile-item">
                <label>学校:</label>
                <p><?php echo htmlspecialchars($profile['School']); ?></p>
            </div>
            <div class="profile-item">
                <label>学科:</label>
                <p><?php echo htmlspecialchars($profile['Department']); ?></p>
            </div>
            <div class="profile-item">
                <label>学部:</label>
                <p><?php echo htmlspecialchars($profile['Faculty']); ?></p>
            </div>
            <div class="profile-item">
                <label>専攻:</label>
                <p><?php echo htmlspecialchars($profile['Major']); ?></p>
            </div>
        </div>

        <p><a href="edit_profile.php">プロフィール編集</a></p>
        <p><a href="board.php">掲示板に戻る</a></p>
    </div>
</body>
</html>
