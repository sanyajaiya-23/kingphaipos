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
<link rel="stylesheet" href="css/fixtable.css">
<link rel="manifest" href="manifest.json">
<link rel="apple-touch-icon" sizes="180x180" href="img/icon_180.png?v=2">
</head>
<body>
<?php
$search=$_REQUEST['search'];
$search_select=$_REQUEST['search_select'];
$view = (isset($_GET['view']) && $_GET['view'] === 'branch') ? 'branch' : 'customer';
?>
  <div class="container-fluid">
    <div class="row">
      <div class="col">

      <form action="" method="post">
      <div class="card mt-3">
  <div class="card-header">
          <div class="form-inline d-flex justify-content-between">
          <div class="d-flex justify-content-start">
          <?php require($_SERVER['DOCUMENT_ROOT']."/modal.php");?>
          <label class="ml-1">ค้นหา :</label>
          <input type="text" name="search" class="form-control col-sm-2 ml-1">
          <select class="form-control ml-2" name="search_select">
            <option value="">เลือก</option>
            <?php
            $sql="
    SELECT
      tb_adress.id_adress,tb_adress.name_adress AS name_ad
    FROM
      tb_adress
    ";
    $data=$db->select($sql);
    foreach($data AS $row){
      if($search_select==$row['id_adress']){
        $selected="selected";
      }else{
        $selected="";
      }
      echo"
      <option value='{$row['id_adress']}'$selected>{$row['name_ad']}</option>
      ";
    }
            ?>
          </select>
          <button type="submit" class="btn btn-outline-success ml-1"><i class="fa fa-search" aria-hidden="true"></i>
</button> 
<?php if ($view === 'branch') { ?>
<a href="person_form_insert.php?tab=branch" class="btn btn-success ml-1" title="เพิ่มสาขา">+</a>
<?php } else { ?>
<a href="person_form_insert.php?search=&search_select=&id_person=&name_person=&name_adress=&action=" class="btn btn-success ml-1" title="เพิ่มลูกค้า">+</a>
<?php } ?>
</div>

<a href="index2.php" class="btn btn-outline-info "><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>
Exit
</a>
        </div>

        
  </div>
  <ul class="nav nav-tabs px-3 pt-2">
    <li class="nav-item"><a class="nav-link<?php echo $view==='customer'?' active':''; ?>" href="name_story.php?search=&search_select=">รายชื่อลูกค้า</a></li>
    <li class="nav-item"><a class="nav-link<?php echo $view==='branch'?' active':''; ?>" href="name_story.php?view=branch">รายชื่อสาขา</a></li>
  </ul>
