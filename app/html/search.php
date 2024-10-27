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
* 削除ボタンで遷移してきたときの処理
*/

?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>掲示板アプリ</title>
        <link rel="stylesheet" href="./style.css">
    </head>
    <body>
        <h1>検索キーワード</h1>
<!-- 投稿フォーム -->
        <section class="post-form">
            <form action="#" method="post">
                <div class="post-form__flex">
                    <div>
                        <label>
                            <p>タグ検索</p>
                            <input type="text" name="post_title" value="<?php if (isset($_SESSION['title'])) echo $_SESSION['title']; ?>">
<!-- エラーメッセージ -->
                            <?php if (isset($err_msg_title)) {
                                echo "<p class='err'>{$err_msg_title}</p>";
                            } ?>
                        </label>
                    </div>
                </div>
                <button class="btn--mg-c" type="submit" name="post_btn" value="post_btn">検索</button>
            </form>
            <form action="search.php" method="post">
                <button type="submit" name="update_btn">検索</button>
                <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
            </form>
        </section>
        <hr>
<!-- 投稿一覧 -->
        <section class="post-list">
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
                        <span>ID：<?php echo $post_item['id']; ?>　</span>
<!-- 投稿タイトル -->
                        <span><?php echo $post_item['title']; ?></span>
<!-- 投稿者ID -->
                        <span>／投稿者：<?php echo $post_item['contributor_id']; ?></span>
<!-- 投稿内容 -->
                        <p class="p-pre"><?php echo $post_item['comment']; ?></p>
<!-- 投稿日時 -->
                        <span class="post-datetime">投稿日時：<?php echo $post_item['created_at']; ?></span>
<!-- 過去に更新されていたら更新日時も表示 -->
                        <?php if ($post_item['created_at'] < $post_item['updated_at']) : ?>
                        <span class="post-datetime post-datetime__updated">更新日時：<?php echo $post_item['updated_at']; ?></span>
                        <?php endif; ?>
                    </form>
<!-- 自分の投稿内容かつセッションが有効な間は編集・削除が可能 -->
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
                    </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['id']) && ($_SESSION['id'] == $post_item['id'])): ?>
                    <p class='updated-post'>更新しました</p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        </section>
    </body>
</html>