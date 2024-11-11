<?php

/**
 * セッション開始
 * セッションの保存期間を1800秒に指定　※任意の秒数へ変更可能 
 * かつ、確実に破棄する
 */
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.gc_divisor', 1);
session_start();

/**
* DB接続情報
*/
const DB_HOST = 'mysql:dbname=board;host=127.0.0.1;charset=utf8';
const DB_USER = 'root';
const DB_PASSWORD = '';

/**
* ID取得処理
*/

$uid=$_SESSION['uid'];// 追加 ID値を渡す
$_SESSION['uid'] = $uid;


try {
    /**
    * DB接続処理
    */
    $pdo = new PDO(DB_HOST, DB_USER, DB_PASSWORD, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // データをカラム名をキーとする連想配列で取得する
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // 例外が発生した際にスローする
    ]);
    $sql = ('
    SELECT UserName, User_type 
    FROM account
    WHERE UID = :uid
    ');
    
    $stmt = $pdo->prepare($sql);
    
    // プレースホルダに検索するuid値をバインド
    $stmt->bindParam(':uid', $uid);
    
    // SQL実行
    $stmt->execute();
    
    // 検索結果を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $username = $result['UserName'];
        $user_type = $result['User_type'];

        echo 'ログイン中ユーザー: ' . $username . '（ユーザータイプ: ' . $user_type . '）';
    } else {
        echo '指定されたUIDは見つかりませんでした。';
    }
    

} catch (PDOException $e) {
    echo '接続失敗' . $e->getMessage();
    exit();
}

/**
* 投稿者ID（20桁）を生成
*/
if (isset($_SESSION['cont_id'])) {
$cont_id = $uid;
} else {
$_SESSION['cont_id'] = $uid;
$cont_id = $uid;
}

/**
 * 投稿ボタンが押下されたときの処理
 */
if (isset($_POST['post_btn'])) {
    // 更新操作用の処理
    unset($_SESSION['id']);
    /**
    * セッション変数に情報を保存して
    * タイトルまたは投稿内容の片方だけが
    * 入力されていた場合、
    * 入力フォームに内容を保持する
    */
    if (isset($_POST['post_title']) && $_POST['post_title'] != '') {
        $_SESSION['title'] = $_POST['post_title'];
    } else {
        unset($_SESSION['title']);
    }
    if (isset($_POST['post_comment']) && $_POST['post_comment'] != '') {
        $_SESSION['comment'] = $_POST['post_comment'];
    } else {
        unset($_SESSION['comment']);
    }
    /**
    * エラーメッセージ格納
    */
    if ($_POST['post_title'] == '') $err_msg_title  = '※タイトルを入力して下さい';
    if ($_POST['post_comment'] == '') $err_msg_comment  = '※投稿内容を入力して下さい';
    /**
    * 必要項目がすべて入力されてたら投稿処理を実行
    */
    if (
    isset($_POST['post_title']) && $_POST['post_title'] != '' &&
    isset($_POST['post_comment']) && $_POST['post_comment'] != ''
    ) {
        $title = $_POST['post_title'];
        $comment = $_POST['post_comment'];
        try {
            /**
            * DB接続処理
            */
            $pdo = new PDO(DB_HOST, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // 例外が発生した際にスローする
            ]);
            /**
            * 投稿内容登録処理
            */
            $sql = ('
            INSERT INTO
            board_info (title, comment, contributor_id, contributor_username,participants)
            VALUES
            (:TITLE, :COMMENT, :CONTRIBUTOR_ID, :CONTRIBUTOR_USERNAME,:PARTICIPANTS)
            ');
            $stmt = $pdo->prepare($sql);
            // プレースホルダーに値をセット
            $stmt->bindValue(':TITLE', $title, PDO::PARAM_STR);
            $stmt->bindValue(':COMMENT', $comment, PDO::PARAM_STR);
            $stmt->bindValue(':CONTRIBUTOR_ID', $cont_id, PDO::PARAM_STR);
            $stmt->bindValue(':CONTRIBUTOR_USERNAME', $username, PDO::PARAM_STR);
            $stmt->bindValue(':PARTICIPANTS', $cont_id, PDO::PARAM_STR);
            // SQL実行
            $stmt->execute();
            // 投稿に成功したらセッション変数を破棄
            unset($_SESSION['title']);
            unset($_SESSION['comment']);
        } catch (PDOException $e) {
            echo '接続失敗' . $e->getMessage();
            exit();
        }
        // DBとの接続を切る
        $pdo = null;
        $stmt = null;
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>掲示板アプリ</title>
    <link rel="stylesheet" href="./board_style.css">
</head>

<body>
    <h1>掲示板アプリ</h1>
    <div style="text-align: right;">
        <a href="view_mypage.php" class="btn">マイページ</a>
    </div>
    <nav>
        <div class="logo">CampusCodeLink</div>
        <div class="nav-links">
            <a href="#">ホーム</a>
            <a href="#">後で見る</a>
            <a href="#">通知</a>
            <a href="#">参加プロジェクト</a>
            <a href="#">アカウント</a>
            <a href="logout.php">ログアウト</a>
        </div>
    </nav>
    <!-- ユーザータイプに応じた内容 -->
    <section>
        <?php if ($user_type === 'student'): ?>
            <h2>学生用掲示板</h2>
            <p>学生用の投稿を表示します。投稿・編集・削除が可能です。</p>
        <?php elseif ($user_type === 'company'): ?>
            <h2>企業用掲示板</h2>
            <p>企業用の投稿を表示します。情報の閲覧のみ可能です。</p>
        <?php else: ?>
            <h2>不明なユーザータイプ</h2>
            <p>適切なユーザータイプでログインしてください。</p> <!-- 不明なユーザータイプの場合の明確なメッセージ -->
        <?php endif; ?>
    </section>

    <?php if ($user_type === 'student') : ?>
        <section class="post-container">

            <!-- 投稿フォーム -->
            <div class="post-form-container">
                <form action="#" method="post">
                    <div class="post-form_flex">

                        <div>
                            <label>
                                <p>タイトル（※最大30文字）</p>
                                <input type="text" name="post_title" value="<?php if (isset($_SESSION['title'])) echo $_SESSION['title']; ?>">
                                <!-- エラーメッセージ -->
                                <?php if (isset($err_msg_title)) {
                                    echo "<p class='err'>{$err_msg_title}</p>";
                                } ?>
                            </label>
                        </div>

                        <div>
                            <label>
                                <p>投稿内容（※最大1000文字）</p>
                                <textarea name="post_comment" cols="50" rows="10"><?php if (isset($_SESSION['comment'])) echo $_SESSION['comment']; ?></textarea>
                                <!-- エラーメッセージ -->
                                <?php if (isset($err_msg_comment)) {
                                    echo "<p class='err'>{$err_msg_comment}</p>";
                                } ?>
                            </label>
                        </div>

                    </div>
                    <button class="btn--mg-c" type="submit" name="post_btn" value="post_btn">投稿</button>
                </form>
                
                <form action="search.php" method="post" style="text-align: right;">
                    <button type="submit" name="apply_btn">検索</button>
                </form>
                
            </div>
        </section>
    <?php endif; ?>
</body>

</html>