<?php if ($view === 'branch') {
  $branchRows = array();
  try { $branchRows = $db->conn->query("SELECT id, code, name, address, phone, is_main, active, lat, lng FROM tb_branch ORDER BY is_main DESC, name")->fetchAll(PDO::FETCH_ASSOC); } catch (Throwable $eB) {}
  $isAdminB = isset($_SESSION['id_status']) && (int)$_SESSION['id_status'] === 1;
?>
  <div class="card-body">
    <div class="form-inline d-flex justify-content-between">
      <label>รายชื่อสาขา</label>
      <div class="d-flex justify-content-end"><label>จำนวนสาขา:</label>
        <input type="text" value="<?php echo count($branchRows); ?>" class="form-control col-sm-3 ml-1" readonly></div>
    </div>
    <div class="table-wrapper">
      <table class="table table-hover table-sm">
        <thead class="text-center"><tr><th>ลำดับที่</th><th>รหัส</th><th>สาขา</th><th>ที่อยู่</th><th>โทร</th><th>พิกัด</th><th>รายการ</th></tr></thead>
        <tbody class="text-center">
        <?php $nb = 0; foreach ($branchRows as $b) { $nb++; $h = function($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }; ?>
          <tr<?php echo ((int)$b['active'] === 1) ? '' : ' class="text-muted"'; ?>>
            <td><?php echo $nb; ?></td>
            <td><?php echo $h($b['code']); ?></td>
            <td><?php echo $h($b['name']); ?><?php echo ((int)$b['is_main'] === 1) ? ' <span class="badge badge-success">หลัก</span>' : ''; ?><?php echo ((int)$b['active'] === 1) ? '' : ' <span class="badge badge-secondary">ปิด</span>'; ?></td>
            <td><?php echo $h($b['address']); ?></td>
            <td><?php echo $h($b['phone']); ?></td>
            <td><?php echo ($b['lat'] !== null && $b['lng'] !== null) ? $h($b['lat']) . ', ' . $h($b['lng']) : '<span class="text-danger">ยังไม่ระบุ</span>'; ?></td>
            <td><?php if ($isAdminB) { ?><a href="person_form_insert.php?tab=branch&edit=<?php echo (int)$b['id']; ?>" class="btn btn-outline-warning"><i class="fa fa-pencil" aria-hidden="true"></i></a><?php } ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
<?php } else { ?>
  <div class="card-body">
    <div class="form-inline d-flex justify-content-between">
  <label>รายชื่อลูกค้า</label>
  <div class="d-flex justify-content-end">
  <label >จำนวนลูกค้า:</label>
    <?php
    $sql="
    SELECT COUNT(id_person)
    FROM tb_person
    WHERE
    tb_person.id_person>0
   AND tb_person.name_person LIKE '%{$search}%'
   AND tb_person.id_adress LIKE '%{$search_select}%'
   
  ";
    $data = $db->select($sql);
    foreach($data AS $row){
      if($row['COUNT(id_person)']){
        $total_price_m=$row['COUNT(id_person)'];
      }else{
        $total_price_m=0;
      }
      echo "
      <input type='text' value='{$total_price_m}' name='sum_plus' class='form-control col-sm-3 ml-1'readonly>
      ";}
    ?>
    </div>
</div>
  <div class="table-wrapper"> 
    <table class="table table-hover table-sm">
  <thead class="text-center" >
    <tr>
      <th>ลำดับที่</th>
      <th>รหัส</th>
      <th>ลูกค้า</th>
      <th>ที่อยู่</th>
      <th>รายการ</th>
    </tr>
  </thead>
  <tbody class="text-center">
  <?php
  $sql="
  SELECT
  tb_person.id_person,tb_person.name_person,tb_person.id_adress,tb_person.name_adress,tb_person.cr_box,tb_person.cr_bigbox,tb_person.bt_loss,tb_person.m_cr_box,tb_person.m_cr_bigbox,tb_person.m_cr,tb_person.check_status
  FROM
    tb_person 
  WHERE
    tb_person.id_person>0
   AND tb_person.name_person LIKE '%{$search}%'
   AND tb_person.id_adress LIKE '%{$search_select}%'
    
  ";
  $data = $db->select($sql);
  $num=0;
  foreach($data as $value){
    if($_SESSION['id_status']==1){

      $num++;
      echo"
      <tr>
      <td>{$num}</td>
      <td>{$value['id_person']}</td>
      <td>{$value['name_person']}</td>
      <td>{$value['name_adress']}</td>
      <td><a href='person_form_insert.php?id_person={$value['id_person']}&name_person={$value['name_person']}&id_adress={$value['id_adress']}&name_adress={$value['name_adress']}&action=edit' class='btn btn-outline-warning'><i class='fa fa-pencil' aria-hidden='true'></i>
      </a>
      <a href='save_person.php?id_person={$value['id_person']}&name_person={$value['name_person']}&id_adress={$value['id_adress']}&name_adress={$value['name_adress']}&action=delete'class='btn btn-outline-danger'><i class='fa fa-trash-o' aria-hidden='true'></i>
      </a>
      </td>
      </tr>
      ";
    }else{
      $num++;
      echo"
      <tr>
      <td>{$num}</td>
      <td>{$value['id_person']}</td>
      <td>{$value['name_person']}</td>
      <td>{$value['name_adress']}</td>
      <td><a href='person_form_insert.php?id_person={$value['id_person']}&name_person={$value['name_person']}&id_adress={$value['id_adress']}&name_adress={$value['name_adress']}&action=edit' class='btn btn-outline-warning'><i class='fa fa-pencil' aria-hidden='true'></i>
      </a>
      </td>
      </tr>
      ";
    }
  }?>
  </tbody>
</table>
  </div>
  </div>
<?php } ?>

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
