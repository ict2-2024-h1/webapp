<?php
    session_start();
    if( !isset($_SESSION['uid'] )) {
        header("Location: logout.php");
        exit;
    }
    require_once("dbconnect.php");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>HOME</title>
</head>
<body>

<?php
$uid=$_SESSION['uid'];

$sql = "SELECT Name FROM Account WHERE UID=:uid";
try {
	$stmt=$dbcon->prepare($sql);
        $stmt->bindParam(":uid", $uid);
        $stmt->execute ();
	$tmp=$stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e){
	echo "GetNameByUid failed:" . $e->getMessage() . "<br>\n";
}

$name=htmlspecialchars($tmp['Name']);

echo<<<EOD
<h1>HELLO, {$name}!</h1>

<a href="logout.php">[Logout]</a>
EOD;
?>
</body>
</html>
