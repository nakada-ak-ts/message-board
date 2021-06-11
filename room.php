<?php

define( 'DB_HOST', 'localhost');
define( 'DB_USER', 'root');
define( 'DB_PASS', '');
define( 'DB_NAME', 'board');

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 変数の初期化
$current_date = null;
//$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

$rooms = array();

try {

	$option = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
	);
	$pdo = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';host='.DB_HOST , DB_USER, DB_PASS, $option);

} catch(PDOException $e) {

	$error_message[] = $e->getMessage();
}
if( !empty($_POST['btn_submit']) ) {

	$room_name = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['room_name']);
	$description = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['description']);

	if( empty($room_name) ) {
		$error_message[] = 'ルーム名を入力してください。';
	} 
    // else {
	// 	$_SESSION['view_name'] = $view_name;
    // }

	if( empty($description) ) {
		$error_message[] = 'ルームの目的を入力してください。';
	} else {

		// 文字数を確認
		if( 100 < mb_strlen($description, 'UTF-8') ) {
			$error_message[] = 'ルームの目的は100文字以内で入力してください。';
		}
	}

	if( empty($error_message) ) {

        $current_date = date("Y-m-d H:i:s");
        $pdo->beginTransaction();

        try {

            // SQL作成
            $stmt = $pdo->prepare("INSERT INTO room (name, description, create_date) VALUES ( :room_name, :description, :create_date )");

            // 値をセット
            $stmt->bindParam( ':room_name', $room_name, PDO::PARAM_STR);
            $stmt->bindParam( ':description', $description, PDO::PARAM_STR);
            $stmt->bindParam( ':create_date', $current_date, PDO::PARAM_STR);


            // SQLクエリの実行
            $res = $stmt->execute();

            // コミット
            $res = $pdo->commit();

        } catch(Exception $e) {

            $pdo->rollBack();
        }

        if( $res ) {
            $_SESSION['success_message'] = 'ルームを作成しました。';
        } else {
            $error_message[] = 'ルームを作成できませんでした';
        }

        // プリペアドステートメントを削除
        $stmt = null;

        header('Location: ./room.php');
        exit;
	}
}

if( !empty($pdo) ) {

    // メッセージのデータを取得する
    $sql = "SELECT * FROM room ORDER BY create_date DESC";
    $rooms = $pdo->query($sql);
}

// データベースの接続を閉じる
$pdo = null;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ルーム</title>
<style>
    <?php require("./main.css"); ?>
</style>
</head>
<body>
<h1>ルーム</h1>

<?php if( !empty($error_message) ): ?>
    <ul class="error_message">
		<?php foreach( $error_message as $value ): ?>
            <li>・<?php echo $value; ?></li>
		<?php endforeach; ?>
    </ul>
<?php endif; ?>
<!-- 新規ルーム作成　-->
<form method="post">
	<div>
		<label for="room_name">ルーム名</label>
		<input id="room_name" type="text" name="room_name">
	</div>
	<div>
		<label for="description">ルームのデスクリプション</label>
		<textarea id="description" name="description"></textarea>
	</div>
	<input type="submit" name="btn_submit" value="新規作成">
</form>

<!-- 全てのルームを表示する　-->
<section>
    <?php if( !empty($rooms) ){ ?>
    <?php foreach( $rooms as $value ){ ?>
    <article>
        <div class="info">
            <p><a href="./room_message.php?room_id=<?php echo $value['id']; ?>"><?php echo $value["name"]; ?></a></p>
            <time><?php echo date('Y年m月d日', strtotime($value['create_date'])); ?></time>
        </div>
        <p><?php echo $value["description"]; ?></p>
    </article>
    <?php } ?>
    <?php } ?>
</section>
</body>
</html>