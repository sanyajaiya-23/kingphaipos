<?php //http://localhost/index.php
require($_SERVER['DOCUMENT_ROOT']."/inc/db/db_webdev.php");
require($_SERVER['DOCUMENT_ROOT']."/inc/auth.php");
kp_require_login();
kp_require_admin(); // บันทึกรายจ่าย = การเงิน เฉพาะแอดมิน
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Document</title>
</head>
<body>
  <?php
  $action=$_REQUEST['action'];
  $id=$_REQUEST['id'];
  $buy=$_REQUEST['buy'];
  $money_buy=$_REQUEST['money_buy'];
  $comment_buy=$_REQUEST['comment_buy'];
if($action=='edit'){
  $sql="
  UPDATE tb_money_out SET
  buy_name='{$buy}',
  money_buy='{$money_buy}',
  comment='{$comment_buy}'
  WHERE
    tb_money_out.id='{$id}'
  ";
  $db->query($sql);
  header("location:report_money_out.php?search_date&search_name=");
}else if($action=='delete'){
  $sql="
  DELETE FROM tb_money_out WHERE tb_money_out.id='{$id}'
  ";
  $db->query($sql);
  header("location:report_money_out.php?search_date&search_name=");
}else {
$sql="
    INSERT INTO tb_money_out(date_report,time_report,buy_name,money_buy,comment)VALUES(
      CURDATE(),CURTIME(),'$buy','$money_buy','$comment_buy')
    ";
    $db->query($sql);
  header("location:report_money_out.php?search_date&search_name=");
}
    
  ?>
</body>
</html>