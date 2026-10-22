<?php
// หน้าร้านออนไลน์ — ลูกค้าเลือกสินค้า สั่งซื้อ และชำระเงิน
//   เข้าเว็บ (/) ตอนยังไม่ล็อกอิน -> index.php ดึงหน้านี้มาแสดง · ลูกค้าล็อกอิน (status=2) มาหน้านี้เสมอ
// ดูสินค้า/ใส่ตะกร้าได้โดยไม่ต้องล็อกอิน (ไม่เรียก kp_require_login) — ตอนจะชำระเงินจึงให้เข้าสู่ระบบ/สมัครสมาชิก
define("KP_ALLOW_CUSTOMER", true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/inc/db/db_webdev.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/inc/auth.php");
kp_secure_session_start();
$shopStatus = isset($_SESSION["id_status"]) ? (int)$_SESSION["id_status"] : -1;
$shopLogged = !empty($_SESSION["login"]);
$v = function ($f) { return (int)@filemtime($_SERVER['DOCUMENT_ROOT'] . "/" . $f); };
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#f1f3f6">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <title>kingphai network · สั่งซื้อออนไลน์</title>
  <link rel="manifest" href="/manifest.json?v=4">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/shop.css?v=<?php echo $v("css/shop.css"); ?>">
</head>
<body>
<div class="sh-ann" id="ann">ส่งฟรีเมื่อซื้อครบตามเงื่อนไข · รับที่สาขาหรือจัดส่งถึงบ้าน</div>
<div class="sh-app" id="app" data-staff="<?php echo ($shopLogged && $shopStatus !== 2) ? 1 : 0; ?>">
  <header class="sh-top">
    <button type="button" class="sh-sq" id="btnMe" aria-label="เมนู"><svg viewBox="0 0 24 24"><rect x="4" y="4" width="6.5" height="6.5" rx="2"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="2"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="2"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="2"/></svg></button>
    <h1 id="storeName">kingphai network</h1>
    <nav class="sh-dnav" aria-label="เมนู"><button type="button" data-nav="home" class="is-on">หน้าแรก</button><button type="button" data-all="1">สินค้าทั้งหมด</button><button type="button" data-nav="orders">ออเดอร์ของฉัน</button></nav>
    <div class="sh-ricons"><?php if ($shopStatus === 1) { ?><a class="sh-sq sh-gear" href="shop_admin.php" aria-label="ตั้งค่าหน้าขายออนไลน์" title="ตั้งค่าหน้าขายออนไลน์"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3.2"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg></a><?php } ?><button type="button" class="sh-sq" id="btnSearch" aria-label="ค้นหา"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="M20 20l-4.2-4.2"/></svg></button>
    <button type="button" class="sh-sq sh-dsk" id="btnCart2" aria-label="ตะกร้า"><svg viewBox="0 0 24 24"><path d="M3 4h2l2.4 11h10.2L20 7H6.2"/><circle cx="9" cy="19" r="1.4"/><circle cx="17" cy="19" r="1.4"/></svg><em id="cartBadge2" hidden>0</em></button>
    <button type="button" class="sh-sq sh-dsk" data-nav="me" aria-label="บัญชี"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg></button></div>
  </header>

  <div class="sh-staffbar" id="staffBar" hidden>คุณเข้าสู่ระบบเป็นพนักงาน/แอดมิน — ดูร้านได้ แต่สั่งซื้อต้องใช้บัญชีลูกค้า · <a href="/select_page.php">กลับหลังบ้าน</a></div>
  <div class="sh-search" id="searchRow" hidden><input id="q" type="search" placeholder="ค้นหาสินค้า" autocomplete="off"></div>

  <section class="sh-hero" id="hero">
    <div class="tx"><h2 class="big" id="heroTitle">สินค้าคุณภาพ<br>ส่งถึงมือคุณ</h2><p id="heroTx">สั่งซื้อออนไลน์ ง่าย ๆ</p><div class="hb"><button type="button" class="sh-pill" id="heroBtn">เลือกซื้อเลย →</button><button type="button" class="sh-pill ol" data-all="1">ดูทั้งหมด</button></div></div>
    <div class="art" id="heroArt" aria-hidden="true">🛍️<i>🥕</i><i>🍎</i><i>🥛</i></div>
  </section>
  <div class="sh-dots" aria-hidden="true"><i class="on"></i><i></i><i></i><i></i></div>

  <div class="sh-sec"><h2>เลือกตามหมวดหมู่</h2><button type="button" class="sh-link" id="btnAllCats">ดูทั้งหมด</button></div>
  <div class="sh-chips" id="deptRow" hidden></div>
  <div class="sh-chips" id="catRow"></div>

  <section id="featWrap" hidden><h2 class="sh-h" id="featTitle">สินค้าแนะนำ</h2><div class="sh-grid" id="featGrid"></div></section>
  <h2 class="sh-h" id="listTitle">สินค้าทั้งหมด</h2>
  <div class="sh-grid" id="grid"></div>
  <div class="sh-empty" id="gridEmpty" hidden>ไม่พบสินค้า</div>
  <button type="button" class="sh-more" id="btnMore" hidden>ดูเพิ่ม</button>

  <section class="sh-promo" id="promo"><div><small id="promoTag">โปรโมชั่นพิเศษ</small><h3 id="promoTx">ส่งฟรี เมื่อซื้อครบตามกำหนด</h3><p id="promoSub">เลือกรับที่สาขาหรือจัดส่งถึงบ้าน</p><button type="button" class="sh-pill ol" data-all="1" id="promoBtn">ช้อปเลย →</button></div><b id="promoArt" aria-hidden="true">🛒</b></section>
  <section class="sh-perks" id="perks">
    <div><i>🚚</i><b>จัดส่งถึงบ้าน</b><small>หรือรับที่สาขา</small></div>
    <div><i>📱</i><b>จ่ายสะดวก</b><small>พร้อมเพย์ · ปลายทาง</small></div>
    <div><i>✅</i><b>สินค้าแท้</b><small>ตรวจสอบสต็อกจริง</small></div>
    <div><i>🔒</i><b>ปลอดภัย</b><small>ข้อมูลของคุณได้รับการปกป้อง</small></div>
  </section>
</div>
<footer class="sh-foot"><div><b id="footName">kingphai network</b><small id="footSub">ร้านค้าออนไลน์ สั่งง่าย ได้ของจริง</small></div><small>© kingphai network</small></footer>

<nav class="sh-nav" id="nav" aria-label="เมนูหลัก">
  <button type="button" data-nav="home" class="is-on" aria-label="หน้าแรก"><svg viewBox="0 0 24 24"><path d="M4 11l8-7 8 7v8a1 1 0 0 1-1 1h-4v-6H9v6H5a1 1 0 0 1-1-1z"/></svg></button>
  <button type="button" id="btnCart" aria-label="ตะกร้า"><svg viewBox="0 0 24 24"><path d="M3 4h2l2.4 11h10.2L20 7H6.2"/><circle cx="9" cy="19" r="1.4"/><circle cx="17" cy="19" r="1.4"/></svg><em id="cartBadge" hidden>0</em></button>
  <button type="button" data-nav="orders" aria-label="ออเดอร์ของฉัน"><svg viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/></svg></button>
  <button type="button" data-nav="me" aria-label="บัญชี"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg></button>
</nav>
<div class="sh-bg" id="sheetBg" hidden></div>
<div class="sh-sheet" id="sheet" hidden></div>
<div class="sh-toast" id="toast" hidden></div>

<script src="/lib/qrcode/qrcode.min.js"></script>
<script src="/json/kp_promptpay_qr.js?v=<?php echo $v("json/kp_promptpay_qr.js"); ?>"></script>
<script src="/json/shop.js?v=<?php echo $v("json/shop.js"); ?>"></script>
<script src="/json/shop_i18n.js?v=<?php echo $v("json/shop_i18n.js"); ?>"></script>
</body>
</html>
