<?php
    require_once("dbconnect.php"); 
    $username = $_POST['username'];
    $pass = $_POST['pass'];

    // Usernameで検索するSQL文
    $sql = "SELECT UID, Pass FROM Account WHERE Username = :username";
    try {
        $stmt = $dbcon->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $tmp = $stmt->fetch(PDO::FETCH_ASSOC);

        // ユーザーが見つからない場合、またはパスワードが一致しない場合のエラーメッセージ
        if ($stmt->rowCount() == 0 || $pass != $tmp['Pass']) {
            echo <<<EOD
            <html>
            <head><title>ERROR</title></head>
            <link rel="stylesheet" type="text/css" href="style.css">
            <body>
            <h1>ERROR</h1>
            <div class="err">ユーザーネームまたはパスワードが違います</div>
            <a href="index.html" class="btn--mg-c">ログイン画面に戻る</a>
            </body>
            </html>
            EOD;
            exit;
        } else {
            // 認証成功の場合、セッションにUIDを保存し、board.phpにリダイレクト
            $uid = $tmp['UID'];
            session_start();
            $_SESSION['uid'] = $uid;
            header("Location: board.php");
            exit();
        }
    } catch (PDOException $e) {
        die($e->getMessage());
    }

    $dbcon = null;
?>
