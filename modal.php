<?php /* เมนูหลังบ้าน POS (ใช้ร่วมหน้ารายชื่อ/รายจ่าย) */ $kpIsAdminM = isset($_SESSION['id_status']) && (int)$_SESSION['id_status'] === 1; ?>
<button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#exampleModal" aria-label="เมนู"><i class="fa fa-bars" aria-hidden="true"></i></button>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="exampleModalLabel"><i class="fa fa-bar-chart" aria-hidden="true"></i> เมนู</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <a href="/index2.php" class="btn btn-outline-success mt-2"><i class="fa fa-map-marker" aria-hidden="true"></i> แผนที่สาขา</a>
        <a href="/pos_sale.php" class="btn btn-outline-success mt-2"><i class="fa fa-shopping-basket" aria-hidden="true"></i> หน้าขาย</a>
        <a href="/name_story.php?search=&search_select=" class="btn btn-outline-danger mt-2"><i class="fa fa-heart" aria-hidden="true"></i> รายชื่อลูกค้า</a>
        <a href="/name_story.php?view=branch" class="btn btn-outline-danger mt-2"><i class="fa fa-building" aria-hidden="true"></i> รายชื่อสาขา</a>
<?php if ($kpIsAdminM) { ?>
        <a href="/pay_money.php?id=&buy_name=&money_buy=&comment=&action=" class="btn btn-outline-secondary mt-2"><i class="fa fa-plus-square-o" aria-hidden="true"></i> เพิ่มรายจ่าย</a>
        <a href="/report_money_out.php?search_date=&search_name=" class="btn btn-outline-dark mt-2"><i class="fa fa-list" aria-hidden="true"></i> ประวัติรายจ่าย</a>
        <a href="/branch_manage.php" class="btn btn-outline-primary mt-2"><i class="fa fa-cog" aria-hidden="true"></i> จัดการสาขา</a>
<?php } ?>
        <a href="/comment_bord.php" class="btn btn-outline-warning mt-2"><i class="fa fa-sticky-note-o" aria-hidden="true"></i> บันทึกสำคัญ</a>
      </div>
      <div class="modal-footer d-flex justify-content-end">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i class="fa fa-sign-out" aria-hidden="true"></i> Close</button>
      </div>
    </div>
  </div>
</div>
