<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/inc/auth.php");
// เข้าเว็บ: ยังไม่ล็อกอิน -> หน้าร้านออนไลน์ · ล็อกอินแล้ว -> ไปหน้าตามประเภทผู้ใช้ (ลูกค้า status=2 = หน้าร้าน, แอดมิน/พนักงาน = หน้าเลือกเมนู)
// หน้า login แสดงเมื่อมี ?login=1 หรือ ?res=... (เช่น ล็อกอินผิด/หมดเวลา) — ไม่ใช้โหมด KP_NO_LOGIN ที่หน้านี้
kp_secure_session_start();
if (!empty($_SESSION["login"]) && !isset($_GET["res"]) && !isset($_GET["login"])) {
  header("Location:" . (((int)($_SESSION["id_status"] ?? 0) === 2) ? "/shop.php" : "/select_page.php"));
  exit;
}
if (!isset($_GET["res"]) && !isset($_GET["login"])) { define("KP_SHOP_EMBED", 1); require __DIR__ . "/shop.php"; exit; }
$res = isset($_GET["res"]) ? $_GET["res"] : "";
$showPopup = ($res === "wrong" || $res === "worgpassword" || $res === "timeout" || $res === "logout" || $res === "reset" || $res === "resigned" || $res === "hold" || $res === "kicked");
$popupTitle = "เข้าสู่ระบบไม่สำเร็จ";
$popupText  = "username หรือ password ผิด";
if ($res === "timeout") {
  $popupTitle = "หมดเวลาใช้งาน";
  $popupText  = "เพื่อความปลอดภัย กรุณาเข้าสู่ระบบใหม่";
}
if ($res === "logout") {
  $popupTitle = "ออกจากระบบแล้ว";
  $popupText  = "ตำแหน่งถูกลบออกจากแผนที่เรียบร้อย";
}
// พนักงานที่ถูกบันทึกลาออกแล้ว (คำขอ 2026-09-27)
if ($res === "resigned") {
  $popupTitle = "บัญชีนี้ถูกปิดการใช้งาน";
  $popupText  = "พนักงานลาออกแล้ว หากเข้าใช้งานผิดพลาด กรุณาติดต่อแอดมิน";
}
// พนักงานที่ถูกพักงาน (คำขอ 2026-09-28)
if ($res === "hold") {
  $popupTitle = "เข้าใช้งานไม่ได้ในขณะนี้";
  $popupText  = "timeout หมดเวลา ติดต่อ Admin";
}
// แอดมินดีดออกจากระบบ (คำขอ 2026-10-08)
if ($res === "kicked") {
  $popupTitle = "ถูกออกจากระบบ";
  $popupText  = "แอดมินปิดการใช้งานของคุณ กรุณาติดต่อแอดมิน";
}
if ($res === "reset") {
  $popupTitle = "การทำงานหยุด";
  $popupText  = "หมดเวลาการLOGIN";
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <!-- PWA: ให้เนื้อหาเต็มจอลงใต้แถบสถานะ (iOS) พื้นหลังจะไม่เว้นขาวด้านบน -->
  <meta name="theme-color" content="#fbf1e1">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="กิ่งไผ่">
  <title>KINGPHAI.com</title>
  <link rel="stylesheet" href="/lib/bootstrap-4.5.2-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lib/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/popup_glass.css?v=4">
  <link rel="manifest" href="manifest.json?v=4">
  <link rel="apple-touch-icon" sizes="180x180" href="img/icon_180.png?v=2">
  <style>
    :root{
      --kp-text:#0f2a1c;
      --kp-sub:rgba(15,42,28,0.65);
      --kp-line:rgba(255,255,255,0.65);
      --kp-line-strong:rgba(255,255,255,0.95);
      --kp-panel:rgba(255,255,255,0.60);
      --kp-panel-deep:rgba(255,255,255,0.38);
      --kp-input:rgba(255,255,255,0.55);
      --kp-input-focus:rgba(255,255,255,0.78);
      --kp-green:#16a34a;
      --kp-green-2:#22c55e;
      --kp-green-3:#4ade80;
      --kp-green-4:#bbf7d0;
      --kp-cyan:rgba(56,189,248,0.35);
      --kp-shadow:0 30px 80px rgba(20,90,60,0.14);
    }
    html, body{
      min-height:100%;
    }
    /* หมายเหตุ: ห้ามตั้ง background ให้ html
       เพราะจะตัดการ propagate พื้นหลังของ body ไปทาเต็ม canvas (ทั้งจอ รวม safe-area)
       ทำให้ gradient ทาแค่ในกล่อง body แล้วเหลือแถบสีทึบด้านล่าง */
    body{
      margin:0;
      min-height:100vh;
      /* iOS standalone: ให้สูงเต็มจอจริง รวม safe-area */
      min-height:100dvh;
      font-family:'Noto Sans Thai','Sarabun','Segoe UI',Tahoma,sans-serif;
      color:var(--kp-text);
      overflow-x:hidden;
      background:
        radial-gradient(900px 560px at 12% 8%, rgba(56,178,232,0.60), transparent 60%),
        radial-gradient(820px 520px at 88% 10%, rgba(94,214,255,0.55), transparent 62%),
        radial-gradient(760px 480px at 50% 58%, rgba(72,214,158,0.50), transparent 64%),
        radial-gradient(700px 460px at 92% 88%, rgba(178,120,244,0.34), transparent 62%),
        radial-gradient(900px 640px at 50% 102%, rgba(255,255,255,0.14), transparent 70%),
        linear-gradient(150deg, #4aaed4 0%, #38bf9c 30%, #2fb88a 55%, #48add4 78%, #5cc2db 100%);
    }
    .kp-fabric-bg{
      position:fixed;
      inset:0;
      z-index:0;
      pointer-events:none;
      background:
        radial-gradient(520px 340px at 18% 22%, rgba(30,200,175,0.55), transparent 70%),
        radial-gradient(480px 320px at 82% 16%, rgba(60,160,235,0.52), transparent 70%),
        radial-gradient(560px 360px at 50% 88%, rgba(60,190,130,0.45), transparent 72%),
        radial-gradient(420px 300px at 90% 78%, rgba(160,100,230,0.32), transparent 70%),
        linear-gradient(180deg, rgba(15,45,52,0.22), rgba(15,45,52,0.00) 45%);
      filter:blur(30px) saturate(1.2);
      opacity:0.95;
    }
    .kp-fabric-bg:before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(420px 260px at 22% 20%, rgba(255,255,255,0.55), transparent 68%),
        radial-gradient(460px 280px at 78% 18%, rgba(224,255,247,0.40), transparent 70%),
        linear-gradient(180deg, rgba(255,255,255,0.30), rgba(255,255,255,0.00) 42%);
      mix-blend-mode:screen;
    }
    .kp-shell{
      min-height:100vh;
      min-height:100dvh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:calc(24px + env(safe-area-inset-top, 0px)) 14px calc(24px + env(safe-area-inset-bottom, 0px)) 14px;
      position:relative;
      z-index:1;
      overflow:hidden;
    }
    .kp-shell:before,
    .kp-shell:after{
      content:"";
      position:absolute;
      border-radius:999px;
      filter:blur(18px);
      pointer-events:none;
      opacity:0.9;
    }
    .kp-shell:before{
      width:420px;
      height:420px;
      top:-160px;
      right:-120px;
      background:radial-gradient(circle at 35% 35%, rgba(94,211,255,0.38), rgba(94,211,255,0) 68%);
    }
    .kp-shell:after{
      width:360px;
      height:360px;
      left:-140px;
      bottom:-140px;
      background:radial-gradient(circle at 35% 35%, rgba(89,255,201,0.30), rgba(89,255,201,0) 72%);
    }
    .kp-panel{
      width:min(460px, calc(100vw - 22px));
      position:relative;
    }
    .kp-panel:before{
      content:"";
      position:absolute;
      left:50%;
      bottom:-28px;
      width:82%;
      height:76px;
      transform:translateX(-50%);
      border-radius:999px;
      background:radial-gradient(ellipse at center, rgba(20,90,60,0.18) 0%, rgba(20,90,60,0.08) 42%, rgba(20,90,60,0) 76%);
      filter:blur(16px);
      pointer-events:none;
    }
    .kp-card{
      position:relative;
      overflow:hidden;
      border-radius:34px;
      border:1px solid rgba(255,255,255,0.75);
      background:
        linear-gradient(180deg, rgba(255,255,255,0.55), rgba(255,255,255,0.30)),
        linear-gradient(180deg, var(--kp-panel), var(--kp-panel-deep));
      -webkit-backdrop-filter:blur(34px) saturate(160%);
      backdrop-filter:blur(34px) saturate(160%);
      box-shadow:
        var(--kp-shadow),
        inset 0 1px 0 rgba(255,255,255,0.85),
        inset 0 -1px 0 rgba(255,255,255,0.20);
    }
    .kp-body{
      position:relative;
      z-index:1;
      padding:34px 28px 24px;
    }
    .kp-card:before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(280px 160px at 18% 10%, rgba(255,255,255,0.65), transparent 76%),
        radial-gradient(300px 180px at 84% 12%, rgba(142,220,255,0.28), transparent 74%),
        linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.02) 54%, rgba(255,255,255,0.06));
      pointer-events:none;
    }
    .kp-card:after{
      content:"";
      position:absolute;
      inset:1px;
      border-radius:33px;
      border:1px solid rgba(255,255,255,0.55);
      pointer-events:none;
    }
    .kp-header{
      text-align:center;
      margin-bottom:20px;
    }
    .kp-logo-wrap{
      width:180px;
      height:180px;
      margin:0 auto 10px;
      padding:26px;
      border-radius:999px;
      border:1px solid rgba(255,255,255,0.80);
      background:
        linear-gradient(180deg, rgba(255,255,255,0.65), rgba(255,255,255,0.30)),
        linear-gradient(180deg, rgba(214,246,224,0.40), rgba(198,236,214,0.20));
      -webkit-backdrop-filter:blur(18px);
      backdrop-filter:blur(18px);
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.85),
        0 16px 36px rgba(20,90,60,0.14);
      display:flex;
      align-items:center;
      justify-content:center;
      position:relative;
      overflow:hidden;
      box-sizing:border-box;
    }
    .kp-logo-wrap:before{
      content:"";
      position:absolute;
      inset:6px;
      border-radius:999px;
      background:
        radial-gradient(circle at 32% 24%, rgba(255,255,255,0.75), rgba(255,255,255,0.08) 46%, rgba(255,255,255,0.00) 66%);
      pointer-events:none;
    }
    .kp-logo{
      display:block;
      width:100%;
      height:100%;
      object-fit:cover;
      border-radius:999px;
      position:relative;
      z-index:1;
      filter:drop-shadow(0 10px 20px rgba(20,90,60,0.16));
    }
    .kp-brand{
      margin:0;
      font-size:0;
      line-height:0;
    }
    .kp-brand-sub{
      display:none;
    }
    .kp-form-title{
      margin:0 0 22px;
      text-align:center;
      font-size:21px;
      line-height:1.2;
      font-weight:900;
      color:var(--kp-text);
      letter-spacing:0.02em;
      text-transform:uppercase;
      text-shadow:0 2px 10px rgba(255,255,255,0.40);
    }
    .kp-field{
      margin-bottom:18px;
    }
    .kp-field-wrap{
      position:relative;
    }
    .kp-field-wrap:before{
      content:"";
      position:absolute;
      inset:1px;
      border-radius:17px;
      background:
        linear-gradient(180deg, rgba(255,255,255,0.45), rgba(255,255,255,0.05) 42%, rgba(255,255,255,0.00)),
        radial-gradient(120px 34px at 22% 16%, rgba(255,255,255,0.45), transparent 72%);
      pointer-events:none;
      z-index:1;
    }
    .kp-input{
      width:100%;
      height:66px;
      border:1.5px solid rgba(255,255,255,0.70);
      border-radius:18px;
      background:
        linear-gradient(180deg, rgba(255,255,255,0.60), rgba(255,255,255,0.32)) !important;
      -webkit-backdrop-filter:blur(24px) saturate(160%);
      backdrop-filter:blur(24px) saturate(160%);
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.70),
        inset 0 -1px 0 rgba(255,255,255,0.10),
        0 16px 32px rgba(20,90,60,0.10);
      color:var(--kp-text);
      font-size:23px;
      font-weight:800;
      padding:0 18px 0 56px;
      position:relative;
      z-index:2;
    }
    .kp-input::placeholder{
      color:rgba(15,42,28,0.50);
      font-weight:700;
      font-size:19px;
    }
    .kp-input:focus{
      color:#0a1f14;
      outline:none;
      border-color:var(--kp-line-strong);
      box-shadow:
        0 0 0 3px rgba(56,189,248,0.18),
        0 18px 34px rgba(20,90,60,0.14) !important;
      background:linear-gradient(180deg, rgba(255,255,255,0.78), rgba(255,255,255,0.46)) !important;
    }
    .kp-field-icon{
      position:absolute;
      left:18px;
      top:50%;
      transform:translateY(-50%);
      color:var(--kp-green);
      font-size:20px;
      pointer-events:none;
      z-index:3;
    }
    .kp-option-row{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      margin:8px 0 18px;
    }
    .kp-remember{
      display:inline-flex;
      align-items:center;
      gap:10px;
      user-select:none;
      cursor:pointer;
      margin:0;
      color:var(--kp-text);
      font-size:15px;
      font-weight:700;
    }
    .kp-remember input{
      width:18px;
      height:18px;
      margin:0;
      accent-color:var(--kp-green);
    }
    .kp-forgot{
      color:var(--kp-sub);
      font-size:14px;
      font-weight:700;
      text-decoration:none;
    }
    .kp-forgot:hover,
    .kp-forgot:focus{
      color:var(--kp-text);
      text-decoration:none;
      outline:none;
    }
    .kp-btn{
      position:relative;
      width:100%;
      height:68px;
      border:1px solid rgba(255,255,255,0.55);
      border-radius:999px;
      background:
        linear-gradient(135deg, #22c55e 0%, #16a34a 48%, #0ea5b7 100%),
        linear-gradient(180deg, rgba(255,255,255,0.30), rgba(255,255,255,0.05));
      -webkit-backdrop-filter:blur(18px) saturate(160%);
      backdrop-filter:blur(18px) saturate(160%);
      color:#ffffff;
      box-shadow:
        0 22px 42px rgba(22,163,74,0.30),
        inset 0 1px 0 rgba(255,255,255,0.40),
        inset 0 -1px 0 rgba(255,255,255,0.10);
      font-size:24px;
      font-weight:900;
      letter-spacing:0.01em;
      overflow:hidden;
      transition:transform .16s ease, filter .16s ease;
      margin-top:6px;
    }
    .kp-btn:before{
      content:"";
      position:absolute;
      inset:1px;
      border-radius:inherit;
      background:
        linear-gradient(180deg, rgba(255,255,255,0.40), rgba(255,255,255,0.00) 54%),
        radial-gradient(180px 42px at 50% 48%, rgba(255,255,255,0.22), transparent 58%),
        radial-gradient(120px 36px at 18% 18%, rgba(255,255,255,0.28), transparent 76%);
      pointer-events:none;
    }
    .kp-btn:after{
      content:"";
      position:absolute;
      width:42%;
      height:120%;
      right:-6%;
      top:-10%;
      border-radius:999px;
      background:linear-gradient(90deg, rgba(255,255,255,0.00), rgba(255,255,255,0.28), rgba(255,255,255,0.02));
      transform:rotate(18deg);
      pointer-events:none;
      opacity:0.92;
    }
    .kp-btn:hover,
    .kp-btn:focus{
      color:#ffffff;
      transform:translateY(-1px) scale(1.01);
      filter:brightness(1.06);
      outline:none;
    }
    .kp-btn:active{
      transform:translateY(0) scale(0.995);
    }

    #kpLoginPopup{
      position:fixed;
      inset:0;
      z-index:5000;
      display:none;
      align-items:center;
      justify-content:center;
      padding:20px;
      background:rgba(20,60,40,0.20);
      -webkit-backdrop-filter:blur(10px) saturate(160%);
      backdrop-filter:blur(10px) saturate(160%);
    }
    #kpLoginPopup.is-show{ display:flex; }
    #kpLoginPopup .cardx{
      width:min(92vw, 360px);
      border-radius:26px;
      background:linear-gradient(180deg, rgba(255,255,255,0.96), rgba(245,255,248,0.92));
      border:1px solid rgba(255,255,255,0.85);
      box-shadow:0 24px 54px rgba(20,60,40,0.20);
      padding:22px 20px 18px;
      text-align:center;
    }
    #kpLoginPopup .iconx{
      width:56px;
      height:56px;
      margin:0 auto 10px;
      border-radius:999px;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-size:30px;
      font-weight:800;
      background:linear-gradient(140deg, #ef4444, #b91c1c);
      box-shadow:0 12px 26px rgba(239,68,68,0.35);
    }
    #kpLoginPopup .titlex{ font-size:20px; font-weight:800; color:#111827; margin-bottom:4px; }
    #kpLoginPopup .textx{ font-size:14px; color:rgba(17,24,39,0.75); line-height:1.35; }
    #kpLoginPopup .btnx{ margin-top:12px; border-radius:999px; padding:9px 20px; }

    .kp-bubbles{
      position:fixed;
      inset:0;
      z-index:0;
      overflow:hidden;
      pointer-events:none;
    }
    .kp-bubble{
      position:absolute;
      bottom:-12%;
      border-radius:50%;
      background:
        radial-gradient(circle at 30% 26%, rgba(255,255,255,0.98), rgba(255,255,255,0.30) 46%, rgba(255,255,255,0.10) 72%, rgba(255,255,255,0) 100%);
      border:1.5px solid rgba(255,255,255,0.85);
      box-shadow:
        inset 0 0 12px rgba(255,255,255,0.65),
        0 0 16px rgba(255,255,255,0.35),
        0 0 4px rgba(10,50,40,0.25);
      animation-name:kp-bubble-rise;
      animation-timing-function:ease-in;
      animation-iteration-count:infinite;
      will-change:transform, opacity;
    }
    @keyframes kp-bubble-rise{
      0%{
        transform:translate(0,0);
        opacity:0;
      }
      10%{
        opacity:0.9;
      }
      50%{
        transform:translate(16px,-58vh);
      }
      90%{
        opacity:0.7;
      }
      100%{
        transform:translate(-12px,-118vh);
        opacity:0;
      }
    }
    @media (prefers-reduced-motion: reduce){
      .kp-bubble{ animation:none; display:none; }
    }

    @media (max-width: 575.98px){
      .kp-shell{
        padding:16px 10px;
      }
      .kp-panel{
        width:100%;
      }
      .kp-body{
        padding:28px 18px 20px;
      }
      .kp-logo-wrap{
        width:150px;
        height:150px;
        padding:20px;
        margin-bottom:6px;
      }
      .kp-form-title{
        font-size:18px;
        margin-bottom:18px;
      }
      .kp-input{
        font-size:20px;
        height:60px;
        padding-left:52px;
      }
      .kp-field-icon{
        left:16px;
        font-size:18px;
      }
      .kp-option-row{
        align-items:flex-start;
        flex-direction:column;
      }
      .kp-btn{
        height:62px;
        font-size:22px;
      }
    }

    /* =====================================================================
       ธีม "กระดาษสามมิติ" (Neumorphism บนกระดาษเนื้อจุด) — ให้เข้าชุดกับหน้าเลือกเมนู (คำขอ 2026-09-28)
       ทับสไตล์กระจก/ฟองอากาศเดิม โดยไม่แตะโครงฟอร์ม (name/id/action เดิมทั้งหมด)
       ===================================================================== */
    :root{
      --np-bg:#f2e8f3;   /* ม่วงชมพูอ่อน (คำขอ 2026-09-28) */
      --np-ink:#3f4a5a;
      --np-sub:#7b8594;
      --np-dark:rgba(196,168,204,0.60);
      --np-light:rgba(255,255,255,0.92);
      --np-dots:radial-gradient(rgba(60,70,85,0.10) 0.7px, transparent 0.8px);
    }
    body.np{
      background:var(--np-bg);
      background-image:var(--np-dots), linear-gradient(160deg, #ece3f8 0%, #f4e7f3 50%, #fae6ee 100%);
      background-size:4px 4px, 100% 100%;
      background-attachment:scroll, fixed;
      color:var(--np-ink);
    }
    body.np .kp-shell:before,
    body.np .kp-shell:after,
    body.np .kp-panel:before,
    body.np .kp-card:before,
    body.np .kp-card:after,
    body.np .kp-logo-wrap:before,
    body.np .kp-field-wrap:before,
    body.np .kp-btn:before,
    body.np .kp-btn:after{ display:none; }
    body.np .kp-panel{ width:min(420px, calc(100vw - 32px)); }
    /* แผ่นกระดาษหลัก ยกลอยจากพื้น */
    body.np .kp-card{
      border:0;
      border-radius:40px;
      background:var(--np-bg);
      background-image:var(--np-dots);
      background-size:4px 4px;
      -webkit-backdrop-filter:none;
      backdrop-filter:none;
      box-shadow:
        22px 22px 48px rgba(180,150,190,0.50),
        -18px -18px 40px rgba(255,255,255,0.95),
        inset 0 1px 0 rgba(255,255,255,0.8);
    }
    body.np .kp-body{ padding:36px 28px 30px; }
    /* โลโก้ในวงกลมนูน */
    body.np .kp-logo-wrap{
      width:136px;
      height:136px;
      padding:10px;
      margin-bottom:22px;
      border:0;
      background:var(--np-bg);
      -webkit-backdrop-filter:none;
      backdrop-filter:none;
      box-shadow:10px 10px 22px var(--np-dark), -10px -10px 22px var(--np-light);
    }
    body.np .kp-logo{ filter:none; }
    body.np .kp-form-title{
      font-size:22px;
      font-weight:600;
      letter-spacing:0;
      text-transform:none;
      text-shadow:none;
      margin-bottom:26px;
      background:linear-gradient(90deg, #f472b6, #38bdf8);
      -webkit-background-clip:text;
      background-clip:text;
      color:transparent;
    }
    /* ช่องกรอก: ยุบลงไปในกระดาษ */
    body.np .kp-field{ margin-bottom:20px; }
    body.np .kp-input{
      height:58px;
      border:0;
      border-radius:18px;
      background:var(--np-bg) !important;
      -webkit-backdrop-filter:none;
      backdrop-filter:none;
      box-shadow:inset 6px 6px 12px var(--np-dark), inset -6px -6px 12px var(--np-light);
      color:var(--np-ink);
      font-size:18px;
      font-weight:500;
      padding:0 18px 0 52px;
    }
    body.np .kp-input::placeholder{ color:var(--np-sub); font-size:16px; font-weight:400; }
    body.np .kp-input:focus{
      color:var(--np-ink);
      background:var(--np-bg) !important;
      box-shadow:
        inset 6px 6px 12px var(--np-dark),
        inset -6px -6px 12px var(--np-light),
        0 0 0 2px rgba(56,189,248,0.45) !important;
    }
    body.np .kp-field-icon{ left:19px; font-size:18px; color:#38bdf8; }
    body.np .kp-field.mb-0 .kp-field-icon{ color:#ec4899; }
    body.np .kp-option-row{ margin:18px 2px 24px; }
    body.np .kp-remember{ color:var(--np-ink); font-size:14px; font-weight:500; }
    body.np .kp-remember input{ accent-color:#22c55e; }
    body.np .kp-forgot{ color:var(--np-sub); font-size:13px; font-weight:500; }
    /* ปุ่มเข้าสู่ระบบ: นูนขึ้น กดแล้วยุบ */
    body.np .kp-btn{
      height:60px;
      margin-top:0;
      border:0;
      border-radius:20px;
      background:var(--np-bg);
      background-image:var(--np-dots);
      background-size:4px 4px;
      -webkit-backdrop-filter:none;
      backdrop-filter:none;
      color:#16a34a;
      font-size:19px;
      font-weight:700;
      letter-spacing:0.02em;
      box-shadow:8px 8px 16px var(--np-dark), -8px -8px 16px var(--np-light);
      transition:box-shadow .18s ease, transform .18s ease;
    }
    body.np .kp-btn:hover,
    body.np .kp-btn:focus{
      color:#15803d;
      filter:none;
      transform:none;
      box-shadow:10px 10px 20px var(--np-dark), -10px -10px 20px var(--np-light);
    }
    body.np .kp-btn:active,
    body.np .kp-btn.btn:not(:disabled):not(.disabled):active,
    body.np .kp-btn.btn:not(:disabled):not(.disabled):active:focus{
      transform:scale(.99);
      color:#15803d;
      background-color:var(--np-bg);
      background-image:var(--np-dots);
      border:0;
      box-shadow:inset 6px 6px 12px var(--np-dark), inset -6px -6px 12px var(--np-light);
    }
    /* ป็อปอัพแจ้งเตือน (ล็อกอินไม่ได้/หมดเวลา ฯลฯ) */
    body.np #kpLoginPopup{
      background:rgba(228,230,233,0.55);
      -webkit-backdrop-filter:blur(6px);
      backdrop-filter:blur(6px);
    }
    body.np #kpLoginPopup .cardx{
      border:0;
      border-radius:30px;
      background:var(--np-bg);
      background-image:var(--np-dots);
      background-size:4px 4px;
      box-shadow:16px 16px 36px rgba(180,150,190,0.50), -12px -12px 30px rgba(255,255,255,0.95);
      padding:26px 22px 22px;
    }
    body.np #kpLoginPopup .iconx{
      width:60px;
      height:60px;
      margin-bottom:14px;
      background:var(--np-bg);
      color:#ef4444;
      font-size:28px;
      font-weight:600;
      box-shadow:inset 5px 5px 10px var(--np-dark), inset -5px -5px 10px var(--np-light);
    }
    body.np #kpLoginPopup .titlex{ color:var(--np-ink); font-weight:600; }
    body.np #kpLoginPopup .textx{ color:var(--np-sub); }
    body.np #kpLoginPopup .btnx{
      margin-top:18px;
      border:0;
      border-radius:16px;
      padding:10px 28px;
      background:var(--np-bg);
      color:#ef4444;
      font-weight:600;
      box-shadow:6px 6px 12px var(--np-dark), -6px -6px 12px var(--np-light);
    }
    body.np #kpLoginPopup .btnx:active{ box-shadow:inset 4px 4px 8px var(--np-dark), inset -4px -4px 8px var(--np-light); }
    @media (max-width: 575.98px){
      body.np .kp-body{ padding:30px 20px 24px; }
      body.np .kp-logo-wrap{ width:120px; height:120px; padding:9px; }
      body.np .kp-input{ height:56px; font-size:17px; }
      body.np .kp-btn{ height:58px; font-size:18px; }
    }
    /* ===== ปุ่มสแกนใบหน้า / ลายนิ้วมือ (Passkey) — คำขอ 2026-09-28 ===== */
    body.np .kp-or{ display:flex; align-items:center; gap:10px; margin:18px 4px 14px; color:var(--np-sub); font-size:12.5px; font-weight:500; }
    body.np .kp-or:before, body.np .kp-or:after{ content:""; flex:1 1 auto; height:2px; border-radius:2px; box-shadow:inset 1px 1px 2px var(--np-dark), inset -1px -1px 2px var(--np-light); }
    body.np .kp-face-btn{
      width:100%; height:56px; border:0; border-radius:20px;
      display:flex; align-items:center; justify-content:center; gap:12px;
      background:linear-gradient(135deg, #e9dcfb 0%, #f8d6e8 100%);
      color:#8b5cf6; font-size:17px; font-weight:700;
      box-shadow:8px 8px 16px var(--np-dark), -8px -8px 16px var(--np-light), inset 0 1px 0 rgba(255,255,255,0.8);
      transition:box-shadow .18s ease;
      -webkit-tap-highlight-color:transparent;
    }
    body.np .kp-face-btn svg{ width:28px; height:28px; fill:none; stroke:#a855f7; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
    body.np .kp-face-btn:active, body.np .kp-face-btn.is-busy{ box-shadow:inset 5px 5px 10px var(--np-dark), inset -5px -5px 10px var(--np-light); }
    body.np .kp-face-btn.is-busy svg{ animation:kpFacePulse 1s ease-in-out infinite; }
    @keyframes kpFacePulse{ 0%,100%{ opacity:1; } 50%{ opacity:.35; } }
    body.np .kp-face-msg{ min-height:18px; margin-top:10px; text-align:center; font-size:13px; font-weight:500; color:#db2777; }
    /* ===== หน้าล็อกแบบแอปธนาคาร: สแกนหน้า 2 ครั้ง -> PIN -> รหัสผ่าน (คำขอ 2026-09-29) ===== */
    .kp-lock{
      position:fixed; inset:0; z-index:5000;
      display:flex; align-items:center; justify-content:center;
      padding:max(env(safe-area-inset-top,0px),24px) 20px max(env(safe-area-inset-bottom,0px),20px);
      background:var(--np-bg);
      background-image:var(--np-dots), linear-gradient(160deg, #ece3f8 0%, #f4e7f3 50%, #fae6ee 100%);
      background-size:4px 4px, 100% 100%;
    }
    .kp-lock[hidden], .kp-lock [hidden]{ display:none !important; }
    .kp-lock__card{ width:min(360px,100%); display:flex; flex-direction:column; align-items:center; text-align:center; }
    .kp-lock__logo{ width:78px; height:78px; border-radius:50%; padding:7px; background:var(--np-bg);
      box-shadow:8px 8px 18px var(--np-dark), -8px -8px 18px var(--np-light); }
    .kp-lock__logo img{ width:100%; height:100%; border-radius:50%; object-fit:cover; display:block; }
    .kp-lock__hi{ margin-top:18px; font-size:14px; color:var(--np-sub); font-weight:500; }
    .kp-lock__name{ margin-top:2px; font-size:22px; font-weight:700; color:var(--np-ink); min-height:28px; }
    .kp-lock__stage{ width:100%; display:flex; flex-direction:column; align-items:center; margin-top:26px; }
    .kp-lock__face{
      width:128px; height:128px; border:0; border-radius:50%; padding:0;
      display:flex; align-items:center; justify-content:center;
      background:linear-gradient(135deg, #efe4fb 0%, #fbdff0 100%);
      box-shadow:12px 12px 24px var(--np-dark), -12px -12px 24px var(--np-light);
      -webkit-tap-highlight-color:transparent;
    }
    .kp-lock__face svg{ width:62px; height:62px; fill:none; stroke:#a855f7; stroke-width:1.5; stroke-linecap:round; stroke-linejoin:round; }
    .kp-lock__face:active{ box-shadow:inset 8px 8px 16px var(--np-dark), inset -8px -8px 16px var(--np-light); }
    .kp-lock__face.is-busy svg{ animation:kpFacePulse 1s ease-in-out infinite; }
    .kp-lock__msg{ margin-top:18px; min-height:20px; font-size:14px; font-weight:500; color:var(--np-ink); }
    .kp-lock__msg.is-bad{ color:#db2777; }
    .kp-lock__dots{ display:flex; gap:14px; margin:16px 0 22px; }
    .kp-lock__dots i{ width:15px; height:15px; border-radius:50%; background:var(--np-bg);
      box-shadow:inset 2px 2px 4px var(--np-dark), inset -2px -2px 4px var(--np-light); transition:background .12s ease; }
    .kp-lock__dots i.is-on{ background:linear-gradient(135deg, #c084fc, #ec4899); box-shadow:0 2px 6px rgba(168,85,247,0.35); }
    .kp-lock__dots.is-shake{ animation:kpLockShake .4s ease; }
    @keyframes kpLockShake{ 0%,100%{ transform:translateX(0); } 20%{ transform:translateX(-10px); } 40%{ transform:translateX(9px); } 60%{ transform:translateX(-6px); } 80%{ transform:translateX(4px); } }
    .kp-lock__pad{ display:grid; grid-template-columns:repeat(3, 70px); gap:18px 26px; justify-content:center; }
    .kp-lock__key{
      width:70px; height:70px; border:0; border-radius:50%; padding:0;
      background:var(--np-bg); color:var(--np-ink); font-size:26px; font-weight:500;
      box-shadow:6px 6px 12px var(--np-dark), -6px -6px 12px var(--np-light);
      display:flex; align-items:center; justify-content:center;
      -webkit-tap-highlight-color:transparent; user-select:none;
    }
    .kp-lock__key:active, .kp-lock__key.is-press{ box-shadow:inset 4px 4px 8px var(--np-dark), inset -4px -4px 8px var(--np-light); }
    .kp-lock__pad, .kp-lock__key{ touch-action:manipulation; -webkit-user-select:none; user-select:none; -webkit-touch-callout:none; }
    .kp-lock__key{ transition:none; will-change:box-shadow; }
    .kp-lock__dots i{ transition:none !important; }
    .kp-lock__key--blank{ visibility:hidden; }
    .kp-lock__key svg{ width:26px; height:26px; fill:none; stroke:#8b5cf6; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
    .kp-lock__link{ margin-top:22px; border:0; background:transparent; color:#8b5cf6; font-size:14px; font-weight:600; padding:6px 10px; }
    .kp-lock__link--sub{ margin-top:8px; color:var(--np-sub); font-weight:500; }
    .kp-pass-notice{ min-height:0; margin:-10px 0 16px; text-align:center; font-size:13px; font-weight:600; color:#db2777; }
    .kp-pass-notice:empty{ display:none; }
  </style>
  <link rel="stylesheet" href="css/kp_clay_np.css?v=<?php echo (int)@filemtime(__DIR__ . "/css/kp_clay_np.css"); ?>">
</head>
<body class="np">
  <form action="login_check.php" method="POST">
    <div class="kp-shell">
      <div class="kp-panel">
        <div class="kp-card">
          <div class="kp-body">
            <div class="kp-header">
              <div class="kp-logo-wrap">
                <img src="img/icon_512.png?v=2" alt="Kingphai logo" class="kp-logo">
              </div>
              <h1 class="kp-brand">kingphai.com</h1>
              <div class="kp-brand-sub">เข้าสู่ระบบเพื่อใช้งานระบบ</div>
            </div>

            <div class="kp-form-title">ยินดีต้อนรับ สู่ กิ่งไผ่</div>
            <div style="text-align:center;margin:-4px 0 12px;font-size:13px"><a href="/" style="color:inherit"><i class="fa fa-shopping-bag"></i> หน้าร้านออนไลน์</a> &nbsp;·&nbsp; <a href="/shop.php#register" style="color:inherit">สมัครสมาชิกลูกค้า</a></div>

            <div class="kp-pass-notice" id="kpPassNotice" aria-live="polite"></div>
            <div class="form-group kp-field">
              <div class="kp-field-wrap">
                <span class="kp-field-icon"><i class="fa fa-user" aria-hidden="true"></i></span>
                <input type="text" name="username" class="form-control kp-input" id="username" autocomplete="username" placeholder="ชื่อผู้ใช้ / Username">
              </div>
            </div>

            <div class="form-group kp-field mb-0">
              <div class="kp-field-wrap">
                <span class="kp-field-icon"><i class="fa fa-lock" aria-hidden="true"></i></span>
                <input type="password" name="password" class="form-control kp-input" id="password" autocomplete="current-password" placeholder="รหัสผ่าน / Password">
              </div>
            </div>

            <div class="kp-option-row">
              <label class="kp-remember">
                <input type="checkbox" id="remember_ui" autocomplete="off">
                <span>จดจำฉัน</span>
              </label>
              <a href="javascript:void(0)" class="kp-forgot" onclick="return false;">ลืมรหัส ติดต่อ Admin</a>
            </div>

            <button type="submit" class="btn btn-success btn-block kp-btn" aria-label="เข้าสู่ระบบ">
              เข้าสู่ระบบ
            </button>

          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- หน้าล็อกแบบแอปธนาคาร: เปิดแอป -> สแกนใบหน้า (2 ครั้ง) -> PIN -> ชื่อผู้ใช้/รหัสผ่าน -->
  <div class="kp-lock" id="kpLock" hidden role="dialog" aria-modal="true" aria-label="ยืนยันตัวตน">
    <div class="kp-lock__card">
      <div class="kp-lock__logo"><img src="img/icon_512.png?v=2" alt=""></div>
      <div class="kp-lock__hi">ยินดีต้อนรับ</div>
      <div class="kp-lock__name" id="kpLockName"></div>

      <div class="kp-lock__stage" id="kpLockFace" hidden>
        <button type="button" class="kp-lock__face" id="kpLockFaceBtn" aria-label="สแกนใบหน้า"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 8V5.5A2.5 2.5 0 0 1 5.5 3H8"/><path d="M16 3h2.5A2.5 2.5 0 0 1 21 5.5V8"/><path d="M21 16v2.5a2.5 2.5 0 0 1-2.5 2.5H16"/><path d="M8 21H5.5A2.5 2.5 0 0 1 3 18.5V16"/><path d="M9 9.5v1"/><path d="M15 9.5v1"/><path d="M12 9.5v3.5h-1"/><path d="M9.5 16a3.5 3.5 0 0 0 5 0"/></svg></button>
        <div class="kp-lock__msg" id="kpLockFaceMsg">กำลังสแกนใบหน้า...</div>
        <button type="button" class="kp-lock__link" id="kpLockToPin" hidden>ใช้ PIN แทน</button>
      </div>

      <div class="kp-lock__stage" id="kpLockPin" hidden>
        <div class="kp-lock__msg" id="kpLockPinMsg">ใส่ PIN 6 หลัก</div>
        <div class="kp-lock__dots" id="kpLockDots"><i></i><i></i><i></i><i></i><i></i><i></i></div>
        <div class="kp-lock__pad" id="kpLockPad">
          <button type="button" class="kp-lock__key" data-k="1">1</button>
          <button type="button" class="kp-lock__key" data-k="2">2</button>
          <button type="button" class="kp-lock__key" data-k="3">3</button>
          <button type="button" class="kp-lock__key" data-k="4">4</button>
          <button type="button" class="kp-lock__key" data-k="5">5</button>
          <button type="button" class="kp-lock__key" data-k="6">6</button>
          <button type="button" class="kp-lock__key" data-k="7">7</button>
          <button type="button" class="kp-lock__key" data-k="8">8</button>
          <button type="button" class="kp-lock__key" data-k="9">9</button>
          <button type="button" class="kp-lock__key kp-lock__key--blank" id="kpLockPadFace" data-k="face" aria-label="สแกนใบหน้า"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 8V5.5A2.5 2.5 0 0 1 5.5 3H8"/><path d="M16 3h2.5A2.5 2.5 0 0 1 21 5.5V8"/><path d="M21 16v2.5a2.5 2.5 0 0 1-2.5 2.5H16"/><path d="M8 21H5.5A2.5 2.5 0 0 1 3 18.5V16"/><path d="M9 9.5v1"/><path d="M15 9.5v1"/><path d="M12 9.5v3.5h-1"/><path d="M9.5 16a3.5 3.5 0 0 0 5 0"/></svg></button>
          <button type="button" class="kp-lock__key" data-k="0">0</button>
          <button type="button" class="kp-lock__key" data-k="del" aria-label="ลบ"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-6-7z"/><path d="M12.5 9.5l5 5M17.5 9.5l-5 5"/></svg></button>
        </div>
      </div>

      <button type="button" class="kp-lock__link kp-lock__link--sub" id="kpLockToPass">เข้าด้วยชื่อผู้ใช้และรหัสผ่าน</button>
    </div>
  </div>

  <!-- Popup Error -->
  <div id="kpLoginPopup" role="dialog" aria-live="polite">
    <div class="cardx">
      <div class="iconx" aria-hidden="true">!</div>
      <div class="titlex"><?php echo htmlspecialchars($popupTitle, ENT_QUOTES, "UTF-8"); ?></div>
      <div class="textx"><?php echo htmlspecialchars($popupText, ENT_QUOTES, "UTF-8"); ?></div>
      <button type="button" class="btn btn-danger btnx" id="kpLoginPopupBtn">ตกลง</button>
    </div>
  </div>

  <script>
    try{
      localStorage.removeItem("index2_map_state");
      localStorage.removeItem("index2_sheet_state");
    }catch(e){}
  </script>
  <script>
    (function(){
      // หมายเหตุ: เก็บรหัสผ่านไว้ใน localStorage แบบข้อความล้วน (ไม่เข้ารหัส)
      // ตามที่ผู้ใช้ต้องการให้ "จดจำฉัน" จำรหัสผ่านด้วย ไม่ใช่แค่ username
      var REMEMBER_KEY = "kp_remember_username";
      var REMEMBER_PASS_KEY = "kp_remember_password";
      var userInput = document.getElementById("username");
      var passInput = document.getElementById("password");
      var rememberBox = document.getElementById("remember_ui");
      var form = userInput ? userInput.closest("form") : null;

      try{
        var saved = localStorage.getItem(REMEMBER_KEY);
        var savedPass = localStorage.getItem(REMEMBER_PASS_KEY);
        if (saved) {
          userInput.value = saved;
          rememberBox.checked = true;
        }
        if (savedPass && passInput) {
          passInput.value = savedPass;
        }
      }catch(e){}

      if (form) {
        form.addEventListener("submit", function(){
          try{
            if (rememberBox.checked) {
              localStorage.setItem(REMEMBER_KEY, userInput.value || "");
              localStorage.setItem(REMEMBER_PASS_KEY, (passInput && passInput.value) || "");
            } else {
              localStorage.removeItem(REMEMBER_KEY);
              localStorage.removeItem(REMEMBER_PASS_KEY);
            }
          }catch(e){}
        });
      }
    })();
  </script>
  <script src="/json/kp_passkey.js?v=3"></script>
  <script>

    // หน้าล็อกแบบแอปธนาคาร (คำขอ 2026-09-29, ปรับ 2026-09-30 ตัดสแกนหน้าออก):
    //   เปิดแอป -> ใส่ PIN 6 หลัก (ผิด 3 ครั้งล็อก) -> ชื่อผู้ใช้/รหัสผ่าน
    (function(){
      var P = window.kpPasskey;
      if (!P) return;
      var RES = <?php echo json_encode((string)$res); ?>;
      if (["hold", "resigned", "wrong", "worgpassword", "reset", "kicked"].indexOf(RES) >= 0) return;
      var hasFace = false; // ตัดสแกนใบหน้าออก ใช้ PIN อย่างเดียว (คำขอ 2026-09-30)
      var hasPin = P.hasPin();
      if (!hasFace && !hasPin) return;

      var lock = document.getElementById("kpLock");
      var stFace = document.getElementById("kpLockFace");
      var stPin = document.getElementById("kpLockPin");
      var faceBtn = document.getElementById("kpLockFaceBtn");
      var faceMsg = document.getElementById("kpLockFaceMsg");
      var toPinBtn = document.getElementById("kpLockToPin");
      var toPass = document.getElementById("kpLockToPass");
      var pinMsg = document.getElementById("kpLockPinMsg");
      var dots = document.getElementById("kpLockDots").children;
      var dotsBox = document.getElementById("kpLockDots");
      var pad = document.getElementById("kpLockPad");
      var padFace = document.getElementById("kpLockPadFace");
      var notice = document.getElementById("kpPassNotice");
      var MAX_FACE = 2;
      var faceFails = 0, faceBusy = false, pin = "", pinBusy = false;

      document.getElementById("kpLockName").textContent = P.savedName() || P.enrolledUser() || "";
      lock.hidden = false;
      // หน้าใส่ PIN ใช้แป้นบนจอ -> ปิดฟอร์มด้านหลังไว้ ไม่ให้ช่องกรอกถูกโฟกัสจนคีย์บอร์ดเด้ง (คำขอ 2026-10-01)
      var bgForm = document.querySelector("form");
      try { if (document.activeElement && document.activeElement.blur) document.activeElement.blur(); } catch (eB) {}
      try { if (bgForm) { bgForm.setAttribute("inert", ""); bgForm.setAttribute("aria-hidden", "true"); } } catch (eI) {}
      try { var pop = document.getElementById("kpLoginPopup"); if (pop) pop.classList.remove("is-show"); } catch (e) {}

      function setMsg(el, t, bad){ el.textContent = t || ""; el.classList.toggle("is-bad", !!bad); }
      function goPass(t){
        lock.hidden = true;
        try { if (bgForm) { bgForm.removeAttribute("inert"); bgForm.removeAttribute("aria-hidden"); } } catch (eI2) {}
        if (notice) notice.textContent = t || "";
      }
      function goRedirect(res){ location.href = res.redirect; }

      // ---------- สแกนใบหน้า ----------
      function showFace(){
        stPin.hidden = true; stFace.hidden = false;
        toPinBtn.hidden = !hasPin;
      }
      function tryFace(auto){
        if (faceBusy) return;
        faceBusy = true;
        faceBtn.classList.add("is-busy");
        setMsg(faceMsg, "กำลังสแกนใบหน้า...");
        var t0 = Date.now();
        P.login().then(goRedirect).catch(function(e){
          faceBusy = false;
          faceBtn.classList.remove("is-busy");
          // เบราว์เซอร์บางตัว (iPhone) ไม่ยอมเปิดสแกนเองโดยไม่แตะก่อน -> ไม่นับเป็นครั้งที่พลาด
          if (auto && Date.now() - t0 < 900) { setMsg(faceMsg, "แตะเพื่อสแกนใบหน้า"); return; }
          faceFails++;
          if (faceFails >= MAX_FACE) {
            if (hasPin) showPin("สแกนใบหน้าไม่ผ่าน กรุณาใส่ PIN", true);
            else goPass("สแกนใบหน้าไม่ผ่าน กรุณาเข้าด้วยชื่อผู้ใช้และรหัสผ่าน");
            return;
          }
          setMsg(faceMsg, "สแกนไม่ผ่าน แตะเพื่อลองอีกครั้ง (เหลือ " + (MAX_FACE - faceFails) + " ครั้ง)", true);
        });
      }
      faceBtn.addEventListener("click", function(){ tryFace(false); });
      toPinBtn.addEventListener("click", function(){ showPin("ใส่ PIN 6 หลัก"); });

      // ---------- PIN ----------
      function paintDots(){ for (var i = 0; i < dots.length; i++) dots[i].classList.toggle("is-on", i < pin.length); }
      function showPin(t, bad){
        stFace.hidden = true; stPin.hidden = false;
        pin = ""; paintDots();
        setMsg(pinMsg, t || "ใส่ PIN 6 หลัก", bad);
        // ปุ่มซ้ายล่างของแป้น = กลับไปสแกนหน้า (เฉพาะเมื่อยังเหลือสิทธิ์สแกน)
        padFace.classList.toggle("kp-lock__key--blank", !(hasFace && faceFails < MAX_FACE));
      }
      function shake(){ dotsBox.classList.remove("is-shake"); void dotsBox.offsetWidth; dotsBox.classList.add("is-shake"); }
      function submitPin(){
        pinBusy = true;
        setMsg(pinMsg, "กำลังตรวจสอบ...");
        P.pinLogin(pin).then(function(res){
          if (res && res.redirect) { goRedirect(res); return; }
          pinBusy = false; pin = ""; paintDots(); shake();
          if (res && res.locked) { goPass(res.msg || "ใส่ PIN ผิดเกินกำหนด กรุณาเข้าด้วยชื่อผู้ใช้และรหัสผ่าน"); return; }
          setMsg(pinMsg, (res && res.msg) || "PIN ไม่ถูกต้อง", true);
        }).catch(function(){
          pinBusy = false; pin = ""; paintDots();
          setMsg(pinMsg, "เชื่อมต่อไม่ได้ ลองใหม่อีกครั้ง", true);
        });
      }
      // ตอบสนองทันทีที่นิ้วแตะ (pointerdown) ไม่ต้องรอ click ซึ่งช้ากว่าบนมือถือ — คำขอ 2026-10-02
      function padPress(b){
        if (!b || pinBusy) return;
        var k = b.getAttribute("data-k");
        if (k === "face") { if (!b.classList.contains("kp-lock__key--blank")) { showFace(); tryFace(false); } return; }
        if (k === "del") { pin = pin.slice(0, -1); paintDots(); return; }
        if (pin.length >= 6) return;
        pin += k; paintDots();
        if (pin.length === 6) setTimeout(submitPin, 60);
      }
      var lastPtr = 0;
      pad.addEventListener("pointerdown", function(e){
        var b = e.target.closest ? e.target.closest(".kp-lock__key") : null;
        if (!b) return;
        e.preventDefault();               // กันซูม/โฟกัส/เลือกข้อความ ที่ทำให้หน่วง
        lastPtr = Date.now();
        b.classList.add("is-press");
        setTimeout(function(){ b.classList.remove("is-press"); }, 120);
        padPress(b);
      });
      // สำรองสำหรับคีย์บอร์ด/เครื่องที่ไม่มี pointer event (ข้ามถ้าเพิ่งกดด้วยนิ้วไปแล้ว)
      pad.addEventListener("click", function(e){
        if (Date.now() - lastPtr < 700) return;
        padPress(e.target.closest ? e.target.closest(".kp-lock__key") : null);
      });

      toPass.addEventListener("click", function(){ goPass(""); });

      // iPhone/iPad: Safari ไม่ยอมให้เว็บเปิด Face ID เองโดยไม่มีการแตะ ถ้าเรียกเองจะขึ้นแผ่น "ลงชื่อเข้า ... ด้วยพาสคีย์"
      // -> รอให้แตะตรงไหนก็ได้บนหน้าล็อก 1 ครั้ง แล้วเปิด Face ID ทันที (ไม่มีแผ่นลงชื่อเข้า) — คำขอ 2026-09-30
      var IS_IOS = /iP(hone|ad|od)/.test(navigator.userAgent || "") ||
        (navigator.platform === "MacIntel" && (navigator.maxTouchPoints || 0) > 1);
      function iosTapAnywhere(e){
        if (stFace.hidden || faceBusy) return;
        var t = e.target;
        if (t && t.closest && (t.closest("#kpLockToPass") || t.closest("#kpLockToPin") || t.closest("#kpLockFaceBtn"))) return;
        tryFace(false);
      }

      // เริ่ม: มีสแกนหน้า -> Android สแกนอัตโนมัติทันที / iPhone แตะหน้าจอ 1 ครั้ง / มีแต่ PIN -> แป้น PIN
      if (hasFace) {
        showFace();
        if (IS_IOS) {
          setMsg(faceMsg, "แตะที่หน้าจอเพื่อสแกนใบหน้า");
          lock.addEventListener("click", iosTapAnywhere);
        } else {
          setTimeout(function(){ tryFace(true); }, 350);
        }
      }
      else showPin("ใส่ PIN 6 หลัก");
    })();
  </script>
  <script src="/lib/jquery-3.5.1.min.js"></script>
  <script src="/lib/bootstrap-4.5.2-dist/js/bootstrap.min.js"></script>
  <script src="/lib/popper.min.js"></script>

  <?php if ($showPopup) { ?>
  <script>
    (function(){
      var pop = document.getElementById("kpLoginPopup");
      var btn = document.getElementById("kpLoginPopupBtn");
      function closePop(){
        pop.classList.remove("is-show");
        try{ history.replaceState({}, document.title, "index.php"); }catch(e){}
      }
      btn.addEventListener("click", closePop);
      pop.addEventListener("click", function(e){
        if (e.target === pop) closePop();
      });
      pop.classList.add("is-show");
    })();
  </script>
  <?php } ?>
</body>
</html>
