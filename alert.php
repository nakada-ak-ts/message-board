<?php

// 管理ページのログインパスワード
define( 'PASSWORD', 'adminPassword');

// データベースの接続情報
define( 'DB_HOST', 'localhost');
define( 'DB_USER', 'root');
//define( 'DB_PASS', 'root');
define( 'DB_PASS', '');
define( 'DB_NAME', 'board');

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 変数の初期化
$current_date = null;
$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

session_start();

// 管理者としてログインしているか確認
if( empty($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true ) {

	// ログインページへリダイレクト
	header("Location: ./admin.php");
	exit;
}

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


if( !empty($pdo) ) {

    // メッセージのデータを取得する
    $sql = "
    SELECT * 
    FROM message
    LEFT JOIN alert_message
    ON message.id = alert_message.message_id
    WHERE alert_id is not null
    ORDER BY alert_message.post_date DESC;    
    ";
    $message_array = $pdo->query($sql);
}

// データベースの接続を閉じる
$pdo = null;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ひと言掲示板 管理ページ（通報レポート）</title>
<style>
	<?php require("./main.css"); ?>
  .alert_delete_button{
    display: flex;
    justify-content: flex-end;
  }
</style>
</head>
<body>
<h1>ひと言掲示板 管理ページ（通報レポート）</h1>
<?php if( !empty($error_message) ): ?>
    <ul class="error_message">
		<?php foreach( $error_message as $value ): ?>
            <li>・<?php echo $value; ?></li>
		<?php endforeach; ?>
    </ul>
<?php endif; ?>
<section>

<a href="./admin.php">admin top</a>

<?php if( !empty($message_array) ){ ?>
  <?php foreach( $message_array as $value ){ ?>

  <article>
      <div class="info">
          <h2><?php echo htmlspecialchars( $value['view_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
          <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
          <p><a href="delete.php?message_id=<?php echo $value['id']; ?>">削除</a></p>
      </div>
      <p><?php echo nl2br( htmlspecialchars( $value['message'], ENT_QUOTES, 'UTF-8') ); ?></p>
      <div class="alert_delete_button">
        <form method="post" action="alert_process.php">
          <input type="hidden" name="alert_message_delete" value="<?php if( !empty($value['id']) ){ echo htmlspecialchars( $value['id'], ENT_QUOTES, 'UTF-8'); } ?>">
          <input type="submit" name="btn_alert_delete" value="通報一覧から削除">
        </form>
      </div>
  </article>
  <?php } ?>
<?php } ?>

</section>
</body>
</html>
