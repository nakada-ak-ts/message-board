<?php

var_dump($_POST);

if( !empty($_POST['btn_alert'])){

  if( !empty($_POST['alert_message']) ){

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

      header('Location: ./');
      exit;
      }
  }

$pdo = null;

?>

