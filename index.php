<?php

define( 'DB_HOST', 'localhost');
define( 'DB_USER', 'root');
define( 'DB_PASS', '');
define( 'DB_NAME', 'board');

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 変数の初期化
$current_date = null;
$message = array();
$message_array = array();
$error_message = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;
$room_id = 0;
$rooms = array();
$which_room = array();

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

if( !empty($_POST['btn_submit']) ) {

    // 空白除去
	$view_name = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['view_name']);
	$message = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['message']);
    $room_id = $_POST["room_id"];

	// 表示名の入力チェック
	if( empty($view_name) ) {
		$error_message[] = '表示名を入力してください。';
	} else {

        // セッションに表示名を保存
		$_SESSION['view_name'] = $view_name;
    }

	// メッセージの入力チェック
	if( empty($message) ) {
		$error_message[] = 'ひと言メッセージを入力してください。';
	} else {

		// 文字数を確認
		if( 100 < mb_strlen($message, 'UTF-8') ) {
			$error_message[] = 'ひと言メッセージは100文字以内で入力してください。';
		}
	}

	if( empty($error_message) ) {
		
		// 書き込み日時を取得
        $current_date = date("Y-m-d H:i:s");

        // トランザクション開始
        $pdo->beginTransaction();

        try {

            // SQL作成
            $stmt = $pdo->prepare("INSERT INTO message (view_name, message, post_date, room_id) VALUES ( :view_name, :message, :current_date, :room_id)");

            // 値をセット
            $stmt->bindParam( ':view_name', $view_name, PDO::PARAM_STR);
            $stmt->bindParam( ':message', $message, PDO::PARAM_STR);
            $stmt->bindParam( ':current_date', $current_date, PDO::PARAM_STR);
            $stmt->bindParam( ':room_id', $room_id, PDO::PARAM_INT);


            // SQLクエリの実行
            $res = $stmt->execute();

            // コミット
            $res = $pdo->commit();

        } catch(Exception $e) {

            // エラーが発生した時はロールバック
            $pdo->rollBack();
        }

        if( $res ) {
            $_SESSION['success_message'] = 'メッセージを書き込みました。';
        } else {
            $error_message[] = '書き込みに失敗しました。';
        }

        // プリペアドステートメントを削除
        $stmt = null;

        header('Location: ./');
        exit;
	}
}

if( !empty($pdo) ) {

    // メッセージのデータを取得する
    $sql = "SELECT * FROM message ORDER BY post_date DESC";
    $message_array = $pdo->query($sql);

    $sql2 = "SELECT * FROM room ORDER BY name ASC";
    $rooms = $pdo->query($sql2);

    // $sql3 = "SELECT m.view_name, m.message, r.name, r.id FROM message m JOIN room r ON m.room_id = r.id";
    // $which_room = $pdo->query($sql3);
}

// require("./alert_process.php");

// データベースの接続を閉じる
$pdo = null;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ひと言掲示板</title>
<style>
	<?php require("./main.css"); ?>
    .alert_delete_button{
        display: flex;
        justify-content: flex-end;
    }
</style>
</head>
<body>
<h1>ひと言掲示板</h1>
<!-- 成功のメッセージを表示する　-->
<?php if( empty($_POST['btn_submit']) && !empty($_SESSION['success_message']) ): ?>
    <p class="success_message"><?php echo htmlspecialchars( $_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>
<!-- エラーメッセージを表示する　-->
<?php if( !empty($error_message) ): ?>
    <ul class="error_message">
		<?php foreach( $error_message as $value ): ?>
            <li>・<?php echo $value; ?></li>
		<?php endforeach; ?>
    </ul>
<?php endif; ?>
<!-- メッセージの投稿　-->
<form method="post">
	<div>
		<label for="view_name">表示名</label>
		<input id="view_name" type="text" name="view_name" value="<?php if( !empty($_SESSION['view_name']) ){ echo htmlspecialchars( $_SESSION['view_name'], ENT_QUOTES, 'UTF-8'); } ?>">
	</div>
	<div>
		<label for="message">ひと言メッセージ</label>
		<textarea id="message" name="message"><?php if( !empty($message) ){ echo htmlspecialchars( $message, ENT_QUOTES, 'UTF-8'); } ?></textarea>
	</div>
    <!-- 投稿したいルームを選択する、DBからルームを取り出す　-->
    <div>
    <label for="room_id">ルーム</label>
        <select name="room_id">
            <?php if( !empty($rooms) ): ?>
            <?php foreach( $rooms as $room ): ?>
                <option value="<?php echo $room["id"]?>"><?php echo $room["name"]; ?></option>
            <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <br>
	<input type="submit" name="btn_submit" value="書き込む">
    <a href="./admin.php">管理ページ</a>
</form>
<hr>
<label>検索</label>
  <form action="search_view.php" method="post">
      <input type="text" name="search_word">
      <input type="submit" name="btn_search" value="投稿を検索">
  </form>
  <hr>
<section>
<?php if( !empty($message_array) ): ?>
<?php foreach( $message_array as $value ): ?>
<article>
    <div class="info">
        <h2><?php echo htmlspecialchars( $value['view_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
        <button><a href="room_message.php?room_id=<?php echo $value['room_id']; ?>">ルームを見る</button>
    </div>
    <p><?php echo nl2br( htmlspecialchars( $value['message'], ENT_QUOTES, 'UTF-8') ); ?></p>
    <div class="alert_delete_button">
        <form method="post" action="alert_process.php">
            <input type="hidden" name="alert_message" value="<?php if( !empty($value['id']) ){ echo htmlspecialchars( $value['id'], ENT_QUOTES, 'UTF-8'); } ?>">
            <input type="submit" name="btn_alert" value="通報">
        </form>
    </div>
</article>
<?php endforeach; ?>
<?php endif; ?>
</section>
</body>
</html>