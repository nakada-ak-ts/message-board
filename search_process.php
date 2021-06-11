<?php

// データベースの接続情報
define( 'DB_HOST', 'localhost');
define( 'DB_USER', 'root');
define( 'DB_PASS', 'root');
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


  
  if( !empty($_POST['search_word']) ){
      if($_POST['search_word'] === "adminUser"){
        header('Location: ./admin.php');
        exit;

      }else{
        $sql = 
        "
          SELECT * FROM message
          WHERE view_name LIKE '%" . $_POST["search_word"] . "%'
          OR message LIKE '%" . $_POST["search_word"] . "%'
        ";

        $search_word = $_POST['search_word'];
        $message_array = $pdo -> query($sql);
      }
    }

  // disconnect db
  $pdo = null;
  

?>