<?php
require_once("dbconnect.php");
// $email=$_POST['email'];
// $pass=$_POST['pass'];
$email = isset($_POST['email']) ? $_POST['email'] : '';
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';


$sql = "SELECT UID, Pass, User_type FROM Account " . " WHERE Email=:email";
try {
	$stmt = $dbcon->prepare($sql);
	$stmt->bindParam(':email', $email);
	$stmt->execute();
	$tmp = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($stmt->rowCount() == 0 || $pass != $tmp['Pass'] || $user_type != $tmp['user_type']) {
		echo <<<EOD
		<html>
		<head><title>ERROR</title></head>
		<body>
		<h1>ERROR</h1>
		<div>メールアドレスまたはパスワードが違います</div>
		<a href=index.html>ログイン画面に戻る</a>
		</body>
		</html>
		EOD;
		exit;
	} else {
		$uid = $tmp['UID'];
		$user_type = $tmp['User_type'];
		session_start();
		$_SESSION['uid'] = $uid;
		$_SESSION['user_type'] = $user_type;

		header("Location: board.php");
		exit();
	}
} catch (PDOException $e) {
	die($e->getMessage());
}

$dbcon = null;
?>
</body>

</html>