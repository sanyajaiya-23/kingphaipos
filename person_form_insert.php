<?php 
require($_SERVER['DOCUMENT_ROOT']."/inc/db/db_webdev.php"); ?>
<?php
require($_SERVER['DOCUMENT_ROOT']."/inc/auth.php");
kp_require_login(); // lockout 4 ชม.
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
<?php
$action=$_REQUEST['action'];
$id_person=$_REQUEST['id_person'];
$name_person=$_REQUEST['name_person'];
$name_adress=$_REQUEST['name_adress'];
$isAdminUser = isset($_SESSION["id_status"]) && (int)$_SESSION["id_status"] === 1;
$tab = (isset($_GET['tab']) && $_GET['tab'] === 'branch' && $isAdminUser) ? 'branch' : 'customer';
?>
  <div class="container-fluid">
    <div class="row">
      <div class="col">
      <div class="bg-dark min-vh-100 d-flex justify-content-center align-items-center">
      <div class="card" style="width: 30rem;">
  <div class="card-header d-flex justify-content-between">
  
    <h4>เพิ่มรายชื่อ</h4>
    <a href="index2.php" class="btn btn-outline-danger"><i class="fa fa-arrow-circle-o-left" aria-hidden="true">Exit</i>
</a>
  </div>
  <ul class="nav nav-tabs px-2 pt-2">
    <li class="nav-item"><a class="nav-link<?php echo $tab==='customer'?' active':''; ?>" href="#" data-tab="customer">เพิ่มรายชื่อลูกค้า</a></li>
    <?php if ($isAdminUser) { ?><li class="nav-item"><a class="nav-link<?php echo $tab==='branch'?' active':''; ?>" href="#" data-tab="branch">เพิ่มรายชื่อสาขา</a></li><?php } ?>
  </ul>
  <div id="paneCustomer" style="<?php echo $tab==='customer'?'':'display:none'; ?>">
  <form action="save_person.php" method="post">
  <input type="hidden" name="action" value="<?php echo $action;?>">
  <input type="hidden" name="id_person" value="<?php echo $id_person;?>">
  <div class="card-body">
    <div class="form-groub">
    <label>ชื่อลูกค้า :</label>
    <input type="text" class="form-control" value="<?php echo$name_person;?>" name="name_person">
    </div>
    <div class="form-groub">
    <label>เลือกกลุ่ม :</label>
    <select class="form-control"name="id_adress">
      <option value="">เลือก</option>
      <?php
    $sql="
    SELECT
      tb_adress.id_adress,tb_adress.name_adress
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
      <option value='{$row['id_adress']}'$selected>{$row['name_adress']}</option>
      ";
    }
    ?>
    </select>
    </div>
    <div class="form-groub">
    <label>ชื่อหมู่บ้าน :</label>
    <input type="text" class="form-control" value="<?php echo$name_adress;?>" name="name_adress">
    </div>
    </div>
  <div class="card-footer text-muted text-center">
    
    <button type="submit" class="btn btn-outline-info">บันทึก</button>
  </div>
  </form>
  </div>
  <?php if ($isAdminUser) { ?>
  <div id="paneBranch" style="<?php echo $tab==='branch'?'':'display:none'; ?>">
    <form id="branchForm">
      <input type="hidden" name="id" value="">
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-4"><label>รหัสสาขา :</label><input class="form-control" name="code" placeholder="เช่น B02" required></div>
          <div class="form-group col-8"><label>ชื่อสาขา :</label><input class="form-control" name="name" required></div>
        </div>
        <div class="form-group"><label>ที่อยู่ :</label><input class="form-control" name="address"></div>
        <div class="form-group"><label>โทร :</label><input class="form-control" name="phone"></div>
        <div class="form-row">
          <div class="form-group col-6"><label>ละติจูด :</label><input class="form-control" name="lat" inputmode="decimal" placeholder="13.7563"></div>
          <div class="form-group col-6"><label>ลองจิจูด :</label><input class="form-control" name="lng" inputmode="decimal" placeholder="100.5018"></div>
        </div>
        <button type="button" id="btnGeo" class="btn btn-sm btn-outline-secondary mb-2">ใช้ตำแหน่งปัจจุบันของเครื่องนี้</button>
        <small class="text-muted d-block">ที่ตั้งนี้จะแสดงเป็นหมุดสาขาบนแผนที่ (คัดลอกพิกัดจาก Google Maps ได้)</small>
      </div>
      <div class="card-footer text-center">
        <button type="submit" class="btn btn-outline-info">บันทึกสาขา</button>
        <button type="button" id="btnBrReset" class="btn btn-outline-secondary" style="display:none">ยกเลิกแก้ไข</button>
      </div>
    </form>
    <div class="px-3 pb-3">
      <h6 class="mt-2">รายชื่อสาขา</h6>
      <div id="branchList" class="list-group"></div>
    </div>
  </div>
  <?php } ?>
