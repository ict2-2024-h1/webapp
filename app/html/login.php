<?php
    require_once("dbconnect.php"); 
    $email=$_POST['email'];
    $pass=$_POST['pass'];

    $sql="SELECT UID, Pass FROM Account ". " WHERE Email=:email";
    try {
        $stmt=$dbcon->prepare($sql);
        $stmt->bindParam(':email', $email );
        $stmt->execute();
	$tmp=$stmt->fetch(PDO::FETCH_ASSOC);
	if ($stmt->rowCount() == 0 || $pass!=$tmp['Pass']){
		echo<<<EOD
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
	}else{
		$uid = $tmp['UID'];
		session_start();
		$_SESSION['uid'] = $uid;
            	header("Location: board.php");
            	exit();
        }
    } catch (PDOException $e) {
        die($e->getMessage());
    }

$dbcon=null;
?>
</body>
</html>

