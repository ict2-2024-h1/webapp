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
    //var_dump($_POST);
    $email  = $_POST['email'];
    $pass = $_POST['pass'];
    $name = $_POST['name'];

    $sql = "INSERT INTO Account" . "(Email, Pass, Name)" . "VALUES (:email, :pass, :name)";
    //echo $sql;

    try {
        $stmt = $dbcon->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':pass', $pass);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        echo "登録が完了しました。<br/>";
    } catch (PDOException $e) {
        echo "そのメールアドレスは登録済みです。<br/>";
    }

    ?>

    <a href="index.html">login</a>
</body>

</html>