</div>
      </div>
    </div>
  </div>
  </div>
<script>
(function(){
  document.querySelectorAll("[data-tab]").forEach(function(a){
    a.addEventListener("click",function(e){
      e.preventDefault();
      var t=a.getAttribute("data-tab");
      document.querySelectorAll("[data-tab]").forEach(function(x){x.classList.toggle("active",x===a);});
      var c=document.getElementById("paneCustomer"), b=document.getElementById("paneBranch");
      if(c) c.style.display=(t==="customer")?"":"none";
      if(b) b.style.display=(t==="branch")?"":"none";
    });
  });
  var f=document.getElementById("branchForm"); if(!f) return;
  function esc(v){return String(v==null?"":v).replace(/[&<>"]/g,function(c){return {"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;"}[c];});}
  function post(fd){return fetch("json/branch.php",{method:"POST",body:fd,headers:{"X-Requested-With":"XMLHttpRequest"}}).then(function(r){return r.json();});}
  var rows=[];
  function resetForm(){f.reset();f.id.value="";document.getElementById("btnBrReset").style.display="none";}
  function load(){
    var fd=new FormData(); fd.append("action","all");
    post(fd).then(function(j){
      rows=j.branches||[];
      document.getElementById("branchList").innerHTML=rows.map(function(b){
        return '<div class="list-group-item d-flex justify-content-between align-items-center'+(+b.active?'':' text-muted')+'">'+
          '<div><b>'+esc(b.name)+'</b> <small>('+esc(b.code)+')</small>'+(+b.is_main?' <span class="badge badge-success">หลัก</span>':'')+(+b.active?'':' <span class="badge badge-secondary">ปิด</span>')+
          '<br><small>'+(b.lat!=null&&b.lng!=null?'📍 '+esc(b.lat)+', '+esc(b.lng):'<span class="text-danger">ยังไม่ระบุพิกัด</span>')+'</small></div>'+
          '<div><button class="btn btn-sm btn-outline-warning ed" data-id="'+b.id+'">แก้ไข</button>'+(+b.is_main?'':' <button class="btn btn-sm btn-outline-danger del" data-id="'+b.id+'">ลบ</button>')+'</div></div>';
      }).join("")||'<div class="text-muted">ยังไม่มีสาขา</div>';
      var q=new URLSearchParams(location.search).get("edit");
      if(q && !load.done){ load.done=true; var btn=document.querySelector('#branchList .ed[data-id="'+q+'"]'); if(btn) btn.click(); }
    });
  }
  f.addEventListener("submit",function(e){
    e.preventDefault();
    var fd=new FormData(f); fd.append("action","save");
    post(fd).then(function(j){ if(!j.ok){alert(j.error||"บันทึกไม่สำเร็จ");return;} resetForm(); load(); });
  });
  document.getElementById("btnBrReset").addEventListener("click",resetForm);
  document.getElementById("btnGeo").addEventListener("click",function(){
    if(!navigator.geolocation){alert("เครื่องนี้ไม่รองรับระบุตำแหน่ง");return;}
    navigator.geolocation.getCurrentPosition(function(p){f.lat.value=p.coords.latitude.toFixed(7);f.lng.value=p.coords.longitude.toFixed(7);},
      function(){alert("อ่านตำแหน่งไม่ได้ (ต้องอนุญาตตำแหน่ง และใช้ https หรือ localhost)");},{enableHighAccuracy:true,timeout:15000});
  });
  document.getElementById("branchList").addEventListener("click",function(e){
    var id=e.target.getAttribute("data-id"); if(!id) return;
    if(e.target.classList.contains("ed")){
      var b=rows.filter(function(r){return String(r.id)===String(id);})[0]; if(!b) return;
      f.id.value=b.id;f.code.value=b.code||"";f.name.value=b.name||"";f.address.value=b.address||"";f.phone.value=b.phone||"";
      f.lat.value=b.lat==null?"":b.lat;f.lng.value=b.lng==null?"":b.lng;
      document.getElementById("btnBrReset").style.display="";
    } else if(e.target.classList.contains("del")){
      if(!confirm("ลบสาขานี้?")) return;
      var fd=new FormData(); fd.append("action","delete"); fd.append("id",id);
      post(fd).then(function(j){ if(!j.ok) alert(j.error||"ลบไม่สำเร็จ"); load(); });
    }
  });
  load();
})();
</script>
</body>
</html>
