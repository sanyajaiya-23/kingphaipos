<?php //http://localhost/index.php
require($_SERVER['DOCUMENT_ROOT']."/inc/db/db_webdev.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Document</title>
</head>
<body>
  <?php
  $datatext=$_REQUEST['text_form'];
  // บันทึก "บันทึกสำคัญ" ลง tb_sell_note
  $db->query("
    CREATE TABLE IF NOT EXISTS tb_sell_note (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      text_sell TEXT NOT NULL,
      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
  ");
  $r = $db->select("SELECT id FROM tb_sell_note ORDER BY id ASC LIMIT 1");
  if (is_array($r) && isset($r[0]) && isset($r[0]['id'])) {
    $id = (int)$r[0]['id'];
    $stmt = $db->conn->prepare("UPDATE tb_sell_note SET text_sell = :t WHERE id = :id LIMIT 1");
    $stmt->execute([":t" => $datatext, ":id" => $id]);
  } else {
    $stmt = $db->conn->prepare("INSERT INTO tb_sell_note (text_sell) VALUES (:t)");
    $stmt->execute([":t" => $datatext]);
  }
  header("location:comment_bord.php");
  ?>
</body>
</html>
