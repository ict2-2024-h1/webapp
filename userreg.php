<?php
require_once("dbconnect.php");
#require_once("dbfunctions.php");
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
</head>

<body>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームからの入力を取得
    $last_name = $_POST['last_name'];
    $last_name_kana = $_POST['last_name_kana'];
    $first_name = $_POST['first_name'];
    $first_name_kana = $_POST['first_name_kana'];
    $username = $_POST['username'];
    $birthdate = $_POST['birthdate'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $user_type = $_POST['user_type']; // ユーザータイプを取得

    // UIDの生成
    do {
        $uid = '';
        for ($i = 0; $i < 16; $i++) {
            $uid .= chr(mt_rand(65, 90)); // A-Z のランダムな文字を生成
        }

        // データベースで確認
        $sql = "SELECT COUNT(*) FROM Account WHERE UID = :uid"; // UIDを確認
        $stmt = $dbcon->prepare($sql);
        $stmt->bindParam(':uid', $uid);
        $stmt->execute();
        
        // すでに存在するか確認
        $exists = $stmt->fetchColumn();
    } while ($exists > 0); // 存在する場合、再度生成

    // SQL文を準備（UIDを含むように修正）
    $sql = "INSERT INTO Account (UID, LastName, LastNameKana, FirstName, FirstNameKana, UserName, Birthdate, Phone, Email, Pass,User_type)
    VALUES (:uid, :last_name, :last_name_kana, :first_name, :first_name_kana, :username, :birthdate, :phone, :email, :pass,:user_type)";

    try {
        $stmt = $dbcon->prepare($sql);
        $stmt->bindParam(':uid', $uid);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':last_name_kana', $last_name_kana);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':first_name_kana', $first_name_kana);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':birthdate', $birthdate);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':pass', $pass);
        $stmt->bindParam(':user_type', $user_type);
        $stmt->execute();

        header("Location: userregcomplete.php"); exit;
    } catch (PDOException $e) {
        // エラーメッセージを表示
        if ($e->getCode() == 23000) { // Duplicate entry
            echo "そのメールアドレスは登録済みです。<br/>";
        } else {
            echo "エラーが発生しました: " . $e->getMessage() . "<br/>";
        }
    }
}
?>

    ?>

    <a href="index.html">login</a>
</body>

</html>