<?php
// หน้า Splash (หน้าแรก) ก่อนเข้า Login
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="theme-color" content="#16a34a">
  <title>KINGPHAI</title>
  <link rel="manifest" href="manifest.json?v=4">
  <link rel="apple-touch-icon" sizes="180x180" href="img/icon_180.png?v=2">
  <style>
    :root{
      --kp-text:#111827;
      --kp-sub: rgba(17,24,39,0.62);
      --kp-border: rgba(17,24,39,0.10);
      --kp-shadow: 0 22px 60px rgba(17,24,39,0.18);
      --kp-green: #16a34a;
      --kp-green2: #22c55e;
    }
    body{
      margin: 0;
      min-height: 100vh;
      background:
        radial-gradient(900px 520px at 20% 0%, rgba(34,197,94,0.22), transparent 58%),
        radial-gradient(900px 520px at 90% 10%, rgba(56,189,248,0.18), transparent 60%),
        linear-gradient(180deg, #f7fffb, #d9fbe1);
      color: var(--kp-text);
      overflow: hidden;
    }
    .kp-shell{
      min-height: 100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding: 0;
      position: relative;
    }
    .kp-logo-full{
      width: min(92vw, 520px);
      height: min(92vw, 520px);
      max-width: 520px;
      max-height: 520px;
      display:flex;
      align-items:center;
      justify-content:center;
      padding: 18px;
      border-radius: 28px;
      background: rgba(255,255,255,0.72);
      border: 1px solid var(--kp-border);
      -webkit-backdrop-filter: blur(14px) saturate(160%);
      backdrop-filter: blur(14px) saturate(160%);
      box-shadow: var(--kp-shadow);
      position: relative;
      overflow: hidden;
      animation: kpIn 240ms ease both;
    }
    .kp-logo-full:before{
      content:"";
      position:absolute;
      inset: -160px -160px auto auto;
      width: 420px;
      height: 420px;
      border-radius: 999px;
      background: radial-gradient(circle at 30% 30%, rgba(34,197,94,0.35), rgba(34,197,94,0.0) 70%);
      pointer-events: none;
    }
    .kp-logo-full img{
      width: 100%;
      height: 100%;
      object-fit: contain;
      display:block;
      position: relative;
      z-index: 1;
    }
    @keyframes kpIn{
      from{ transform: translateY(6px) scale(.985); opacity: .0; }
      to{ transform: translateY(0) scale(1); opacity: 1; }
    }
  </style>
</head>
<body>
  <div class="kp-shell" aria-label="กำลังเริ่มต้นระบบ">
    <div class="kp-logo-full" aria-hidden="true">
      <img src="img/logo.png" alt="KINGPHAI">
    </div>
  </div>

  <script>
    // ไปหน้า login ภายใน 1 วินาที
    setTimeout(function(){
      window.location.href = "/index.php";
    }, 1000);
  </script>
</body>
</html>
