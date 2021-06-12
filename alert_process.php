<?php

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

if( !empty($_POST['btn_alert']) && !empty($_POST['alert_message']) ){
  $sql = "SELECT message_id FROM alert_message WHERE message_id = ".$_POST['alert_message'];
  $alert_message_id = $pdo->query($sql);
  foreach( $alert_message_id as $value);

  // すでに通報されている場合は、通報のコメントのみ表示する
  if(  $_POST['alert_message'] !== $value[0] ){

    $current_date = date("Y-m-d H:i:s");

      // トランザクション開始
      $pdo->beginTransaction();

      try {

          // SQL作成
          $stmt = $pdo->prepare("INSERT INTO alert_message (message_id, post_date) VALUES ( :message_id, :current_date)");

          // 値をセット
          $stmt->bindValue( ':message_id', $_POST['alert_message'], PDO::PARAM_STR);
          $stmt->bindValue( ':current_date', $current_date, PDO::PARAM_STR);

          // SQLクエリの実行
          $res = $stmt->execute();

          // コミット
          $res = $pdo->commit();

      } catch(Exception $e) {

          // エラーが発生した時はロールバック
          $pdo->rollBack();
      }
      

      if( $res ) {
        $_SESSION['success_message'] = '通報しました。';
      } else {
        $error_message[] = '通報に失敗しました。';
      }

      // プリペアドステートメントを削除
      $stmt = null;

      header('Location: ./index.php');
      exit;

    }else{
      $_SESSION['success_message'] = '通報しました。';
      header('Location: ./index.php');
      exit;
    }
}


// 通報DBから削除
if( !empty($_POST['btn_alert_delete']) ){
  // トランザクション開始
  $pdo->beginTransaction();

  try {

      // SQL作成
      $alert_stmt = $pdo->prepare("DELETE FROM alert_message WHERE message_id = :delete_alert_message_id");

      // 値をセット
      $alert_stmt->bindValue( ':delete_alert_message_id', $_POST['alert_message_delete'], PDO::PARAM_INT);

      // SQLクエリの実行
      $alert_stmt->execute();

      // コミット
      $res = $pdo->commit();

  } catch(Exception $e) {

      // エラーが発生した時はロールバック
      $pdo->rollBack();
  }

  // 削除に成功したら一覧に戻る
  if( $res ) {
      header("Location: ./alert.php");
      exit;
  }
}

$pdo = null;

?>

