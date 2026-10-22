<?php
// จัดการสาขา + กำหนดว่าพนักงานคนไหนเข้าสาขาไหนได้ (แอดมินเท่านั้น)
require($_SERVER['DOCUMENT_ROOT'] . "/inc/db/db_webdev.php");
require($_SERVER['DOCUMENT_ROOT'] . "/inc/auth.php");
kp_require_login();
kp_require_admin();
?>
<!DOCTYPE html>
<html lang="th"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>จัดการสาขา</title>
<link rel="stylesheet" href="/lib/bootstrap-4.5.2-dist/css/bootstrap.min.css">
</head><body class="p-3" style="background:#f3f5f7">
<div class="container" style="max-width:820px">
  <a href="select_page.php">&larr; กลับเมนู</a>
  <h4 class="mt-2">สาขา</h4>
  <table class="table table-sm bg-white"><thead><tr><th>รหัส</th><th>ชื่อ</th><th>ที่อยู่</th><th>โทร</th><th>ใช้งาน</th><th></th></tr></thead><tbody id="rows"></tbody></table>
  <h6>เพิ่มสาขา</h6>
  <form id="f" class="form-inline mb-4">
    <input class="form-control mr-2 mb-2" name="code" placeholder="รหัส เช่น B02" required style="width:110px">
    <input class="form-control mr-2 mb-2" name="name" placeholder="ชื่อสาขา" required>
    <input class="form-control mr-2 mb-2" name="address" placeholder="ที่อยู่">
    <input class="form-control mr-2 mb-2" name="phone" placeholder="โทร" style="width:130px">
    <button class="btn btn-success mb-2">เพิ่ม</button>
  </form>
  <h4>พนักงาน &harr; สาขาที่เข้าได้</h4>
  <p class="text-muted small">แอดมินเห็นทุกสาขาอยู่แล้ว · พนักงานที่ไม่ได้ติ๊กสาขาใดเลยจะเข้าได้เฉพาะสาขาหลัก · ติ๊กหลายสาขา = สลับสาขาได้</p>
  <div id="staff" class="bg-white p-2"></div>
</div>
<script>
function esc(s){return String(s==null?"":s).replace(/[&<>"]/g,function(c){return {"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;"}[c];});}
function post(d){var fd=new FormData();for(var k in d){[].concat(d[k]).forEach(function(v){fd.append(Array.isArray(d[k])?k+"[]":k,v);});}
  return fetch("json/branch.php",{method:"POST",body:fd,headers:{"X-Requested-With":"XMLHttpRequest"}}).then(function(r){return r.json();});}
var BR=[];
function load(){
  post({action:"all"}).then(function(j){BR=j.branches||[];
    document.getElementById("rows").innerHTML=BR.map(function(b){return '<tr data-id="'+b.id+'"><td><input class="form-control form-control-sm" data-k="code" value="'+esc(b.code)+'"></td><td><input class="form-control form-control-sm" data-k="name" value="'+esc(b.name)+'"></td><td><input class="form-control form-control-sm" data-k="address" value="'+esc(b.address)+'"></td><td><input class="form-control form-control-sm" data-k="phone" value="'+esc(b.phone)+'"></td><td><input type="checkbox" data-k="active" '+(+b.active?'checked':'')+'></td><td><button class="btn btn-sm btn-primary sv">บันทึก</button></td></tr>';}).join("");
    return post({action:"staff"});
  }).then(function(j){
    document.getElementById("staff").innerHTML=(j.staff||[]).map(function(u){
      return '<div class="border-bottom py-2" data-uid="'+u.id_admin+'"><b>'+esc(u.name||u.user)+'</b> <small class="text-muted">('+esc(u.user)+(+u.id_status===1?' · แอดมิน':'')+')</small><br>'+
        BR.filter(function(b){return +b.active;}).map(function(b){return '<label class="mr-3"><input type="checkbox" value="'+b.id+'" '+(u.branches.indexOf(+b.id)>=0?'checked':'')+'> '+esc(b.name)+'</label>';}).join("")+
        ' <button class="btn btn-sm btn-outline-primary as">บันทึก</button></div>';}).join("");
  });
}
document.addEventListener("click",function(e){
  if(e.target.classList.contains("sv")){var tr=e.target.closest("tr"),d={action:"save",id:tr.dataset.id};
    tr.querySelectorAll("[data-k]").forEach(function(i){d[i.dataset.k]=i.type==="checkbox"?(i.checked?1:0):i.value;});
    post(d).then(function(j){alert(j.ok?"บันทึกแล้ว":j.error);load();});}
  if(e.target.classList.contains("as")){var dv=e.target.closest("[data-uid]"),ids=[].map.call(dv.querySelectorAll("input:checked"),function(i){return i.value;});
    post({action:"assign",id_admin:dv.dataset.uid,branches:ids}).then(function(j){alert(j.ok?"บันทึกแล้ว":j.error);});}
});
document.getElementById("f").addEventListener("submit",function(e){e.preventDefault();var d={action:"save"};
  new FormData(this).forEach(function(v,k){d[k]=v;});post(d).then(function(j){if(!j.ok)alert(j.error);else this.reset();load();}.bind(this));});
load();
</script></body></html>
