<?php

// データベースの接続情報
define( 'DB_HOST', 'localhost');
define( 'DB_USER', 'root');
define( 'DB_PASS', '');
define( 'DB_NAME', 'board');

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 変数の初期化
$current_date = null;
$message_array = array();
$error_message = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

session_start();

// データベースに接続
try {

    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
    );
    $pdo = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';host='.DB_HOST , DB_USER, DB_PASS, $option);

} catch(PDOException $e) {

    // 接続エラーのときエラー内容を取得する
    $error_message[] = $e->getMessage();
}

if( !empty($_GET['room_id']) ) {

     $stmt = $pdo->prepare("SELECT view_name,message,post_date FROM message WHERE message.room_id = :room_id");

     // 値をセット
     $stmt->bindValue( ':room_id', $_GET['room_id'], PDO::PARAM_INT);
 
     // SQLクエリの実行
     $stmt->execute();
 
     // 表示するデータを取得
     $message_array = $stmt->fetchAll();
 
     // 投稿データが取得できないときは管理ページに戻る
     if( empty($message_array) ) {
         header("Location: ./php");
         exit;
     }
 }

 $stmt = null;
 $pdo = null;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ルームのメッセージ</title>
<style>
    <?php require("./main.css"); ?>
</style>
</head>
<body>
<h1>Welcome to Room...</h1>

<!-- エラ〜メッセージを表示する　-->
<?php if( !empty($error_message) ): ?>
    <ul class="error_message">
		<?php foreach( $error_message as $value ): ?>
            <li>・<?php echo $value; ?></li>
		<?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- ホームページへ移動ボタン、新ルームを作成ボタン-->
<button name="#"><a href="./index.php">ホーム</a></button>
<button name="#"><a href="./room.php">新規ルーム</a></button>

<!-- ルームごとのメッセージを表示する　-->
<section>
<?php if( !empty($message_array) ): ?>
<?php foreach( $message_array as $value ): ?>
<article>
    <div class="info">
        <h2><?php echo htmlspecialchars( $value['view_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
    </div>
    <p><?php echo nl2br( htmlspecialchars( $value['message'], ENT_QUOTES, 'UTF-8') ); ?></p>
</article>
<?php endforeach; ?>
<?php endif; ?>
</section>
</body>
</html>