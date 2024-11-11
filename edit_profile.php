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


// フォームが送信された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 入力された情報を取得
    $bio = $_POST['bio'];
    $school = $_POST['school'];
    $department = $_POST['department'];
    $faculty = $_POST['faculty'];
    $major = $_POST['major'];

    // プロフィール画像の処理（画像がアップロードされた場合）
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $upload_dir = 'uploads/';
        $filename = basename($_FILES['profile_pic']['name']);
        $filepath = $upload_dir . $filename;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filepath);
    } else {
        $filepath = $profile['ProfilePic']; // 画像が変更されない場合、現在の画像を使用
    }

    // プロフィールの更新
    $update_query = "UPDATE account SET Bio = ?, School = ?, Department = ?, Faculty = ?, Major = ?, ProfilePic = ?";
    $stmt = $pdo->prepare($update_query);
    $stmt->execute([$bio, $school, $department, $faculty, $major, $filepath]);

    // 更新が成功した場合、リダイレクト
    header('Location: profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール編集</title>
    <link rel="stylesheet" href="mypage_style.css">
</head>
<body>
    <div class="container">
        <h1>プロフィール編集</h1>
        <form action="edit_profile.php" method="post" enctype="multipart/form-data">
            <div>
                <label for="profile_pic">アイコン</label>
                <input type="file" name="profile_pic" id="profile_pic">
            </div>
            <div>
                <label for="bio">自己紹介</label>
                <textarea name="bio" id="bio" required><?php echo htmlspecialchars($profile['Bio']); ?></textarea>
            </div>
            <div>
                <label for="school">学校</label>
                <input type="text" name="school" id="school" value="<?php echo htmlspecialchars($profile['School']); ?>">
            </div>
            <div>
                <label for="department">学科</label>
                <input type="text" name="department" id="department" value="<?php echo htmlspecialchars($profile['Department']); ?>">
            </div>
            <div>
                <label for="faculty">学部</label>
                <input type="text" name="faculty" id="faculty" value="<?php echo htmlspecialchars($profile['Faculty']); ?>">
            </div>
            <div>
                <label for="major">専攻</label>
                <input type="text" name="major" id="major" value="<?php echo htmlspecialchars($profile['Major']); ?>">
            </div>
            <button type="submit">更新</button>
        </form>
        <p><a href="profile.php">マイページに戻る</a></p>
    </div>
</body>
</html>
