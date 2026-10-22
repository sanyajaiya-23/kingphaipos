<?php
// ตั้งค่าหน้าขายออนไลน์ (แอดมิน) — ออเดอร์ · ตั้งค่าร้าน · ตกแต่ง · สินค้าออนไลน์ · คูปอง · เครดิตลูกค้า
// เข้าจากฟันเฟืองที่หน้า select_page โดยไม่ต้องเข้าหน้าขาย POS (ใช้โค้ดชุดเดียวกับ json/pos_shop.js)
require($_SERVER['DOCUMENT_ROOT'] . "/inc/db/db_webdev.php");
require($_SERVER['DOCUMENT_ROOT'] . "/inc/auth.php");
kp_require_admin();
$v = function ($f) { return (int)@filemtime(__DIR__ . "/" . $f); };
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#e5e8f7">
  <title>ตั้งค่าหน้าขายออนไลน์ · kingphai network</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/pos_sale.css?v=<?php echo $v("css/pos_sale.css"); ?>">
  <link rel="stylesheet" href="css/kp_clay.css?v=<?php echo $v("css/kp_clay.css"); ?>">
</head>
<body>
<div class="ps-app" id="app" data-admin="1">
  <section class="ps-view is-active" id="v-shop">
    <header class="ps-head"><a class="ps-back" href="select_page.php" aria-label="กลับ" style="text-decoration:none;display:grid;place-items:center">‹</a><h2>ตั้งค่าหน้าขายออนไลน์</h2><span class="ps-head__sp"></span></header>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin:6px 0 4px">
      <a class="ps-btn ps-btn--ghost" href="shop.php" target="_blank" rel="noopener" style="text-decoration:none">🛍️ ดูหน้าร้านจริง</a>
    </div>
    <div id="shopBody"></div>
  </section>
  <div class="ps-sheetbg" id="sheetBg" hidden></div>
  <div class="ps-sheet" id="sheet" hidden></div>
  <div class="ps-toast" id="toast" hidden></div>
</div>
<script>
(function () {
  "use strict";
  var $ = function (s, r) { return (r || document).querySelector(s); };
  function esc(v) { return String(v == null ? "" : v).replace(/[&<>"']/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]; }); }
  function fmt(n) { n = Number(n) || 0; return "฿" + n.toLocaleString("th-TH", { minimumFractionDigits: (Math.round(n * 100) % 100) ? 2 : 0, maximumFractionDigits: 2 }); }
  function qfmt(q) { return String(+Number(q).toFixed(3)); }
  var tt = null;
  function toast(msg, err) { var t = $("#toast"); t.textContent = msg; t.className = "ps-toast" + (err ? " err" : ""); t.hidden = false; clearTimeout(tt); tt = setTimeout(function () { t.hidden = true; }, 2400); }
  function api(action, data, opt) {
    opt = opt || {};
    var url = "json/pos_api.php?action=" + encodeURIComponent(action), init = { credentials: "same-origin", headers: { "X-Requested-With": "XMLHttpRequest" } };
    if (opt.json) { init.method = "POST"; init.headers["Content-Type"] = "application/json"; init.body = JSON.stringify(data || {}); }
    else if (opt.form) { init.method = "POST"; init.body = data; }
    else if (data) Object.keys(data).forEach(function (k) { if (data[k] !== undefined && data[k] !== null && data[k] !== "") url += "&" + encodeURIComponent(k) + "=" + encodeURIComponent(data[k]); });
    return fetch(url, init).then(function (r) { var ct = r.headers.get("content-type") || ""; if (ct.indexOf("json") < 0) { if (r.redirected || r.status === 401 || r.status === 403) location.href = "index.php"; throw new Error("เซิร์ฟเวอร์ตอบกลับผิดรูปแบบ"); } return r.json(); })
      .then(function (j) { if (!j.ok) throw new Error(j.error || "เกิดข้อผิดพลาด"); return j; });
  }
  function openSheet(html) { $("#sheet").innerHTML = html; $("#sheet").hidden = false; $("#sheetBg").hidden = false; }
  function closeSheet() { $("#sheet").hidden = true; $("#sheetBg").hidden = true; $("#sheet").innerHTML = ""; }
  document.addEventListener("click", function (ev) { var t = ev.target; if (t.closest && (t.closest("[data-close]") || t.id === "sheetBg")) closeSheet(); });
  var S = { boot: null, view: "shop", cust: null };
  // รายชื่อสาขาให้ตัวกรองออเดอร์ (แอดมิน)
  window.KPPOS = { api: api, esc: esc, fmt: fmt, qfmt: qfmt, toast: toast, openSheet: openSheet, closeSheet: closeSheet, S: S, $: $, loadBoot: function () { return Promise.resolve(); }, renderPay: function () {}, go: function () {} };
  api("bootstrap").then(function (j) { S.boot = j; }).catch(function () {}).then(function () {
    var s = document.createElement("script"); s.src = "json/pos_shop.js?v=<?php echo $v("json/pos_shop.js"); ?>";
    s.onload = function () { if (window.KPPOSX && KPPOSX.shop) KPPOSX.shop(); };
    document.body.appendChild(s);
  });
})();
</script>
</body>
</html>
