<?php
  require('./search_process.php');
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ひと言掲示板｜検索結果</title>
<style>
  <?php
    require('./main.css');
  ?>

  .menubar{
    margin: 0 0 20px 0;
  }
  
</style>
</head>
<body>
<h1>ひと言掲示板｜検索結果</h1>

<div class="menubar">
  <a href="./index.php">top</a>
</div>

  <h2>検索ワード【 <?php echo $search_word ?> 】</h2>

  <?php if(!empty($message_array)){ ?>
    <?php foreach( $message_array as $value){ ?>
    <!-- message -->
      <article>
        <div class="info">
          <h2><?php echo htmlspecialchars($value['view_name'],ENT_QUOTES,'UTF-8');?></h2>
          <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date']));?></time>
        </div>
        <p>
          <?php echo nl2br(htmlspecialchars($value['message'],ENT_QUOTES,'UTF-8')); ?>
        </p>
        <form method="post" action="alert_process.php">
          <input type="hidden" name="alert_message" value="<?php if( !empty($value['id']) ){ echo htmlspecialchars( $value['id'], ENT_QUOTES, 'UTF-8'); } ?>">
          <input type="submit" name="btn_alert" value="通報">
        </form>
      </article>
    <?php } ;?>
  <?php }; ?>
</section>
</body>
</html>
