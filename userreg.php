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

    try{
        do {
			$uid =
			chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) .
			chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) .
			chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) .
			chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) .
			chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) .
			chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) .
			chr(mt_rand(65, 90)) . chr(mt_rand(65, 90));
			session_start();
	
			// データベースで確認
			$sql = "SELECT COUNT(*) FROM Account WHERE UID = :uid"; // UIDを確認
			$stmt = $dbcon->prepare($sql);
			$stmt->bindParam(':uid', $uid);
			$stmt->execute();
			
			// すでに存在するか確認
			$exists = $stmt->fetchColumn();
		} while ($exists > 0); // 存在する場合、再度生成

    $sql = "INSERT INTO Account"."(UID, Email, Pass, Name)"."VALUES (:uid, :email, :pass, :name)";
	//echo $sql;


        $stmt = $dbcon->prepare($sql);
        $stmt->bindParam(':uid', $uid);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':pass', $pass);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
		echo "登録が完了しました。<br/>";
	}catch(PDOException $e){
		echo "エラー: " . $e->getMessage() . "<br/>";
	}
	
?>

<a href="index.html">login</a>
</body>
</html>


