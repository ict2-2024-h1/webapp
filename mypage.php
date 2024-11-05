<?php
// セッション開始
session_start();

// DB接続情報
const DB_HOST = 'mysql:dbname=board;host=127.0.0.1;charset=utf8';
const DB_USER = 'root';
const DB_PASSWORD = '';

// UID取得
$uid = $_SESSION['uid']; // セッションからUIDを取得
// UIDが正しく取得できているか確認
if (!$uid) {
    header('Location: login.php'); // セッションがない場合、ログインページへリダイレクト
    exit();
}


try {
    // DB接続
    $pdo = new PDO(DB_HOST, DB_USER, DB_PASSWORD, [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    // ユーザー情報の取得
    $sql = 'SELECT * FROM account WHERE UID = :uid';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':uid', $uid);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo 'ユーザー情報が見つかりませんでした。';
        exit();
    }
    
    // 更新処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $last_name = $_POST['last_name'];
        $last_name_kana = $_POST['last_name_kana'];
        $first_name = $_POST['first_name'];
        $first_name_kana = $_POST['first_name_kana'];
        $username = $_POST['username'];
        $birthdate = $_POST['birthdate'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $pass = $_POST['pass']; // パスワードはハッシュ化しない

        // 更新SQL
        $update_sql = 'UPDATE account SET LastName = :last_name, LastNameKana = :last_name_kana, FirstName = :first_name, FirstNameKana = :first_name_kana, UserName = :username, Birthdate = :birthdate, Phone = :phone, Email = :email, Pass = :pass WHERE UID = :uid';

        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->bindParam(':uid', $uid);
        $update_stmt->bindParam(':last_name', $last_name);
        $update_stmt->bindParam(':last_name_kana', $last_name_kana);
        $update_stmt->bindParam(':first_name', $first_name);
        $update_stmt->bindParam(':first_name_kana', $first_name_kana);
        $update_stmt->bindParam(':username', $username);
        $update_stmt->bindParam(':birthdate', $birthdate);
        $update_stmt->bindParam(':phone', $phone);
        $update_stmt->bindParam(':email', $email);
        $update_stmt->bindParam(':pass', $pass);
        $update_stmt->execute();

        if ($update_stmt->execute()) {
            // 更新成功後に最新のデータを再取得
            $stmt = $pdo->prepare("SELECT * FROM account WHERE UID = :uid");
            $stmt->bindParam(':uid', $uid);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        echo '情報を更新しました。';
    }
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
    <title>マイページ</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <h1>マイページ</h1>
    <form action="" method="post">
        <div>
            <label for="last_name">姓</label>
            <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['LastName']); ?>" required>
        </div>
        <div>
            <label for="last_name_kana">姓（カナ）</label>
            <input type="text" name="last_name_kana" id="last_name_kana" value="<?php echo htmlspecialchars($user['LastNameKana']); ?>" required>
        </div>
        <div>
            <label for="first_name">名</label>
            <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['FirstName']); ?>" required>
        </div>
        <div>
            <label for="first_name_kana">名（カナ）</label>
            <input type="text" name="first_name_kana" id="first_name_kana" value="<?php echo htmlspecialchars($user['FirstNameKana']); ?>" required>
        </div>
        <div>
            <label for="username">ユーザー名</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['UserName']); ?>" required>
        </div>
        <div>
            <label for="birthdate">生年月日</label>
            <input type="date" name="birthdate" id="birthdate" value="<?php echo htmlspecialchars($user['Birthdate']); ?>" required>
        </div>
        <div>
            <label for="phone">電話番号</label>
            <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user['Phone']); ?>" required>
        </div>
        <div>
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
        </div>
        <div>
            <label for="pass">パスワード</label>
            <input type="password" name="pass" id="pass" value="<?php echo htmlspecialchars($user['Pass']); ?>" required>
        </div>
        <button type="submit">更新</button>
    </form>
    <a href="board.php">掲示板に戻る</a>
</body>

</html>
