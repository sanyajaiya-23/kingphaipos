<?php //http://localhost/index.php
require($_SERVER['DOCUMENT_ROOT']."/inc/db/db_webdev.php"); ?>
<?php
require($_SERVER['DOCUMENT_ROOT']."/inc/auth.php");
kp_require_login(); // lockout 4 ชม.
kp_require_admin(); // Kingphai App for Tap: เฉพาะ Admin
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>KINGPHAI.com</title>
  <link rel="stylesheet" href="/lib/bootstrap-4.5.2-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lib/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/popup_glass.css?v=4">
  <link rel="manifest" href="manifest.json">
  <link rel="apple-touch-icon" sizes="180x180" href="img/icon_180.png?v=2">
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col">
    <form action="save_comment.php" method="post">
    <div class="card mt-2">
  <div class="card-header">
    <div class="form-inline form-horizontal d-flex justify-content-between">
    <?php require($_SERVER['DOCUMENT_ROOT']."/modal.php");?>
    <h4><i class="fa fa-cubes" aria-hidden="true"></i>
บันทึกการขาย กรณีพิเศษ</h4>

    <a href="index2.php" class="btn btn-outline-info"><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>Exit</a>
    </div>
  </div>
  <div class="card-body">
  <div class="form-group">
    <label for="FormControlTextarea1">บันทึกสำคัญ</label>
<?php
$sql="
SELECT
  tb_sell_note.text_sell
FROM
  tb_sell_note
LIMIT 1
";
$db->query("
  CREATE TABLE IF NOT EXISTS tb_sell_note (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    text_sell TEXT NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8
");
// ensure 1 row
$r = $db->select("SELECT id FROM tb_sell_note ORDER BY id ASC LIMIT 1");
if (!is_array($r) || !isset($r[0]) || !isset($r[0]['id'])) {
  $db->query("INSERT INTO tb_sell_note (text_sell) VALUES ('')");
}
$data=$db->select($sql);
foreach($data AS $row){
  echo"
  <textarea class='form-control' id='FormControlTextarea1' rows='8' name='text_form'>{$row['text_sell']}</textarea>
  ";
}
?>

  </div>
  </div>
  <div class="card-footer">
  <?php
  if($_SESSION['id_status']==1){
    echo"
     <button type='submit'class='btn btn-outline-success'><i class='fa fa-download' aria-hidden='true'></i>บันทึก</button>
    ";
  }
  ?>
 
    

  </div>
</div>
</form>
    </div>
  </div>
</div>
<script src="/lib/jquery-3.5.1.min.js"></script>
  <script src="/lib/bootstrap-4.5.2-dist/js/bootstrap.min.js"></script>
  <script src="/lib/popper.min.js"></script>
<script src="/json/kp_time_hm.js?v=1"></script>
<script src="/json/kp_datepicker.js?v=1"></script>
</body>
</html>
