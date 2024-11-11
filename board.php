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
    // タイトルと内容が入力されているかチェック
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

    // カテゴリ、使用言語、募集人数も確認
    if (isset($_POST['post_category']) && $_POST['post_category'] != '') {
        $_SESSION['category'] = $_POST['post_category'];
    } else {
        unset($_SESSION['category']);
    }

    if (isset($_POST['post_language']) && $_POST['post_language'] != '') {
        $_SESSION['language'] = $_POST['post_language'];
    } else {
        unset($_SESSION['language']);
    }

    if (isset($_POST['post_recruitment_count']) && $_POST['post_recruitment_count'] != '') {
        $_SESSION['recruitment_count'] = $_POST['post_recruitment_count'];
    } else {
        unset($_SESSION['recruitment_count']);
    }

    // 必要項目がすべて入力されている場合、投稿処理を実行
    if (
        isset($_POST['post_title']) && $_POST['post_title'] != '' &&
        isset($_POST['post_comment']) && $_POST['post_comment'] != '' &&
        isset($_POST['post_recruitment_count']) && $_POST['post_recruitment_count'] != ''
    ) {
        $title = $_POST['post_title'];
        $comment = $_POST['post_comment'];
        $category = $_POST['post_category'];
        $language = $_POST['post_language'];
        $recruitment_count = $_POST['post_recruitment_count'];

        try {
            // DB接続
            $pdo = new PDO(DB_HOST, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // 投稿内容登録処理
            $sql = ('
                INSERT INTO board_info (title, comment, contributor_id, contributor_username, participants, category, language, recruitment_count)
                VALUES (:TITLE, :COMMENT, :CONTRIBUTOR_ID, :CONTRIBUTOR_USERNAME, :PARTICIPANTS, :CATEGORY, :LANGUAGE, :RECRUITMENT_COUNT)
            ');
            $stmt = $pdo->prepare($sql);
            // プレースホルダーに値をセット
            $stmt->bindValue(':TITLE', $title, PDO::PARAM_STR);
            $stmt->bindValue(':COMMENT', $comment, PDO::PARAM_STR);
            $stmt->bindValue(':CONTRIBUTOR_ID', $cont_id, PDO::PARAM_STR);
            $stmt->bindValue(':CONTRIBUTOR_USERNAME', $username, PDO::PARAM_STR);
            $stmt->bindValue(':PARTICIPANTS', $cont_id, PDO::PARAM_STR);
            $stmt->bindValue(':CATEGORY', $category, PDO::PARAM_STR);
            $stmt->bindValue(':LANGUAGE', $language, PDO::PARAM_STR);
            $stmt->bindValue(':RECRUITMENT_COUNT', $recruitment_count, PDO::PARAM_INT);

            // SQL実行
            $stmt->execute();

            // 投稿成功後、セッション変数を破棄
            unset($_SESSION['title']);
            unset($_SESSION['comment']);
            unset($_SESSION['category']);
            unset($_SESSION['language']);
            unset($_SESSION['recruitment_count']);
        } catch (PDOException $e) {
            echo '接続失敗' . $e->getMessage();
            exit();
        }
        // DBとの接続を切る
        $pdo = null;
        $stmt = null;
    }
}
/**
 * 投稿一覧取得処理
 */
try {
    /**
    * DB接続処理
    */
    $pdo = new PDO(DB_HOST, DB_USER, DB_PASSWORD, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // データをカラム名をキーとする連想配列で取得する
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // 例外が発生した際にスローする
    ]);
    $sql = ('
    SELECT * 
    FROM board_info 
    ORDER BY id DESC
    ');
    $stmt = $pdo->prepare($sql);
    // SQL実行
    $stmt->execute();
    // 投稿情報を辞書形式ですべて取得
    $post_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '接続失敗' . $e->getMessage();
    exit();
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
    <nav>
        <!-- ロゴ画像 -->
         <img src="logo.png" alt="ロゴ画像" class="logo">
        <div class="nav-links">
            <a href="#">通知</a>
            <a href="#">プロジェクト</a>
            <a href="profile.php">プロフィール</a>
            <a href="view_mypage.php">アカウント詳細</a>
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

                        <!-- カテゴリ -->
                        <div>
                            <label>
                                <p>カテゴリ</p>
                                <input type="text" name="post_category" value="<?php if (isset($_SESSION['category'])) echo $_SESSION['category']; ?>">
                                <!-- エラーメッセージ -->
                                <?php if (isset($err_msg_comment)) {
                                    echo "<p class='err'>{$err_msg_comment}</p>";
                                } ?>
                            </label>
                        </div>

                        <!-- 使用言語 -->
                        <div>
                            <label>
                                <p>使用言語</p>
                                <input type="text" name="post_language" value="<?php if (isset($_SESSION['language'])) echo $_SESSION['language']; ?>">
                                <!-- エラーメッセージ -->
                                <?php if (isset($err_msg_comment)) {
                                    echo "<p class='err'>{$err_msg_comment}</p>";
                                } ?>
                            </label>
                        </div>

                        <!-- 募集人数 -->
                        <div>
                            <label>
                                <p>募集人数</p>
                                <input type="number" name="post_recruitment_count" value="<?php if (isset($_SESSION['recruitment_count'])) echo $_SESSION['recruitment_count']; ?>">
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
            
            <!-- 投稿一覧 -->
            <div class="post-list-container">
                <?php if (count($post_list) === 0) : ?>
                    <!-- 投稿が無いときはメッセージを表示する -->
                    <p class="no-post-msg">現在、投稿はありません。</p>
                <?php else : ?>
                    <ul>
                        <!-- 投稿情報の出力 -->
                        <?php foreach ($post_list as $post_item) : ?>
                            <li>
                                <form action="" method="post">
                                    <!-- 投稿ID -->
                                <span>ID：<?php echo $post_item['id']; ?></span>
                                <!-- 投稿タイトル -->
                                <span><?php echo $post_item['title']; ?></span>
                                <!-- カテゴリ -->
                                <span>カテゴリ：<?php echo $post_item['category']; ?></span>
                                <!-- 使用言語 -->
                                <span>使用言語：<?php echo $post_item['language']; ?></span>
                                <!-- 募集人数 -->
                                <span>募集人数：<?php echo $post_item['recruitment_count']; ?>名</span>
                                <!-- 投稿者ID -->
                                <span>／投稿者：<?php echo isset($post_item['contributor_username']) ? $post_item['contributor_username'] : '不明なユーザー'; ?></span>
                                <!-- 投稿内容 -->
                                <p class="p-pre"><?php echo $post_item['comment']; ?></p>
                                <!-- 投稿日時 -->
                                <span class="post-datetime">投稿日時：<?php echo $post_item['created_at']; ?></span>
                                <!-- 更新日時 -->
                                <?php if ($post_item['created_at'] < $post_item['updated_at']) : ?>
                                    <span class="post-datetime post-datetime__updated">更新日時：<?php echo $post_item['updated_at']; ?></span>
                                    <?php endif; ?>
                                </form>
                                <!-- 自分の投稿内容かつセッションが有効な間は編集・削除が可能 -->
                                <?php if (strpos($post_item['participants'], $cont_id) !== false) : ?>
                                    <form action="Paticipation.php" method="post">
                                        <button type="submit" name="participate_btn">詳細へ移動</button>
                                        <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
                                    </form>
                                <?php endif; ?>
                                <?php if (strpos($post_item['participants'], $cont_id) === false) : ?>
                                    <?php if (strpos($post_item['participants_wait'], $cont_id) === false) : ?>
                                        <form action="Paticipation_wait.php" method="post">
                                            <button type="submit" name="apply_btn">参加申請</button>
                                            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($post_item['contributor_id'] === $cont_id) : ?>
                                    <div class="btn-flex">
                                        <form action="update-edit.php" method="post">
                                            <button type="submit" name="update_btn">編集</button>
                                            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
                                        </form>
                                        <form action="delete-confirm.php" method="post">
                                            <button type="submit" name="delete_btn">削除</button>
                                            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
                                        </form>
                                        <form action="Paticipation_edit.php" method="post">
                                            <button type="submit" name="confirm_btn">参加者管理</button>
                                            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
                                        </form>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['id']) && ($_SESSION['id'] == $post_item['id'])): ?>
                                    <p class='updated-post'>更新しました</p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</body>

</html>
