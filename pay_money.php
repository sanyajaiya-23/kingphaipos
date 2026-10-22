<?php //http://localhost/index.php
require($_SERVER['DOCUMENT_ROOT']."/inc/db/db_webdev.php"); ?>
<?php
require($_SERVER['DOCUMENT_ROOT']."/inc/auth.php");
kp_require_login(); // lockout 4 ชม.
kp_require_admin(); // เฉพาะ Admin
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
  <link rel="stylesheet" href="css/fixtable.css">
  <link rel="manifest" href="manifest.json">
  <link rel="apple-touch-icon" sizes="180x180" href="img/icon_180.png?v=2">

  
</head>
<body>
<?php
$id=$_REQUEST['id'];
$buy_name=$_REQUEST['buy_name'];
$money_buy=$_REQUEST['money_buy'];
$comment=$_REQUEST['comment'];
$action=$_REQUEST['action'];
?>

<form action="save_money_out.php" method="POST">
<div class="bg-dark min-vh-100 d-flex justify-content-center align-items-center">

      <div class="card shadow-lg border border-primary" style="width: 50rem;">
      <div class="card-header ">
      <div class="form-inline d-flex justify-content-between">
      <h2 class="text-info">บันทึกรายจ่าย</h2>
        <a href="index2.php" class="btn btn-outline-success"><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>Exit</a>
      </div>
      </div>
        <div class="card-body ">
        

        <?php
  date_default_timezone_set('Asia/Bangkok');
  $days =['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
  $months=[1=>'มกราคม','กุมภาพันธ์','มีนาคม','เมษยน','พฤษภาคม','มิถุนายน','กรกฏาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
  $d=date('w');
  $day=$days[$d];
  $date=date('j');
  $m=date('n');
  $month=$months[$m];
  $year=date('Y')+543;
  $time=date('H:i:s');
?>
<b class="text-dark"><?php echo "วัน$day ที่ $date เดือน$month พ.ศ $year";?></b>
<hr class="border-success">
<br>
<br>
<input type="hidden" name="id" value="<?php echo$id;?>">
<input type="hidden" name="action" value="<?php echo$action;?>">
  <div class="form-group">
    <label for="buy">รายการซื้อ</label>
    <input type="text" value="<?php echo$buy_name;?>"name="buy"class="form-control" id="buy">
  </div>
  <div class="form-group">
    <label for="money_buy">จำนวนเงิน</label>
    <input type="text" value="<?php echo$money_buy;?>"name="money_buy"class="form-control" id="money_buy">
  </div>
  <div class="form-group">
    <label for="comment_buy">รายละเอียดเพิ่มเติม</label>
    <input type="text" value="<?php echo$comment;?>"name="comment_buy"class="form-control" id="comment_buy">
  </div>

  
        </div>
        <div class="card-footer">
        
        <button type="submit" class="btn btn-outline-info"><i class="fa fa-sign-in" aria-hidden="true"></i>บันทึก</button>

        </div>
      </div>
   
  </div>
</form>
<script src="/lib/jquery-3.5.1.min.js"></script>
  <script src="/lib/bootstrap-4.5.2-dist/js/bootstrap.min.js"></script>
  <script src="/lib/popper.min.js"></script>
<script src="/json/kp_time_hm.js?v=1"></script>
<script src="/json/kp_datepicker.js?v=1"></script>
</body>
</html>
