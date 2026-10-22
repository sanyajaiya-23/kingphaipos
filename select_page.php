<?php
// select_page.php — หน้าเลือกเมนู (ปรับใหม่ให้ทันสมัย)
require($_SERVER['DOCUMENT_ROOT'] . "/inc/db/db_webdev.php");
require($_SERVER['DOCUMENT_ROOT'] . "/inc/auth.php");
require($_SERVER['DOCUMENT_ROOT'] . "/inc/branch.php");
kp_require_login(); // lockout 4 ชม.
$kpBranches = kp_branches_for_user();
$kpBranchId = kp_current_branch_id();

$res = isset($_GET["res"]) ? (string)$_GET["res"] : "";
$isAdmin = isset($_SESSION["id_status"]) && (int)$_SESSION["id_status"] === 1;
// พนักงานที่เลือกสาขาแล้ว -> เข้าหน้าขายตลอดจนกว่าจะออกจากระบบ
if (!$isAdmin && !empty($_SESSION["branch_picked"])) { header("Location:/pos_sale.php"); exit; }
$idStatus = isset($_SESSION["id_status"]) ? (int)$_SESSION["id_status"] : 0;
$kpName = trim((string)($_SESSION["admin_name"] ?? ($_SESSION["admin_user"] ?? "")));
$kpRole = $isAdmin ? "แอดมิน" : "พนักงาน";
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="theme-color" content="#e5e8f7">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="KP">
  <title>KINGPHAI</title>
  <link rel="stylesheet" href="/lib/bootstrap-4.5.2-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/lib/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="manifest" href="manifest.json?v=4">
  <link rel="apple-touch-icon" sizes="180x180" href="img/icon_180.png?v=2">
  <style>
    :root{
      --kp-text: #0f172a;
      --kp-sub: rgba(15,23,42,0.66);
      --kp-border: rgba(255,255,255,0.65);
      --kp-line: rgba(15,23,42,0.10);
      --kp-panel: rgba(255,255,255,0.55);
      --kp-panel-strong: rgba(255,255,255,0.68);
      --kp-card: rgba(255,255,255,0.52);
      --kp-shadow: 0 30px 80px rgba(20,90,60,0.16);
      --kp-green: #16a34a;
      --kp-green2: #22c55e;
      --kp-blue: #38bdf8;
      --kp-cyan: #7dd3fc;
      --kp-dark: #0a1f14;
    }
    /* หมายเหตุ: ห้ามตั้ง background ให้ html
       เพราะจะตัดการ propagate พื้นหลังของ body ไปทาเต็ม canvas (ทั้งจอ รวม safe-area) */
    body{
      min-height: 100vh;
      min-height: 100dvh;
      background:
        radial-gradient(900px 560px at 12% 8%, rgba(56,178,232,0.60), transparent 60%),
        radial-gradient(820px 520px at 88% 10%, rgba(94,214,255,0.55), transparent 62%),
        radial-gradient(760px 480px at 50% 58%, rgba(72,115,214,0.50), transparent 64%),
        radial-gradient(700px 460px at 92% 88%, rgba(178,120,244,0.34), transparent 62%),
        radial-gradient(900px 640px at 50% 102%, rgba(255,255,255,0.14), transparent 70%),
        linear-gradient(150deg, #4aaed4 0%, #3860bf 30%, #2f58b8 55%, #48add4 78%, #5c82db 100%);
      color: var(--kp-text);
      overflow-x: hidden;
    }
    .kp-fabric-bg{
      position:fixed;
      inset:0;
      z-index:0;
      pointer-events:none;
      background:
        radial-gradient(520px 340px at 18% 22%, rgba(30,81,200,0.55), transparent 70%),
        radial-gradient(480px 320px at 82% 16%, rgba(60,160,235,0.52), transparent 70%),
        radial-gradient(560px 360px at 50% 88%, rgba(60,190,130,0.45), transparent 72%),
        radial-gradient(420px 300px at 90% 78%, rgba(160,100,230,0.32), transparent 70%),
        linear-gradient(180deg, rgba(15,26,52,0.22), rgba(15,26,52,0.00) 45%);
      filter:blur(30px) saturate(1.2);
      opacity:0.95;
    }
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
        0 0 4px rgba(10,22,50,0.25);
      animation-name:kp-bubble-rise;
      animation-timing-function:ease-in;
      animation-iteration-count:infinite;
      will-change:transform, opacity;
    }
    @keyframes kp-bubble-rise{
      0%{ transform:translate(0,0); opacity:0; }
      10%{ opacity:0.9; }
      50%{ transform:translate(16px,-58vh); }
      90%{ opacity:0.7; }
      100%{ transform:translate(-12px,-118vh); opacity:0; }
    }
    @media (prefers-reduced-motion: reduce){
      .kp-bubble{ animation:none; display:none; }
    }
    .kp-shell{
      min-height: 100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding: 28px 14px;
      position: relative;
      overflow: hidden;
    }
    .kp-shell:before,
    .kp-shell:after{
      content:"";
      position:absolute;
      border-radius:999px;
      filter: blur(16px);
      pointer-events:none;
      opacity:.8;
    }
    .kp-shell:before{
      width: 360px;
      height: 360px;
      top: -120px;
      right: -80px;
      background: radial-gradient(circle at 30% 30%, rgba(94,211,255,0.38), rgba(94,211,255,0) 68%);
    }
    .kp-shell:after{
      width: 340px;
      height: 340px;
      left: -120px;
      bottom: -120px;
      background: radial-gradient(circle at 30% 30%, rgba(89,139,255,0.30), rgba(89,139,255,0) 72%);
    }
    .kp-panel{
      width: min(980px, 96vw);
      border-radius: 28px;
      border: 1px solid var(--kp-border);
      background:
        linear-gradient(180deg, rgba(255,255,255,0.55), rgba(255,255,255,0.30)),
        linear-gradient(180deg, var(--kp-panel), rgba(255,255,255,0.30));
      -webkit-backdrop-filter: blur(34px) saturate(160%);
      backdrop-filter: blur(34px) saturate(160%);
      box-shadow:
        var(--kp-shadow),
        inset 0 1px 0 rgba(255,255,255,0.85),
        inset 0 -1px 0 rgba(255,255,255,0.20);
      overflow:hidden;
      position: relative;
      z-index: 1;
    }
    .kp-panel:before{
      content:"";
      position:absolute;
      inset: -160px -160px auto auto;
      width: 420px;
      height: 420px;
      border-radius: 999px;
      background: radial-gradient(circle at 30% 30%, rgba(56,189,248,0.22), rgba(56,189,248,0.0) 70%);
      pointer-events: none;
    }
    .kp-panel:after{
      content:"";
      position:absolute;
      inset: 1px;
      border-radius: 27px;
      border: 1px solid rgba(255,255,255,0.55);
      pointer-events:none;
    }
    .kp-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      padding: 18px 18px 14px 18px;
      border-bottom: 1px solid var(--kp-line);
      position: relative;
      z-index: 1;
    }
    .kp-brand{
      display:flex;
      align-items:center;
      gap: 12px;
      min-width: 0;
    }
    .kp-logo{
      width: 52px;
      height: 52px;
      border-radius: 999px;
      background:
        linear-gradient(180deg, rgba(255,255,255,0.65), rgba(255,255,255,0.30)),
        rgba(255,255,255,0.30);
      border: 1px solid rgba(255,255,255,0.80);
      display:flex;
      align-items:center;
      justify-content:center;
      overflow:hidden;
      flex: 0 0 auto;
      box-shadow: 0 16px 30px rgba(20,90,60,0.16);
    }
    .kp-logo img{
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 999px;
    }
    .kp-title{
      font-weight: 1000;
      font-size: 20px;
      line-height: 1.1;
      letter-spacing: .3px;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
      color: var(--kp-text);
    }
    .kp-subtitle{
      margin-top: 4px;
      font-size: 12px;
      font-weight: 900;
      color: var(--kp-sub);
    }
    .kp-badge{
      padding: 7px 12px;
      border-radius: 999px;
      border: 1px solid rgba(255,255,255,0.70);
      background: rgba(255,255,255,0.45);
      font-weight: 900;
      font-size: 12px;
      color: var(--kp-text);
      white-space: nowrap;
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.55);
    }
    .kp-badge--logo{
      width: 52px;
      height: 52px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 999px;
      background:
        linear-gradient(180deg, rgba(255,255,255,0.65), rgba(255,255,255,0.30)),
        rgba(255,255,255,0.30);
      border: 1px solid rgba(255,255,255,0.80);
      box-shadow: 0 14px 28px rgba(20,90,60,0.14);
    }
    .kp-badge--logo img{
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 999px;
    }
    .kp-body{
      padding: 10px 18px 18px 18px;
      position: relative;
      z-index: 1;
    }
    .kp-cards{
      display:grid;
      grid-template-columns: repeat(3, minmax(0,1fr));
      gap: 14px;
    }
    .kp-cards.kp-cards--two{
      grid-template-columns: repeat(2, minmax(0,1fr));
    }
    .kp-card{
      position: relative;
      border-radius: 22px;
      border: 1px solid rgba(255,255,255,0.65);
      background:
        linear-gradient(180deg, rgba(255,255,255,0.55), rgba(255,255,255,0.28)),
        var(--kp-card);
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.70),
        0 18px 38px rgba(20,90,60,0.14);
      padding: 16px 16px 16px 16px;
      min-height: 148px;
      overflow:hidden;
      transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
    }
    .kp-card:before{
      content:"";
      position:absolute;
      inset:-50px -40px auto auto;
      width: 140px;
      height: 140px;
      border-radius: 999px;
      background: radial-gradient(circle at 30% 30%, rgba(34,197,94,0.18), rgba(34,197,94,0.0) 70%);
      transform: rotate(12deg);
    }
    .kp-card:after{
      content:"";
      position:absolute;
      inset:0;
      background:
        linear-gradient(135deg, rgba(255,255,255,0.35), rgba(255,255,255,0.00) 34%),
        radial-gradient(180px 40px at 50% 100%, rgba(255,255,255,0.12), transparent 60%);
      pointer-events:none;
    }
    .kp-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 24px 52px rgba(20,90,60,0.20);
      border-color: rgba(56,189,248,0.35);
    }
    .kp-card__top{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 10px;
      position: relative;
      z-index: 1;
    }
    .kp-ic{
      width: 44px;
      height: 44px;
      border-radius: 16px;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size: 18px;
      font-weight: 900;
      color: #ffffff;
      box-shadow: 0 14px 28px rgba(20,90,60,0.18);
      flex: 0 0 auto;
    }
    .kp-ic--tablet{ background: linear-gradient(140deg, #fb7185, #ef4444); color: #ffffff; }
    .kp-ic--mobile{ background: linear-gradient(140deg, #22c55e, #16a34a); color: #ffffff; }
    .kp-ic--logout{ background: linear-gradient(140deg, #60a5fa, #3b82f6); color: #ffffff; }
    .kp-card__title{
      margin-top: 10px;
      font-size: 15px;
      font-weight: 1000;
      position: relative;
      z-index: 1;
      color: var(--kp-text);
    }
    .kp-card__desc{
      margin-top: 4px;
      font-size: 12px;
      font-weight: 900;
      color: var(--kp-sub);
      position: relative;
      z-index: 1;
    }
    .kp-card__btn{
      margin-top: 14px;
      width: 100%;
      border-radius: 999px;
      font-weight: 1000;
      position: relative;
      z-index: 1;
      border: 1px solid rgba(255,255,255,0.30);
      box-shadow:
        0 14px 28px rgba(20,90,60,0.16),
        inset 0 1px 0 rgba(255,255,255,0.30);
      background-image: none !important;
    }
    .kp-card .btn-danger{
      background: linear-gradient(140deg, rgba(248,113,113,0.94), rgba(239,68,68,0.90)) !important;
    }
    .kp-card .btn-success{
      background: linear-gradient(140deg, rgba(34,197,94,0.94), rgba(22,163,74,0.90)) !important;
    }
    .kp-card .btn-primary{
      background: linear-gradient(140deg, rgba(96,165,250,0.94), rgba(59,130,246,0.90)) !important;
    }
    .kp-disabled{
      opacity: 0.55;
      filter: grayscale(0.15);
    }
    .kp-disabled .kp-card__btn{
      pointer-events: none;
    }
    .kp-disabled:hover{
      transform: none;
      box-shadow: 0 16px 34px rgba(17,24,39,0.10);
      border-color: rgba(17,24,39,0.10);
    }

    @media (max-width: 900px){
      .kp-cards{ grid-template-columns: 1fr; }
      .kp-cards.kp-cards--two{ grid-template-columns: 1fr; }
      .kp-card{ min-height: 132px; }
      .kp-head{ padding: 16px 16px 12px 16px; }
      .kp-body{ padding: 8px 16px 16px 16px; }
    }

    @media (max-width: 575.98px){
      .kp-panel{
        width: min(100%, calc(100vw - 18px));
        border-radius: 24px;
      }
      .kp-panel:after{
        border-radius: 23px;
      }
      .kp-title{
        font-size: 18px;
      }
      .kp-subtitle{
        font-size: 11px;
      }
      .kp-badge--logo{
        display:none;
      }
    }

    /* ปุ่มเมนูทรงกลมแบบ Neumorphism ตามรูปตัวอย่างที่ผู้ใช้ส่งมา (คำขอ 2026-09-28)
       แผ่นกลมสีขาวนวล + วงแสงจาง ๆ รอบนอก + แสงสีเรืองออกจากขอบล่าง (สีต่างกันตามปุ่ม) + ไอคอนเส้นบางสีเข้ม */
    .kp-icons{
      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      gap: 44px;
      padding: 40px 0 26px 0;
    }
    .kp-iconbtn{
      --rim: #fbbf24;                        /* สีแสงขอบล่าง */
      --rim-glow: rgba(251,191,36,0.55);
      --size: 112px;
      position: relative;
      width: var(--size);
      height: var(--size);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none !important;
      background: linear-gradient(180deg, #f7f7f6 0%, #e9e9e7 100%);
      box-shadow:
        inset 0 2px 1px rgba(255,255,255,0.95),      /* ขอบบนสว่าง */
        inset 0 -2px 3px rgba(0,0,0,0.06),
        0 4px 1px -1px var(--rim),                   /* เส้นแสงสีที่ขอบล่าง */
        0 9px 18px -3px var(--rim-glow),             /* แสงสีฟุ้งใต้ปุ่ม */
        0 22px 34px -10px rgba(40,40,40,0.28),       /* เงาลอยจากพื้น */
        0 0 0 calc(var(--size) * 0.17) rgba(255,255,255,0.32),   /* วงแสงจาง ๆ รอบนอก (halo) */
        0 0 calc(var(--size) * 0.22) calc(var(--size) * 0.12) rgba(255,255,255,0.20);
      transition: box-shadow .25s ease, translate .2s ease;
      -webkit-tap-highlight-color: transparent;
      opacity: 0;
      scale: .94;
      animation: kp-tile-in .6s cubic-bezier(.2,.8,.2,1) forwards;
    }
    .kp-iconbtn:nth-child(2){ animation-delay: .08s; }
    @keyframes kp-tile-in{ to{ opacity: 1; scale: 1; } }
    .kp-iconbtn__orb{
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .kp-iconbtn svg{
      width: calc(var(--size) * 0.36);
      height: calc(var(--size) * 0.36);
      stroke: #27292f;
      fill: none;
      stroke-width: 1.4;
      stroke-linecap: round;
      stroke-linejoin: round;
      filter: drop-shadow(0 1px 1px rgba(0,0,0,0.12));
    }
    .kp-iconbtn:hover{
      box-shadow:
        inset 0 2px 1px rgba(255,255,255,0.95),
        inset 0 -2px 3px rgba(0,0,0,0.06),
        0 5px 1px -1px var(--rim),
        0 12px 26px -2px var(--rim-glow),
        0 24px 38px -10px rgba(40,40,40,0.30),
        0 0 0 calc(var(--size) * 0.17) rgba(255,255,255,0.40),
        0 0 calc(var(--size) * 0.22) calc(var(--size) * 0.12) rgba(255,255,255,0.24);
    }
    /* กดแล้วยุบลง แสงขอบล่างหรี่ลง */
    .kp-iconbtn:active{
      translate: 0 2px;
      transition-duration: .08s;
      box-shadow:
        inset 0 2px 5px rgba(0,0,0,0.10),
        0 2px 1px -1px var(--rim),
        0 5px 10px -3px var(--rim-glow),
        0 10px 18px -10px rgba(40,40,40,0.25),
        0 0 0 calc(var(--size) * 0.17) rgba(255,255,255,0.32);
    }
    .kp-iconbtn:focus-visible{ outline: 3px solid var(--rim); outline-offset: 6px; }
    .kp-iconbtn--tablet{ --rim: #a78bfa; --rim-glow: rgba(139,92,246,0.55); }
    .kp-iconbtn--mobile{ --rim: #3464d3; --rim-glow: rgba(16,67,185,0.60); }
    .kp-iconbtn--main{ --size: 132px; }
    .kp-iconbtn--logout{ --rim: #f87171; --rim-glow: rgba(239,68,68,0.60); --size: 66px; }
    .kp-iconbtn--logout svg{ stroke: #b91c1c; stroke-width: 1.8; }
    .kp-icons--bottom{ padding: 6px 0 14px 0; }
    .kp-icons--bottom .kp-iconbtn{ animation-delay: .18s; }
    .kp-iconbtn.is-busy{ pointer-events: none; }
    .kp-iconbtn.is-busy svg{ animation: kp-spin .9s linear infinite; }
    @keyframes kp-spin{ to{ transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce){
      .kp-iconbtn{ animation: none; opacity: 1; scale: 1; }
    }
    @media (max-width: 380px){
      .kp-icons{ gap: 34px; }
      .kp-iconbtn{ --size: 96px; }
      .kp-iconbtn--main{ --size: 114px; }
      .kp-iconbtn--logout{ --size: 60px; }
    }

    /* -----------------------------
       Permission Prompt (หลัง login)
    ------------------------------*/
    .kp-perm-modal{
      position: fixed;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 18px 14px;
      background: rgba(20,32,60,0.20);
      z-index: 99999;
      -webkit-backdrop-filter: blur(10px) saturate(160%);
      backdrop-filter: blur(10px) saturate(160%);
    }
    .kp-perm-card{
      width: min(560px, 94vw);
      border-radius: 22px;
      border: 1px solid rgba(255,255,255,0.75);
      background:
        linear-gradient(180deg, rgba(255,255,255,0.90), rgba(255,255,255,0.72));
      color: var(--kp-text);
      box-shadow: 0 28px 80px rgba(20,32,60,0.22);
      -webkit-backdrop-filter: blur(24px) saturate(160%);
      backdrop-filter: blur(24px) saturate(160%);
      overflow: hidden;
    }
    .kp-perm-head{
      padding: 14px 14px 10px 14px;
      border-bottom: 1px solid var(--kp-line);
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
    }
    .kp-perm-title{
      font-size: 14px;
      font-weight: 1100;
      color: var(--kp-text);
      letter-spacing: 0.1px;
    }
    .kp-perm-close{
      appearance: none;
      border: 0;
      background: transparent;
      font-size: 20px;
      line-height: 1;
      color: var(--kp-sub);
      padding: 8px 10px;
    }
    .kp-perm-body{
      padding: 12px 14px 14px 14px;
      color: var(--kp-sub);
      font-size: 13px;
      font-weight: 900;
      line-height: 1.45;
    }
    .kp-perm-list{
      margin: 10px 0 0 0;
      padding: 0;
      list-style: none;
      display: grid;
      gap: 10px;
    }
    .kp-perm-item{
      border: 1px solid rgba(15,23,42,0.10);
      background: rgba(255,255,255,0.55);
      border-radius: 16px;
      padding: 10px 10px;
      display:flex;
      gap: 10px;
      align-items:flex-start;
    }
    .kp-perm-ic{
      flex: 0 0 auto;
      width: 36px;
      height: 36px;
      border-radius: 12px;
      display:flex;
      align-items:center;
      justify-content:center;
      background: rgba(22,163,74,0.14);
      color: var(--kp-green);
      font-size: 18px;
    }
    .kp-perm-k{
      font-weight: 1100;
      color: var(--kp-text);
      margin-bottom: 2px;
    }
    .kp-perm-v{
      font-weight: 900;
      color: var(--kp-sub);
      font-size: 12px;
    }
    .kp-perm-actions{
      padding: 12px 14px 14px 14px;
      display:flex;
      gap: 10px;
      justify-content:flex-end;
      border-top: 1px solid var(--kp-line);
      background: rgba(255,255,255,0.30);
    }
    .kp-perm-btn{
      border-radius: 999px;
      font-weight: 1100;
      padding: 10px 14px;
      border: 1px solid rgba(15,23,42,0.16);
      background: rgba(255,255,255,0.60);
      color: var(--kp-text);
    }
    .kp-perm-btn--primary{
      border-color: rgba(34,197,94,0.35);
      background: linear-gradient(140deg, #22c55e, #16a34a);
      color: #ffffff;
    }

    /* =====================================================================
       ธีม "กระดาษสามมิติ" (Neumorphism บนกระดาษเนื้อจุด) ตามรูปตัวอย่าง — คำขอ 2026-09-28
       ===================================================================== */
    :root{
      --np-bg: #e4e6e9;
      --np-ink: #3f4a5a;
      --np-sub: #7b8594;
      --np-dark: rgba(163,172,186,0.60);
      --np-light: rgba(255,255,255,0.92);
      --np-dots: radial-gradient(rgba(60,70,85,0.10) 0.7px, transparent 0.8px);
    }
    body.np{
      background: var(--np-bg);
      background-image: var(--np-dots);
      background-size: 4px 4px;
      color: var(--np-ink);
    }
    .np-stage{
      min-height: 100vh;
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 28px 16px;
    }
    /* แผ่นกระดาษหลัก (ยกขึ้นจากพื้น) */
    .np-paper{
      width: min(440px, 100%);
      border-radius: 40px;
      padding: 30px 24px 26px;
      background: var(--np-bg);
      background-image: var(--np-dots);
      background-size: 4px 4px;
      box-shadow:
        22px 22px 48px rgba(150,160,175,0.55),
        -18px -18px 40px rgba(255,255,255,0.95),
        inset 0 1px 0 rgba(255,255,255,0.8);
    }
    /* นูน / ยุบ แบบ neumorphism ใช้ร่วมกันทุกกล่อง */
    .np-raised{
      background: var(--np-bg);
      background-image: var(--np-dots);
      background-size: 4px 4px;
      border-radius: 18px;
      box-shadow: 8px 8px 16px var(--np-dark), -8px -8px 16px var(--np-light);
    }
    /* ---- หัว: นาฬิกาใหญ่ + วัน/วันที่ + โลโก้ ---- */
    /* ---- วิดเจ็ตนาฬิกา ---- */
.kp-wg{box-sizing:border-box;display:grid;grid-template-columns:36cqw 1fr;align-items:center;gap:4cqw;width:100%;padding:4.5cqw 5cqw 4.5cqw 4.5cqw;border-radius:8cqw;
  font-family:"Prompt","Noto Sans Thai",system-ui,sans-serif;color:#1b2236;
  background:linear-gradient(145deg,#fff1f8 0%,#f0e9ff 55%,#e2f0ff 100%);
  box-shadow:0 10px 22px rgba(120,134,170,.28),0 2px 0 rgba(255,255,255,.95) inset,0 -3px 8px rgba(160,170,200,.18) inset}
.kp-wg__clk{width:100%;aspect-ratio:1;filter:drop-shadow(0 6px 8px rgba(120,100,180,.35))}
.kp-wg__clk svg{width:100%;height:100%;display:block}
.kp-wg__clk line{stroke-linecap:round}
.kp-wg__clk .hh{stroke:#161a26;stroke-width:4.2}.kp-wg__clk .mm{stroke:#161a26;stroke-width:3.2}
.kp-wg__clk .ss{stroke:#ef3b5a;stroke-width:1.1}.kp-wg__clk .cd{fill:#ef3b5a;stroke:#fff;stroke-width:.8}
.kp-wg__inf{min-width:0;display:flex;flex-direction:column;gap:1cqw}
.kp-wg__inf small{font-size:3.7cqw;color:#7a8499;font-weight:500;line-height:1.1}
.kp-wg__inf>b{font-size:5.6cqw;font-weight:600;line-height:1.15;color:#1b2236;white-space:nowrap;padding-bottom:2cqw;border-bottom:1px solid rgba(122,132,153,.3)}
.kp-wg__wx{display:grid;grid-template-columns:repeat(3,1fr);gap:1cqw;padding-top:1.4cqw;text-align:center}
.kp-wg__wx>div{display:flex;flex-direction:column;align-items:center;gap:.4cqw;min-width:0}
.kp-wg__wx .ic{width:7.2cqw;height:7.2cqw;color:#6a4fe0;opacity:.35}
.kp-wg__wx.is-on .ic{opacity:1}
.kp-wg__wx .ic svg{width:100%;height:100%;display:block}
.kp-wg__wx b{font-size:4.9cqw;font-weight:500;line-height:1;color:#1b2236}
.kp-wg__wx em{font-style:normal;font-size:3.2cqw;color:#7a8499;line-height:1}
.kp-wg__mini{display:none}
.np-wgbox{container-type:inline-size;container-name:kpwgc;width:100%;max-width:440px;margin:0 auto}
.np-wgbox .kp-wg{grid-template-columns:30cqw 1fr;gap:3.5cqw;padding:3.5cqw 4.5cqw 3.5cqw 3.5cqw;border-radius:7cqw}
.np-wgbox .kp-wg__inf small{font-size:3.8cqw}
.np-wgbox .kp-wg__inf>b{font-size:5.6cqw;padding-bottom:1.6cqw}
    /* หน้า select แอดมิน: ปุ่มหลักแค่ 2 + ฟันเฟือง */
    .np-tiles--main{ display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-top:16px; }
    .np-tilewrap{ position:relative; display:flex; }
    .np-tilewrap .np-tile{ flex:1; }
    .np-gear{ position:absolute; top:8px; right:8px; width:34px; height:34px; border-radius:12px; display:grid; place-items:center; background:#fff; box-shadow:0 2px 8px rgba(18,40,90,.14); }
    .np-gear svg{ width:19px; height:19px; fill:none; stroke:#7b5cf0; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
    .np-gear:active{ transform:scale(.94); }
    .np-clock .np-colon{ animation:npBlink 1s steps(1) infinite; }
    .np-clock small{ font-size:.38em; font-weight:500; margin-left:4px; opacity:.75; letter-spacing:0; }
    @keyframes npBlink{ 50%{ opacity:.25; } }
    .np-clock{ font-variant-numeric:tabular-nums; }
    .np-top{ display: flex; align-items: center; gap: 12px; padding: 0 4px; }
    .np-clock{
      font-size: 52px;
      font-weight: 300;
      line-height: 1;
      letter-spacing: -1px;
      background: linear-gradient(90deg, #f472b6 0%, #f472b6 39%, #38bdf8 41%, #38bdf8 100%);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      white-space: nowrap;
    }
    .np-date{ min-width: 0; line-height: 1.15; }
    .np-date__day{ font-size: 19px; font-weight: 600; color: var(--np-ink); }
    .np-date__d{ font-size: 13px; font-weight: 500; color: var(--np-sub); white-space: nowrap; }
    .np-logo{
      margin-left: auto;
      width: 46px; height: 46px; flex: 0 0 46px;
      border-radius: 50%;
      padding: 4px;
      box-shadow: 5px 5px 10px var(--np-dark), -5px -5px 10px var(--np-light);
      background: var(--np-bg);
    }
    .np-logo img{ width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block; }
    /* ---- แถบทักทาย (แบบแถบสภาพอากาศในตัวอย่าง) ---- */
    .np-bar{
      margin-top: 26px;
      padding: 14px 18px;
      display: flex; align-items: center; justify-content: space-between; gap: 10px;
      font-size: 15px; font-weight: 500; color: var(--np-ink);
    }
    .np-bar__name{ min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .np-bar__role{ font-size: 13px; font-weight: 600; color: var(--np-sub); white-space: nowrap; }
    .np-alert{ margin-top: 18px; padding: 12px 14px; border-radius: 14px; font-size: 13px; font-weight: 600; color: #be125a;
      box-shadow: inset 4px 4px 8px var(--np-dark), inset -4px -4px 8px var(--np-light); }
    /* ---- ปุ่มเมนู: กล่องนูนสี่เหลี่ยมมุมมน + ไอคอนเส้นสี ไม่มีข้อความ ---- */
    .np-tiles{
      margin-top: 30px;
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 24px;
    }
    .np-tiles--one{ grid-template-columns: minmax(0, 1fr); }
    .np-tile{
      aspect-ratio: 1 / 1;
      display: flex; align-items: center; justify-content: center;
      text-decoration: none !important;
      border-radius: 26px;
      transition: box-shadow .18s ease, transform .18s ease;
      -webkit-tap-highlight-color: transparent;
    }
    .np-tiles--one .np-tile{ aspect-ratio: auto; height: 150px; }
    .np-tile svg{
      width: 54px; height: 54px;
      fill: none; stroke: var(--ic, #38bdf8); stroke-width: 1.5;
      stroke-linecap: round; stroke-linejoin: round;
    }
    .np-tile:hover{ box-shadow: 10px 10px 20px var(--np-dark), -10px -10px 20px var(--np-light); }
    /* กดแล้วยุบลงไปในกระดาษ */
    .np-tile:active, .np-tile.is-busy{
      box-shadow: inset 6px 6px 12px var(--np-dark), inset -6px -6px 12px var(--np-light);
      transform: scale(.985);
    }
    .np-tile:focus-visible{ outline: 2px solid var(--ic); outline-offset: 4px; }
    .np-tile--mobile{ --ic: #22c55e; }
    .np-tile--tablet{ --ic: #ec4899; }
    .np-tile.is-busy{ pointer-events: none; }
    .np-tile.is-busy svg{ animation: np-spin .9s linear infinite; }
    @keyframes np-spin{ to{ transform: rotate(360deg); } }
    /* ออกจากระบบ: ปุ่มเล็ก ล่างสุด สีแดง */
    .np-bottom{ margin-top: 30px; display: flex; justify-content: center; }
    .np-logout{
      width: 64px; height: 64px; border-radius: 20px;
      display: flex; align-items: center; justify-content: center;
      text-decoration: none !important;
      transition: box-shadow .18s ease;
      -webkit-tap-highlight-color: transparent;
    }
    .np-logout svg{ width: 26px; height: 26px; fill: none; stroke: #ef4444; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .np-logout:active{ box-shadow: inset 5px 5px 10px var(--np-dark), inset -5px -5px 10px var(--np-light); }
    /* ===== สแกนใบหน้า / ลายนิ้วมือ (Passkey) — คำขอ 2026-09-28 ===== */
    .np-bottom{ gap: 22px; }
    .np-facebtn{
      width: 64px; height: 64px; border-radius: 20px; border: 0; padding: 0; position: relative;
      display: flex; align-items: center; justify-content: center;
      transition: box-shadow .18s ease; -webkit-tap-highlight-color: transparent;
    }
    .np-facebtn svg{ width: 28px; height: 28px; fill: none; stroke: #a855f7; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .np-facebtn:active{ box-shadow: inset 5px 5px 10px var(--np-dark), inset -5px -5px 10px var(--np-light); }
    .np-facebtn__on{ position: absolute; top: 6px; right: 6px; width: 12px; height: 12px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 2px var(--np-bg); display: none; }
    .np-facebtn.is-on .np-facebtn__on{ display: block; }
    .np-face{
      margin-top: 22px; padding: 14px; border-radius: 22px;
      display: flex; align-items: center; gap: 12px;
      background: linear-gradient(135deg, #efe6fb 0%, #fbe3ef 100%);
      box-shadow: 8px 8px 16px var(--np-dark), -8px -8px 16px var(--np-light);
    }
    .np-face__ic{ flex: 0 0 auto; width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.7); }
    .np-face__ic svg{ width: 26px; height: 26px; fill: none; stroke: #a855f7; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .np-face__tx{ flex: 1 1 auto; min-width: 0; line-height: 1.25; }
    .np-face__tx b{ display: block; font-size: 14.5px; font-weight: 700; color: var(--np-ink); }
    .np-face__tx small{ display: block; font-size: 12px; color: var(--np-sub); margin-top: 2px; }
    .np-face__go{ flex: 0 0 auto; border: 0; border-radius: 14px; padding: 9px 14px; font-size: 14px; font-weight: 700; color: #ffffff;
      background: linear-gradient(135deg, #a78bfa, #ec4899); box-shadow: 0 6px 14px rgba(168,85,247,0.35); }
    .np-face__go.is-off{ background: linear-gradient(135deg, #fda4af, #ef4444); box-shadow: 0 6px 14px rgba(239,68,68,0.30); }
    .np-face__go:disabled{ opacity: .6; }
    /* แป้นตั้ง PIN บนหน้าจอ (ไม่ใช้คีย์บอร์ดมือถือ) — คำขอ 2026-10-01 */
    .np-pinpad{ position: fixed; inset: 0; z-index: 3000; display: flex; align-items: center; justify-content: center;
      padding: max(env(safe-area-inset-top,0px),24px) 20px max(env(safe-area-inset-bottom,0px),20px);
      background: var(--np-bg); background-image: var(--np-dots); background-size: 4px 4px; }
    .np-pinpad[hidden]{ display: none !important; }
    .np-pinpad__card{ width: min(360px,100%); display: flex; flex-direction: column; align-items: center; text-align: center; }
    .np-pinpad__ic{ width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
      background: var(--np-bg); box-shadow: 8px 8px 16px var(--np-dark), -8px -8px 16px var(--np-light); }
    .np-pinpad__ic svg{ width: 32px; height: 32px; fill: none; stroke: #8b5cf6; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .np-pinpad__title{ margin-top: 18px; font-size: 20px; font-weight: 700; color: var(--np-ink); }
    .np-pinpad__msg{ margin-top: 6px; min-height: 20px; font-size: 14px; color: var(--np-sub); }
    .np-pinpad__msg.is-bad{ color: #db2777; font-weight: 600; }
    .np-pinpad__dots{ display: flex; gap: 14px; margin: 18px 0 24px; }
    .np-pinpad__dots i{ width: 15px; height: 15px; border-radius: 50%; background: var(--np-bg);
      box-shadow: inset 2px 2px 4px var(--np-dark), inset -2px -2px 4px var(--np-light); }
    .np-pinpad__dots i.is-on{ background: linear-gradient(135deg, #c084fc, #ec4899); box-shadow: 0 2px 6px rgba(168,85,247,0.35); }
    .np-pinpad__dots.is-shake{ animation: npPinShake .4s ease; }
    @keyframes npPinShake{ 0%,100%{ transform: translateX(0); } 20%{ transform: translateX(-10px); } 40%{ transform: translateX(9px); } 60%{ transform: translateX(-6px); } 80%{ transform: translateX(4px); } }
    .np-pinpad__keys{ display: grid; grid-template-columns: repeat(3, 70px); gap: 18px 26px; justify-content: center; }
    .np-pinpad__key{ width: 70px; height: 70px; border: 0; border-radius: 50%; padding: 0; background: var(--np-bg); color: var(--np-ink);
      font-size: 26px; font-weight: 500; box-shadow: 6px 6px 12px var(--np-dark), -6px -6px 12px var(--np-light);
      display: flex; align-items: center; justify-content: center; -webkit-tap-highlight-color: transparent; user-select: none; }
    .np-pinpad__key:active, .np-pinpad__key.is-press{ box-shadow: inset 4px 4px 8px var(--np-dark), inset -4px -4px 8px var(--np-light); }
    .np-pinpad__keys, .np-pinpad__key{ touch-action: manipulation; -webkit-user-select: none; user-select: none; -webkit-touch-callout: none; }
    .np-pinpad__key--blank{ visibility: hidden; }
    .np-pinpad__key svg{ width: 26px; height: 26px; fill: none; stroke: #8b5cf6; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .np-pinpad__cancel{ margin-top: 22px; border: 0; background: transparent; color: var(--np-sub); font-size: 14px; font-weight: 600; padding: 6px 12px; }
    .np-face{ flex-wrap: wrap; }
    .np-face__pin{ flex: 1 1 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 4px; }
    .np-face__pin input{
      min-width: 0; height: 46px; border: 0; border-radius: 14px; padding: 0 12px;
      background: rgba(255,255,255,0.75); color: var(--np-ink); font-size: 18px; letter-spacing: 6px; text-align: center;
      box-shadow: inset 3px 3px 6px rgba(163,172,186,0.45), inset -3px -3px 6px rgba(255,255,255,0.9);
    }
    .np-face__pin input::placeholder{ font-size: 13px; letter-spacing: 0; color: var(--np-sub); }
    .np-face__pin .np-face__go{ grid-column: 1 / -1; height: 44px; }
    .np-face__x{ flex: 0 0 auto; width: 30px; height: 30px; border: 0; border-radius: 50%; background: rgba(255,255,255,0.7); color: var(--np-sub); font-size: 18px; line-height: 1; padding: 0; }

    /* ---- หน้าแอดมิน: เมนูมีป้ายชื่อ จัดกลุ่ม เป็นระเบียบ ---- */
    .np-paper--wide{ width: min(720px, 100%); }
    .np-branch{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin:14px 4px 0; padding:10px 14px; border-radius:16px;
      box-shadow: inset 4px 4px 8px var(--np-dark), inset -4px -4px 8px var(--np-light); }
    .np-branch__lb{ display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--np-sub); white-space:nowrap; }
    .np-branch__lb svg{ width:18px; height:18px; fill:none; stroke:#1445b8; stroke-width:1.7; stroke-linecap:round; stroke-linejoin:round; }
    .np-branch select{ flex:1; min-width:0; max-width:240px; border:0; border-radius:12px; padding:8px 12px; background:rgba(255,255,255,.8); color:var(--np-ink); font:inherit; font-weight:600; }
    .np-sec{ margin:26px 6px 0; font-size:12px; font-weight:700; letter-spacing:.6px; color:var(--np-sub); text-transform:uppercase; }
    .np-tiles--hub{ margin-top:12px; gap:16px; }
    .np-tile--lbl{ flex-direction:column; gap:10px; aspect-ratio:auto; min-height:112px; padding:14px 8px; text-align:center; }
    .np-tile--lbl svg{ width:38px; height:38px; }
    .np-tile--lbl span{ font-size:14px; font-weight:600; color:var(--np-ink); line-height:1.2; }
    .np-tile--wide{ grid-column:1 / -1; flex-direction:row; gap:16px; min-height:92px; }
    .np-tile--wide svg{ width:42px; height:42px; }
    .np-tile--wide span{ font-size:18px; }
    @media (min-width: 700px){ .np-tiles--sales{ grid-template-columns:repeat(4, minmax(0,1fr)); } .np-tiles--sales .np-tile--wide{ grid-column:span 2; flex-direction:column; gap:10px; } .np-tiles--sales .np-tile--wide span{ font-size:16px; } .np-tiles--manage{ grid-template-columns:repeat(3, minmax(0,1fr)); } .np-paper--wide{ padding:34px 32px 30px; } }
    @media (max-width: 380px){
      .np-paper{ padding: 24px 18px 22px; border-radius: 34px; }
      .np-clock{ font-size: 44px; }
      .np-date__day{ font-size: 17px; }
      .np-tiles{ gap: 18px; }
      .np-tiles--hub{ gap: 12px; }
      .np-tile svg{ width: 46px; height: 46px; }
      .np-tile--lbl svg{ width: 34px; height: 34px; }
    }
  </style>
  <link rel="stylesheet" href="css/kp_clay_np.css?v=<?php echo (int)@filemtime(__DIR__ . "/css/kp_clay_np.css"); ?>">
</head>
<body class="np">
  <div class="np-stage">
    <div class="np-paper<?php echo $isAdmin ? ' np-paper--wide' : ''; ?>">
      <!-- วิดเจ็ตนาฬิกา: นาฬิกาเข็ม + วันที่ไทย + อากาศ (เหมือนหน้าแผนที่) -->
      <div class="np-wgbox">
      <div class="kp-wg" id="kpWg">
        <div class="kp-wg__clk"><svg viewBox="0 0 100 100" aria-hidden="true">
          <defs><linearGradient id="kwRim" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset=".45" stop-color="#ffc9e2"/><stop offset="1" stop-color="#b9a6ff"/></linearGradient>
          <radialGradient id="kwFace" cx=".4" cy=".35" r=".8"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#eceff6"/></radialGradient></defs>
          <circle cx="50" cy="50" r="48" fill="url(#kwRim)"/><circle cx="50" cy="50" r="48" fill="none" stroke="rgba(255,255,255,.9)" stroke-width="1.2"/>
          <circle cx="50" cy="50" r="40" fill="url(#kwFace)" stroke="rgba(120,134,170,.35)" stroke-width="1"/>
          <g id="kwTicks"></g>
          <circle cx="50" cy="50" r="9" fill="none" stroke="rgba(27,34,54,.35)" stroke-width=".8"/>
          <line id="kwHr" x1="50" y1="50" x2="50" y2="29" class="hh"/><line id="kwMn" x1="50" y1="50" x2="50" y2="17" class="mm"/>
          <line id="kwSc" x1="50" y1="60" x2="50" y2="13" class="ss"/><circle cx="50" cy="50" r="3.4" class="cd"/>
        </svg></div>
        <div class="kp-wg__inf">
          <small id="kwWd"></small>
          <div class="kp-wg__mini"><b id="kwT">--:--</b><span id="kwDs"></span></div>
          <b id="kwDate"></b>
          <div class="kp-wg__wx" id="kwWx">
            <div><span class="ic" id="wxI0"></span><b id="wxT0">--°</b><em>ตอนนี้</em></div>
            <div><span class="ic" id="wxI1"></span><b id="wxT1">--°</b><em id="wxL1">--:--</em></div>
            <div><span class="ic" id="wxI2"></span><b id="wxT2">--°</b><em id="wxL2">--:--</em></div>
          </div>
        </div>
      </div>
      <script>
      (function(){
        var DL=["อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์"],
            ML=["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
        var MSH=["ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
        function $(i){return document.getElementById(i);}
        function p2(n){return (n<10?"0":"")+n;}
        function rot(el,deg){if(el)el.setAttribute("transform","rotate("+deg+" 50 50)");}
        function set(i,t){var e=$(i);if(e&&e.textContent!==t)e.textContent=t;}
        var g=$("kpTicks")||$("kwTicks");
        if(g&&!g.firstChild){var h="";for(var i=0;i<60;i++){var big=i%5===0,r1=big?31:34,r2=37.5,a=i*Math.PI/6/5;
          h+='<line x1="'+(50+Math.sin(a)*r1).toFixed(2)+'" y1="'+(50-Math.cos(a)*r1).toFixed(2)+'" x2="'+(50+Math.sin(a)*r2).toFixed(2)+'" y2="'+(50-Math.cos(a)*r2).toFixed(2)+'" stroke="#1b2236" stroke-width="'+(big?1.7:.6)+'" stroke-linecap="round"/>';}
          g.innerHTML=h;}
        function tick(){
          if(!$("kpWg"))return;
          var d=new Date(),hh=d.getHours(),m=d.getMinutes(),s=d.getSeconds();
          set("kwWd","วัน"+DL[d.getDay()]);
          set("kwDate",d.getDate()+" "+ML[d.getMonth()]+" "+(d.getFullYear()+543));
          set("kwT",p2(hh)+":"+p2(m));set("kwDs","วัน"+DL[d.getDay()].slice(0,3)+" "+d.getDate()+" "+MSH[d.getMonth()]);
          rot($("kwHr"),(hh%12)*30+m*0.5);rot($("kwMn"),m*6+s*0.1);rot($("kwSc"),s*6);
        }
        tick();setInterval(function(){if(!document.hidden)tick();},1000);

        /* ---- พยากรณ์อากาศ ---- */
        var IC={
          sun:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
          part:'<circle cx="8" cy="8" r="3"/><path d="M8 1.5v1.2M1.5 8h1.2M3.4 3.4l.9.9M12.6 3.4l-.9.9"/><path d="M8 19h9a3.5 3.5 0 0 0 .4-7 5 5 0 0 0-9.4 1.2A3 3 0 0 0 8 19z"/>',
          cloud:'<path d="M7 19h10a4 4 0 0 0 .5-8 6 6 0 0 0-11.4 1.6A3.4 3.4 0 0 0 7 19z"/>',
          rain:'<path d="M7 14h10a4 4 0 0 0 .5-8 6 6 0 0 0-11.4 1.6A3.4 3.4 0 0 0 7 14z"/><path d="M8 17l-1 3M12 17l-1 3M16 17l-1 3"/>',
          storm:'<path d="M7 14h10a4 4 0 0 0 .5-8 6 6 0 0 0-11.4 1.6A3.4 3.4 0 0 0 7 14z"/><path d="M12.5 14l-2.5 4h3l-2 4"/>',
          fog:'<path d="M7 13h10a4 4 0 0 0 .5-8 6 6 0 0 0-11.4 1.6A3.4 3.4 0 0 0 7 13z"/><path d="M4 17h16M6 21h12"/>'
        };
        function kind(c){if(c===0)return"sun";if(c<=2)return"part";if(c===3)return"cloud";if(c===45||c===48)return"fog";if(c>=95)return"storm";if(c>=51)return"rain";return"cloud";}
        function ico(k){return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">'+IC[k]+'</svg>';}
        var loc=window.KP_WX||{lat:13.7563,lng:100.5018};
        function wx(){
          if(!window.fetch||!$("kwWx"))return;
          var u="https://api.open-meteo.com/v1/forecast?latitude="+loc.lat+"&longitude="+loc.lng+"&hourly=temperature_2m,weathercode&timezone=Asia%2FBangkok&forecast_days=2";
          fetch(u).then(function(r){return r.json();}).then(function(j){
            var T=j.hourly.time,n=new Date(),key=n.getFullYear()+"-"+p2(n.getMonth()+1)+"-"+p2(n.getDate())+"T"+p2(n.getHours())+":00",i=T.indexOf(key);
            if(i<0)return;
            for(var k=0;k<3;k++){
              var t=j.hourly.temperature_2m[i+k],c=j.hourly.weathercode[i+k];
              if(t==null)continue;
              $("wxI"+k).innerHTML=ico(kind(c));set("wxT"+k,Math.round(t)+"°C");
              if(k>0)set("wxL"+k,T[i+k].slice(11,16));
            }
            $("kwWx").classList.add("is-on");
          }).catch(function(){});
        }
        wx();setInterval(wx,30*60*1000);
      })();
      </script>
      </div>

      <!-- แถบทักทาย -->
      <div class="np-bar np-raised">
        <span class="np-bar__name">สวัสดี<?php echo $kpName !== "" ? ", " . htmlspecialchars($kpName, ENT_QUOTES, "UTF-8") : ""; ?></span>
        <span class="np-bar__role"><?php echo htmlspecialchars($kpRole, ENT_QUOTES, "UTF-8"); ?></span>
      </div>
      <?php if ($isAdmin) { ?>
      <label class="np-branch">
        <span class="np-branch__lb"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 21V8l8-5 8 5v13"/><path d="M9 21v-6h6v6"/></svg>สาขาที่ใช้งาน</span>
        <select id="kpBranchSel" aria-label="สาขาที่ใช้งาน">
          <?php foreach ($kpBranches as $b) { ?>
            <option value="<?php echo (int)$b["id"]; ?>"<?php echo ((int)$b["id"] === $kpBranchId) ? " selected" : ""; ?>><?php echo htmlspecialchars($b["name"], ENT_QUOTES, "UTF-8"); ?></option>
          <?php } ?>
        </select>
      </label>
      <?php } ?>

      <?php if($res === "adminonly"){ ?>
        <div class="np-alert">หน้า Tablet ใช้งานได้เฉพาะแอดมิน — ใช้ปุ่มมือถือแทน</div>
      <?php } ?>

      <!-- เมนูหลัก -->
      <?php if ($isAdmin) { ?>
      <div class="np-tiles np-tiles--hub np-tiles--main">
        <a class="np-tile np-tile--lbl np-raised" id="btnEnterMobile" href="index2.php" style="--ic:#22c55e" title="แผนที่สาขา">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.4"/></svg><span>แผนที่สาขา</span>
        </a>
        <div class="np-tilewrap">
          <a class="np-tile np-tile--lbl np-raised" href="shop.php" style="--ic:#8b5cf6" title="หน้าขายออนไลน์">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9l1.5-5h13L20 9"/><path d="M4 9a2.7 2.7 0 0 0 5.3 0 2.7 2.7 0 0 0 5.4 0A2.7 2.7 0 0 0 20 9"/><path d="M5 12v8h14v-8"/></svg><span>หน้าขายออนไลน์</span>
          </a>
        </div>
      </div>
      <?php } else { ?>
      <div class="np-tiles np-tiles--one">
        <a class="np-tile np-tile--mobile np-raised" id="btnEnterMobile" href="index2.php" title="Mobile" aria-label="เข้าใช้งานแบบมือถือ (แผนที่)">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.4"/></svg>
        </a>
      </div>
      <?php } ?>

      <!-- ชวนเปิดใช้ / ปิดใช้ สแกนใบหน้า-ลายนิ้วมือบนเครื่องนี้ (Passkey) -->
      <div class="np-face" id="npFaceCard" style="display:none;">
        <div class="np-face__ic"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><circle cx="9" cy="15.5" r="0.9"/><circle cx="12" cy="15.5" r="0.9"/><circle cx="15" cy="15.5" r="0.9"/></svg></div>
        <div class="np-face__tx"><b id="npFaceTitle">ตั้ง PIN เข้าแอป</b><small id="npFaceSub">เปิดแอปแล้วใส่ PIN 6 หลัก ไม่ต้องพิมพ์ชื่อผู้ใช้/รหัสผ่าน</small></div>
        <button type="button" class="np-face__go" id="npFaceGo">เปิดใช้</button>
        <button type="button" class="np-face__x" id="npFaceX" aria-label="ปิด">&times;</button>
      </div>

      <!-- ออกจากระบบ -->
      <div class="np-bottom">
        <button type="button" class="np-facebtn np-raised" id="npFaceBtn" style="display:none;" title="PIN เข้าแอป" aria-label="ตั้งค่า PIN เข้าแอป">
          <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><circle cx="9" cy="15.5" r="0.9"/><circle cx="12" cy="15.5" r="0.9"/><circle cx="15" cy="15.5" r="0.9"/></svg><span class="np-facebtn__on" aria-hidden="true"></span>
        </button>
        <a class="np-logout np-raised" href="/logout.php" title="ออกจากระบบ" aria-label="ออกจากระบบ">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v8"/><path d="M6.4 6.6a8 8 0 1 0 11.2 0"/></svg>
        </a>
      </div>
    </div>
  </div>

  <!-- Permission prompt (หลัง login) -->
  <div id="kpPermModal" class="kp-perm-modal" role="dialog" aria-modal="true" aria-label="ขออนุญาตสิทธิ์การใช้งาน">
    <div class="kp-perm-card">
      <div class="kp-perm-head">
        <div class="kp-perm-title">ขออนุญาตสิทธิ์เพื่อการใช้งาน</div>
        <button type="button" class="kp-perm-close" id="kpPermClose" aria-label="ปิด">×</button>
      </div>
      <div class="kp-perm-body">
        เพื่อให้ระบบทำงานได้ครบถ้วน กรุณาอนุญาตสิทธิ์ที่จำเป็นดังนี้
        <ul class="kp-perm-list">
          <li class="kp-perm-item">
            <div class="kp-perm-ic"><i class="fa fa-map-marker" aria-hidden="true"></i></div>
            <div>
              <div class="kp-perm-k">ตำแหน่ง (Location)</div>
              <div class="kp-perm-v">ใช้แสดงตำแหน่งปัจจุบันและนำทางบนแผนที่</div>
            </div>
          </li>
          <li class="kp-perm-item">
            <div class="kp-perm-ic"><i class="fa fa-bell" aria-hidden="true"></i></div>
            <div>
              <div class="kp-perm-k">การแจ้งเตือน (Notifications)</div>
              <div class="kp-perm-v">ถ้าอุปกรณ์รองรับ จะขอสิทธิ์เพื่อแจ้งเตือนเพิ่มเติม</div>
            </div>
          </li>
        </ul>
      </div>
      <div class="kp-perm-actions">
        <button type="button" class="kp-perm-btn" id="kpPermLater">ไว้ก่อน</button>
        <button type="button" class="kp-perm-btn kp-perm-btn--primary" id="kpPermAllow">อนุญาต</button>
      </div>
    </div>
  </div>

  <script src="/json/pwa.js?v=1"></script>
  <script>
    // กดเข้า "Mobile" -> เข้าหน้าแผนที่ทันที (ไม่รออ่านจบ)
    // แล้วให้หน้า index2 เป็นคนพูดทักทาย (รองรับ iPhone ที่ต้องใช้ gesture)
    (function(){
      var btn = document.getElementById("btnEnterMobile");
      if(!btn) return;

      btn.addEventListener("click", function(e){
        // UI เล็กน้อยแล้วปล่อยให้ลิงก์พาไปทันที
        try{
          btn.classList.add("is-busy");
          btn.setAttribute("aria-disabled","true");
          btn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-6.2-8.6"/></svg>';
        }catch(e1){}
      });
    })();

    // นาฬิกา + วันที่ภาษาไทย (พ.ศ.) บนหัวหน้า
    (function(){
      var DAYS = ["อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์"];
      var MONTHS = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
      function pad(n){ return (n < 10 ? "0" : "") + n; }
      function tick(){
        try{
          var d = new Date();
          document.getElementById("npClock").innerHTML = pad(d.getHours()) + '<span class="np-colon">:</span>' + pad(d.getMinutes()) + "<small>" + pad(d.getSeconds()) + "</small>";
          document.getElementById("npDay").textContent = "วัน" + DAYS[d.getDay()];
          document.getElementById("npDate").textContent = d.getDate() + " " + MONTHS[d.getMonth()] + " " + (d.getFullYear() + 543);
        }catch(e){}
      }
      tick();
      setInterval(tick, 1000);
    })();

    // -----------------------------
    // Permission prompt (หลัง login)
    // -----------------------------
    (function(){
      var modal = document.getElementById("kpPermModal");
      if (!modal) return;
      var btnAllow = document.getElementById("kpPermAllow");
      var btnLater = document.getElementById("kpPermLater");
      var btnClose = document.getElementById("kpPermClose");

      function showPermModal(){
        try { modal.style.display = "flex"; } catch(e){}
      }
      function hidePermModal(){
        try { modal.style.display = "none"; } catch(e){}
      }

      function markPrompted(){
        try { localStorage.setItem("kp_perm_prompted_v1", "1"); } catch(e){}
      }
      function wasPrompted(){
        try { return localStorage.getItem("kp_perm_prompted_v1") === "1"; } catch(e){ return false; }
      }

      async function requestLocation(){
        return new Promise(function(resolve){
          try{
            if (!navigator.geolocation) { resolve(false); return; }
            navigator.geolocation.getCurrentPosition(
              function(){ resolve(true); },
              function(){ resolve(false); },
              { enableHighAccuracy: false, timeout: 9000, maximumAge: 60000 }
            );
          }catch(e){ resolve(false); }
        });
      }

      async function requestNotifications(){
        try{
          if (!("Notification" in window) || !Notification.requestPermission) return false;
          if (Notification.permission !== "default") return (Notification.permission === "granted");
          var p = await Notification.requestPermission();
          return (p === "granted");
        }catch(e){
          return false;
        }
      }

      async function requestAll(){
        try{
          if (btnAllow) { btnAllow.disabled = true; btnAllow.textContent = "กำลังขอสิทธิ์..."; }
        }catch(e0){}

        // ขอทีละอย่าง (เพื่อให้ popup ของระบบแสดงได้ชัดเจน)
        await requestLocation();
        await requestNotifications();

        markPrompted();
        hidePermModal();

        try{
          if (btnAllow) { btnAllow.disabled = false; btnAllow.textContent = "อนุญาต"; }
        }catch(e1){}
      }

      if (btnAllow) btnAllow.addEventListener("click", function(){ requestAll(); });
      if (btnLater) btnLater.addEventListener("click", function(){ markPrompted(); hidePermModal(); });
      if (btnClose) btnClose.addEventListener("click", function(){ markPrompted(); hidePermModal(); });
      modal.addEventListener("click", function(e){
        try{
          if (e.target === modal) { markPrompted(); hidePermModal(); }
        }catch(e2){}
      });

      // แสดงหลัง login (ที่หน้าเลือกเมนู) แค่ครั้งเดียวต่อเครื่อง
      try{
        if (!wasPrompted()) {
          setTimeout(showPermModal, 450);
        }
      }catch(e3){}
    })();
  </script>

  <!-- แป้นตั้ง PIN บนหน้าจอ (ไม่เด้งคีย์บอร์ดมือถือ) -->
  <div class="np-pinpad" id="npPinPad" hidden role="dialog" aria-modal="true" aria-label="ตั้ง PIN">
    <div class="np-pinpad__card">
      <div class="np-pinpad__ic"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><circle cx="9" cy="15.5" r="0.9"/><circle cx="12" cy="15.5" r="0.9"/><circle cx="15" cy="15.5" r="0.9"/></svg></div>
      <div class="np-pinpad__title" id="npPadTitle">ตั้ง PIN 6 หลัก</div>
      <div class="np-pinpad__msg" id="npPadMsg">ใช้เปิดแอปครั้งต่อไป</div>
      <div class="np-pinpad__dots" id="npPadDots"><i></i><i></i><i></i><i></i><i></i><i></i></div>
      <div class="np-pinpad__keys" id="npPadKeys"><button type="button" class="np-pinpad__key" data-k="1">1</button><button type="button" class="np-pinpad__key" data-k="2">2</button><button type="button" class="np-pinpad__key" data-k="3">3</button><button type="button" class="np-pinpad__key" data-k="4">4</button><button type="button" class="np-pinpad__key" data-k="5">5</button><button type="button" class="np-pinpad__key" data-k="6">6</button><button type="button" class="np-pinpad__key" data-k="7">7</button><button type="button" class="np-pinpad__key" data-k="8">8</button><button type="button" class="np-pinpad__key" data-k="9">9</button><button type="button" class="np-pinpad__key np-pinpad__key--blank" tabindex="-1" aria-hidden="true"></button><button type="button" class="np-pinpad__key" data-k="0">0</button><button type="button" class="np-pinpad__key" data-k="del" aria-label="ลบ"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-6-7z"/><path d="M12.5 9.5l5 5M17.5 9.5l-5 5"/></svg></button></div>
      <button type="button" class="np-pinpad__cancel" id="npPadCancel">ยกเลิก</button>
    </div>
  </div>
  <script src="/json/kp_passkey.js?v=3"></script>
  <script>
    // สแกนใบหน้า + PIN เข้าแอป (แบบแอปธนาคาร) — ตั้งค่าบนหน้านี้หลังเข้าระบบ (คำขอ 2026-09-28/29)
    (function(){
      var P = window.kpPasskey;
      if (!P) return;
      var ME = <?php echo json_encode((string)($_SESSION["admin_user"] ?? ""), JSON_UNESCAPED_UNICODE); ?>;
      var MYNAME = <?php echo json_encode((string)$kpName, JSON_UNESCAPED_UNICODE); ?>;
      var card = document.getElementById("npFaceCard");
      var title = document.getElementById("npFaceTitle");
      var sub = document.getElementById("npFaceSub");
      var go = document.getElementById("npFaceGo");
      var x = document.getElementById("npFaceX");
      var fbtn = document.getElementById("npFaceBtn");
      var FACE_OK = false;

      function mine(){ return !P.enrolledUser() || P.enrolledUser() === ME; }
      function faceOn(){ return false; } // ตัดสแกนใบหน้าออก ใช้ PIN อย่างเดียว (คำขอ 2026-09-30)
      function pinOn(){ return P.hasPin() && mine(); }
      function isOn(){ return faceOn() || pinOn(); }
      function paint(){ fbtn.classList.toggle("is-on", isOn()); }
      function hideCard(){ card.style.display = "none"; }
      function showCard(mode){
        go.disabled = false; go.style.display = ""; go.classList.remove("is-off");
        card.setAttribute("data-mode", mode);
        if (mode === "off") {
          title.textContent = "เปิดใช้ PIN เข้าแอปอยู่";
          sub.textContent = (faceOn() ? "สแกนใบหน้า" : "") + (faceOn() && pinOn() ? " + " : "") + (pinOn() ? "PIN" : "") + " — ต้องการปิดบนเครื่องนี้ไหม";
          go.textContent = "ปิดใช้"; go.classList.add("is-off");
        } else if (mode === "pin") {
          title.textContent = "ตั้ง PIN 6 หลัก";
          sub.textContent = "ครั้งหน้าเปิดแอปแล้วใส่ PIN นี้ได้เลย";
          go.textContent = "ตั้ง PIN";
          openPad();
        } else {
          title.textContent = FACE_OK ? "เข้าแอปด้วยสแกนใบหน้า" : "ตั้ง PIN เข้าแอป";
          sub.textContent = FACE_OK ? "เปิดแอปแล้วสแกนหน้าได้เลย ไม่ต้องพิมพ์รหัส (ตั้ง PIN สำรองด้วย)" : "เปิดแอปแล้วใส่ PIN 6 หลัก ไม่ต้องพิมพ์ชื่อผู้ใช้/รหัสผ่าน";
          go.textContent = "เปิดใช้";
        }
        card.style.display = "";
      }

      // เข้าระบบสำเร็จแล้ว: ปลดล็อก PIN ที่อาจโดนล็อก + จำชื่อไว้ทักทายบนหน้าล็อก
      try { P.pinUnlock(); } catch (e) {}
      if (isOn()) P.saveName(MYNAME || ME);

      P.supported().then(function(ok){
        FACE_OK = false;
        fbtn.style.display = "";
        paint();
        if (P.skipped()) return;
        if (!isOn()) showCard("on");
        else if (faceOn() && !pinOn()) showCard("pin");
      });

      fbtn.addEventListener("click", function(){
        if (card.style.display !== "none") { hideCard(); return; }
        showCard(isOn() ? "off" : "on");
      });
      x.addEventListener("click", function(){
        if (!isOn() || card.getAttribute("data-mode") === "pin") P.skip();
        hideCard();
      });
      go.addEventListener("click", function(){
        go.disabled = true;
        var mode = card.getAttribute("data-mode");
        if (mode === "off") {
          P.removeAll().then(function(){
            paint(); title.textContent = "ปิดใช้แล้ว"; sub.textContent = "เข้าครั้งหน้าใช้ชื่อผู้ใช้และรหัสผ่านตามปกติ";
            go.style.display = "none";
            setTimeout(hideCard, 1800);
          });
          return;
        }
        if (!FACE_OK) { showCard("pin"); return; }
        P.register(ME).then(function(){
          P.saveName(MYNAME || ME);
          paint();
          showCard("pin");
          sub.textContent = "สแกนหน้าพร้อมแล้ว ตั้ง PIN สำรองไว้ใช้เมื่อสแกนไม่ผ่าน";
        }).catch(function(e){
          go.disabled = false;
          sub.textContent = (e && e.message) || "เปิดใช้ไม่สำเร็จ";
        });
      });
      // ---------- แป้นตั้ง PIN บนหน้าจอ: ใส่ 6 หลัก -> ยืนยันอีกครั้ง -> บันทึก ----------
      var pad = document.getElementById("npPinPad");
      var padTitle = document.getElementById("npPadTitle");
      var padMsg = document.getElementById("npPadMsg");
      var padDotsBox = document.getElementById("npPadDots");
      var padDots = padDotsBox.children;
      var padStep = 1, padFirst = "", padVal = "", padBusy = false;
      function padPaint(){ for (var i = 0; i < padDots.length; i++) padDots[i].classList.toggle("is-on", i < padVal.length); }
      function padSay(t, bad){ padMsg.textContent = t || ""; padMsg.classList.toggle("is-bad", !!bad); }
      function padShake(){ padDotsBox.classList.remove("is-shake"); void padDotsBox.offsetWidth; padDotsBox.classList.add("is-shake"); }
      function openPad(){
        try { if (document.activeElement && document.activeElement.blur) document.activeElement.blur(); } catch (e) {}
        padStep = 1; padFirst = ""; padVal = ""; padBusy = false;
        padTitle.textContent = "ตั้ง PIN 6 หลัก"; padSay("ใช้เปิดแอปครั้งต่อไป"); padPaint();
        pad.hidden = false;
      }
      function closePad(){ pad.hidden = true; go.disabled = false; }
      function padDone(){
        if (padStep === 1) {
          padFirst = padVal; padVal = ""; padStep = 2;
          padTitle.textContent = "ยืนยัน PIN อีกครั้ง"; padSay("ใส่ PIN เดิมอีกครั้ง"); padPaint();
          return;
        }
        if (padVal !== padFirst) {
          padShake(); padStep = 1; padFirst = ""; padVal = ""; padPaint();
          padTitle.textContent = "ตั้ง PIN 6 หลัก"; padSay("PIN ไม่ตรงกัน กรุณาตั้งใหม่", true);
          return;
        }
        padBusy = true; padSay("กำลังบันทึก...");
        P.setPin(padVal).then(function(){
          P.saveName(MYNAME || ME);
          try { localStorage.setItem("kp_pk_user", ME); } catch (e) {}
          paint();
          closePad();
          go.style.display = "none";
          title.textContent = "ตั้ง PIN เรียบร้อย";
          sub.textContent = "ครั้งหน้าเปิดแอป ใส่ PIN 6 หลักได้เลย";
          card.style.display = "";
          setTimeout(hideCard, 3000);
        }).catch(function(e){
          padBusy = false; padVal = ""; padPaint(); padShake();
          padSay((e && e.message) || "ตั้ง PIN ไม่สำเร็จ", true);
        });
      }
      function npPadPress(b){
        if (!b || padBusy) return;
        var k = b.getAttribute("data-k");
        if (!k) return;
        if (k === "del") { padVal = padVal.slice(0, -1); padPaint(); return; }
        if (padVal.length >= 6) return;
        padVal += k; padPaint();
        if (padVal.length === 6) setTimeout(padDone, 80);
      }
      var npLastPtr = 0;
      var npKeys = document.getElementById("npPadKeys");
      // ตอบสนองทันทีที่แตะ (pointerdown) — คำขอ 2026-10-02
      npKeys.addEventListener("pointerdown", function(e){
        var b = e.target.closest ? e.target.closest(".np-pinpad__key") : null;
        if (!b) return;
        e.preventDefault();
        npLastPtr = Date.now();
        b.classList.add("is-press");
        setTimeout(function(){ b.classList.remove("is-press"); }, 120);
        npPadPress(b);
      });
      npKeys.addEventListener("click", function(e){
        if (Date.now() - npLastPtr < 700) return;
        npPadPress(e.target.closest ? e.target.closest(".np-pinpad__key") : null);
      });
      document.getElementById("npPadCancel").addEventListener("click", closePad);
    })();
  </script>
<script>
(function(){
  var sel=document.getElementById("kpBranchSel"); if(!sel) return;
  sel.addEventListener("change",function(){
    var fd=new FormData(); fd.append("action","switch"); fd.append("id",sel.value);
    fetch("json/branch.php",{method:"POST",body:fd,headers:{"X-Requested-With":"XMLHttpRequest"}})
      .then(function(r){return r.json();}).then(function(j){ if(!j.ok){alert(j.error||"สลับสาขาไม่ได้");location.reload();} });
  });
})();
</script>
</body>
</html>
