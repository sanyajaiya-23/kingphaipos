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
  $search_date=$_REQUEST['search_date'];
  $search_name=$_REQUEST['search_name'];

?>
<form>
  <div clas="container-fluid">
    <div class="row">
      <div class="col">
      <div class="card border-success mt-3">
  <div class="card-header border-success">
  <div class="form-inline d-flex justify-content-between">
  <div class="form-row align-items-center d-flex justify-content-start">
  <?php require($_SERVER['DOCUMENT_ROOT']."/modal.php");?>
  <lable class="ml-1 mr-2">ค้นหา:</lable>
      <input type="date" name="search_date" value="<?php echo$search_date;?>"class="form-control">
      <lable class="mr-2 ml-2">ชื่อ:</lable>
      <input type="text" name="search_name" value="<?php echo$search_name;?>"class="form-control">
      <button type="submit" class="btn btn-success ml-1"><i class="fa fa-search-plus" aria-hidden="true"></i></button>
      </div>
  <div class="d-flex justify-content-end">
  
     <a href="pay_money.php?id=&buy_name=&money_buy=&comment=&action=" class="btn btn-outline-info mr-1"><i class="fa fa-plus" aria-hidden="true"></i>
ซื้อเพิ่ม</a>
     <a href="index2.php" class="btn btn-outline-success"><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>Exit</a>
    </div>
    </div>
  </div>

  <div class="card-body text-success">
  <div class="form-inline d-flex justify-content-between">
  <h4>รายจ่าย</h4>
  <div class="d-flex justify-content-end">
  <?php
    $sql="
    SELECT SUM(money_buy)
    FROM tb_money_out
    WHERE 
    tb_money_out.id>0
    AND tb_money_out.date_report LIKE '%{$search_date}%'
    AND tb_money_out.buy_name LIKE '%{$search_name}%'
  ";
    $data = $db->select($sql);
    foreach($data AS $row){
         if($row['SUM(money_buy)']){
        $total_price_m=$row['SUM(money_buy)'];
      }else{
        $total_price_m=0;
      }
      echo"
      <label class='mr-1'>สรุปรายจ่าย:</label>
      <input type='text' value='{$total_price_m}' name='sum_money_out' class='form-control col-sm-3 'readonly>
      <label class='ml-1 mr-2'>บาท</label>
      ";
    }
    ?>
    </div>
    </div>
  <div class="table-wrapper mt-2">
  <table class="table table-hover table-sm ">
  <thead class="text-center" >
    <tr>
      <th>ลำดับที่</th>
      <th>วันที่</th>
      <th>เวลา</th>
      <th>รายการ</th>
      <th>ราคา</th>
      <th>รายละเอียด</th>
      <th>เพิ่มเติม</th>
    </tr>
  </thead>
  <tbody class="text-center">
  <?php
  $sql="
  SELECT
  tb_money_out.id,tb_money_out.date_report,tb_money_out.time_report,tb_money_out.buy_name,tb_money_out.money_buy,tb_money_out.comment
  FROM
    tb_money_out
  WHERE
  tb_money_out.id>0
   AND tb_money_out.buy_name LIKE '%{$search_name}%'
   AND tb_money_out.date_report LIKE '%{$search_date}%'
  ";
  $data = $db->select($sql);
  $num=0;
  foreach($data as $value){
  $num++;
  echo"
  <tr>
  <td>{$num}</td>
  <td>{$value['date_report']}</td>
  <td>{$value['time_report']}</td>
  <td>{$value['buy_name']}</td>
  <td>{$value['money_buy']}</td>
  <td>{$value['comment']}</td>
  <td><a href='pay_money.php?id={$value['id']}&buy_name={$value['buy_name']}&money_buy={$value['money_buy']}&comment={$value['comment']}&action=edit' class='btn btn-outline-warning'><i class='fa fa-pencil' aria-hidden='true'></i>
      </a>
      <a href='save_money_out.php?id={$value['id']}&buy={$value['buy_name']}&money_buy={$value['money_buy']}&comment_buy={$value['comment']}&action=delete'class='btn btn-outline-danger'><i class='fa fa-trash-o' aria-hidden='true'></i>
      </a>
      </td>
  </tr>
  ";
  }?>
  </tbody>
</table>

  </div>

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
