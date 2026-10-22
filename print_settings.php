<?php
/**
 * print_settings.php — ตั้งค่า "พิมพ์ใบเสร็จผ่านแอป iPad (คิว)"
 * หน้าเดี่ยว แยกจากหน้าอื่น (แนวเดียวกับ line_settings.php)
 * - แสดง Server URL + Device Token ให้กรอกในแอป React Native
 * - สร้าง token ใหม่ได้
 * - สลับ "โหมดคิว" (เก็บใน localStorage ให้ index2 อ่าน) เปิด=พิมพ์ผ่านแอป iPad, ปิด=Web Bluetooth เดิม
 * - ดูคิวงานพิมพ์ล่าสุด
 */
require($_SERVER['DOCUMENT_ROOT']."/inc/db/db_webdev.php");
require($_SERVER['DOCUMENT_ROOT']."/inc/auth.php");
kp_require_login();
$isAdmin = (isset($_SESSION['id_status']) && (int)$_SESSION['id_status'] === 1);
if (!$isAdmin) { http_response_code(403); echo "forbidden"; exit; }

// สร้างตาราง + อ่าน/สร้าง device token (ใช้ตรรกะเดียวกับ print_queue.php)
// ห่อ try/catch เพื่อให้เห็นสาเหตุบนหน้า แทนที่จะขาวเปล่า/ไม่ขึ้น token
$deviceToken = "";
$setupError = "";
function kp_ps_gen_token() {
  try { return bin2hex(random_bytes(24)); }
  catch (Exception $e) { return substr(hash('sha256', uniqid('', true) . mt_rand()), 0, 48); }
}
try {
  $db->conn->exec("
    CREATE TABLE IF NOT EXISTS tb_print_config (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      device_token VARCHAR(64) NOT NULL,
      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");
  $db->conn->exec("
    CREATE TABLE IF NOT EXISTS tb_print_queue (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      job_ref VARCHAR(80) NULL,
      payload_b64 LONGTEXT NOT NULL,
      meta_json TEXT NULL,
      status ENUM('pending','printing','done','error') NOT NULL DEFAULT 'pending',
      error_text VARCHAR(255) NULL,
      device_id VARCHAR(80) NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id), KEY idx_status (status), KEY idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");
  $st = $db->conn->prepare("SELECT device_token FROM tb_print_config ORDER BY id ASC LIMIT 1");
  $st->execute();
  $row = $st->fetch(PDO::FETCH_ASSOC);
  $deviceToken = $row ? (string)($row["device_token"] ?? "") : "";
  // ยังไม่มีแถว หรือ token ว่าง -> สร้างใหม่
  if ($deviceToken === "") {
    $tok = kp_ps_gen_token();
    if ($row) {
      $db->conn->exec("DELETE FROM tb_print_config"); // เคลียร์แถวว่างที่อาจค้าง
    }
    $db->conn->prepare("INSERT INTO tb_print_config (device_token) VALUES (:t)")->execute([":t" => $tok]);
    $deviceToken = $tok;
  }
} catch (Exception $e) {
  $setupError = $e->getMessage();
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'www.kingphai.com';
$serverBase = $scheme . "://" . $host;

// คิวล่าสุด
$jobs = [];
try {
  $stj = $db->conn->prepare("SELECT id, job_ref, status, error_text, created_at, updated_at FROM tb_print_queue ORDER BY id DESC LIMIT 15");
  $stj->execute();
  $jobs = $stj->fetchAll(PDO::FETCH_ASSOC);
  if (!is_array($jobs)) $jobs = [];
} catch (Exception $e) { $jobs = []; }
function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="theme-color" content="#0f172a">
  <title>ตั้งค่าพิมพ์ผ่านแอป iPad</title>
  <link rel="stylesheet" href="lib/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root{ --bg:#0f172a; --card:rgba(255,255,255,0.07); --line:rgba(255,255,255,0.12);
      --text:#fff; --muted:rgba(255,255,255,0.55); --green:#22c55e; --green-dark:#16a34a; }
    *{ box-sizing:border-box; }
    body{ margin:0; background:var(--bg); color:var(--text); font-family:'Noto Sans Thai','Sarabun','Segoe UI',Tahoma,sans-serif;
      padding:calc(16px + env(safe-area-inset-top,0px)) 14px calc(28px + env(safe-area-inset-bottom,0px)); }
    .wrap{ max-width:640px; margin:0 auto; }
    .top{ display:flex; align-items:center; gap:12px; margin-bottom:16px; }
    .back{ width:40px; height:40px; border-radius:12px; border:1px solid var(--line); background:var(--card);
      color:var(--text); display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:18px; }
    h1{ font-size:19px; font-weight:900; margin:0; }
    .sub{ color:var(--muted); font-size:12px; margin-top:2px; }
    .card{ background:var(--card); border:1px solid var(--line); border-radius:16px; padding:16px; margin-bottom:14px; }
    .label{ font-size:12px; font-weight:900; color:var(--muted); margin-bottom:6px; }
    .val{ font-family:ui-monospace,Menlo,Consolas,monospace; font-size:13px; word-break:break-all;
      background:rgba(0,0,0,0.25); border:1px solid var(--line); border-radius:10px; padding:10px 12px; }
    .row{ display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-top:8px; }
    .btn{ border:0; border-radius:12px; padding:10px 16px; font-weight:900; font-size:14px; cursor:pointer; color:#fff; }
    .btn-green{ background:linear-gradient(140deg,var(--green),var(--green-dark)); }
    .btn-ghost{ background:rgba(255,255,255,0.08); border:1px solid var(--line); }
    .btn-warn{ background:#b91c1c; }
    .switch{ display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .toggle{ position:relative; width:56px; height:32px; flex:0 0 auto; }
    .toggle input{ opacity:0; width:0; height:0; }
    .slider{ position:absolute; inset:0; background:rgba(255,255,255,0.18); border-radius:999px; transition:.2s; }
    .slider:before{ content:""; position:absolute; height:24px; width:24px; left:4px; top:4px; background:#fff; border-radius:50%; transition:.2s; }
    .toggle input:checked + .slider{ background:var(--green); }
    .toggle input:checked + .slider:before{ transform:translateX(24px); }
    table{ width:100%; border-collapse:collapse; font-size:12px; }
    th,td{ text-align:left; padding:7px 6px; border-bottom:1px solid var(--line); }
    th{ color:var(--muted); font-weight:900; }
    .pill{ display:inline-block; padding:2px 8px; border-radius:999px; font-weight:900; font-size:11px; }
    .st-done{ background:rgba(34,197,94,0.18); color:#4ade80; }
    .st-pending{ background:rgba(148,163,184,0.18); color:#cbd5e1; }
    .st-printing{ background:rgba(59,130,246,0.18); color:#93c5fd; }
    .st-error{ background:rgba(239,68,68,0.18); color:#fca5a5; }
    .hint{ color:var(--muted); font-size:12px; line-height:1.5; margin-top:8px; }
    .ok-tip{ color:#4ade80; font-size:12px; font-weight:900; margin-top:8px; min-height:16px; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <a class="back" href="index2.php" title="กลับ"><i class="fa fa-angle-left"></i></a>
      <div>
        <h1>พิมพ์ใบเสร็จผ่านแอป iPad</h1>
        <div class="sub">ตั้งค่าคิวพิมพ์ + แอปพิมพ์ Bluetooth บน iPhone/iPad</div>
      </div>
    </div>

    <?php if ($setupError !== ""): ?>
    <div class="card" style="border-color:rgba(239,68,68,0.5);background:rgba(239,68,68,0.08);">
      <div class="label" style="color:#fca5a5;">เกิดข้อผิดพลาดกับฐานข้อมูล (จึงยังไม่มี Token)</div>
      <div class="val" style="color:#fca5a5;"><?php echo esc($setupError); ?></div>
      <div class="hint">มักเกิดจากผู้ใช้ฐานข้อมูลไม่มีสิทธิ์ CREATE TABLE บน host จริง — แจ้งผู้ดูแล host หรือส่งข้อความนี้กลับมาได้เลย</div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="switch">
        <div>
          <div style="font-weight:900;">โหมดพิมพ์ผ่านแอป iPad (คิว)</div>
          <div class="hint" style="margin-top:2px;">เปิด = กดพิมพ์บนเว็บจะส่งงานให้แอป iPad พิมพ์ • ปิด = ใช้ Web Bluetooth เดิม (Android/คอม)</div>
        </div>
        <label class="toggle">
          <input type="checkbox" id="queueModeToggle">
          <span class="slider"></span>
        </label>
      </div>
      <div class="ok-tip" id="queueModeTip"></div>
    </div>

    <div class="card">
      <div class="label">ค่าที่ต้องกรอกในแอป (Kingphai Print)</div>
      <div style="margin-bottom:10px;">
        <div class="label">Server URL</div>
        <div class="val" id="serverUrl"><?php echo esc($serverBase); ?></div>
      </div>
      <div>
        <div class="label">Device Token</div>
        <div class="val" id="deviceToken"><?php echo esc($deviceToken); ?></div>
      </div>
      <div class="row">
        <button class="btn btn-ghost" id="copyBtn"><i class="fa fa-copy"></i> คัดลอกทั้งคู่</button>
        <button class="btn btn-warn" id="regenBtn"><i class="fa fa-refresh"></i> สร้าง Token ใหม่</button>
      </div>
      <div class="hint">สร้าง Token ใหม่แล้ว ต้องกรอกใหม่ในแอปทุกเครื่อง (ของเก่าจะใช้ไม่ได้ทันที)</div>
      <div class="ok-tip" id="tokenTip"></div>
    </div>

    <div class="card">
      <div class="label">คิวงานพิมพ์ล่าสุด</div>
      <table>
        <thead><tr><th>#</th><th>บิล</th><th>สถานะ</th><th>เวลา</th></tr></thead>
        <tbody>
          <?php if (!count($jobs)): ?>
            <tr><td colspan="4" style="color:var(--muted);padding:14px 6px;">ยังไม่มีงานพิมพ์</td></tr>
          <?php else: foreach ($jobs as $j):
            $st = (string)$j['status'];
            $cls = 'st-'.$st;
          ?>
            <tr>
              <td><?php echo esc($j['id']); ?></td>
              <td><?php echo esc($j['job_ref'] ?: '-'); ?></td>
              <td><span class="pill <?php echo esc($cls); ?>"><?php echo esc($st); ?></span>
                <?php if ($st==='error' && $j['error_text']): ?><div style="color:#fca5a5;margin-top:2px;"><?php echo esc($j['error_text']); ?></div><?php endif; ?>
              </td>
              <td style="color:var(--muted);white-space:nowrap;"><?php echo esc(substr((string)$j['updated_at'],5,11)); ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
      <div class="row"><button class="btn btn-ghost" onclick="location.reload()"><i class="fa fa-refresh"></i> รีเฟรช</button></div>
    </div>
  </div>

  <script>
    (function(){
      var KEY = "kp_print_queue_mode";
      var toggle = document.getElementById("queueModeToggle");
      var tip = document.getElementById("queueModeTip");
      try { toggle.checked = String(localStorage.getItem(KEY)) === "1"; } catch(e){}
      toggle.addEventListener("change", function(){
        try { localStorage.setItem(KEY, toggle.checked ? "1" : "0"); } catch(e){}
        tip.textContent = toggle.checked ? "เปิดโหมดคิวแล้ว — กดพิมพ์บนเว็บจะส่งให้แอป iPad" : "ปิดโหมดคิว — ใช้ Web Bluetooth เดิม";
      });

      var serverUrl = <?php echo json_encode($serverBase, JSON_UNESCAPED_SLASHES); ?>;
      var token = document.getElementById("deviceToken").textContent.trim();
      document.getElementById("copyBtn").addEventListener("click", function(){
        var txt = "Server URL: " + serverUrl + "\nDevice Token: " + token;
        try { navigator.clipboard.writeText(txt); document.getElementById("tokenTip").textContent = "คัดลอกแล้ว"; }
        catch(e){ document.getElementById("tokenTip").textContent = "คัดลอกไม่สำเร็จ ก็อปเองได้"; }
      });

      document.getElementById("regenBtn").addEventListener("click", function(){
        if (!confirm("สร้าง Device Token ใหม่? ต้องกรอกใหม่ในแอปทุกเครื่อง")) return;
        fetch("json/print_queue.php?action=regen_token", { method:"POST", credentials:"same-origin" })
          .then(function(r){ return r.json(); })
          .then(function(j){
            if (j && j.ok && j.device_token) {
              document.getElementById("deviceToken").textContent = j.device_token;
              token = j.device_token;
              document.getElementById("tokenTip").textContent = "สร้าง Token ใหม่แล้ว";
            } else {
              document.getElementById("tokenTip").textContent = "สร้างไม่สำเร็จ";
            }
          })
          .catch(function(){ document.getElementById("tokenTip").textContent = "สร้างไม่สำเร็จ"; });
      });
    })();
  </script>
<script src="/json/kp_time_hm.js?v=1"></script>
<script src="/json/kp_datepicker.js?v=1"></script>
</body>
</html>
