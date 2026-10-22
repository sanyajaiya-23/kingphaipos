<?php
require($_SERVER['DOCUMENT_ROOT']."/inc/db/db_webdev.php");
require($_SERVER['DOCUMENT_ROOT']."/inc/auth.php");
require($_SERVER['DOCUMENT_ROOT']."/inc/google_maps_config.php");
kp_require_login(); // lockout 4 ชม.
// พนักงานที่เลือกสาขาแล้ว (กด Open จากแผนที่) -> เข้าหน้าขายตลอดจนกว่าจะออกจากระบบ
if ((!isset($_SESSION["id_status"]) || (int)$_SESSION["id_status"] !== 1) && !empty($_SESSION["branch_picked"])) { header("Location:/pos_sale.php"); exit; }
$kpJustLogin = (!empty($_SESSION["just_login"]));
if (isset($_SESSION["just_login"])) unset($_SESSION["just_login"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="theme-color" content="#fbf1e1">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="KP">
  <title>POS kingphai</title>
  <link rel="stylesheet" href="lib/bootstrap-4.5.2-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="lib/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/fixm.css">
  <link rel="stylesheet" href="css/button.css">
  <link rel="stylesheet" href="css/etc.css?v=181">
  <link rel="stylesheet" href="css/index2_sale_sheet.css?v=85">
  <link rel="stylesheet" href="css/popup_glass.css?v=4">
  <link rel="stylesheet" href="lib/select2.min.css">
  <link rel="stylesheet" href="css/kp_toast.css?v=8">
  <link rel="stylesheet" href="css/bbtscan.css">
  <link rel="stylesheet" href="css/progressbar.css?v=1">
  <link rel="manifest" href="manifest.json?v=4">
  <link rel="apple-touch-icon" sizes="180x180" href="img/icon_180.png?v=2">
  <script>
    // ครั้งแรกหลัง login: ให้โฟกัสตำแหน่งปัจจุบันทันที (ไม่ไปโผล่ที่ state เดิม)
    window.KP_JUST_LOGIN = <?php echo $kpJustLogin ? "true" : "false"; ?>;
  </script>

  <!-- กลับไปใช้แบบเดิม v=3.8 (ไม่ใช้ popup ลูกค้าแบบ custom) -->
  <style>
    /* Modernize Google Maps InfoWindow (ลูกค้า) */
    .gm-style .gm-style-iw-c{
      padding: 0 !important;
      border-radius: 16px !important;
      border: 1px solid rgba(17,24,39,0.10) !important;
      box-shadow: 0 18px 44px rgba(0,0,0,0.22) !important;
      background: rgba(255,255,255,0.98) !important;
      overflow: hidden !important;
      max-width: none !important; /* เอาข้อจำกัดความกว้างเดิมออก */
    }
    .gm-style .gm-style-iw-d{
      overflow: hidden !important;
      max-height: none !important;
      max-width: none !important;
    }
    /* ปุ่มปิด InfoWindow (X) — ลอยสวย ไม่ทับ content */
    .gm-style .gm-ui-hover-effect{
      top: 8px !important;
      right: 8px !important;
      border-radius: 999px !important;
      background: rgba(255,255,255,0.98) !important;
      opacity: 1 !important;
      width: 34px !important;
      height: 34px !important;
      border: 1px solid rgba(17,24,39,0.10) !important;
      box-shadow: 0 10px 22px rgba(0,0,0,0.12) !important;
      z-index: 20 !important;
      position: relative !important;
      padding: 0 !important;
      transition: transform .12s ease, background .12s ease, box-shadow .12s ease !important;
    }
    .gm-style .gm-ui-hover-effect > span{
      background-color: #111 !important;
      position: absolute !important;
      left: 50% !important;
      top: 50% !important;
      margin: 0 !important;
      transform: translate(-50%,-50%) scale(1.05) !important; /* ให้อยู่กึ่งกลางปุ่ม */
    }
    .gm-style .gm-ui-hover-effect:hover{
      background: rgba(17,24,39,0.04) !important;
      transform: translateY(-1px);
      box-shadow: 0 14px 28px rgba(0,0,0,0.14) !important;
    }
    .gm-style .gm-ui-hover-effect:active{
      transform: scale(0.98);
    }

    .kp-iw{
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      /* ให้พอดีกับข้อมูล แต่ไม่ล้นจอ */
      width: auto;
      min-width: 280px;
      max-width: min(86vw, 420px);
      position: relative;
    }
    /* ไม่ fix ความกว้างตาม media query เพื่อให้ "พอดีกับข้อมูล" */

    /* แถบบนของ InfoWindow สำหรับปุ่ม OPEN */
    .kp-iw__topbar{
      height: 54px;
      display:flex;
      align-items:center;
      justify-content:center;
      gap: 8px;
      border-bottom: 1px solid rgba(17,24,39,0.06);
      background: rgba(255,255,255,0.96);
      /* กันพื้นที่มุมขวาบนให้ปุ่ม X ลอยไม่ทับแถบ OPEN */
      padding-right: 44px;
      position: relative;
    }
    .kp-nav-mini{
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      height: 34px;
      padding: 0 12px;
      border-radius: 999px;
      border: 1px solid rgba(17,24,39,0.10);
      background: rgba(17,24,39,0.04);
      color: rgba(17,24,39,0.82);
      font-size: 12px;
      font-weight: 1000;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .kp-nav-mini:active{ transform: translateY(-50%) scale(0.98); }
    .kp-open-pill{
      border: 0;
      background: #ef4444;
      color: #fff;
      height: 38px;
      padding: 0 18px;
      border-radius: 999px;
      font-weight: 1000;
      letter-spacing: .6px;
      box-shadow: 0 12px 24px rgba(239,68,68,0.28);
      transition: transform .12s ease, background .12s ease, box-shadow .12s ease;
    }
    .kp-open-pill:hover{
      background: #dc2626;
      transform: translateY(-1px);
      box-shadow: 0 16px 28px rgba(239,68,68,0.32);
    }
    .kp-open-pill:active{ transform: scale(0.98); }
    .kp-open-pill:focus{ outline: none; }

    /* ปุ่ม back / refresh บนหัว sheet — ดีไซน์กระจกใหม่ */
    /* หมายเหตุ: ห้ามใส่ !important ที่ display ตรงนี้
       เพราะต้องให้กฎ #searchSheet.bottom-sheet--collapsed .sheet-actions{display:none}
       ใน css/etc.css ชนะตอนแผ่นพับหุบ (หน้าแผนที่) ไม่งั้นแถบค้นหา/รีเฟรชจะโผล่ทับแผนที่ */
    .sheet-actions{
      display:flex;
      align-items:center !important;
      gap: 10px !important;
      flex: 1 1 auto !important;
      justify-content: flex-end !important;
    }
    .sheet-refresh-btn{
      width: 46px !important;
      height: 46px !important;
      border-radius: 999px !important;
      border: 1.5px solid rgba(255,255,255,0.65) !important;
      background:
        linear-gradient(180deg, rgba(255,255,255,0.85), rgba(240,253,244,0.65)) !important;
      -webkit-backdrop-filter: blur(14px) saturate(160%);
      backdrop-filter: blur(14px) saturate(160%);
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.85),
        0 8px 18px rgba(20,90,60,0.14) !important;
      color: #16a34a !important;
      font-size: 16px;
      display:flex !important;
      align-items:center !important;
      justify-content:center !important;
      transition: transform .14s ease, box-shadow .14s ease, filter .14s ease;
    }
    /* ต้องมี !important เพราะกฎด้านบนตั้ง display:flex !important ไว้
       (ถ้าใช้ inline style / .css() ธรรมดาจะสู้ไม่ได้ ปุ่มจะโผล่พร้อมกันทั้งคู่) */
    .sheet-refresh-btn.is-hidden{
      display:none !important;
    }
    .sheet-refresh-btn:hover{
      background:
        linear-gradient(180deg, rgba(255,255,255,0.95), rgba(220,252,231,0.75)) !important;
      transform: translateY(-1px);
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.90),
        0 10px 22px rgba(20,90,60,0.18) !important;
    }
    .sheet-refresh-btn:active{
      transform: translateY(0) scale(0.94);
    }


    /* กล่องของ "จุด driver/admin" ให้พอดีกับข้อมูล (เล็กลง) */
    .kp-iw-user{
      width: auto;
      min-width: 220px;
      max-width: min(78vw, 320px);
    }
    .kp-iw-user .kp-iw__head{ padding: 10px 10px 8px 10px; }
    .kp-iw-user .kp-iw__title{ font-size: 13px; }
    .kp-iw-user .kp-iw__pill{
      padding: 5px 8px;
      font-size: 11px;
    }
    .kp-iw-user .kp-iw__pill .dot{ width: 9px; height: 9px; box-shadow: 0 0 0 3px rgba(156,163,175,0.18); }
    .kp-iw-user .kp-iw__rows{ padding: 8px 10px 10px 10px; }
    .kp-iw-user .kp-iw__row{ padding: 5px 0; }
    .kp-iw-user .kp-iw__k{ font-size: 11px; }
    .kp-iw-user .kp-iw__v{ font-size: 12px; }
    .kp-iw-user .kp-iw__foot{ padding: 8px 10px 10px 10px; font-size: 11px; }
    .kp-iw__head{
      padding: 12px 12px 10px 12px;
      border-bottom: 1px solid rgba(17,24,39,0.08);
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:10px;
    }
    .kp-iw__title{
      font-weight: 900;
      font-size: 14px;
      color:#111827;
      line-height: 1.1;
    }
    .kp-iw__pill{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
      white-space: nowrap;
      border: 1px solid rgba(17,24,39,0.10);
      background: rgba(17,24,39,0.04);
      color: rgba(17,24,39,0.78);
    }
    .kp-iw__pill .dot{
      width: 10px;
      height: 10px;
      border-radius: 999px;
      background: #9CA3AF;
      box-shadow: 0 0 0 4px rgba(156,163,175,0.18);
      flex: 0 0 auto;
    }
    .kp-iw__rows{ padding: 10px 12px 12px 12px; }
    .kp-iw__row{
      display:flex;
      gap:10px;
      padding: 6px 0;
      border-bottom: 1px dashed rgba(17,24,39,0.10);
    }
    .kp-iw__row:last-child{ border-bottom:0; }
    .kp-iw__k{
      width: 88px;
      flex: 0 0 auto;
      font-size: 11px;
      font-weight: 900;
      color: rgba(17,24,39,0.48);
      letter-spacing: .3px;
      text-transform: uppercase;
    }
    .kp-iw__v{
      flex: 1 1 auto;
      font-size: 13px;
      font-weight: 800;
      color:#111827;
      word-break: break-word;
    }
    .kp-iw__foot{
      padding: 10px 12px 12px 12px;
      border-top: 1px solid rgba(17,24,39,0.06);
      display:flex;
      justify-content:space-between;
      align-items:center;
      font-size: 12px;
      font-weight: 800;
      color: rgba(17,24,39,0.72);
    }
    .kp-iw__foot--cancel{
      padding-top: 0;
      justify-content: flex-start;
    }
    .kp-iw__cancel-label{
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      user-select: none;
      font-size: 12px;
      font-weight: 900;
      color: #b45309;
    }
    .kp-iw__cancel-cb{
      width: 16px;
      height: 16px;
      accent-color: #d97706;
      cursor: pointer;
    }
    .kp-sale-ui .kp-savebar{
      position: sticky;
      bottom: 0;
      z-index: 20;
      margin-top: 8px;
      padding: 10px 12px calc(10px + env(safe-area-inset-bottom, 0px)) 12px;
    }
    .kp-savebar__left{
      width: 100%;
      display:flex;
      flex-direction:column;
      gap: 3px;
    }
    .kp-sale-ui .kp-savebar__label{
      font-size: 11px;
      font-weight: 900;
      line-height: 1;
      color:#111827;
    }
    .kp-sale-ui .kp-savebar__amount{
      display:flex;
      align-items:center;
      justify-content:space-between;
      flex-wrap: nowrap;
      gap: clamp(8px, 2.5vw, 14px);
      width: 100%;
      min-width: 0;
    }
    #todayPayAmount{
      display:flex;
      align-items:center;
      /* ยอมย่อได้ (min-width:0) + ไม่ยึด width:100% ที่ทำให้ล้นไปทับปุ่มบนจอแคบ */
      flex: 1 1 0%;
      min-width: 0;
      width: auto;
      /* ย่อ/ขยายตามความกว้างจอ กันตัวเลขหลายหลักดันทับปุ่ม */
      font-size: clamp(26px, 9vw, 46px);
      line-height: .95;
      font-weight: 500;
      letter-spacing: -.03em;
      color:#111827;
      padding-top: 0;
      padding-right: 2px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    /* ============================================================
       แถบปุ่มบันทึก/พิมพ์ — ใช้ #id ล้วนกันชนกับกฎเก่าใน etc.css
       (.kp-savebar__btn / --cube / --save / --print มี base rule เดิมอยู่
       ที่บางเคสมี specificity เท่ากันแล้วแพ้/ชนะไม่แน่นอน ทำให้ปุ่มเพี้ยน)
       บันทึก = ปุ่มหลัก ใหญ่เต็มกรอบที่เหลือ / พิมพ์ = ไอคอนเล็กด้านข้าง
       ============================================================ */
    #kpSaveBtnRow{
      display:flex;
      flex-direction:row;
      align-items:center;
      /* ปุ่ม % ชิดซ้ายสุด / ปุ่มบันทึกชิดขวาสุดของแถบล่าง (คำขอ 2026-09-09)
         #todayPayAmount ที่เคยอยู่ในแถวนี้ถูกซ่อนอยู่แล้ว (display:none) แถวนี้จึงเป็น
         เนื้อหาเดียวของ .kp-savebar__left ให้ขยายเต็มความกว้างแล้วดันสองปุ่มไปคนละฝั่งได้เลย */
      justify-content: space-between;
      gap: clamp(10px, 3.5vw, 20px);
      flex: 1 1 auto;
      width: 100%;
      min-width: 0;
      margin-left: 0;
    }
    #kpMainSaveBtn{
      display:flex;
      align-items:center;
      justify-content:center;
      /* กว้างยืดหยุ่นตามจอ ไม่ล้นไปทับตัวเลขยอด */
      flex: 0 1 auto;
      /* ย่อขนาดปุ่มบันทึกให้เล็กลง (คำขอ 2026-09-09 — เดิมใหญ่เกินไป) */
      width: clamp(92px, 26vw, 130px);
      min-width: 0;
      height: clamp(50px, 15vw, 66px);
      min-height: 0;
      padding: 0 8px;
      border: 0;
      border-radius: 16px;
      background:
        radial-gradient(circle at top left, rgba(255,255,255,0.20), transparent 36%),
        linear-gradient(180deg, #45c46f 0%, #1f9d52 100%);
      box-shadow: 0 8px 16px rgba(39,174,96,0.32);
      color:#fff;
      font-size: clamp(15px, 4.4vw, 20px);
      font-weight: 1000;
      line-height: .95;
      letter-spacing: -.01em;
      white-space: nowrap;
      transition: transform .14s ease, box-shadow .14s ease, filter .14s ease;
    }
    #kpMainSaveBtn .fa{ display:none; }
    #kpMainSaveBtn span{ font-size: inherit; font-weight: 1000; }
    #kpMainSaveBtn:hover,
    #kpMainSaveBtn:focus{
      color:#fff;
      filter: brightness(1.02);
      transform: translateY(-1px);
    }
    #kpMainSaveBtn:active{
      transform: translateY(0) scale(0.98);
      box-shadow: 0 4px 10px rgba(39,174,96,0.20);
    }

    /* ปุ่ม % แบบวงแหวนความคืบหน้า (โทนเข้ม) ตามภาพตัวอย่างที่ผู้ใช้ส่งมา (คำขอ 2026-09-09)
       แทนที่ทรงกลมสีเขียว/แดงแบบเดิม — ใช้ conic-gradient เป็นวงแหวน + ตัวเลข/ป้ายข้อความด้านขวา
       สีวงแหวน/ทิศทางเปลี่ยนตาม pct_change (ขึ้น=เขียว, ลง=แดง) ผ่าน setGrowthButtonText() */
    #kpQuick25Btn.kp-growth-btn{
      display: inline-flex;
      align-items: center;
      gap: clamp(8px, 2.4vw, 12px);
      flex: 0 0 auto;
      /* ทำให้ปุ่ม "อ้วน" ขึ้น (สูง+กว้าง) ใกล้เคียงปุ่มบันทึกมากขึ้น (คำขอ 2026-09-09) */
      height: clamp(58px, 17vw, 74px);
      padding: 0 clamp(14px, 4vw, 20px) 0 clamp(6px, 2vw, 9px);
      border: 0;
      border-radius: 999px;
      background: linear-gradient(180deg, #2c303a 0%, #14161b 100%);
      box-shadow:
        0 10px 20px rgba(0,0,0,0.30),
        inset 0 1px 0 rgba(255,255,255,0.08);
      color: #fff;
      transition: transform .14s ease, box-shadow .14s ease, filter .14s ease;
    }
    #kpQuick25Btn.kp-growth-btn:hover,
    #kpQuick25Btn.kp-growth-btn:focus{
      color: #fff;
      filter: brightness(1.08);
      transform: translateY(-1px);
    }
    #kpQuick25Btn.kp-growth-btn:active{
      transform: translateY(0) scale(0.97);
    }
    #kpQuick25Btn.kp-growth-btn.is-open{
      box-shadow:
        0 10px 20px rgba(0,0,0,0.30),
        inset 0 0 0 2px rgba(255,255,255,0.35);
    }
    .kp-growth-btn__ring{
      position: relative;
      width: clamp(40px, 11.5vw, 52px);
      height: clamp(40px, 11.5vw, 52px);
      flex: 0 0 auto;
      border-radius: 50%;
      background: conic-gradient(var(--kp-ring-color, rgba(255,255,255,0.5)) calc(var(--kp-ring-pct, 0) * 1%), rgba(255,255,255,0.16) 0);
    }
    .kp-growth-btn__ring::before{
      content: "";
      position: absolute;
      inset: 4px;
      border-radius: 50%;
      background: #1a1c22;
    }
    .kp-growth-btn__text{
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      line-height: 1.1;
      min-width: 0;
    }
    /* ตัดป้าย "ความเชื่อมั่น" ออกแล้ว เหลือแค่ตัวเลข % — จัดกึ่งกลางแนวตั้งแทนการเรียงเป็นคอลัมน์ (คำขอ 2026-09-15) */
    .kp-growth-btn__text--nolabel{
      justify-content: center;
    }
    .kp-growth-btn__pct{
      font-size: clamp(17px, 5vw, 21px);
      font-weight: 1000;
      color: #fff;
      white-space: nowrap;
    }
    .kp-growth-btn__label{
      font-size: clamp(10px, 2.8vw, 12.5px);
      font-weight: 700;
      color: rgba(255,255,255,0.56);
      white-space: nowrap;
    }
    /* เส้นแบ่ง + ปุ่มโทรหาลูกค้า แทรกในปุ่ม % เดียวกัน ตามภาพตัวอย่างที่ผู้ใช้ส่งมา (คำขอ 2026-09-09) */
    .kp-growth-btn__divider{
      width: 1px;
      align-self: stretch;
      margin: 10px 0;
      background: rgba(255,255,255,0.18);
      flex: 0 0 auto;
    }
    .kp-growth-btn__call{
      display: flex;
      align-items: center;
      justify-content: center;
      width: clamp(36px, 10.5vw, 46px);
      height: clamp(36px, 10.5vw, 46px);
      border-radius: 50%;
      flex: 0 0 auto;
      cursor: pointer;
      /* ทรงกลมเขียวสว่างมีมิติ (ปรับให้สว่าง/เขียวชัดขึ้นตามคำขอ 2026-09-09 — เดิมจางเกินไป) */
      background: radial-gradient(circle at 34% 26%, rgba(255,255,255,0.60) 0%, rgba(134,239,172,0.65) 26%, #22c55e 62%, #15803d 100%);
      box-shadow:
        inset 0 1px 1px rgba(255,255,255,0.55),
        inset 0 -3px 5px rgba(0,50,20,0.35),
        0 0 14px rgba(74,222,128,0.80),
        0 0 3px rgba(190,255,205,0.9);
      color: #f0fff4;
      font-size: clamp(15px, 4.4vw, 18px);
      transition: transform .14s ease, filter .14s ease, opacity .14s ease;
    }
    .kp-growth-btn__call:hover,
    .kp-growth-btn__call:focus{
      filter: brightness(1.15);
      outline: none;
    }
    .kp-growth-btn__call:active{
      transform: scale(0.92);
    }
    /* is-disabled: เดิมกดไม่ได้เลยถ้าไม่มีเบอร์ แต่ตอนนี้กดได้เสมอ (เปิดป็อปอัพให้เพิ่มเบอร์ได้)
       เลยแค่จางลงเป็นสัญญาณบอกว่ายังไม่มีเบอร์ ไม่ตัด cursor:pointer ออก (คำขอ 2026-09-09) */
    .kp-growth-btn__call.is-disabled{
      opacity: 0.55;
    }

    /* ป็อปอัพรายละเอียดลูกค้า + จัดการเบอร์โทร (คำขอ 2026-09-09) */
    .kp-callinfo-row{
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 9px 0;
      border-bottom: 1px solid rgba(17,24,39,0.08);
      flex-wrap: wrap;
    }
    .kp-callinfo-row:last-child{
      border-bottom: 0;
    }
    .kp-callinfo-k{
      flex: 0 0 72px;
      font-weight: 700;
      color: #6b7280;
      font-size: 13.5px;
      padding-top: 4px;
    }
    .kp-callinfo-v{
      flex: 1 1 auto;
      min-width: 0;
      font-weight: 600;
      color: #111827;
      padding-top: 4px;
      word-break: break-word;
    }
    .kp-callinfo-tel{
      display: flex;
      align-items: center;
      gap: 8px;
      flex: 1 1 auto;
      min-width: 0;
      flex-wrap: wrap;
    }
    .kp-callinfo-tel-edit{
      flex: 1 1 100%;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 6px;
      flex-wrap: wrap;
    }
    .kp-callinfo-tel-edit input{
      flex: 1 1 160px;
      min-width: 0;
    }
    .kp-callinfo-tel-edit__btns{
      display: flex;
      gap: 6px;
      flex: 0 0 auto;
    }
    /* รายการเบอร์โทร (หลายเบอร์ต่อลูกค้า): แถวไม่มีป้ายกำกับ แต่ละแถวกดโทร/ลบแยกกันได้ (คำขอ 2026-09-14) */
    .kp-tel-list{
      flex: 1 1 100%;
      display: flex;
      flex-direction: column;
      gap: 6px;
      min-width: 0;
    }
    .kp-tel-list__empty{
      font-size: 13.5px;
      color: rgba(17,24,39,0.45);
      padding-top: 4px;
    }
    .kp-tel-row{
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 6px 10px;
      background: rgba(17,24,39,0.035);
      border-radius: 10px;
    }
    .kp-tel-row__num{
      flex: 1 1 auto;
      min-width: 0;
      font-weight: 600;
      color: #111827;
      word-break: break-word;
    }
    /* ใช้ .modal นำหน้า เพิ่มความจำเพาะ (specificity) ให้ชนะกฎ ".modal .btn" ใน popup_glass.css
       (min-height:46px / padding:0 20px / border-radius:999px และตอนจอเล็ก flex:1 1 auto)
       ซึ่งแต่ก่อนไปทับปุ่มวงกลมเล็กนี้จนบิดเบี้ยว/ยืดเต็มแถวบนมือถือ (พบ+แก้ 2026-09-15) */
    .modal .kp-tel-row__call,
    .modal .kp-tel-row__del{
      -webkit-appearance: none;
      appearance: none;
      box-sizing: border-box;
      flex: 0 0 34px;
      width: 34px;
      height: 34px;
      min-width: 34px;
      min-height: 34px;
      max-width: 34px;
      max-height: 34px;
      padding: 0;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
    }
    .modal .kp-tel-row__call .fa,
    .modal .kp-tel-row__del .fa{
      margin: 0;
      font-size: 14px;
    }
    .kp-tel-add{
      flex: 1 1 100%;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 6px;
      flex-wrap: wrap;
    }
    .kp-tel-add input{
      flex: 1 1 160px;
      min-width: 0;
    }
    .kp-tel-add__btns{
      display: flex;
      gap: 6px;
      flex: 0 0 auto;
    }
    /* ปุ่ม "โทร" ในป็อปอัพ: เขียวสว่างเด่น ตามสีปุ่มโทรเล็กที่ปรับใหม่ */
    .kp-btn-call{
      background: linear-gradient(180deg, #4ade80 0%, #16a34a 100%);
      border: 0;
      color: #fff;
      font-weight: 700;
      box-shadow: 0 6px 14px rgba(22,163,74,0.35);
    }
    .kp-btn-call:hover,
    .kp-btn-call:focus{
      color: #fff;
      filter: brightness(1.08);
    }
    .kp-btn-call:active{
      filter: brightness(0.96);
    }
    .kp-btn-call:disabled{
      opacity: 0.5;
      box-shadow: none;
    }

    /* ปุ่มพิมพ์: กรอบสี่เหลี่ยมจัตุรัสสูงเต็มแถว (86x86) เท่าปุ่มบันทึก
       เส้นกรอบ+ไอคอนสีขาว มีเงาช่วยให้ลอยเด่นและอ่านออกบนพื้นสีอ่อน */
    #btnReceiptPreview{
      position: relative; /* กันข้อความซ่อน (sr-only) หลุดไปดันเลย์เอาต์ */
      display:flex;
      align-items:center;
      justify-content:center;
      /* จัตุรัสเสมอ + ย่อตามจอพร้อมปุ่มบันทึก (aspect-ratio ล็อกให้ไม่บิดเป็นสี่เหลี่ยมผืนผ้า) */
      flex: 0 0 auto;
      width: clamp(66px, 19vw, 86px);
      min-width: 0;
      height: clamp(66px, 19vw, 86px);
      aspect-ratio: 1 / 1;
      min-height: 0;
      padding: 0;
      margin: 0;
      border: 2.5px solid #ffffff;
      border-radius: 18px;
      text-align:center;
      color:#ffffff;
      /* พื้นจางๆ นิดหน่อย ช่วยให้ของขาวไม่จมหายไปกับพื้นการ์ดสีอ่อน */
      background: rgba(17,24,39,0.10);
      /* เงานอก = ยกกรอบให้ลอย, เงาใน = ขอบขาวดูคมมีมิติ */
      box-shadow:
        0 8px 18px rgba(17,24,39,0.22),
        inset 0 1px 0 rgba(255,255,255,0.35);
      transition: transform .14s ease, opacity .14s ease, color .14s ease, background .14s ease, box-shadow .14s ease;
    }
    /* โชว์เฉพาะไอคอน — ข้อความยังอยู่ใน DOM ให้ JS/screen reader ใช้ */
    #btnReceiptPreview .kp-savebar__btn-icon{
      display:flex;
      align-items:center;
      justify-content:center;
      width: 100%;
      height: 100%;
    }
    /* ไอคอนใหญ่เต็มกรอบ (เว้นขอบในนิดเดียวให้หายใจ) + เงาให้ตัวไอคอนขาวดูมีมิติ
       ย่อตามกรอบด้วย clamp เพื่อไม่ให้ล้นออกนอกกล่องบนจอแคบ */
    #btnReceiptPreview .kp-print-ico{
      font-size: clamp(34px, 11vw, 54px);
      line-height: 1;
      text-shadow: 0 2px 6px rgba(17,24,39,0.35);
    }
    #btnReceiptPreview .kp-savebar__btn-copy{
      position:absolute;
      width:1px;
      height:1px;
      overflow:hidden;
      clip: rect(0 0 0 0);
      white-space: nowrap;
    }
    #btnReceiptPreview:hover{
      color:#ffffff;
      background: rgba(17,24,39,0.16);
      transform: translateY(-1px);
      box-shadow:
        0 12px 22px rgba(17,24,39,0.28),
        inset 0 1px 0 rgba(255,255,255,0.42);
    }
    #btnReceiptPreview:active{
      background: rgba(17,24,39,0.22);
      transform: translateY(0) scale(0.94);
      box-shadow:
        0 4px 10px rgba(17,24,39,0.20),
        inset 0 1px 0 rgba(255,255,255,0.30);
    }
    /* สถานะการพิมพ์: ไอคอน/กรอบขาวเสมอ เปลี่ยนแค่โทนพื้นให้สื่อสถานะ */
    #btnReceiptPreview[data-accent='muted']{ background: rgba(100,116,139,0.30); }
    #btnReceiptPreview[data-accent='printing']{ background: rgba(31,157,82,0.38); }
    #btnReceiptPreview[data-accent='error']{ background: rgba(220,38,38,0.38); }
    /* เลือกสถานะ "ไม่จ่าย" -> เตือนด้วยพื้นแดงสดตอนปุ่มยัง "พร้อมพิมพ์" (ไม่ทับสถานะกำลังพิมพ์/error ที่สำคัญกว่า) */
    #btnReceiptPreview.kp-print-unpaid[data-accent='ready']{
      background: #ff0000;
      border-color: #ff0000;
    }
    #btnReceiptPreview.kp-print-unpaid[data-accent='ready']:hover{
      background: #ff2222;
      border-color: #ff2222;
    }
    #btnReceiptPreview.is-busy .kp-print-ico{ opacity: .7; }
    #btnReceiptPreview.is-busy{ cursor: wait; }
    .kp-comm__btn--print{
      color:#fff;
      border:0;
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 55%, #b91c1c 100%);
      box-shadow: 0 16px 32px rgba(220,38,38,0.26);
    }
    .kp-comm__btn--print:hover,
    .kp-comm__btn--print:focus{
      color:#fff;
      filter: brightness(1.03);
      transform: translateY(-1px);
    }
    .kp-comm__btn--print .fa{
      margin-right: 6px;
    }
    .kp-plus-final__actions{
      align-items: stretch;
    }
    .kp-plus-final__actions .kp-comm__btn{
      min-height: 48px;
      font-weight: 900;
      border-radius: 14px;
    }
    .kp-plus-final__print-hint{
      margin-top: 8px;
      font-size: 12px;
      font-weight: 800;
      color: rgba(17,24,39,0.58);
    }
    .kp-print-hint{
      margin-top: 1px;
      font-size: 9px;
      font-weight: 900;
      color: rgba(17,24,39,0.64);
      line-height: 1.05;
    }
    .kp-print-hint strong{
      color: rgba(17,24,39,0.84);
    }
    .kp-sale-ui .kp-savebar__sub{
      font-size: 9px;
      line-height: 1.05;
      color: rgba(17,24,39,0.72);
      margin-top: 0;
      max-width: calc(100% - 108px);
    }
    .kp-receipt-modal .modal-dialog{
      max-width: 420px;
    }
    .kp-receipt-modal .modal-body{
      padding: 12px 12px 14px 12px;
    }
    .kp-receipt-status{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 14px;
      background: rgba(17,24,39,0.04);
      border: 1px solid rgba(17,24,39,0.08);
      margin-bottom: 10px;
    }
    /* แถบเลือกเครื่องพิมพ์ Bluetooth (กดได้) */
    .kp-receipt-device{
      display:flex;
      align-items:center;
      gap:12px;
      width:100%;
      text-align:left;
      padding:12px 14px;
      border-radius:14px;
      border:1.5px solid rgba(22,163,74,0.22);
      background:#f0fdf4;
      cursor:pointer;
      margin-bottom:10px;
      transition: background .15s ease, border-color .15s ease, transform .12s ease;
    }
    .kp-receipt-device:hover{ background:#e7fbee; border-color:rgba(22,163,74,0.38); }
    .kp-receipt-device:active{ transform: scale(0.99); }
    .kp-receipt-device__ic{
      width:38px; height:38px; flex:0 0 auto;
      border-radius:999px;
      background:#dcfce7;
      color:#16a34a;
      display:inline-flex; align-items:center; justify-content:center;
      font-size:17px;
    }
    .kp-receipt-device__tx{
      flex:1 1 auto; min-width:0;
      font-size:14px; font-weight:900; color:#0f172a; line-height:1.25;
    }
    .kp-receipt-device__tx strong{ font-weight:1000; }
    .kp-receipt-device__tx small{
      display:block; margin-top:2px;
      font-size:11px; font-weight:800; color:rgba(15,23,42,0.55);
    }
    .kp-receipt-device__ch{ color:rgba(15,23,42,0.35); flex:0 0 auto; }
    .kp-receipt-status__label{
      font-size: 12px;
      font-weight: 1000;
      color: rgba(17,24,39,0.62);
    }
    .kp-receipt-status__text{
      font-size: 13px;
      font-weight: 1000;
      color: rgba(17,24,39,0.86);
      text-align: right;
    }
    .kp-receipt-paper{
      width: 100%;
      max-width: 300px;
      margin: 0 auto;
      padding: 10px 12px 12px 12px;
      border-radius: 8px;
      border: 1px solid rgba(17,24,39,0.10);
      background: #fff;
      box-shadow: 0 8px 18px rgba(17,24,39,0.08);
    }
    .kp-receipt-paper__no{
      text-align:right;
      font-size: 12px;
      line-height: 1;
      font-weight: 1000;
      color:#111827;
      margin-bottom: 2px;
    }
    .kp-receipt-paper__store{
      text-align:center;
      font-size: 24px;
      line-height: 1.02;
      font-weight: 1000;
      color:#111827;
      letter-spacing: -.03em;
      margin-bottom: 8px;
    }
    .kp-receipt-paper__bill-title{
      text-align:center;
      font-size: 16px;
      line-height: 1.06;
      font-weight: 1000;
      color:#111827;
      margin-bottom: 2px;
    }
    .kp-receipt-paper__addr{
      text-align:center;
      font-size: 11px;
      line-height: 1.12;
      font-weight: 900;
      color:#111827;
      margin-top: 1px;
    }
    .kp-receipt-paper__phone{
      text-align:center;
      font-size: 12px;
      line-height: 1.1;
      font-weight: 1000;
      color:#111827;
      margin-top: 6px;
      margin-bottom: 6px;
    }
    .kp-receipt-paper__rule{
      border-top: 1px solid rgba(17,24,39,0.86);
      margin: 5px 0;
    }
    .kp-receipt-paper__customer,
    .kp-receipt-paper__outstanding,
    .kp-receipt-paper__today-head,
    .kp-receipt-paper__money{
      font-size: 12px;
      line-height: 1.12;
      font-weight: 1000;
      color:#111827;
    }
    .kp-receipt-paper__customer{
      margin-top: 1px;
    }
    .kp-receipt-paper__outstanding{
      margin-top: 1px;
    }
    .kp-receipt-paper__today-head{
      margin-top: 1px;
      margin-bottom: 4px;
    }
    .kp-receipt-paper__items{
      margin-bottom: 3px;
    }
    .kp-receipt-paper__item-row{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 8px;
      padding: 0;
      font-size: 13px;
      line-height: 1.18;
      font-weight: 1000;
      color:#111827;
    }
    .kp-receipt-paper__item-row strong{
      font-size: 14px;
      font-weight: 1000;
    }
    /* แถวรายการแบบมีคอลัมน์ ราคา/จำนวน/เป็นเงิน (คำขอ 2026-09-14) */
    .kp-receipt-paper__item-row--priced{
      justify-content: flex-start;
    }
    .kp-receipt-paper__item-row--priced span:nth-child(1){
      flex: 1 1 auto;
      min-width: 0;
      text-align: left;
      /* กันตัวหนังสือตกบรรทัด เช่น "คงค้างเก่า" (คำขอ 2026-09-15) — บังคับบรรทัดเดียว
         แล้วเผื่อพื้นที่คอลัมน์นี้เพิ่มขึ้น (ลดคอลัมน์ตัวเลขแต่ละช่องจาก 26% เหลือ 22%) */
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .kp-receipt-paper__item-row--priced span:nth-child(2),
    .kp-receipt-paper__item-row--priced span:nth-child(3),
    .kp-receipt-paper__item-row--priced span:nth-child(4),
    .kp-receipt-paper__item-row--priced strong{
      flex: 0 0 22%;
      text-align: right;
    }
    /* คอลัมน์ "ราคา" (ช่องที่ 2) ให้เป็นตัวบางกว่าคอลัมน์อื่น (คำขอ 2026-09-15) */
    .kp-receipt-paper__item-row--priced span:nth-child(2){
      font-weight: 400;
    }
    .kp-receipt-paper__item-row--head{
      font-size: 11px;
      font-weight: 800;
      color: rgba(17,24,39,0.6);
      padding-bottom: 2px;
    }
    .kp-receipt-paper__money{
      margin-top: 2px;
    }
    /* แจ้งขวดหาย/รับขวดคืน บนใบเสร็จ (คำขอ 2026-09-14) */
    .kp-receipt-paper__bottleloss{
      margin-top: 2px;
      font-size: 12.5px;
      font-weight: 1000;
      color: #b45309;
    }
    .kp-receipt-paper__net{
      text-align:center;
      font-size: 22px;
      line-height: 1.05;
      font-weight: 1000;
      color:#111827;
      letter-spacing: -.02em;
      margin-top: 6px;
      margin-bottom: 8px;
    }
    .kp-receipt-paper__status{
      text-align:center;
      font-size: 19px;
      line-height: 1.04;
      font-weight: 1000;
      color:#111827;
      text-decoration: underline;
      text-decoration-thickness: 1px;
      text-underline-offset: 3px;
    }
    .kp-receipt-paper__thanks{
      text-align:center;
      font-size: 14px;
      font-weight: 1000;
      color:#111827;
      margin-top: 6px;
    }
    .kp-receipt-error{
      display:none;
      margin-top: 10px;
      padding: 10px 12px;
      border-radius: 12px;
      background: rgba(239,68,68,0.08);
      color: #b91c1c;
      font-size: 12px;
      font-weight: 900;
    }
    .kp-receipt-actions{
      display:flex;
      justify-content:flex-end;
      gap: 8px;
      margin-top: 12px;
    }
    .kp-receipt-actions .btn{
      border-radius: 999px;
      font-weight: 1000;
      padding: 10px 16px;
    }
    /* ปุ่ม icon บันทึก/แชร์ ใบเสร็จ — ใช้แทนปุ่มข้อความแบบเดิม (คำขอ 2026-09-10) */
    .kp-receipt-iconbtns{
      display:flex;
      justify-content:center;
      gap: 26px;
      margin-top: 10px;
    }
    .kp-receipt-iconbtn{
      display:flex;
      flex-direction:column;
      align-items:center;
      gap:5px;
      background:none;
      border:0;
      padding:2px 4px;
      cursor:pointer;
    }
    .kp-receipt-iconbtn:disabled{ opacity:.5; cursor:not-allowed; }
    .kp-receipt-iconbtn__ic{
      width:46px; height:46px;
      border-radius:999px;
      background:#dcfce7;
      color:#16a34a;
      display:inline-flex; align-items:center; justify-content:center;
      font-size:19px;
      transition: background .15s ease, transform .12s ease;
    }
    .kp-receipt-iconbtn:active .kp-receipt-iconbtn__ic{ transform: scale(0.94); }
    .kp-receipt-iconbtn__ic--share{
      background:#dbeafe;
      color:#2563eb;
    }
    .kp-receipt-iconbtn__tx{
      font-size:12px;
      font-weight:900;
      color: rgba(17,24,39,0.72);
    }

    /* Force override: special-sale bottom sheet */
    .kp-sale-ui .kp-qty-card{
      border-radius: 24px !important;
      border: 1px solid rgba(210,236,219,0.94) !important;
      background:
        radial-gradient(220px 90px at 18% 10%, rgba(255,255,255,0.28), rgba(255,255,255,0) 70%),
        linear-gradient(180deg, rgba(236,249,241,0.96), rgba(215,240,226,0.88)) !important;
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.96),
        0 12px 24px rgba(8,48,30,0.10) !important;
      -webkit-backdrop-filter: blur(22px) !important;
      backdrop-filter: blur(22px) !important;
    }
    .kp-sale-ui .kp-qty-row{
      border-bottom: 1px solid rgba(196,225,209,0.62) !important;
    }
    .kp-sale-ui .kp-qty-btn{
      border-radius: 999px !important;
      border: 1px solid rgba(206,233,215,0.96) !important;
      background:
        radial-gradient(90px 34px at 28% 14%, rgba(255,255,255,0.42), rgba(255,255,255,0) 64%),
        linear-gradient(180deg, rgba(242,251,245,0.98), rgba(214,240,225,0.90)) !important;
      color: rgba(57,64,60,0.96) !important;
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.96),
        0 8px 16px rgba(8,48,30,0.10) !important;
    }
    .kp-sale-ui .kp-qty-label{
      color: rgba(61,68,65,0.96) !important;
      background: linear-gradient(180deg, rgba(232,245,237,0.96), rgba(214,238,224,0.84)) !important;
      border-radius: 999px !important;
    }
    .kp-sale-ui .kp-qty-value{
      color: rgba(58,64,61,0.96) !important;
    }
    .kp-sale-ui .form-control,
    .kp-sale-ui .input-group-text{
      border-radius: 18px !important;
      border: 1px solid rgba(206,233,215,0.92) !important;
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.92),
        0 8px 16px rgba(8,48,30,0.08) !important;
    }
    .kp-sale-ui .form-control{
      background:
        radial-gradient(120px 30px at 18% 14%, rgba(255,255,255,0.34), rgba(255,255,255,0) 72%),
        linear-gradient(180deg, rgba(242,251,245,0.98), rgba(221,242,230,0.90)) !important;
      color: rgba(58,64,61,0.96) !important;
      -webkit-backdrop-filter: blur(18px) !important;
      backdrop-filter: blur(18px) !important;
    }
    .kp-sale-ui .form-control::placeholder{
      color: rgba(94,104,100,0.74) !important;
    }
    .kp-sale-ui .input-group-text{
      background: linear-gradient(180deg, rgba(237,248,241,0.98), rgba(216,240,226,0.88)) !important;
      color: rgba(82,92,88,0.96) !important;
      -webkit-backdrop-filter: blur(18px) !important;
      backdrop-filter: blur(18px) !important;
    }
    .kp-sale-ui .kp-paybar{
      border-radius: 24px !important;
      background:
        radial-gradient(220px 80px at 18% 12%, rgba(255,255,255,0.30), rgba(255,255,255,0) 72%),
        linear-gradient(180deg, rgba(238,249,242,0.96), rgba(213,238,223,0.88)) !important;
      border: 1px solid rgba(206,233,215,0.92) !important;
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.92),
        0 12px 24px rgba(8,48,30,0.08) !important;
      -webkit-backdrop-filter: blur(22px) !important;
      backdrop-filter: blur(22px) !important;
    }
    .kp-sale-ui .kp-paybar__label{
      color: rgba(63,70,67,0.96) !important;
    }
    .kp-sale-ui .kp-seg{
      border: 1px solid rgba(204,232,214,0.92) !important;
      background: linear-gradient(180deg, rgba(243,251,246,0.96), rgba(223,242,231,0.88)) !important;
      -webkit-backdrop-filter: blur(18px) !important;
      backdrop-filter: blur(18px) !important;
    }
    .kp-sale-ui .kp-seg__item{
      color: rgba(61,68,65,0.96) !important;
    }
    .kp-sale-ui .kp-seg__item:has(input:checked),
    .kp-sale-ui .kp-seg__item.is-active{
      background: linear-gradient(180deg, rgba(50,220,116,0.96), rgba(25,186,84,0.92)) !important;
      color: rgba(247,255,250,0.98) !important;
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.24) !important;
    }

    /* Force override: special-sale accordion headers */
    .kp-comm__pane.kp-plus-glass .kp-plus-sec{
      overflow: hidden !important;
      border-radius: 22px !important;
      border: 1px solid rgba(255,255,255,0.22) !important;
    }
    .kp-comm__pane.kp-plus-glass .kp-plus-sec__head{
      position: relative !important;
      border-bottom: 0 !important;
      box-shadow: inset 0 -1px 0 rgba(255,255,255,0.10);
    }
    .kp-comm__pane.kp-plus-glass .kp-plus-sec__body{
      border-top: 1px solid rgba(255,255,255,0.12) !important;
      margin-top: -1px !important;
      background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.00)) !important;
    }
    .kp-comm__pane.kp-plus-glass .kp-plus-sec__icon{
      background:
        radial-gradient(80px 34px at 24% 16%, rgba(255,255,255,0.30), rgba(255,255,255,0) 60%),
        linear-gradient(180deg, rgba(255,255,255,0.20), rgba(255,255,255,0.08)) !important;
      border: 1px solid rgba(255,255,255,0.22) !important;
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.24) !important;
      color: #49b7a0 !important;
    }
    .kp-comm__pane.kp-plus-glass .kp-plus-sec__icon .fa{
      color: #49b7a0 !important;
    }
    .kp-comm__pane.kp-plus-glass .kp-plus-sec__chev{
      border: 1px solid rgba(255,255,255,0.18) !important;
      background: rgba(255,255,255,0.14) !important;
    }

    /* -----------------------------
       Sheet home (ค้นหาลูกค้า + ขายเพิ่มเติม) — ธีมเดียวกับ select_page
    ------------------------------*/
    .kp-sheet-home{
      position: relative;
      min-height: calc(100dvh - 64px);
      margin: 0;
      padding: 16px 16px calc(26px + env(safe-area-inset-bottom, 0px)) 16px;
      border-radius: 0;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      background: transparent;
      box-shadow: none;
    }
    .kp-sheet-home__bubbles{
      position: absolute;
      inset: 0;
      z-index: 0;
      overflow: hidden;
      pointer-events: none;
      border-radius: inherit;
    }
    .kp-sheet-home__bubble{
      position: absolute;
      bottom: -14%;
      border-radius: 50%;
      background:
        radial-gradient(circle at 30% 26%, rgba(255,255,255,0.98), rgba(255,255,255,0.30) 46%, rgba(255,255,255,0.10) 72%, rgba(255,255,255,0) 100%);
      border: 1.5px solid rgba(255,255,255,0.85);
      box-shadow:
        inset 0 0 10px rgba(255,255,255,0.60),
        0 0 12px rgba(255,255,255,0.30);
      animation-name: kp-bubble-rise;
      animation-timing-function: ease-in;
      animation-iteration-count: infinite;
    }
    @keyframes kp-bubble-rise{
      0%{ transform:translate(0,0); opacity:0; }
      10%{ opacity:0.9; }
      50%{ transform:translate(10px,-52vh); }
      90%{ opacity:0.7; }
      100%{ transform:translate(-8px,-102vh); opacity:0; }
    }
    @media (prefers-reduced-motion: reduce){
      .kp-sheet-home__bubble{ animation:none; display:none; }
    }
    .kp-sheet-home__inner{
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 380px;
      flex: 1 1 auto;
      min-height: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .kp-sheet-home__search{
      width: 100%;
      height: 58px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      border: 1px solid rgba(255,255,255,0.75);
      border-radius: 999px;
      background:
        linear-gradient(180deg, rgba(255,255,255,0.60), rgba(255,255,255,0.32));
      -webkit-backdrop-filter: blur(20px) saturate(160%);
      backdrop-filter: blur(20px) saturate(160%);
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.70),
        0 12px 26px rgba(20,90,60,0.16);
      color: #0f2a1c;
      font-size: 17px;
      font-weight: 900;
    }
    .kp-sheet-home__search-icon{
      font-size: 16px;
      color: #16a34a;
    }
    .kp-sheet-home__mid{
      flex: 1 1 auto;
      width: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    .kp-sheet-home__plus{
      width: 118px;
      height: 118px;
      border-radius: 999px;
      border: none;
      background:
        linear-gradient(180deg, rgba(255,255,255,0.36), rgba(220,252,231,0.16));
      -webkit-backdrop-filter: blur(22px) saturate(160%);
      backdrop-filter: blur(22px) saturate(160%);
      box-shadow:
        0 16px 34px rgba(22,163,74,0.22),
        inset 0 1px 0 rgba(255,255,255,0.65),
        inset 0 -1px 0 rgba(255,255,255,0.10);
      color: #16a34a;
      font-size: 42px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform .16s ease, filter .16s ease;
    }
    .kp-sheet-home__plus:hover,
    .kp-sheet-home__plus:focus{
      transform: translateY(-1px) scale(1.03);
      filter: brightness(1.05);
      outline: none;
    }
    .kp-sheet-home__plus:active{
      transform: translateY(0) scale(0.97);
    }
    .kp-sheet-home__plus-label{
      font-size: 19px;
      font-weight: 1000;
      color: #111827;
      text-shadow: 0 1px 0 rgba(255,255,255,0.60);
    }
  </style>
  <!-- ธีม "กระดาษสามมิติ" ของหน้าขาย — โหลดเป็นไฟล์สุดท้ายเพื่อทับสไตล์เดิม (คำขอ 2026-09-28) -->
  <link rel="stylesheet" href="css/np_sale.css?v=4">
  <!-- ธีมกระดาษสามมิติ: ป็อปอัพทุกตัว (.kp-modal) + กล่องข้อมูลบนแผนที่ (คำขอ 2026-09-28) -->
  <link rel="stylesheet" href="css/np_popup.css?v=47.<?php echo (int)@filemtime(__DIR__ . "/css/np_popup.css"); ?>">
  <link rel="stylesheet" href="css/np_clay.css?v=13">
  <link rel="stylesheet" href="css/np_neu.css?v=9">
  <link rel="stylesheet" href="css/np_form.css?v=3">
  <link rel="stylesheet" href="css/np_iw.css?v=8">
  <link rel="stylesheet" href="css/np_cashsub.css?v=80">
  <link rel="stylesheet" href="css/np_contact.css?v=6">
  <link rel="stylesheet" href="css/kp_clay_map.css?v=<?php echo (int)@filemtime(__DIR__ . "/css/kp_clay_map.css"); ?>">
</head>
<body class="index2-body">

<!-- ส่งสิทธิ์จาก PHP -> JS เพื่อกำหนด icon marker -->
<?php
/* ตั้งค่าวิดเจ็ตของผู้ใช้ (ปุ่มฟันเฟือง) — ไม่เคยตั้ง (หรือยังไม่เคยเลือกปุ่ม) = แสดงทุกปุ่มเหมือนเดิม · $kpWp เป็น null = หน้าตาเหมือนเดิมทุกอย่าง */
$kpWp = null;
try {
  if (!defined('KP_WIDGET_PREFS_INCLUDE_ONLY')) define('KP_WIDGET_PREFS_INCLUDE_ONLY', 1);
  if (is_file(__DIR__ . "/json/widget_prefs.php") && isset($db) && function_exists('kp_get_current_user')) {
    require_once(__DIR__ . "/json/widget_prefs.php");
    $kpWp = wp_load($db, kp_get_current_user());
  }
} catch (Exception $e) { $kpWp = null; }
if (!function_exists('kp_wp_hide')) {
  function kp_wp_hide($k) {
    global $kpWp;
    if (!$kpWp) return '';
    if ($k === 'gear' || $k === 'menu') return (isset($kpWp['aux_hide']) && is_array($kpWp['aux_hide']) && in_array($k, $kpWp['aux_hide'], true)) ? ' data-swhide="1"' : '';
    return (is_array($kpWp['show']) && !in_array($k, $kpWp['show'], true)) ? ' data-swhide="1"' : '';
  }
}
?>
<script>
  window.KP_WIDGET_PREFS = <?php echo json_encode($kpWp); ?>;
  window.KP_ID_STATUS = <?php echo isset($_SESSION['id_status']) ? (int)$_SESSION['id_status'] : 0; ?>;
  window.KP_USER = <?php echo json_encode(isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : (isset($_SESSION['name_regis']) ? $_SESSION['name_regis'] : "")); ?>;
  window.KP_NAME = <?php echo json_encode(isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : (isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : (isset($_SESSION['name_regis']) ? $_SESSION['name_regis'] : ""))); ?>;
</script>

<!-- Google Map background (เต็มจอ) -->
<div id="map" class="map-screen" aria-label="แผนที่"></div>

<!-- (ลบปุ่มเข็มทิศมุมขวาบนออกแล้ว) -->

<?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1){ ?>
<!-- แอดมิน: ไอคอนผู้ใช้ที่ออนไลน์อยู่ ด้านข้างแผนที่ (คำขอ 2026-10-08) — แตะ = ติดตามตำแหน่ง / ดีดออก (json/online_users_ui.js) -->
<div id="npOnline" class="np-on is-empty" aria-label="ผู้ใช้ที่ออนไลน์"></div>
<?php } ?>
<!-- ปุ่มควบคุมแผนที่แบบไอคอน (แนวตั้งด้านขวา) -->
<div id="mapControls" class="map-controls" aria-label="ปุ่มควบคุมแผนที่">
  <!-- ทรงปุ่มเป็นออร์บวงกลมเรืองแสงหมุนวน + ออร่าสีฟ้า + จุดสามจุดห้อยใต้ปุ่ม ไม่มีขอบขาว (คำขอ 2026-09-09)
       ลวดลายสีสันอยู่ใน .kp-orb-surface (ลูกในสุด มี overflow:hidden ของตัวเอง) เพื่อให้ตัวปุ่มเองไม่ต้องครอบ
       overflow:hidden อีกต่อไป — จุดสามจุดที่ห้อยอยู่ใต้ปุ่มจะได้โผล่พ้นขอบวงกลมออกมาได้ ไม่โดนตัด
       .kp-orb-dots ผูก animation แกว่งไว้กับ class "is-shaking" ที่ระบบแจ้งเตือนแชทเดิม (map.js: setBellShake)
       เติมให้ #mapControlsToggle อยู่แล้วเมื่อมีข้อความใหม่ -> จุดจะเขย่าแกว่งไปมาอัตโนมัติ -->
  <!-- วิดเจ็ตเล็กข้างแผนที่ (แทนลูกโป่งออร์บเดิม — คำขอ 2026-09-28): นาฬิกา + กระดิ่งแจ้งเตือน + ปุ่มเครื่องมือ
       ปุ่มเครื่องมือยังเป็น #mapControlsToggle ตัวเดิม (กดแล้วกางปุ่มควบคุมแผนที่เหมือนเดิม) -->
  <div class="np-sw<?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1){ echo ' np-sw--admin'; } if($kpWp && !empty($kpWp['fold'])){ echo ' np-sw--folded'; } ?>" id="npSideWidget">
    <div class="np-sw__screen" aria-hidden="true">
      <!-- วิดเจ็ตแนวบนสุดของจอ (คำขอ 2026-10-08): นาฬิกาแผ่นพลิก + วันที่ตัวหนา + ปุ่มไอคอนเล็ก -->
      <div class="np-fl"><span class="np-fl__t" id="npFlH">--</span><span class="np-fl__t" id="npFlM">--</span></div>
      <div class="np-fd"><b id="npSwDate"></b><span id="npSwDow"></span></div>
      <!-- วิดเจ็ตเวลาแบบการ์ดยาว (คำขอ 2026-10-21): นาฬิกาเข็ม + วันที่ไทย (พ.ศ.) + พยากรณ์อากาศ 3 ช่วง (Open-Meteo)
           เปลี่ยนพิกัดได้ที่ window.KP_WX = {lat:..,lng:..} ก่อนโหลดสคริปต์นี้ (ค่าเริ่มต้น กรุงเทพฯ) -->
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
    <div class="np-sw__grid">
    <!-- ปุ่มค้นหาลูกค้า + Focus ย้ายจากรายการที่ซ่อนไว้มาไว้ในวิดเจ็ต (คำขอ 2026-09-29) — id เดิม JS เดิมทำงานเหมือนเดิม -->
    <button type="button" class="np-sw__btn np-sw__btn--search" id="btnBranchSearch" title="ค้นหาสาขา" aria-label="ค้นหาสาขา">
      <i class="fa fa-search" aria-hidden="true"></i>
    </button>
    <button type="button" class="np-sw__btn np-sw__btn--focus" id="btnFocus20m" title="โฟกัสตำแหน่งของฉัน" aria-label="โฟกัสตำแหน่งของฉัน">
      <i class="fa fa-crosshairs" aria-hidden="true"></i>
    </button>
    <button type="button" class="np-sw__btn np-sw__btn--type" id="btnMapType" title="สลับดาวเทียม/แผนที่" aria-label="สลับดาวเทียม/แผนที่">
      <i class="fa fa-globe" aria-hidden="true"></i>
    </button>
    <button type="button" class="np-sw__btn np-sw__btn--refresh" id="btnRefreshMap" title="รีเฟรชแผนที่" aria-label="รีเฟรชแผนที่">
      <i class="fa fa-refresh" aria-hidden="true"></i>
    </button>
    <a href="/logout.php" class="np-sw__btn np-sw__btn--logout" id="btnLogoutWidget" title="ออกจากระบบ" aria-label="ออกจากระบบ" onclick="try{ sessionStorage.clear(); }catch(e){}">
      <i class="fa fa-sign-out" aria-hidden="true"></i>
    </a>
    <button id="mapControlsToggle" type="button" class="map-control-btn map-controls__toggle np-sw__tools" title="เมนู" aria-label="เมนู" aria-haspopup="true" aria-expanded="false" aria-controls="mapControlsList"<?php echo kp_wp_hide('menu'); ?>>
      <span class="kp-orb-surface" aria-hidden="true"></span>
      <svg class="np-sw__tools-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14M5 12h14M5 17h14"/></svg>
      <span id="mapControlsToggleBadge" class="kp-notify-badge" aria-hidden="true" style="display:none;"></span>
      <span class="kp-orb-dots" aria-hidden="true">
        <span class="kp-orb-dots__d"></span>
        <span class="kp-orb-dots__d"></span>
        <span class="kp-orb-dots__d"></span>
      </span>
    </button>
    </div>
    <!-- ปุ่มซ่อน/แสดงปุ่มในวิดเจ็ต (คำขอ 2026-10-08) — เริ่มต้น = แสดงปุ่มทั้งหมด -->
    <button type="button" class="np-sw__fold" id="npSwFold" aria-label="<?php echo ($kpWp && !empty($kpWp['fold'])) ? 'แสดงปุ่ม' : 'ซ่อนปุ่ม'; ?>" aria-expanded="<?php echo ($kpWp && !empty($kpWp['fold'])) ? 'false' : 'true'; ?>" title="ซ่อน/แสดงปุ่ม">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 15l6-6 6 6"/></svg>
      <span class="np-sw__foldbadge" id="npSwFoldBadge" aria-hidden="true"></span>
    </button>
  </div>
  <script>
  /* วิดเจ็ตเริ่มต้นแบบย่อ (คำขอ 2026-10-21): แตะที่นาฬิกา -> ปุ่มแผ่ออกนุ่มๆ / ไม่มีการกดต่อ 2 วินาที -> ยุบกลับเอง (kpSwAuto) */
  (function kpSwAuto(){
    var w=document.getElementById("npSideWidget"),f=document.getElementById("npSwFold");
    if(!w||!f)return;
    var IDLE=5000,CLOSE=240,t=null,closing=false;
    w.classList.add("np-sw--folded");f.setAttribute("aria-expanded","false");f.setAttribute("aria-label","เปิดปุ่ม");
    function open(){return !w.classList.contains("np-sw--folded");}
    function listOpen(){var m=document.getElementById("mapControls");return !!(m&&m.classList.contains("is-open"));}
    function clear(){if(t){clearTimeout(t);t=null;}}
    function arm(){clear();if(!open())return;t=setTimeout(function(){if(listOpen()){arm();return;}collapse();},IDLE);}
    function collapse(){
      clear();if(!open()||closing)return;closing=true;w.classList.add("np-sw--closing");
      setTimeout(function(){w.classList.remove("np-sw--closing");closing=false;if(open())f.click();},CLOSE);
    }
    ["pointerdown","touchstart","keydown","click"].forEach(function(ev){w.addEventListener(ev,function(){if(open()&&!closing)arm();},true);});
    try{new MutationObserver(function(){if(open())arm();else clear();}).observe(w,{attributes:true,attributeFilter:["class"]});}catch(e){}
    var card=w.querySelector(".kp-wg");
    if(card)card.addEventListener("click",function(){if(open())collapse();});
  })();
  </script>
  </div>

<div id="searchSheet" class="bottom-sheet bottom-sheet--collapsed">
  <div class="bottom-sheet-header">
    <!-- (ลบวงแหวนฟองรอบลูกแก้วออกแล้ว — 24 ชิ้นเคลื่อนไหวทับแผนที่ตลอดเวลา
         ทำให้เลื่อนแผนที่หน่วงและเครื่องร้อน) -->
    <!-- แสงเรืองรอบนอก: ต้องอยู่ "นอก" ลูกแก้ว เพราะลูกแก้วมี overflow:hidden
         ถ้าวางเป็น ::after ข้างใน บน iOS Safari การคลิปจะไม่ทำงานแล้วโผล่เป็นเงาสี่เหลี่ยม -->
    <span class="kp-orb-glow" aria-hidden="true"></span>
    <div class="bottom-sheet-handle kp-orb" aria-label="ลากเพื่อสไลด์ขึ้นลง">
      <span class="kp-orb__aura" aria-hidden="true"></span>
      <span class="kp-orb__aura kp-orb__aura--2" aria-hidden="true"></span>
      <span class="kp-orb__aura kp-orb__aura--3" aria-hidden="true"></span>
      <span class="kp-orb__gloss" aria-hidden="true"></span>
      <!-- รูปพนักงานที่ล็อกอินอยู่ (ถ้ามี) — ไม่มีรูปก็เป็นลูกแก้วเหมือนเดิม (คำขอ 2026-09-27) -->
      <img id="staffOrbPhoto" class="kp-orb__photo" src="" alt="" style="display:none;">
    </div>
    <!-- ลบ .sheet-search-shell ออกทั้งก้อนตามคำขอ (ช่องค้นหาลูกค้า + ปุ่มเมนู/อวาตาร์ +
         ปุ่ม home/refresh) — สั่งลบ 2026-09-08: คนขับจะไม่มีปุ่มเมนูอื่นแทน (ยืนยันจากผู้ใช้แล้ว) -->
    <!-- ซ่อน <select class="selectdata"> ไว้ (มองไม่เห็น ไม่มี UI ให้กด) เพราะโค้ดหลายจุด
         (กดหมุดลูกค้าบนแผนที่ -> Index2SelectPersonById -> เติมค่า + เปิดฟอร์มขาย, ฯลฯ)
         ยังอ้างอิง .selectdata เป็นตัวกลาง trigger เหตุการณ์ select2:select อยู่ — ถ้าลบ element
         นี้ไปด้วย ปุ่ม "เปิด" บนหมุดแผนที่จะกดแล้วไม่เปิดหน้าขายให้ (บั๊กที่เจอ 2026-09-08 หลังลบช่องค้นหา) -->
    <div style="display:none;" aria-hidden="true">
      <select class="selectdata form-control"></select>
    </div>
  </div>
  <div class="bottom-sheet-body">
    <div class="container">

    <div id="sheetSearchHome" class="kp-sheet-home">
      <div class="kp-sheet-home__bubbles" aria-hidden="true">
        <?php
          $sheetBubbleSeeds = [
            [6,  16, 13.5, -3.0], [16, 24, 16.0, -8.0], [26, 10, 12.0, -5.5],
            [36, 28, 18.5, -1.5], [48, 14, 14.5, -10.0],[58, 22, 17.0, -6.5],
            [70, 12, 13.0, -12.5],[80, 26, 19.5, -2.5], [90, 18, 15.5, -9.5],
          ];
          foreach ($sheetBubbleSeeds as $b) {
            list($left, $size, $dur, $delay) = $b;
            $op = 0.40 + (($size % 20) / 38);
            echo '<span class="kp-sheet-home__bubble" style="left:' . $left . '%;width:' . $size . 'px;height:' . $size . 'px;animation-duration:' . $dur . 's;animation-delay:' . $delay . 's;opacity:' . $op . ';"></span>';
          }
        ?>
      </div>
      <div class="kp-sheet-home__inner">
        <div class="kp-sheet-home__mid">
          <div class="kp-sheet-home__item">
            <button type="button" id="sheetHomePlusBtn" class="kp-sheet-home__plus" aria-label="ขายเพิ่มเติม">
              <i class="fa fa-plus" aria-hidden="true"></i>
            </button>
            <div class="kp-sheet-home__plus-label">ขายเพิ่มเติม</div>
          </div>
          <?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']!==1){ ?>
          <!-- ส่งยอดเงินสดตอนเย็น (คำขอ 2026-10-02) — เฉพาะคนขับ อยู่หน้าเดียวกับปุ่มขายเพิ่มเติม -->
          <div class="kp-sheet-home__item">
            <button type="button" id="btnCashSubmit" class="kp-sheet-home__plus kp-sheet-home__cash" title="ส่งยอดเงินสด" aria-label="ส่งยอดเงินสด" data-toggle="modal" data-target="#cashSubmitModal">
              <span class="cs-baht" aria-hidden="true">฿</span>
            </button>
            <div class="kp-sheet-home__plus-label">ส่งยอด</div>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>

<?php
if($_SESSION['id_status']==1){
  echo"
<ul class='nav nav-tabs' id='myTab' role='tablist'>
  <li class='nav-item' id='index2MenuLi' role='presentation'>
    <a class='nav-link active' id='home-tab' data-toggle='tab' href='#home' role='tab' aria-controls='home' aria-selected='true' style='display:none;'></a>
  </li>
  <li class='nav-item' role='presentation'>
    <a class='nav-link' id='contact-tab' data-toggle='tab' href='#contact' role='tab' aria-controls='contact' aria-selected='false'><i class='fa fa-venus-double text-danger' aria-hidden='true'></i></a>
  </li>

</ul>
<div class='tab-content' id='myTabContent'>
  <div class='tab-pane fade show active' id='home' role='tabpanel' aria-labelledby='home-tab'> 
  ";
}
else{
  echo"
  <ul class='nav nav-tabs' id='myTab' role='tablist'>
  <li class='nav-item' id='index2MenuLi' role='presentation'>
    <a class='nav-link active' id='home-tab' data-toggle='tab' href='#home' role='tab' aria-controls='home' aria-selected='true' style='display:none;'></a>
  </li>
  <li class='nav-item' role='presentation'>
    <a class='nav-link' id='contact-tab' data-toggle='tab' href='#contact' role='tab' aria-controls='contact' aria-selected='false'><i class='fa fa-venus-double text-danger' aria-hidden='true'></i></a>
  </li>

</ul>
<div class='tab-content' id='myTabContent'>
  <div class='tab-pane fade show active' id='home' role='tabpanel' aria-labelledby='home-tab'> 
  ";
}
?>
    
    <!-- ซ่อนแถบหัวเดิม (คงไว้เพื่อรองรับสคริปต์เดิมบางส่วน) -->
    <div style="display:none;">
      <button type="button" class="btn btn-link p-0" id="btnBackTop" aria-label="ย้อนกลับ" style="visibility:hidden;">
        <i class="fa fa-arrow-left" aria-hidden="true"></i>
      </button>
      <span id="select_name_top"></span>
    </div>

 <form action="json/person_save.php" method="post" id="form-search">
 <input type="hidden" name="id_person" id="id_person_input" value="">
 <input type="hidden" name="name_person" id="name_person_input" value="">
 <input type="hidden" name="id_adress" id="id_adress_input" value="">
 <input type="hidden" name="name_adress" id="name_adress_input" value="">
 <input type="hidden" name="cr_box" id="cr_box_input" value="">
 <input type="hidden" name="cr_bigbox" id="cr_bigbox_input" value="">
 <input type="hidden" name="m_cr_box" id="m_cr_box_input" value="">
 <input type="hidden" name="m_cr_bigbox" id="m_cr_bigbox_input" value="">
 <input type="hidden" name="m_cr" id="m_cr_input" value="">
 <input type="hidden" name="bt_loss" id="bt_loss_input" value="">
 <input type="hidden" name="photo_path" id="photo_path_input" value="">
 <input type="hidden" name="tel_person" id="tel_person_input" value="">
 <!-- ราคาขายลัง/ถังเฉพาะการขายครั้งนี้ (ตั้งจากปุ่มดินสอ — json/sale_price_edit.js) ว่าง = ราคามาตรฐาน -->
 <input type="hidden" name="sale_price_for" id="sale_price_for_input" value="">
 <input type="hidden" name="sale_price_box" id="sale_price_box_input" value="">
 <input type="hidden" name="sale_price_bigbox" id="sale_price_bigbox_input" value="">
  <!-- UI ตามตัวอย่าง (เฉพาะใน sheet) -->
  <div class="kp-sale-ui">
    <div class="kp-sale-hero">
      <div class="kp-sale-hero__main">
        <!-- สลับจาก "ยอดคงค้าง" เป็น "ยอดชำระวันนี้" ตามคำขอ (2026-09-08)
             — ย้าย #kpOutstandingTotal ไปโชว์เป็นแถวเล็กในกล่องข้อมูลลูกค้าแทน (ต่อจากขวดค้าง) -->
        <div class="kp-sale-card__title">ยอดชำระวันนี้</div>
        <div class="kp-sale-card__value">
          <span id="todayPayAmountHero">0</span>
          <span class="kp-sale-card__unit">บาท</span>
        </div>
        <div id="kpSalePriceNote" class="kp-sale-pricenote" hidden></div>
        <!-- ปุ่มประวัติ อยู่ใต้บรรทัด "บาท" / แถวไอคอน (เครื่องคิดเลข + ดูรูป) อยู่ใต้ปุ่มประวัติ (คำขอ 2026-09-27) -->
        <div class="kp-sale-actions kp-sale-actions--stack">
          <button type="button" id="btnCustomerHistory" class="kp-sale-btn kp-sale-btn--ghost" disabled>ประวัติ</button>
        </div>
        <div class="kp-sale-iconrow">
          <button type="button" id="btnCalcTool" class="kp-icon-btn--bare kp-icon-btn--calc"
                  title="เครื่องคิดเลข" aria-label="เครื่องคิดเลข">
            <i class="fa fa-calculator" aria-hidden="true"></i>
          </button>
          <button type="button" id="btnSalePrice" class="kp-icon-btn--bare kp-icon-btn--edit" disabled
                  title="แก้ไขราคาขาย (ครั้งนี้)" aria-label="แก้ไขราคาขาย">
            <i class="fa fa-pencil" aria-hidden="true"></i>
          </button>
          <button type="button" id="btnCustomerPhoto" class="kp-icon-btn--bare" disabled title="ดูรูปภาพลูกค้า">
            <i class="fa fa-search-plus" aria-hidden="true"></i>
          </button>
        </div>
      </div>
      <div class="kp-sale-hero__cust">
        <div id="select_name" class="kp-sale-cust-name"></div>
        <div class="kp-sale-cust-meta">
          <div>รหัส : <b id="select_id"></b></div>
          <div>ที่อยู่ : <span id="select_adress"></span></div>
          <div>เช่า/ยืม : <span id="select_cr"></span></div>
          <div>ขวดค้าง : <span id="select_bt"></span></div>
          <!-- ไม่มียอดค้าง (0) ให้แสดงแค่ "-" ไม่ต้องโชว์เลข 0/หน่วย/รายละเอียด (คำขอ 2026-09-27) -->
          <div>ยอดคงค้าง : <span id="kpOutstandingTotal">-</span><span id="kpOutstandingUnit" style="display:none;"> บาท</span> <span id="select_crm" class="kp-sale-card__sub"></span></div>
        </div>
      </div>
    </div>

    <!-- ซ่อน input เดิม (คงไว้เพื่อ submit + คำนวณยอด) -->
    <input type="hidden" id="cal" value="">
    <input type="hidden" data-prefix="ขายลัง" value="0" min="0" step="1" name="sell_box" id="sellbox3">
    <input type="hidden" data-prefix="ขายถัง" value="0" min="0" step="1" name="sell_bigbox" id="sellbigbox3">
    <input type="hidden" data-prefix="คืนลัง" value="0" min="0" step="1" name="re_box" id="rebox3">
    <input type="hidden" data-prefix="คืนถัง" value="0" min="0" step="1" name="re_bigbox" id="rebigbox3">
    <input type="hidden" data-prefix="ขวดหาย" value="0" min="-100" step="1" name="bt_b" id="btloss3">

    <div class="kp-qty-card" aria-label="ขาย">
      <div class="kp-qty-row" data-target="#sellbox3">
        <button type="button" class="kp-qty-btn" data-delta="-1">−</button>
        <div class="kp-qty-label">ขายลัง</div>
        <div class="kp-qty-value" data-value-for="#sellbox3">0</div>
        <button type="button" class="kp-qty-btn" data-delta="1">+</button>
      </div>
      <div class="kp-qty-row" data-target="#sellbigbox3">
        <button type="button" class="kp-qty-btn" data-delta="-1">−</button>
        <div class="kp-qty-label">ขายถัง</div>
        <div class="kp-qty-value" data-value-for="#sellbigbox3">0</div>
        <button type="button" class="kp-qty-btn" data-delta="1">+</button>
      </div>
    </div>

    <div class="kp-qty-card" aria-label="คืน">
      <div class="kp-qty-row" data-target="#rebox3">
        <button type="button" class="kp-qty-btn" data-delta="-1">−</button>
        <div class="kp-qty-label">คืนลัง</div>
        <div class="kp-qty-value" data-value-for="#rebox3">0</div>
        <button type="button" class="kp-qty-btn" data-delta="1">+</button>
      </div>
      <div class="kp-qty-row" data-target="#rebigbox3">
        <button type="button" class="kp-qty-btn" data-delta="-1">−</button>
        <div class="kp-qty-label">คืนถัง</div>
        <div class="kp-qty-value" data-value-for="#rebigbox3">0</div>
        <button type="button" class="kp-qty-btn" data-delta="1">+</button>
      </div>
    </div>

    <div class="kp-qty-card" aria-label="ขวดหาย">
      <div class="kp-qty-row" data-target="#btloss3">
        <button type="button" class="kp-qty-btn" data-delta="-1">−</button>
        <div class="kp-qty-label">ขวดหาย</div>
        <div class="kp-qty-value" data-value-for="#btloss3">0</div>
        <button type="button" class="kp-qty-btn" data-delta="1">+</button>
      </div>
    </div>

    <div class="kp-payline">
      <select class="form-control" name="select_status">
      <option value="1">จ่ายทั้งหมด</option>
      <option value="2">จ่ายเก่า</option>
      <option value="3">จ่ายใหม่</option>
      <option value="4">จ่าย คงค้าง</option>
      <option value="0">ไม่จ่าย</option>
    </select>
      <!-- เดิมเป็น .input-group + กล่อง "บาท" แยกข้างๆ (ดูเป็นปุ่ม) — ตามคำขอ (2026-09-15)
           ลบกล่อง "บาท" แยกออก เอาคำว่า "บาท" ไปต่อท้ายตัวเลขในช่องเดียวกันแทน
           ช่องที่เห็น (new_m_cr_display) เป็นแค่ตัวโชว์ผล ไม่ส่งค่าไป backend
           ค่าจริงที่ส่งไปบันทึก/คำนวณยังอยู่ที่ id="new_m_cr" (hidden) เหมือนเดิมทุกอย่าง ไม่กระทบ logic เดิม -->
      <input type="text" class="form-control" id="new_m_cr_display" readonly placeholder="ระบุยอดคงค้าง">
      <input type="hidden" name="new_m_cr" id="new_m_cr" value="">
    </div>

    <!-- วิธีชำระ (วางก่อนปุ่มบันทึก) -->
    <div class="kp-paybar mt-2" aria-label="วิธีชำระเงิน">
    <div class="kp-paybar__label">วิธีชำระเงิน</div>
    <div class="kp-seg" role="radiogroup" aria-label="เลือกวิธีชำระเงิน">
      <label class="kp-seg__item">
        <input type="radio" name="pay_method" value="cash" checked>
        <span>เงินสด</span>
      </label>
      <label class="kp-seg__item">
        <input type="radio" name="pay_method" value="promptpay">
        <span>โอน/สแกน</span>
      </label>
    </div>
    </div>

    <!-- แถบบันทึกด้านล่าง (ตามตัวอย่าง) -->
    <!-- ลบป้าย "ยอดชำระวันนี้" + ไอคอนพิมพ์ใบเสร็จออกจากแถบล่างสุดตามคำขอ (2026-09-08)
         (ยอดชำระวันนี้ย้ายไปโชว์เด่นในกล่องแดงด้านบนแทนแล้ว)
         #todayPayAmount ยังต้องอยู่ใน DOM (ซ่อนสนิทไว้) เพราะ getTodayPayAmount()/ป็อปอัพ QR
         และตัวสร้างใบเสร็จ (print_bridge) ยังอ่านค่าจาก id นี้อยู่ — ถ้าลบไปด้วยยอดใน QR/ใบเสร็จจะหาย -->
    <div class="kp-savebar">
    <div class="kp-savebar__left">
      <span id="todayPayAmount" style="display:none;" aria-hidden="true">0</span>
      <div class="kp-savebar__btns" id="kpSaveBtnRow">
        <!-- ปุ่ม % ย้ายมาจากกล่องแดงด้านบนตามคำขอ (2026-09-09)
             ตรรกะ/อีเวนต์ทั้งหมดอยู่ใน json/index2_sale_sheet.js ผูกกับ id นี้อยู่แล้ว ไม่ต้องแก้ JS
             เพิ่มปุ่มโทรหาลูกค้า (kpCallCustomerBtn) เป็น span คั่นด้วยเส้นแบ่งอยู่ในปุ่มเดียวกันตามภาพตัวอย่าง (คำขอ 2026-09-09)
             ใช้ span+role=button แทน <button> ซ้อน <button> (HTML ไม่อนุญาต) แล้วกัน event ไม่ให้ไปเปิดป็อปอัพ % ทับซ้อนกันด้วย stopPropagation ใน JS
             ตัวเลขบนปุ่มเปลี่ยนจาก "% เทียบเดือนก่อน" เป็น "% ความเชื่อมั่นการจ่ายเงิน" ของลูกค้าคนนี้แทน
             (คำนวณจากประวัติซื้อขายจริงใน json/customer_payment_confidence.php) ส่วนกดปุ่มแล้วยังเด้งป็อปอัพ
             ยอดขาย 6 เดือนเหมือนเดิมทุกอย่าง ไม่เปลี่ยน (คำขอ 2026-09-14)
             ตัดป้ายคำอธิบาย "ความเชื่อมั่น" ใต้ตัวเลขออกแล้วตามคำขอ (2026-09-15) เหลือแค่ตัวเลข % -->
        <button type="button" id="kpQuick25Btn" class="kp-growth-btn">
          <span class="kp-growth-btn__ring" aria-hidden="true" style="--kp-ring-pct:0; --kp-ring-color:rgba(255,255,255,0.5);"></span>
          <span class="kp-growth-btn__text kp-growth-btn__text--nolabel">
            <span class="kp-growth-btn__pct kp-sale-btn__main">-%</span>
          </span>
          <span class="kp-growth-btn__divider" aria-hidden="true"></span>
          <span class="kp-growth-btn__call" id="kpCallCustomerBtn" role="button" tabindex="0" title="โทรหาลูกค้า" aria-label="โทรหาลูกค้า">
            <i class="fa fa-phone" aria-hidden="true"></i>
          </span>
        </button>
        <button type="submit" id="kpMainSaveBtn" class="kp-savebar__btn kp-savebar__btn--save">
          <span>บันทึก</span>
        </button>
      </div>
    </div>
    </div>
  </div><!-- /.kp-sale-ui -->
  </form>

  </div><!-- /#home (tab-pane) -->

  <!-- Modal: ระบุจำนวนเงินคงค้าง (จ่ายคงค้าง) -->
  <div class="modal fade" id="outstandingModal" tabindex="-1" role="dialog" aria-labelledby="outstandingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="outstandingLabel">ระบุจำนวนเงินคงค้าง</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-2">
            <label class="font-weight-bold mb-1">จำนวนเงินคงค้าง (บาท)</label>
            <input type="number" class="form-control" id="outstandingAmount" min="0" step="1" placeholder="เช่น 0, 150, 300">
            <small class="form-text text-muted">ระบบจะบันทึกยอด “คงค้างหลังจ่าย” ตามจำนวนที่ระบุ</small>
          </div>
          <div class="d-flex justify-content-end" style="gap:8px;">
            <button type="button" class="btn btn-light" id="outstandingCancel">ยกเลิก</button>
            <button type="button" class="btn btn-success" id="outstandingSave">บันทึก</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: ดูรูปภาพลูกค้า (จากช่องแนบรูปตอนเพิ่ม/แก้ไขรายชื่อลูกค้า) — คำขอ 2026-09-09 -->
  <div class="modal fade" id="customerPhotoModal" tabindex="-1" role="dialog" aria-labelledby="customerPhotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="customerPhotoLabel">รูปภาพลูกค้า</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
          <!-- สปินเนอร์กันเคสรูปโหลดไม่ทัน (ก่อนหน้านี้เห็นภาพไม่ชัด/แตก ต้องกดปุ่มซ้ำสองครั้ง)
               คำขอ 2026-09-09: โชว์ป็อปอัพทันที + สปินเนอร์ระหว่างโหลด แล้วค่อยสลับเป็นรูปตอนโหลดเสร็จ -->
          <div id="customerPhotoModalLoading" class="kp-photo-modal__loading" style="display:none;">
            <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
            <div>กำลังโหลดรูปภาพ...</div>
          </div>
          <!-- เคสยังไม่มีรูปเลย: โชว์แทนรูป เพื่อให้อัพโหลดรูปแรกจากป็อปอัพนี้ได้ (คำขอ 2026-09-09) -->
          <div id="customerPhotoModalEmpty" class="kp-photo-modal__empty" style="display:none;">
            <i class="fa fa-image" aria-hidden="true"></i>
            <div>ยังไม่มีรูปภาพ</div>
          </div>
          <img id="customerPhotoModalImg" src="" alt="รูปภาพลูกค้า" style="max-width:100%;max-height:70vh;border-radius:12px;">

          <!-- ปุ่มถ่ายรูป/อัพโหลด/บันทึก/ลบรูปภาพ ใต้ภาพ (คำขอ 2026-09-09, เพิ่มปุ่มถ่ายรูป 2026-09-16) -->
          <!-- 2 ช่อง: capture="environment" = เปิดกล้องหลังของมือถือเลย / อีกช่องคือเลือกไฟล์จากคลังรูป/เครื่อง -->
          <input type="file" id="customerPhotoModalFile" accept="image/*" style="display:none;">
          <input type="file" id="customerPhotoModalCamera" accept="image/*" capture="environment" style="display:none;">
          <div class="kp-photo-modal__actions">
            <div class="kp-photo-modal__actions-row">
              <!-- เปลี่ยนเป็นปุ่มไอคอนล้วน ไม่มีคำอธิบายข้อความ (คำขอ 2026-09-14) — เก็บ title/aria-label ไว้เพื่อ accessibility -->
              <button type="button" class="btn btn-outline-secondary kp-icon-only-btn" id="customerPhotoModalCameraBtn" title="ถ่ายรูป" aria-label="ถ่ายรูป">
                <i class="fa fa-camera" aria-hidden="true"></i>
              </button>
              <button type="button" class="btn btn-outline-secondary kp-icon-only-btn" id="customerPhotoModalUploadBtn" title="อัพโหลดรูป" aria-label="อัพโหลดรูป">
                <i class="fa fa-upload" aria-hidden="true"></i>
              </button>
              <button type="button" class="btn btn-success kp-icon-only-btn" id="customerPhotoModalSaveBtn" style="display:none;" title="บันทึก" aria-label="บันทึก">
                <i class="fa fa-check" aria-hidden="true"></i>
              </button>
              <button type="button" class="btn btn-outline-danger kp-icon-only-btn" id="customerPhotoModalDeleteBtn" style="display:none;" title="ลบรูปภาพ" aria-label="ลบรูปภาพ">
                <i class="fa fa-trash" aria-hidden="true"></i>
              </button>
            </div>
            <button type="button" class="btn btn-light" id="customerPhotoModalCloseBtn" data-dismiss="modal">
              <i class="fa fa-times" aria-hidden="true"></i> ปิด
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: ยืนยันการลบรูปภาพลูกค้า อีกชั้นหนึ่ง (คำขอ 2026-09-14)
       เดิมใช้ confirm() ของเบราว์เซอร์ ซึ่งกดผ่านง่ายเกินไปเพราะเป็นนิสัยกดยืนยันแบบไม่ทันคิด
       โดยเฉพาะหลังเปลี่ยนปุ่มลบเป็นไอคอนล้วน (ไม่มีคำอธิบาย) เสี่ยงกดพลาดโดนปุ่มลบมากขึ้น
       เพิ่ม popup แยกอีกชั้น ต้องกดปุ่ม "ลบรูปภาพ" ในนี้อีกทีถึงจะลบจริง -->
  <div class="modal fade" id="customerPhotoDeleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="customerPhotoDeleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="customerPhotoDeleteConfirmLabel">ยืนยันการลบรูปภาพ</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="kp-delete-confirm__text">
            ต้องการลบรูปภาพลูกค้ารายนี้ใช่ไหม? <b>ลบแล้วกู้คืนไม่ได้</b>
          </p>
          <div class="kp-delete-confirm__actions">
            <button type="button" class="btn btn-light" id="customerPhotoDeleteConfirmCancelBtn" data-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-danger" id="customerPhotoDeleteConfirmOkBtn">
              <i class="fa fa-trash" aria-hidden="true"></i> ลบรูปภาพ
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: รายละเอียดลูกค้า + โทร/จัดการเบอร์โทร (กดจากปุ่มโทรที่ปุ่ม % หน้าขาย) — คำขอ 2026-09-09
       แก้/ลบเบอร์โทร: admin เท่านั้น (ตามสิทธิ์เดียวกับเมนู "เพิ่มรายชื่อ") — ผู้ใช้ทั่วไปเห็นแค่ดูเบอร์+กดโทร -->
  <div class="modal fade" id="customerCallModal" tabindex="-1" role="dialog" aria-labelledby="customerCallLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="customerCallLabel">ข้อมูลลูกค้า</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="ccIdPerson" value="">
          <div class="kp-callinfo-row">
            <span class="kp-callinfo-k">ชื่อ</span>
            <span class="kp-callinfo-v" id="ccName">-</span>
          </div>
          <div class="kp-callinfo-row">
            <span class="kp-callinfo-k">ที่อยู่</span>
            <span class="kp-callinfo-v" id="ccAddress">-</span>
          </div>
          <!-- รองรับลูกค้ามีได้หลายเบอร์โทร (คำขอ 2026-09-14) — แสดงเป็นรายการ แต่ละเบอร์กดโทร/ลบแยกกันได้
               (เดิมมีได้เบอร์เดียว แก้/ลบ/เพิ่มในช่องเดียว) -->
          <div class="kp-callinfo-row kp-callinfo-row--tel">
            <span class="kp-callinfo-k">เบอร์โทร</span>
            <div class="kp-tel-list" id="ccTelList">
              <div class="kp-tel-list__empty" id="ccTelListEmpty" style="display:none;">ยังไม่มีเบอร์โทร</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-success" id="ccTelAddToggleBtn" style="display:none;">
              <i class="fa fa-plus" aria-hidden="true"></i> เพิ่มเบอร์โทร
            </button>
            <div class="kp-tel-add" id="ccTelAddRow" style="display:none;">
              <input type="tel" class="form-control" id="ccTelInput" placeholder="เบอร์โทร เช่น 081-234-5678" inputmode="tel" maxlength="30">
              <div class="kp-tel-add__btns">
                <button type="button" class="btn btn-sm btn-light" id="ccTelCancelAddBtn">ยกเลิก</button>
                <button type="button" class="btn btn-sm btn-success" id="ccTelSaveBtn">
                  <i class="fa fa-check" aria-hidden="true"></i> เพิ่มเบอร์
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light btn-block" data-dismiss="modal">ปิด</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: ยืนยันการลบเบอร์โทร อีกชั้นหนึ่ง (คำขอ 2026-09-14 — เหมือน popup ยืนยันลบรูปภาพ) -->
  <div class="modal fade" id="ccTelDeleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="ccTelDeleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="ccTelDeleteConfirmLabel">ยืนยันการลบเบอร์โทร</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="kp-delete-confirm__text">
            ต้องการลบเบอร์ <b id="ccTelDeleteConfirmNum">-</b> ใช่ไหม?
          </p>
          <div class="kp-delete-confirm__actions">
            <button type="button" class="btn btn-light" id="ccTelDeleteConfirmCancelBtn" data-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-danger" id="ccTelDeleteConfirmOkBtn">
              <i class="fa fa-trash" aria-hidden="true"></i> ลบเบอร์นี้
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: หลังกดปุ่มบันทึก ให้เลือกพิมพ์ใบเสร็จ หรือบันทึกอย่างเดียว
       ไม่กดอะไรภายใน 5 วิ -> บันทึกอัตโนมัติ + โชว์คำขอบคุณ (คำขอ 2026-09-08)
       data-backdrop=static + data-keyboard=false: ห้ามปิดโดยไม่เลือก ต้องกดปุ่มใดปุ่มหนึ่งหรือรอครบเวลา -->
  <div class="modal fade" id="kpSaveChoiceModal" tabindex="-1" role="dialog" aria-labelledby="kpSaveChoiceLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal kp-choice-modal">
        <div class="modal-header">
          <button type="button" class="close" id="kpSaveChoiceCancelBtn" aria-label="ปิด" title="ยกเลิก / กลับไปแก้ไข">
            <span aria-hidden="true">&times;</span>
          </button>
          <h5 class="modal-title" id="kpSaveChoiceLabel">บันทึกการขาย</h5>
          <div class="kp-choice-modal__sub">เลือกวิธีดำเนินการต่อ</div>
        </div>
        <div class="modal-body kp-choice-modal__body">
          <div class="kp-choice-row">
            <button type="button" id="kpSaveChoicePrintBtn" class="kp-choice-btn kp-choice-btn--print">
              <span class="kp-choice-btn__ic"><i class="fa fa-print" aria-hidden="true"></i></span>
              <span class="kp-choice-btn__label">พิมพ์ใบเสร็จ</span>
            </button>
            <button type="button" id="kpSaveChoiceSaveBtn" class="kp-choice-btn kp-choice-btn--save">
              <span class="kp-choice-btn__ic"><i class="fa fa-check" aria-hidden="true"></i></span>
              <span class="kp-choice-btn__label">บันทึก</span>
            </button>
          </div>
          <div class="kp-choice-countdown" id="kpSaveChoiceCountdown">
            ระบบจะบันทึกอัตโนมัติใน <b id="kpSaveChoiceCountdownNum">15</b> วินาที
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: หลังกดปุ่มบันทึกที่หน้าขายพิเศษ ให้เลือกพิมพ์บิล/ใบเสร็จ หรือบันทึกการขายอย่างเดียว
       เหมือนหน้าขายปกติ (โครงเดียวกับ #kpSaveChoiceModal) ไม่กดอะไรภายใน 5 วิ -> บันทึกอัตโนมัติ (คำขอ 2026-09-17)
       data-backdrop=static + data-keyboard=false: ห้ามปิดโดยไม่เลือก ต้องกดปุ่มใดปุ่มหนึ่งหรือรอครบเวลา -->
  <div class="modal fade" id="kpPlusSaveChoiceModal" tabindex="-1" role="dialog" aria-labelledby="kpPlusSaveChoiceLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal kp-choice-modal">
        <div class="modal-header">
          <button type="button" class="close" id="kpPlusSaveChoiceCancelBtn" aria-label="ปิด" title="ยกเลิก / กลับไปแก้ไข">
            <span aria-hidden="true">&times;</span>
          </button>
          <h5 class="modal-title" id="kpPlusSaveChoiceLabel">บันทึกการขายพิเศษ</h5>
          <div class="kp-choice-modal__sub">เลือกวิธีดำเนินการต่อ</div>
        </div>
        <div class="modal-body kp-choice-modal__body">
          <div class="kp-choice-row">
            <button type="button" id="kpPlusSaveChoicePrintBtn" class="kp-choice-btn kp-choice-btn--print">
              <span class="kp-choice-btn__ic"><i class="fa fa-print" aria-hidden="true"></i></span>
              <span class="kp-choice-btn__label">พิมพ์บิล/ใบเสร็จ</span>
            </button>
            <button type="button" id="kpPlusSaveChoiceSaveBtn" class="kp-choice-btn kp-choice-btn--save">
              <span class="kp-choice-btn__ic"><i class="fa fa-check" aria-hidden="true"></i></span>
              <span class="kp-choice-btn__label">บันทึกการขาย</span>
            </button>
          </div>
          <div class="kp-choice-countdown" id="kpPlusSaveChoiceCountdown">
            ระบบจะบันทึกอัตโนมัติใน <b id="kpPlusSaveChoiceCountdownNum">15</b> วินาที
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: พิมพ์ใบเสร็จ -->
  <div class="modal fade kp-receipt-modal" id="receiptPrintModal" tabindex="-1" role="dialog" aria-labelledby="receiptPrintLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="receiptPrintLabel">ตัวอย่างใบเสร็จ</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="kp-receipt-status">
            <div class="kp-receipt-status__label">สถานะ</div>
            <div class="kp-receipt-status__text" id="receiptPrintStatus" data-state="ready">พร้อมพิมพ์</div>
          </div>
          <button type="button" class="kp-receipt-device" id="receiptPickPrinterBtn">
            <span class="kp-receipt-device__ic"><i class="fa fa-bluetooth-b" aria-hidden="true"></i></span>
            <span class="kp-receipt-device__tx">
              เครื่องพิมพ์: <strong id="receiptPrinterName">ยังไม่ได้เลือกเครื่อง</strong>
              <small id="receiptPrinterHint">แตะเพื่อเลือก / เปลี่ยนเครื่องพิมพ์</small>
            </span>
            <i class="fa fa-chevron-right kp-receipt-device__ch" aria-hidden="true"></i>
          </button>
          <div style="margin:10px 0 8px 0;font-size:13px;font-weight:700;color:#111827;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" id="receiptShowQrToggle" />
              <span>แสดง QR code ในบิล</span>
            </label>
          </div>
          <div id="receiptPreviewBody"></div>
          <div class="kp-receipt-iconbtns">
            <button type="button" class="kp-receipt-iconbtn" id="receiptShareBtn" title="แชร์ หรือบันทึกรูปใบเสร็จลงเครื่อง (ไม่ได้บันทึกรายการขาย ต้องกดพิมพ์เพื่อบันทึกรายการขาย)" aria-label="แชร์ หรือบันทึกรูปใบเสร็จลงเครื่อง (ไม่ได้บันทึกรายการขาย ต้องกดพิมพ์เพื่อบันทึกรายการขาย)">
              <span class="kp-receipt-iconbtn__ic kp-receipt-iconbtn__ic--share"><i class="fa fa-share-alt" aria-hidden="true"></i></span>
              <span class="kp-receipt-iconbtn__tx">แชร์ / บันทึก</span>
            </button>
          </div>
          <div id="receiptPrintError" class="kp-receipt-error"></div>
          <div class="kp-receipt-actions">
            <button type="button" class="btn btn-light" data-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-outline-danger" id="receiptPrintRetryBtn" style="display:none;">พิมพ์ซ้ำ</button>
            <button type="button" class="btn btn-primary" id="receiptPrintConfirmBtn">พิมพ์</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: ประวัติลูกค้า -->
  <div class="modal fade" id="customerHistoryModal" tabindex="-1" role="dialog" aria-labelledby="customerHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <div class="kp-history-header-left">
            <h5 class="modal-title" id="customerHistoryLabel">ประวัติลูกค้า</h5>
            <!-- แถวปุ่มพิมพ์ อยู่บรรทัดใต้หัวข้อ (คำขอ 2026-09-21) -->
            <div class="kp-history-print-row">
            <!-- ปุ่มพิมพ์บิล/ใบเสร็จนอกเวลาขาย: แสดงเฉพาะยอดค้างล่าสุดของลูกค้า พิมพ์อย่างเดียว ไม่บันทึกรายการขายใหม่ (คำขอ 2026-09-15) -->
            <button type="button" class="kp-history-print-btn" id="btnHistoryPrintDueBill" title="พิมพ์บิล/ใบเสร็จ (แจ้งยอดค้างล่าสุด)">
              <i class="fa fa-print" aria-hidden="true"></i>
              <span>พิมพ์บิล/ใบเสร็จ</span>
            </button>
            <!-- พิมพ์ประวัติย่อ: รายการล่าสุด N รายการของลูกค้าคนนี้ ลงกระดาษ 58mm (คำขอ 2026-09-21) -->
            <button type="button" class="kp-history-print-btn" id="btnHistoryPrintSummary" title="พิมพ์ประวัติย่อ (รายการล่าสุด)">
              <i class="fa fa-list-ul" aria-hidden="true"></i>
              <span>พิมพ์ประวัติย่อ</span>
            </button>
            </div>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="customerHistoryMeta" class="kp-history-meta"></div>
          <div class="kp-history-toolbar">
            <div class="kp-history-toolbar__left">เรียง</div>
            <select id="historyOrder" class="form-control form-control-sm kp-history-order" style="width:auto;display:inline-block;">
              <option value="desc" selected>ปัจจุบัน ล่าสุด</option>
              <option value="asc">เก่าไปล่าสุด</option>
            </select>
            <div class="kp-history-toolbar__mid">แสดง</div>
            <select id="historyLimit" class="form-control form-control-sm kp-history-limit" style="width:auto;display:inline-block;">
              <option value="20">20</option>
              <option value="50" selected>50</option>
              <option value="100">100</option>
            </select>
            <div class="kp-history-toolbar__right">รายการ</div>
          </div>
          <div id="customerHistoryLoading" class="kp-history-loading" style="display:none;">กำลังโหลด...</div>
          <div id="customerHistoryList" class="kp-history-list"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: รายละเอียดรายการประวัติ (ครบถ้วน) -->
  <div class="modal fade" id="customerHistoryDetailModal" tabindex="-1" role="dialog" aria-labelledby="customerHistoryDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="customerHistoryDetailLabel">รายละเอียด</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="customerHistoryDetailBody"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: แก้ไข/ลบบิล จากประวัติลูกค้า (หน้าขาย) — คำขอ 2026-10-11
       แอดมินแก้ได้ทุกบิล · คนขับแก้ได้เฉพาะบิลที่ตัวเองบันทึกภายในวันนั้น (ตรวจที่ json/sell_crud.php) · ใช้ type=text เพราะ select.js ทำให้ number เป็นสปินเนอร์ -->
  <div class="modal fade" id="custBillEditModal" tabindex="-1" role="dialog" aria-labelledby="custBillEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="custBillEditLabel"><i class="fa fa-cog" aria-hidden="true"></i> แก้ไขบิล</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="cbe_id" value="">
          <div class="kp-sub" id="cbeMeta"></div>
          <div class="form-row mt-2">
            <div class="form-group col-6">
              <label class="kp-label2">ขาย (ลัง)</label>
              <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control" id="cbe_sell_box" autocomplete="off">
            </div>
            <div class="form-group col-6">
              <label class="kp-label2">ขาย (ถัง)</label>
              <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control" id="cbe_sell_bigbox" autocomplete="off">
            </div>
            <div class="form-group col-6">
              <label class="kp-label2">คืน (ลัง)</label>
              <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control" id="cbe_re_box" autocomplete="off">
            </div>
            <div class="form-group col-6">
              <label class="kp-label2">คืน (ถัง)</label>
              <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control" id="cbe_re_bigbox" autocomplete="off">
            </div>
            <div class="form-group col-6">
              <label class="kp-label2">ขวด (เพิ่ม/ลด)</label>
              <input type="text" class="form-control" id="cbe_bt_b" autocomplete="off">
            </div>
            <div class="form-group col-6">
              <label class="kp-label2">สถานะการจ่าย</label>
              <select class="form-control" id="cbe_check_status">
                <option value="1">จ่ายทั้งหมด</option>
                <option value="2">จ่ายเก่า</option>
                <option value="3">จ่ายใหม่</option>
                <option value="4">จ่ายคงค้าง</option>
                <option value="0">ไม่จ่าย</option>
              </select>
            </div>
            <div class="form-group col-6">
              <label class="kp-label2">วิธีชำระ</label>
              <select class="form-control" id="cbe_type_bill">
                <option value="">ไม่ระบุ</option>
                <option value="เงินสด">เงินสด</option>
                <option value="โอน">โอน</option>
              </select>
            </div>
          </div>
          <div class="form-group" id="cbeNewCrWrap" style="display:none;">
            <label class="kp-label2">ยอดคงค้างใหม่ (เฉพาะ “จ่ายคงค้าง”)</label>
            <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control" id="cbe_new_mcr" value="0" autocomplete="off">
          </div>
          <div class="small text-muted mt-1">* ยอดเงิน/ยอดคงค้างของลูกค้าจะถูกคำนวณใหม่ให้อัตโนมัติ · ทุกการแก้ไขมีบันทึกประวัติ (ใคร/เมื่อไหร่/ค่าเดิม→ค่าใหม่)</div>
          <div class="d-flex mt-3" style="gap:10px;">
            <button type="button" class="btn btn-outline-danger" id="cbeDeleteBtn"><i class="fa fa-trash" aria-hidden="true"></i> ลบบิล</button>
            <button type="button" class="btn btn-success flex-fill" id="cbeSaveBtn" style="font-weight:800;"><i class="fa fa-check"></i> บันทึก</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: ตัวอย่างใบเสร็จก่อนพิมพ์ (คำขอ 2026-09-17)
       ใช้ร่วมกันทั้งปุ่มพิมพ์ที่ "ประวัติการขาย" และ "ประวัติลูกค้า" (หน้าขาย) — บิลขายปกติ/ขายพิเศษ
       เป็นแค่ตัวอย่างข้อความ ไม่ใช่รูปขาว-ดำแบบที่เคยถูกลบออกไปแล้วตามคำขอ 2026-09-10 -->
  <!-- Modal: พิมพ์ประวัติย่อของลูกค้า — เลือกจำนวนรายการ + ดูตัวอย่างก่อนพิมพ์ (คำขอ 2026-09-21) -->
  <!-- kp-receipt-modal: ให้ปัดขึ้น-ลงในกรอบ modal ได้ (แก้เดียวกับหน้าตัวอย่างใบเสร็จ) — คำขอแก้ 2026-09-21 -->
  <!-- กล่องยืนยันกลาง ใช้ซ้ำได้ทุกที่ (คำขอ 2026-09-27) — แทน confirm/prompt ของเบราว์เซอร์
       เพราะหน้าตาไม่เข้ากับแอป และบน PWA บางเครื่องถูกบล็อก -->
  <div class="modal fade kp-receipt-modal" id="kpConfirmModal" tabindex="-1" role="dialog" aria-labelledby="kpConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="kpConfirmTitle">ยืนยัน</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="kpConfirmText" class="kp-confirm__text"></div>
          <div id="kpConfirmList" class="kp-confirm__list" style="display:none;"></div>
          <div id="kpConfirmInputBox" style="display:none;margin-top:10px;">
            <label id="kpConfirmInputLabel" style="font-size:12px;font-weight:800;color:rgba(17,24,39,0.6);margin:0 0 2px 2px;"></label>
            <input type="text" id="kpConfirmInput" class="form-control form-control-sm" maxlength="255">
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-light" id="kpConfirmCancel" data-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-primary" id="kpConfirmOk">ยืนยัน</button>
        </div>
      </div>
    </div>
  </div>

  <!-- จัดการพนักงาน (แอดมิน) — ออกแบบใหม่ 2026-09-27 (ปุ่มเป็นไอคอน / ตัวเลือกเป็น dropdown /
       ลงเวลาเป็นตารางติ๊กถูก / มีประวัติรายคน + ผูกกับระบบรายจ่าย) -->
  <div class="modal fade kp-receipt-modal" id="staffHrModal" tabindex="-1" role="dialog" aria-labelledby="staffHrLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <div style="display:flex;align-items:center;gap:8px;min-width:0;">
            <!-- ย้อนกลับไปเมนูหลัก (คำขอ 2026-09-27) -->
            <button type="button" class="kp-staff-gear-btn kp-back-btn" id="hrBackBtn" title="ย้อนกลับ" aria-label="ย้อนกลับ">
              <i class="fa fa-arrow-left" aria-hidden="true"></i>
            </button>
            <h5 class="modal-title" id="staffHrLabel">จัดการพนักงาน</h5>
            <!-- ปุ่มเพิ่มพนักงาน ต่อท้ายหัวข้อ (ย้ายจากแถบวันที่ คำขอ 2026-09-27) -->
            <button type="button" class="kp-icon-act kp-icon-act--ok kp-hr-addbtn" id="hrAddToggle" title="เพิ่มพนักงาน" aria-label="เพิ่มพนักงาน"><i class="fa fa-user-plus" aria-hidden="true"></i></button>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="kp-seg kp-hr-tabs" role="group" aria-label="แท็บจัดการพนักงาน">
            <button type="button" class="kp-seg__btn is-on" data-hrtab="staff" title="พนักงาน"><i class="fa fa-users" aria-hidden="true"></i> พนักงาน</button>
            <button type="button" class="kp-seg__btn" data-hrtab="his" title="ประวัติ"><i class="fa fa-history" aria-hidden="true"></i> ประวัติ</button>
          </div>

          <!-- ===== แท็บพนักงาน ===== -->
          <div class="kp-hr-pane" id="hrPaneStaff">
            <!-- แถบนำทางวัน (กึ่งกลาง) -->
            <div class="kp-hr-bar kp-hr-attbar">
              <div class="kp-hr-attbar__left">
                <span id="hrStaffMsg" class="kp-hr-msg"></span>
              </div>
              <div class="kp-hr-attbar__right">
                <button type="button" class="kp-icon-act" id="hrAttPrev" title="วันก่อนหน้า" aria-label="วันก่อนหน้า"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
                <!-- โชว์วันที่แบบไทยเต็ม "จันทร์ ที่ 2 กันยายน 2569" — ช่อง date จริงซ้อนโปร่งใสทับไว้ แตะแล้วเปิดปฏิทินของเครื่องเหมือนเดิม (คำขอ 2026-09-27) -->
                <label class="kp-hr-datebox" for="hrAttDate">
                  <span id="hrAttDateText" class="kp-hr-datebox__t">-</span>
                  <input type="date" id="hrAttDate" class="kp-hr-datebox__in" aria-label="วันที่ลงเวลา">
                </label>
                <button type="button" class="kp-icon-act" id="hrAttNext" title="วันถัดไป" aria-label="วันถัดไป"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
              </div>
            </div>
            <!-- ตัวกรอง ทำงานอยู่/ทั้งหมด/ลาออกแล้ว — แถวล่างวันที่ อยู่กึ่งกลางหน้า (คำขอ 2026-09-27) -->
            <div class="kp-hr-filterrow">
              <select id="hrStaffFilter" class="form-control form-control-sm kp-hr-filter-sel" style="width:auto;" aria-label="แสดงพนักงาน">
                <option value="active">ทำงานอยู่</option>
                <option value="all">ทั้งหมด</option>
                <option value="resigned">ลาออกแล้ว</option>
              </select>
            </div>


            <div id="hrStaffList" class="kp-hr-list"><div class="text-center text-muted py-3">กำลังโหลด...</div></div>
            <!-- ยอดเงินสดที่คนขับส่งของวันที่เลือก — แอดมินตรวจแล้วกดยืนยัน (คำขอ 2026-10-02) -->
            <div id="hrCashSubBox" hidden></div>
            <!-- เงินสดที่เก็บได้จริง ย้ายไปเป็นไอคอนในวิดเจ็ต (#btnCashActual / #cashActualModal) แล้ว (คำขอ 2026-10-04) -->
            <div class="kp-hr-savebar">
              <div class="kp-hr-savebar__sum">
                <div class="kp-hr-savebar__k">รวมค่าแรงวันนี้</div>
                <div><b id="hrAttTotal">0</b> บาท <span id="hrAttDirty" class="kp-hr-dirty"></span></div>
              </div>
              <button type="button" class="btn btn-success kp-btn-text" id="hrAttSave">บันทึกข้อมูล</button>
            </div>
          </div>

          <!-- ===== แท็บประวัติ: ค่าแรงรายวัน (ระบุช่วงวันได้) / สรุปรายเดือน · รายคน หรือ รวมทุกคน ===== -->
          <div class="kp-hr-pane" id="hrPaneHis" style="display:none;">
            <div class="kp-hr-bar kp-hr-fbar">
              <select id="hrHisMode" class="form-control form-control-sm" aria-label="รูปแบบ">
                <option value="day">รายวัน</option>
                <option value="month">รายเดือน</option>
              </select>
              <select id="hrHisQuick" class="form-control form-control-sm" aria-label="ช่วงเวลา">
                <option value="">ช่วงเวลา</option>
                <option value="today">วันนี้</option>
                <option value="yesterday">เมื่อวาน</option>
                <option value="7">7 วันล่าสุด</option>
                <option value="month">เดือนนี้</option>
                <option value="lastmonth">เดือนที่แล้ว</option>
              </select>
              <select id="hrHisMonths" class="form-control form-control-sm" aria-label="ย้อนหลัง" style="display:none;">
                <option value="3">3 เดือน</option>
                <option value="6" selected>6 เดือน</option>
                <option value="12">12 เดือน</option>
              </select>
            </div>
            <div class="kp-hr-bar kp-hr-fbar">
              <select id="hrHisUser" class="form-control form-control-sm" aria-label="พนักงาน"></select>
            </div>
            <!-- ช่วงวันที่แบบการ์ดนูน 2 ช่อง แสดงวันที่ภาษาไทย (คำขอ 2026-10-02) — ตัวเลือกวันที่จริงซ้อนโปร่งใสอยู่ด้านบน -->
            <div class="kp-hr-range" id="hrHisDayBar">
              <label class="kp-hr-range__box" for="hrHisFrom">
                <span class="kp-hr-range__ic"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" rx="4"/><path d="M3.5 10h17M8.5 3v4M15.5 3v4"/><circle cx="8.5" cy="14.5" r="1.1" class="d1"/><circle cx="12" cy="14.5" r="1.1"/><circle cx="15.5" cy="14.5" r="1.1"/></svg></span>
                <span class="kp-hr-range__txt"><span class="kp-hr-range__k">ตั้งแต่</span><span class="kp-hr-range__v" id="hrHisFromText">-</span></span>
                <input type="date" id="hrHisFrom" class="kp-hr-range__in" aria-label="ตั้งแต่วันที่">
              </label>
              <span class="kp-hr-range__arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
              <label class="kp-hr-range__box" for="hrHisTo">
                <span class="kp-hr-range__ic is-to"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" rx="4"/><path d="M3.5 10h17M8.5 3v4M15.5 3v4"/><circle cx="8.5" cy="14.5" r="1.1" class="d1"/><circle cx="12" cy="14.5" r="1.1"/><circle cx="15.5" cy="14.5" r="1.1"/></svg></span>
                <span class="kp-hr-range__txt"><span class="kp-hr-range__k">ถึง</span><span class="kp-hr-range__v" id="hrHisToText">-</span></span>
                <input type="date" id="hrHisTo" class="kp-hr-range__in" aria-label="ถึงวันที่">
              </label>
            </div>
            <span id="hrHisMsg" class="kp-hr-msg"></span>
            <div id="hrHisBox" class="kp-hr-list"><div class="text-center text-muted py-3">กำลังโหลด...</div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">ปิด</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ป็อปอัพเพิ่มพนักงาน (เปิดจากปุ่ม + บนหัวข้อ "จัดการพนักงาน") — คำขอ 2026-09-28 -->
  <div class="modal fade kp-receipt-modal" id="hrAddModal" tabindex="-1" role="dialog" aria-labelledby="hrAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="hrAddModalLabel"><i class="fa fa-user-plus" aria-hidden="true"></i> เพิ่มพนักงาน</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="kp-staff-form">
            <div class="kp-staff-field"><label>ชื่อ-สกุล <span class="kp-staff-req">*</span></label><input type="text" id="hrNewName" class="form-control form-control-sm" maxlength="80"><div class="kp-staff-err" id="hrErrName"></div></div>
            <div class="kp-staff-field"><label>สิทธิ์การใช้งาน</label>
              <select id="hrNewRole" class="form-control form-control-sm">
                <option value="2">พนักงาน</option>
                <option value="1">แอดมิน</option>
              </select>
            </div>
            <div class="kp-staff-field"><label>ชื่อผู้ใช้ (เข้าแอป) <small>ไม่บังคับ</small></label><input type="text" id="hrNewUser" class="form-control form-control-sm" maxlength="60" autocomplete="off" spellcheck="false" placeholder="เว้นว่าง = สร้างรหัสให้เอง"><div class="kp-staff-err" id="hrErrUser"></div></div>
            <div class="kp-staff-field"><label>รหัสผ่าน <small>ไม่บังคับ</small></label><input type="text" id="hrNewPass" class="form-control form-control-sm" maxlength="60" autocomplete="new-password" placeholder="เว้นว่าง = ไม่เข้าแอป"><div class="kp-staff-err" id="hrErrPass"></div></div>
            <div class="kp-staff-field"><label>วันที่เริ่มงาน</label><input type="date" id="hrNewStart" class="form-control form-control-sm"></div>
            <div class="kp-staff-field"><label>รูปแบบค่าจ้าง</label>
              <select id="hrNewType" class="form-control form-control-sm">
                <option value="daily">รายวัน</option>
                <option value="monthly">รายเดือน</option>
              </select>
            </div>
            <div class="kp-staff-field"><label>ค่าจ้าง (บาท) <small id="hrNewRateHint">ต่อวัน</small></label><input type="number" id="hrNewRate" class="form-control form-control-sm" min="0" step="1"></div>
            <div class="kp-staff-field">
              <label class="kp-hr-check"><input type="checkbox" id="hrNewHasOt"> มีค่า OT</label>
              <input type="number" id="hrNewOt" class="form-control form-control-sm" min="0" step="1" placeholder="บาท/ชั่วโมง" style="display:none;">
            </div>
          </div>
          <div id="hrAddMsg" class="kp-hr-msg" style="margin-top:8px;"></div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-light" id="hrAddCancel" data-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-success" id="hrAddSave"><i class="fa fa-save" aria-hidden="true"></i> บันทึกพนักงานใหม่</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ป็อปอัพตั้งค่าค่าจ้างรายคน (เปิดจากปุ่มฟันเฟืองบนการ์ดพนักงาน) — คำขอ 2026-09-28 -->
  <div class="modal fade kp-receipt-modal" id="hrPayModal" tabindex="-1" role="dialog" aria-labelledby="hrPayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="hrPayModalLabel"><i class="fa fa-cog" aria-hidden="true"></i> ตั้งค่าค่าจ้าง</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="kp-hr-paywho">
            <div class="kp-hr-ava" id="hrPayAva"><i class="fa fa-user" aria-hidden="true"></i></div>
            <div style="min-width:0;">
              <div class="kp-hr-paywho__name" id="hrPayName">-</div>
              <div class="kp-hr-paywho__user" id="hrPayUser">-</div>
            </div>
          </div>
          <div class="kp-staff-form">
            <div class="kp-staff-field"><label>รูปแบบค่าจ้าง</label>
              <select id="hrPayType" class="form-control form-control-sm">
                <option value="daily">รายวัน</option>
                <option value="monthly">รายเดือน</option>
              </select>
            </div>
            <div class="kp-staff-field"><label>ค่าจ้าง (บาท) <small id="hrPayRateHint">ต่อวัน</small></label><input type="number" id="hrPayRate" class="form-control form-control-sm" min="0" step="1" inputmode="numeric"></div>
            <div class="kp-staff-field kp-staff-field--full">
              <label class="kp-hr-check"><input type="checkbox" id="hrPayHasOt"> มีค่า OT</label>
              <input type="number" id="hrPayOt" class="form-control form-control-sm" min="0" step="1" inputmode="numeric" placeholder="บาท/ชั่วโมง" style="display:none;">
            </div>
          </div>
          <div id="hrPayMsg" class="kp-hr-msg" style="margin-top:8px;"></div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-light" data-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-primary" id="hrPaySave"><i class="fa fa-save" aria-hidden="true"></i> บันทึก</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ข้อมูลพนักงาน (คำขอ 2026-09-27)
       เปิดมาเป็นหน้า "ดูข้อมูล" ก่อน ต้องกดปุ่มฟันเฟืองถึงจะเข้าโหมดแก้ไข
       ปุ่มฟันเฟืองบนรูป = เลือกจากแกลเลอรี / ถ่ายรูป / ลบรูป -->
  <div class="modal fade kp-receipt-modal" id="staffPhotoModal" tabindex="-1" role="dialog" aria-labelledby="staffPhotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <div style="display:flex;align-items:center;gap:8px;min-width:0;">
            <!-- ย้อนกลับไปหน้าก่อนหน้า (จัดการพนักงาน) — โชว์เฉพาะตอนเปิดมาจากหน้านั้น -->
            <button type="button" class="kp-staff-gear-btn kp-back-btn" id="staffBackBtn" title="ย้อนกลับ" aria-label="ย้อนกลับ" style="display:none;">
              <i class="fa fa-arrow-left" aria-hidden="true"></i>
            </button>
            <h5 class="modal-title" id="staffPhotoLabel">ข้อมูลพนักงาน</h5>
          </div>
          <div style="display:flex;align-items:center;gap:6px;">
            <button type="button" class="kp-staff-gear-btn" id="staffEditToggle" title="แก้ไขข้อมูล" aria-label="แก้ไขข้อมูล" style="display:none;">
              <i class="fa fa-cog" aria-hidden="true"></i>
            </button>
            <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
        <div class="modal-body">
          <!-- แอดมินเลือกดู/แก้ข้อมูลพนักงานคนอื่นได้ (แถวนี้ซ่อนไว้สำหรับพนักงานทั่วไป) -->
          <div id="staffPhotoPickRow" style="display:none;margin-bottom:10px;">
            <label style="font-size:13px;font-weight:800;color:#111827;margin:0 0 4px 0;">เลือกพนักงาน</label>
            <select id="staffPhotoUser" class="form-control form-control-sm"></select>
          </div>

          <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
            <div class="kp-staff-avatar">
              <div id="staffPhotoPreview" class="kp-staff-photo-preview">
                <i class="fa fa-user" aria-hidden="true"></i>
              </div>
              <!-- ฟันเฟืองบนรูป: เปิดเมนูจัดการรูป (โชว์เฉพาะคนที่มีสิทธิ์แก้) -->
              <button type="button" class="kp-staff-avatar__gear" id="staffPhotoGear" title="จัดการรูป" aria-label="จัดการรูป" style="display:none;">
                <i class="fa fa-cog" aria-hidden="true"></i>
              </button>
              <div class="kp-staff-photo-menu" id="staffPhotoMenu" style="display:none;">
                <button type="button" data-act="gallery"><i class="fa fa-picture-o" aria-hidden="true"></i> เลือกจากแกลเลอรี</button>
                <button type="button" data-act="camera"><i class="fa fa-camera" aria-hidden="true"></i> ถ่ายรูป</button>
                <button type="button" data-act="delete" class="is-danger"><i class="fa fa-trash" aria-hidden="true"></i> ลบรูป</button>
              </div>
            </div>
            <div id="staffPhotoName" style="font-weight:1000;color:#111827;font-size:17px;"></div>
            <div id="staffPhotoMsg" style="font-size:12px;font-weight:800;color:rgba(17,24,39,0.6);text-align:center;"></div>
          </div>

          <!-- ช่องเลือกไฟล์ 2 ตัว: จากแกลเลอรี กับ ถ่ายรูปด้วยกล้องหลัง -->
          <input type="file" id="staffPhotoFile" accept="image/*" style="display:none;">
          <input type="file" id="staffPhotoCam" accept="image/*" capture="environment" style="display:none;">

          <!-- โหมดดูข้อมูล -->
          <div id="staffViewBox" class="kp-staff-view">
            <div class="kp-staff-vrow"><span class="kp-staff-vk">ชื่อ-สกุล</span><span class="kp-staff-vv" id="svName">-</span></div>
            <div class="kp-staff-vrow"><span class="kp-staff-vk">เพศ</span><span class="kp-staff-vv" id="svGender">-</span></div>
            <div class="kp-staff-vrow"><span class="kp-staff-vk">เลขบัตรประชาชน</span><span class="kp-staff-vv" id="svNationalId">-</span></div>
            <div class="kp-staff-vrow"><span class="kp-staff-vk">ชื่อเล่น</span><span class="kp-staff-vv" id="svNickname">-</span></div>
            <div class="kp-staff-vrow"><span class="kp-staff-vk">เบอร์โทร</span><span class="kp-staff-vv" id="svTel">-</span></div>
            <div class="kp-staff-vrow"><span class="kp-staff-vk">ที่อยู่</span><span class="kp-staff-vv" id="svAddress">-</span></div>
            <div class="kp-staff-vrow"><span class="kp-staff-vk">พิกัด</span><span class="kp-staff-vv" id="svGeo">-</span></div>
            <div class="kp-staff-vrow kp-staff-vrow--block"><span class="kp-staff-vk">ประวัติการทำงาน</span><span class="kp-staff-vv" id="svWorkHistory">-</span></div>
          </div>

          <!-- โหมดแก้ไข (เปิดด้วยปุ่มฟันเฟือง) -->
          <div id="staffEditBox" class="kp-staff-form" style="display:none;">
            <div class="kp-staff-field"><label>ชื่อ-สกุล <span class="kp-staff-req">*</span></label><input type="text" id="spName" class="form-control form-control-sm" maxlength="80"><div class="kp-staff-err" id="errName"></div></div>
            <!-- เพศ: ปุ่มเลือกแบบแถบ (สวยกว่า dropdown ของระบบ และแตะง่ายบนมือถือ) — คำขอ 2026-09-27
                 ค่าที่เลือกเก็บใน input ซ่อน #spGender เหมือนเดิม โค้ดส่วนบันทึกจึงไม่ต้องแก้ -->
            <div class="kp-staff-field"><label>เพศ</label>
              <div class="kp-seg" id="spGenderSeg" role="group" aria-label="เพศ">
                <button type="button" class="kp-seg__btn" data-v="ชาย"><i class="fa fa-mars" aria-hidden="true"></i> ชาย</button>
                <button type="button" class="kp-seg__btn" data-v="หญิง"><i class="fa fa-venus" aria-hidden="true"></i> หญิง</button>
                <button type="button" class="kp-seg__btn" data-v="อื่น ๆ">อื่น ๆ</button>
              </div>
              <input type="hidden" id="spGender" value="">
              <div class="kp-staff-err" id="errGender"></div>
            </div>
            <div class="kp-staff-field"><label>เลขบัตรประชาชน</label><input type="text" id="spNationalId" class="form-control form-control-sm" inputmode="numeric" maxlength="20" placeholder="13 หลัก"><div class="kp-staff-err" id="errNationalId"></div></div>
            <div class="kp-staff-field"><label>ชื่อเล่น</label><input type="text" id="spNickname" class="form-control form-control-sm" maxlength="60"><div class="kp-staff-err" id="errNickname"></div></div>
            <div class="kp-staff-field"><label>เบอร์โทร</label><input type="tel" id="spTel" class="form-control form-control-sm" inputmode="tel" maxlength="40" placeholder="เช่น 081-234-5678"><div class="kp-staff-err" id="errTel"></div></div>
            <div class="kp-staff-field kp-staff-field--full">
              <label>ที่อยู่</label>
              <input type="text" id="spAddress" class="form-control form-control-sm" maxlength="255">
              <div class="kp-staff-err" id="errAddress"></div>
              <div class="kp-staff-geo">
                <input type="text" id="spLat" class="form-control form-control-sm" placeholder="ละติจูด" inputmode="decimal">
                <input type="text" id="spLng" class="form-control form-control-sm" placeholder="ลองจิจูด" inputmode="decimal">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSpPinLocation">
                  <i class="fa fa-map-marker" aria-hidden="true"></i> ปักหมุดที่อยู่
                </button>
              </div>
              <div class="kp-staff-err" id="errGeo"></div>
            </div>
            <div class="kp-staff-field kp-staff-field--full"><label>ประวัติการทำงาน</label><textarea id="spWorkHistory" class="form-control form-control-sm" rows="4" maxlength="2000" placeholder="เช่น เริ่มงาน 1 ม.ค. 2566 ตำแหน่งคนขับรถส่งน้ำ"></textarea><div class="kp-staff-err" id="errWorkHistory"></div></div>
            <!-- ────── ส่วนตั้งค่าบัญชี user/pass (เฉพาะแอดมิน) ────── -->
            <div class="kp-staff-field--full kp-staff-acct" id="spAcctSection" style="display:none;">
              <div class="kp-staff-sep"><i class="fa fa-key" aria-hidden="true"></i> บัญชีผู้ใช้</div>
              <div class="kp-staff-field">
                <label>ชื่อผู้ใช้ (เข้าแอป) <small>เปลี่ยนได้</small></label>
                <input type="text" id="spAcctUser" class="form-control form-control-sm" maxlength="60" autocomplete="off" spellcheck="false" placeholder="a-z 0-9 _ . -">
                <div class="kp-staff-err" id="errAcctUser"></div>
              </div>
              <div class="kp-staff-field"><label>รหัสผ่านใหม่ <small>(เว้นว่างถ้าไม่เปลี่ยน)</small></label><input type="password" id="spAcctPass" class="form-control form-control-sm" maxlength="60" autocomplete="new-password" placeholder="อย่างน้อย 4 ตัวอักษร"><div class="kp-staff-err" id="errAcctPass"></div></div>
              <div class="kp-staff-field"><label>ยืนยันรหัสผ่านใหม่</label><input type="password" id="spAcctPassConf" class="form-control form-control-sm" maxlength="60" autocomplete="new-password" placeholder="กรอกซ้ำอีกครั้ง"><div class="kp-staff-err" id="errAcctPassConf"></div></div>
            </div>
          </div>
          <div id="staffSaveMsg" style="margin-top:8px;font-size:12px;font-weight:800;color:rgba(17,24,39,0.6);"></div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-light" id="staffCancelBtn" data-dismiss="modal">ปิด</button>
          <button type="button" class="btn btn-primary" id="staffSaveBtn" style="display:none;">
            <i class="fa fa-save" aria-hidden="true"></i> บันทึกข้อมูล
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ป็อปอัพปักหมุดที่อยู่ (แผนที่ใหม่ แยกจากแผนที่หลักของหน้า) — คำขอ 2026-09-27 -->
  <div class="modal fade kp-receipt-modal" id="staffPinMapModal" tabindex="-1" role="dialog" aria-labelledby="staffPinMapLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="staffPinMapLabel">ปักหมุดที่อยู่</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body p-0">
          <div class="kp-pinmap-wrap">
            <div id="staffPinMapCanvas" class="kp-pinmap-canvas"></div>
            <div class="kp-pinmap-pin" aria-hidden="true"><i class="fa fa-map-marker" aria-hidden="true"></i></div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-outline-secondary" id="btnStaffPinFocus">
            <i class="fa fa-crosshairs" aria-hidden="true"></i> โฟกัส
          </button>
          <div style="display:flex;gap:8px;">
            <button type="button" class="btn btn-light" id="btnStaffPinCancel" data-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary" id="btnStaffPinSave">
              <i class="fa fa-check" aria-hidden="true"></i> บันทึก
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- เครื่องคิดเลขช่วยคิดเงินหน้างาน (คำขอ 2026-09-27) — คิดเลขอย่างเดียว ไม่ยุ่งกับยอดขาย/ยอดค้าง -->
  <div class="modal fade kp-receipt-modal" id="calcModal" tabindex="-1" role="dialog" aria-labelledby="calcModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="calcModalLabel">เครื่องคิดเลข</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="kp-calc-screen">
            <div class="kp-calc-expr" id="calcExpr">&nbsp;</div>
            <div class="kp-calc-out" id="calcOut">0</div>
          </div>
          <div class="kp-calc-pad">
            <button type="button" class="kp-calc-btn kp-calc-btn--fn" data-k="C">C</button>
            <button type="button" class="kp-calc-btn kp-calc-btn--fn" data-k="back">&#9003;</button>
            <button type="button" class="kp-calc-btn kp-calc-btn--fn" data-k="%">%</button>
            <button type="button" class="kp-calc-btn kp-calc-btn--op" data-k="/">&divide;</button>
            <button type="button" class="kp-calc-btn " data-k="7">7</button>
            <button type="button" class="kp-calc-btn " data-k="8">8</button>
            <button type="button" class="kp-calc-btn " data-k="9">9</button>
            <button type="button" class="kp-calc-btn kp-calc-btn--op" data-k="*">&times;</button>
            <button type="button" class="kp-calc-btn " data-k="4">4</button>
            <button type="button" class="kp-calc-btn " data-k="5">5</button>
            <button type="button" class="kp-calc-btn " data-k="6">6</button>
            <button type="button" class="kp-calc-btn kp-calc-btn--op" data-k="-">&minus;</button>
            <button type="button" class="kp-calc-btn " data-k="1">1</button>
            <button type="button" class="kp-calc-btn " data-k="2">2</button>
            <button type="button" class="kp-calc-btn " data-k="3">3</button>
            <button type="button" class="kp-calc-btn kp-calc-btn--op" data-k="+">+</button>
            <button type="button" class="kp-calc-btn kp-calc-btn--wide" data-k="0">0</button>
            <button type="button" class="kp-calc-btn " data-k=".">.</button>
            <button type="button" class="kp-calc-btn kp-calc-btn--eq" data-k="=">=</button>
            </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade kp-receipt-modal" id="historySummaryModal" tabindex="-1" role="dialog" aria-labelledby="historySummaryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="historySummaryLabel">พิมพ์ประวัติย่อ</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- แถบเลือก/เปลี่ยนเครื่องพิมพ์ ชุดเดียวกับหน้าตัวอย่างใบเสร็จอื่น ๆ (คำขอ 2026-09-21) -->
          <button type="button" class="kp-receipt-device" id="historySummaryPickPrinterBtn">
            <span class="kp-receipt-device__ic"><i class="fa fa-bluetooth-b" aria-hidden="true"></i></span>
            <span class="kp-receipt-device__tx">
              เครื่องพิมพ์: <strong id="historySummaryPrinterName">ยังไม่ได้เลือกเครื่อง</strong>
              <small id="historySummaryPrinterHint">แตะเพื่อเลือก / เปลี่ยนเครื่องพิมพ์</small>
            </span>
            <i class="fa fa-chevron-right kp-receipt-device__ch" aria-hidden="true"></i>
          </button>
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
            <label style="font-size:13px;font-weight:700;color:#111827;margin:0;">ย้อนหลัง</label>
            <input type="number" id="historySummaryCount" class="form-control form-control-sm"
                   min="1" max="100" step="1" value="10" style="width:90px;">
            <label style="font-size:13px;font-weight:700;color:#111827;margin:0;">รายการล่าสุด</label>
            <div style="display:flex;gap:6px;margin-left:auto;">
              <button type="button" class="btn btn-sm btn-light kp-hsum-preset" data-n="5">5</button>
              <button type="button" class="btn btn-sm btn-light kp-hsum-preset" data-n="10">10</button>
              <button type="button" class="btn btn-sm btn-light kp-hsum-preset" data-n="20">20</button>
            </div>
          </div>
          <div id="historySummaryPreview"
               style="background:#fff;border:1px solid rgba(17,24,39,.12);border-radius:8px;padding:10px 12px;margin-bottom:10px;">
            <div class="text-center text-muted py-3">กำลังโหลด...</div>
          </div>
          <!-- ปุ่มบันทึกรูป/แชร์ ชุดเดียวกับหน้าตัวอย่างใบเสร็จอื่น ๆ (คำขอ 2026-09-21) -->
          <div class="kp-receipt-iconbtns">
            <button type="button" class="kp-receipt-iconbtn" id="historySummaryShareBtn" title="แชร์ หรือบันทึกรูปสลิปลงเครื่อง" aria-label="แชร์ หรือบันทึกรูปสลิปลงเครื่อง">
              <span class="kp-receipt-iconbtn__ic kp-receipt-iconbtn__ic--share"><i class="fa fa-share-alt" aria-hidden="true"></i></span>
              <span class="kp-receipt-iconbtn__tx">แชร์ / บันทึก</span>
            </button>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-light" data-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-success" id="historySummaryPrintBtn">
            <i class="fa fa-print" aria-hidden="true"></i> พิมพ์
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="historyPrintPreviewModal" tabindex="-1" role="dialog" aria-labelledby="historyPrintPreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="historyPrintPreviewLabel">ตัวอย่างใบเสร็จ</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- แถบเลือก/เปลี่ยนเครื่องพิมพ์ Bluetooth เหมือนหน้าขายปกติ/ขายพิเศษ
               (เดิมหน้านี้ไม่มีปุ่มค้นหา/เลือกเครื่องพิมพ์ ต้องไปจับคู่ที่หน้าขายก่อนเท่านั้น — คำขอ 2026-09-17) -->
          <button type="button" class="kp-receipt-device" id="historyPrintPickPrinterBtn">
            <span class="kp-receipt-device__ic"><i class="fa fa-bluetooth-b" aria-hidden="true"></i></span>
            <span class="kp-receipt-device__tx">
              เครื่องพิมพ์: <strong id="historyPrintPrinterName">ยังไม่ได้เลือกเครื่อง</strong>
              <small id="historyPrintPrinterHint">แตะเพื่อเลือก / เปลี่ยนเครื่องพิมพ์</small>
            </span>
            <i class="fa fa-chevron-right kp-receipt-device__ch" aria-hidden="true"></i>
          </button>
          <div style="margin:0 0 8px 0;font-size:13px;font-weight:700;color:#111827;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" id="historyPrintShowQrToggle" />
              <span>แสดง QR code ในบิล</span>
            </label>
          </div>
          <div id="historyPrintPreviewBody"></div>
          <div class="kp-receipt-iconbtns">
            <button type="button" class="kp-receipt-iconbtn" id="historyPrintShareBtn" title="แชร์ หรือบันทึกรูปใบเสร็จลงเครื่อง" aria-label="แชร์ หรือบันทึกรูปใบเสร็จลงเครื่อง">
              <span class="kp-receipt-iconbtn__ic kp-receipt-iconbtn__ic--share"><i class="fa fa-share-alt" aria-hidden="true"></i></span>
              <span class="kp-receipt-iconbtn__tx">แชร์ / บันทึก</span>
            </button>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-light" data-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-success" id="historyPrintPreviewConfirmBtn">
            <i class="fa fa-print" aria-hidden="true"></i> พิมพ์
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: รายละเอียดรายการซื้อขาย (จากหน้า "ประวัติการซื้อขาย")
       หมายเหตุ: แก้ไข/ลบ ทำเป็น "สลับส่วนภายใน modal เดียวกัน" (ไม่เปิด modal ใหม่ซ้อน)
       เพื่อกันบั๊ก iOS Safari ที่บางเครื่องเปิด modal ที่สองซ้อนแล้วจอมัว/ต้องแตะซ้ำถึงจะชัด -->
  <div class="modal fade" id="sellHistoryDetailModal" tabindex="-1" role="dialog" aria-labelledby="sellHistoryDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="sellHistoryDetailLabel">รายละเอียด</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- ส่วนที่ 1: มุมมองรายละเอียด (ปกติ) -->
          <div id="sellHistoryDetailView">
            <div id="sellHistoryDetailBody"></div>
          </div>

          <!-- ส่วนที่ 2: ฟอร์มแก้ไขรายการขายปกติ (tb_sell) -->
          <div id="sellHistEditSection" style="display:none;">
            <input type="hidden" id="sh_id" value="">
            <div class="kp-sub" id="shEditMeta"></div>

            <div class="form-row mt-2">
              <div class="form-group col-6">
                <label class="kp-label2">ขาย (ลัง)</label>
                <input type="number" class="form-control" id="sh_sell_box" min="0" step="1">
              </div>
              <div class="form-group col-6">
                <label class="kp-label2">ขาย (ถัง)</label>
                <input type="number" class="form-control" id="sh_sell_bigbox" min="0" step="1">
              </div>
              <div class="form-group col-6">
                <label class="kp-label2">คืน (ลัง)</label>
                <input type="number" class="form-control" id="sh_re_box" min="0" step="1">
              </div>
              <div class="form-group col-6">
                <label class="kp-label2">คืน (ถัง)</label>
                <input type="number" class="form-control" id="sh_re_bigbox" min="0" step="1">
              </div>
              <div class="form-group col-6">
                <label class="kp-label2">ขวด (เพิ่ม/ลด)</label>
                <input type="number" class="form-control" id="sh_bt_b" step="1">
              </div>
              <div class="form-group col-6">
                <label class="kp-label2">สถานะการจ่าย</label>
                <select class="form-control" id="sh_check_status">
                  <option value="1">จ่ายทั้งหมด</option>
                  <option value="2">จ่ายเก่า</option>
                  <option value="3">จ่ายใหม่</option>
                  <option value="4">จ่ายคงค้าง</option>
                  <option value="0">ไม่จ่าย</option>
                </select>
              </div>
              <div class="form-group col-6">
                <label class="kp-label2">วิธีชำระ</label>
                <select class="form-control" id="sh_type_bill">
                  <option value="">ไม่ระบุ</option>
                  <option value="เงินสด">เงินสด</option>
                  <option value="โอน">โอน</option>
                </select>
              </div>
            </div>

            <div class="form-group" id="shNewCrWrap" style="display:none;">
              <label class="kp-label2">ยอดคงค้างใหม่ (เฉพาะ “จ่ายคงค้าง”)</label>
              <input type="number" class="form-control" id="sh_new_mcr" min="0" step="1" value="0">
            </div>
            <div class="small text-muted mt-2">* ยอดคงค้างของลูกค้าจะถูกคำนวณใหม่ให้อัตโนมัติหลังบันทึก</div>
          </div>

          <!-- ส่วนที่ 3: ฟอร์มแก้ไขบิลขายพิเศษ (tb_plus) -->
          <div id="sellHistPlusEditSection" style="display:none;">
            <input type="hidden" id="shp_no_bill" value="">
            <div class="form-row">
              <div class="form-group col-6">
                <label class="kp-label2">ชื่อลูกค้า</label>
                <input type="text" class="form-control" id="shp_name_person">
              </div>
              <div class="form-group col-6">
                <label class="kp-label2">หมู่บ้าน</label>
                <input type="text" class="form-control" id="shp_name_adress">
              </div>
            </div>
            <div class="small text-muted mb-2">รายการสินค้า</div>
            <div id="shp_items"></div>
          </div>
        </div>

        <!-- ฟุตเตอร์: มุมมองรายละเอียด
             หมายเหตุ: ห้ามใส่ class "d-flex" (Bootstrap ทำเป็น display:flex !important
             จะทับ display:none ที่ตั้งไว้ และทำให้ toggle ด้วย jQuery ไม่ได้ผล ฟุตเตอร์จะค้างโชว์ตลอด)
             .modal-footer ของ Bootstrap เป็น display:flex อยู่แล้วโดยไม่มี !important จึง toggle ได้ปกติ -->
        <div class="modal-footer justify-content-between" id="sellHistoryDetailFooter" style="display:none;">
          <button type="button" class="btn btn-danger" id="kpShdDeleteBtn"><i class="fa fa-trash"></i> ลบ</button>
          <button type="button" class="btn btn-success" id="kpShdEditBtn"><i class="fa fa-pencil"></i> แก้ไข</button>
        </div>
        <!-- ฟุตเตอร์: กำลังแก้ไขรายการขายปกติ -->
        <div class="modal-footer justify-content-between" id="shEditFooter" style="display:none;">
          <button type="button" class="btn btn-light" id="shEditCancelBtn">ยกเลิก</button>
          <button type="button" class="btn btn-success" id="shSaveBtn"><i class="fa fa-check"></i> บันทึก</button>
        </div>
        <!-- ฟุตเตอร์: กำลังแก้ไขบิลขายพิเศษ -->
        <div class="modal-footer justify-content-between" id="shpEditFooter" style="display:none;">
          <button type="button" class="btn btn-light" id="shpEditCancelBtn">ยกเลิก</button>
          <button type="button" class="btn btn-success" id="shpSaveBtn"><i class="fa fa-check"></i> บันทึก</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: QR พร้อมเพย์ -->
  <div class="modal fade" id="payQrModal" tabindex="-1" role="dialog" aria-labelledby="payQrLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="payQrLabel">สแกนเพื่อชำระ (พร้อมเพย์)</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
          <!-- รายละเอียดที่มาของยอด (ขายวันนี้กี่ลัง/ถัง + ยอดเก่าเท่าไหร่): อยู่บนสุด เหนือ QR (คำขอ 2026-09-14)
               ซ่อนไว้ถ้าไม่มีข้อมูล (ไม่ได้ขาย/ไม่มียอดเก่า) — อัปเดตค่าใน json/index2_ui.js -->
          <div class="kp-qr-amount-top" id="payQrBreakdown" style="display:none;">
            <div class="kp-qr-breakdown__row"><span>ขายวันนี้</span><b id="payQrBreakdownSell">0 ลัง, 0 ถัง</b></div>
            <div class="kp-qr-breakdown__row"><span>ยอดเก่า</span><b id="payQrBreakdownOld">฿ 0.00</b></div>
          </div>
          <!-- src ถูกแทนที่ด้วย QR ที่ฝังยอดเงินอัตโนมัติตอนเปิด popup (json/index2_ui.js)
               ถ้าสร้างไม่สำเร็จจะ fallback กลับมาใช้รูปนี้ตามเดิม — คำขอ 2026-09-09 -->
          <img src="img/QRprom.jpg?v=1.3" data-fallback-src="img/QRprom.jpg?v=1.3" id="payQrImg" class="img-fluid kp-qr-img" alt="QR พร้อมเพย์">
          <!-- ยอดที่ต้องชำระ: กลับไปไว้ใต้ QR เหมือนเดิม (คำขอแก้ 2026-09-14 — ย้ายกลับจากที่เคยลองไว้บนสุด) -->
          <div class="mt-2">
            <div style="font-weight:1000;color:rgba(17,24,39,0.65);font-size:13px;">ยอดที่ต้องชำระ</div>
            <div id="payQrAmount" style="font-weight:1000;color:rgba(17,24,39,0.92);font-size:28px;line-height:1.1;">฿ 0.00</div>
            <div id="payQrHint" style="font-weight:900;color:rgba(17,24,39,0.55);font-size:12px;">(อ้างอิงจาก “ยอดชำระวันนี้”)</div>
          </div>
          <div class="mt-2" style="font-weight:1000;color:rgba(17,24,39,0.78);font-size:14px;">ชื่อบัญชี: สัญญา ใจยา</div>
          <!-- เผื่อลูกค้าสแกน QR ไม่ได้ ให้มีเลขไว้โอนมือได้ (คำขอ 2026-09-09) -->
          <div class="mt-1" style="font-weight:800;color:rgba(17,24,39,0.65);font-size:13px;">หมายเลขโอนเงิน ที่ พร้อมเพย์ 090-893-9463 สัญญา ใจยา</div>
          <!-- ป็อปอัพนี้เด้งเฉพาะตอนกดปุ่ม "บันทึก" (เลือกวิธีชำระ "โอน/สแกน" เฉยๆ จะไม่เด้งแล้ว — คำขอแก้ 2026-09-14)
               "ยกเลิก" = ปิดป็อปอัพเฉยๆ ไม่บันทึก (กลับไปแก้ไขรายการขายได้)
               "ตกลง" = ยืนยันว่าลูกค้าโอนเงินแล้ว บันทึกการขายให้อัตโนมัติ (ตรรกะอยู่ใน json/select.js) -->
          <div class="mt-3 kp-delete-confirm__actions">
            <button type="button" class="btn btn-light" id="payQrCancelBtn">ยกเลิก</button>
            <button type="button" class="btn btn-success" id="payQrOkBtn">ตกลง</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: เมนู (Hamburger) -->
  <div class="modal fade" id="index2MenuModal" tabindex="-1" role="dialog" aria-labelledby="index2MenuLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal kp-menu-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="index2MenuLabel">เมนู</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="kp-menu-list">
            <?php $isAdmin = (isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1); ?>
            <a href="/pos_sale.php" class="kp-menu-item">
              <span class="kp-menu-ic"><i class="fa fa-shopping-basket" aria-hidden="true"></i></span>
              <span class="kp-menu-tx">หน้าขาย</span>
              <i class="fa fa-chevron-right kp-menu-ch" aria-hidden="true"></i>
            </a>
            <button type="button" class="kp-menu-item" data-kp-tab="#contact">
              <span class="kp-menu-ic"><i class="fa fa-address-book-o" aria-hidden="true"></i></span>
              <span class="kp-menu-tx">รายชื่อลูกค้า / สาขา</span>
              <i class="fa fa-chevron-right kp-menu-ch" aria-hidden="true"></i>
            </button>
            <?php if ($isAdmin) { ?>
            <a href="/pos_sale.php#hist" class="kp-menu-item">
              <span class="kp-menu-ic"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
              <span class="kp-menu-tx">ประวัติการขาย</span>
              <i class="fa fa-chevron-right kp-menu-ch" aria-hidden="true"></i>
            </a>
            <a href="/pos_sale.php#report" class="kp-menu-item">
              <span class="kp-menu-ic"><i class="fa fa-line-chart" aria-hidden="true"></i></span>
              <span class="kp-menu-tx">สรุป / กราฟ</span>
              <i class="fa fa-chevron-right kp-menu-ch" aria-hidden="true"></i>
            </a>
            <a href="/pos_sale.php#staff" class="kp-menu-item">
              <span class="kp-menu-ic"><i class="fa fa-users" aria-hidden="true"></i></span>
              <span class="kp-menu-tx">จัดการพนักงาน</span>
              <i class="fa fa-chevron-right kp-menu-ch" aria-hidden="true"></i>
            </a>
            <a href="/pos_sale.php#jobs" class="kp-menu-item">
              <span class="kp-menu-ic"><i class="fa fa-id-card-o" aria-hidden="true"></i></span>
              <span class="kp-menu-tx">ใบสมัครงาน</span>
              <i class="fa fa-chevron-right kp-menu-ch" aria-hidden="true"></i>
            </a>
            <a href="/report_money_out.php" class="kp-menu-item">
              <span class="kp-menu-ic"><i class="fa fa-money" aria-hidden="true"></i></span>
              <span class="kp-menu-tx">รายจ่าย</span>
              <i class="fa fa-chevron-right kp-menu-ch" aria-hidden="true"></i>
            </a>
            <a href="/branch_manage.php" class="kp-menu-item">
              <span class="kp-menu-ic"><i class="fa fa-building-o" aria-hidden="true"></i></span>
              <span class="kp-menu-tx">จัดการสาขา</span>
              <i class="fa fa-chevron-right kp-menu-ch" aria-hidden="true"></i>
            </a>
            <?php } ?>
            <a href="select_page.php" class="kp-menu-item kp-menu-item--danger">
              <span class="kp-menu-ic kp-menu-ic--danger"><i class="fa fa-power-off" aria-hidden="true"></i></span>
              <span class="kp-menu-tx">ออกจากระบบ</span>
              <i class="fa fa-chevron-right kp-menu-ch" aria-hidden="true"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: แจ้งเตือน -->
  <div class="modal fade" id="notifyModal" tabindex="-1" role="dialog" aria-labelledby="notifyLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="notifyLabel">
            <?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1){ ?>
              แชท (Admin)
            <?php } else { ?>
              แจ้งเตือน
            <?php } ?>
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1){ ?>
            <div class="form-group mb-2">
              <label class="font-weight-bold mb-1">พิมพ์ข้อความ (ส่งถึงทุกคน)</label>
              <textarea id="notifyMessage" class="form-control" rows="3" placeholder="พิมพ์ข้อความ..."></textarea>
            </div>
            <div class="d-flex justify-content-end mb-3" style="gap:8px;">
              <button type="button" class="btn btn-light" id="btnCancelNotify">ยกเลิก</button>
              <button type="button" class="btn btn-success" id="btnSendNotify">ส่ง</button>
            </div>
            <hr>
          <?php } ?>
          <div id="notifyList" class="kp-notify-list"></div>
          <?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1){ ?>
            <div class="kp-notify-seen" id="notifySeenLine" style="display:none;"></div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: ค้นหาลูกค้าบนแผนที่ -->
  <?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']!==1){ ?>
  <!-- ส่งยอดเงินสดตอนเย็น (คนขับ) — คำขอ 2026-10-02 -->
  <div class="modal fade" id="cashSubmitModal" tabindex="-1" role="dialog" aria-labelledby="cashSubmitLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="cashSubmitLabel">ส่งยอดเงินสด</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="cs-date" id="csDate"></div>
          <div id="csState"></div>
          <div class="cs-sys" id="csSys"></div>
          <div id="csForm">
            <div id="csStepForm">
              <div class="cs-hint">นับเงินสดที่เก็บได้ทั้งหมดของวันนี้ แล้วกรอกยอดรวม (ไม่รวมเงินโอน) ก่อนกดส่ง</div>
              <label class="cs-lbl" for="csAmount">ยอดเงินสดที่นับได้</label>
              <div class="cs-amt">
                <input type="text" inputmode="decimal" id="csAmount" class="form-control" placeholder="0" autocomplete="off">
                <i>บาท</i>
              </div>
              <div class="cs-diff" id="csDiff"></div>
              <label class="cs-lbl" for="csNote">หมายเหตุ (ไม่บังคับ)</label>
              <input type="text" id="csNote" class="form-control" maxlength="200" placeholder="เช่น ค่าน้ำแข็ง 10 บาท" autocomplete="off">
              <!-- ลัง/ถัง รายรอบ (5 รอบ) + ขายพิเศษ แพค/กล่อง (คำขอ 2026-10-08) — ไม่บังคับ -->
              <div class="cs-stock" id="csStock">
                <div class="cs-stock__t"><i class="fa fa-cubes" aria-hidden="true"></i> ลัง / ถัง รายรอบ</div>
                <div class="cs-stock__g">
                  <span class="cs-stock__gh"></span>
                  <span class="cs-stock__gh cs-stock__gh--o">ออก</span><span class="cs-stock__dv"></span><span class="cs-stock__gh cs-stock__gh--l">เหลือ</span>
                  <span class="cs-stock__h"></span>
                  <span class="cs-stock__h">ลัง</span><span class="cs-stock__h">ถัง</span><span class="cs-stock__dv"></span><span class="cs-stock__h">ลัง</span><span class="cs-stock__h">ถัง</span>
                  <?php $csK = ["co" => "ลังออก", "to" => "ถังออก", "cl" => "ลังเหลือ", "tl" => "ถังเหลือ"]; for ($csI = 1; $csI <= 5; $csI++) { ?>
                  <b class="cs-stock__r">รอบ <?php echo $csI; ?></b>
                  <?php $csN = 0; foreach ($csK as $csKey => $csLbl) { if ($csN++ === 2) { ?><span class="cs-stock__dv"></span><?php } ?>
                  <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="5" class="cs-n cs-n--<?php echo $csKey[1] === 'o' ? 'o' : 'l'; ?>" data-r="<?php echo $csI - 1; ?>" data-k="<?php echo $csKey; ?>" aria-label="รอบ <?php echo $csI; ?> <?php echo $csLbl; ?>" placeholder="0" autocomplete="off">
                  <?php } ?>
                  <?php } ?>
                </div>
                <div class="cs-stock__sum" id="csStockSum"></div>
                <div class="cs-stock__sp">
                  <b>ขายพิเศษ</b>
                  <label>แพค <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="5" class="cs-n" data-k="sp" aria-label="ขายพิเศษ แพค" placeholder="0" autocomplete="off"></label>
                  <label>กล่อง <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="5" class="cs-n" data-k="sb" aria-label="ขายพิเศษ กล่อง" placeholder="0" autocomplete="off"></label>
                </div>
              </div>
              <button type="button" class="btn btn-success cs-btn" id="csGo">ส่งยอด</button>
            </div>
            <div id="csStepConfirm" style="display:none;">
              <div class="cs-confirm">
                <div class="cs-confirm__k">ตรวจทานยอดที่จะส่ง</div>
                <div class="cs-confirm__v" id="csConfirmAmt">฿ 0</div>
                <div class="cs-confirm__d" id="csConfirmDiff"></div>
                <div class="cs-confirm__n">หมายเหตุ: <span id="csConfirmNote">—</span></div>
                <div class="cs-confirm__s" id="csConfirmStock"></div>
                <div class="cs-confirm__w">ยอดจะถูกบันทึกเข้าระบบทันที และส่งสรุปเข้า LINE · แก้ไขยอดได้ภายหลัง กรุณาตรวจยอดก่อนกดยืนยัน</div>
              </div>
              <button type="button" class="btn btn-success cs-btn" id="csSend">ยืนยันส่งยอด</button>
              <button type="button" class="btn cs-btn cs-btn--ghost" id="csBack">กลับไปแก้ยอด</button>
            </div>
          </div>
          <div class="cs-msg" id="csMsg" role="status"></div>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>

  <?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1){ ?>
  <!-- ใบสมัครงาน (แอดมิน) — หน้าใหม่ในเมนูสามขีด · รายการ/รายละเอียดวาดโดย json/job_apply_ui.js (คำขอ 2026-10-19) -->
  <div class="modal fade kp-receipt-modal" id="jobApplyModal" tabindex="-1" role="dialog" aria-labelledby="jobApplyLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <div style="display:flex;align-items:center;gap:8px;min-width:0;">
            <button type="button" class="kp-staff-gear-btn kp-back-btn" id="jobBackBtn" title="ย้อนกลับ" aria-label="ย้อนกลับ"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
            <h5 class="modal-title" id="jobApplyLabel">ใบสมัครงาน</h5>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body"><div id="kpJobBox"></div></div>
      </div>
    </div>
  </div>
  <?php } ?>

  <?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1){ ?>
  <!-- เงินสดที่เก็บได้จริง (แอดมิน) — ย้ายจากหน้าจัดการพนักงาน คำขอ 2026-10-04 -->
  <div class="modal fade" id="cashActualModal" tabindex="-1" role="dialog" aria-labelledby="cashActualLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="cashActualLabel">เงินสดที่เก็บได้จริง</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="ca-datebar">
            <button type="button" class="kp-icon-act" id="caPrev" title="วันก่อนหน้า" aria-label="วันก่อนหน้า"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
            <label class="kp-hr-datebox" for="caDate">
              <span id="caDateText" class="kp-hr-datebox__t">-</span>
              <input type="date" id="caDate" class="kp-hr-datebox__in" aria-label="วันที่">
            </label>
            <button type="button" class="kp-icon-act" id="caNext" title="วันถัดไป" aria-label="วันถัดไป"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
          </div>
          <div id="caDrivers"></div>
          <div class="ca-card">
            <div class="ca-row">
              <label class="ca-k" for="caActual">เงินสดที่เก็บได้จริง</label>
              <div class="ca-in">
                <span class="ca-drvbox" id="caDrvBox" hidden title="ยอดรวมที่คนขับส่งมา"><small>ยอดส่ง</small><b id="caDrv">0</b></span>
                <span class="ca-slash" id="caSlash" hidden>/</span>
                <input type="text" inputmode="decimal" id="caActual" class="form-control" placeholder="0" autocomplete="off">
                <span>บาท</span>
              </div>
            </div>
            <div class="ca-cmp" id="caCmp"></div>
          </div>
          <!-- โอนเข้าวันนี้ (สรุปภาพรวม + รายการ) — คำขอ 2026-10-19 -->
          <div id="caTransfer" class="ca-tf" hidden></div>
          <button type="button" class="btn btn-success cs-btn" id="caSave">บันทึก</button>
          <div class="cs-msg" id="caMsg" role="status"></div>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>

  <div class="modal fade" id="mapSearchModal" tabindex="-1" role="dialog" aria-labelledby="mapSearchLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="mapSearchLabel">ค้นหาลูกค้าบนแผนที่</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-2">
            <label class="font-weight-bold mb-1">รหัส / ชื่อ ลูกค้า</label>
            <div style="display:flex;gap:8px;align-items:flex-start;">
              <div style="flex:1 1 auto;min-width:0;">
                <select id="mapCustomerSearch" class="form-control" style="width:100%;"></select>
              </div>
              <button type="button" id="mapVoiceSearchBtn" class="btn btn-outline-success" title="พูดค้นหา" style="flex:0 0 auto;">
                <i class="fa fa-microphone" aria-hidden="true"></i>
              </button>
            </div>
            <small class="form-text text-muted" id="mapVoiceSearchHint">จะแสดงเฉพาะลูกค้าที่มีพิกัดบนแผนที่</small>
          </div>
          <div class="d-flex justify-content-end" style="gap:8px;">
            <button type="button" class="btn btn-light" data-dismiss="modal">ปิด</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: เลือกลูกค้าจากผลค้นหาด้วยเสียง (กรณีชื่อซ้ำ/ใกล้เคียงหลายคน) -->
  <div class="modal fade" id="voicePickModal" tabindex="-1" role="dialog" aria-labelledby="voicePickLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="voicePickLabel">เลือกลูกค้า</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="text-muted mb-2" id="voicePickHeard" style="font-size:12px;font-weight:900;"></div>
          <div id="voicePickList" style="max-height:52vh;overflow:auto;"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light btn-block" data-dismiss="modal">ปิด</button>
        </div>
      </div>
    </div>
  </div>

  <?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1){ ?>
  <!-- Modal: เลือก Focus ติดตามการเคลื่อนไหว (Admin) -->
  <div class="modal fade" id="mapFollowModal" tabindex="-1" role="dialog" aria-labelledby="mapFollowLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="mapFollowLabel">ติดตามการเคลื่อนไหว</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="text-muted" style="font-size:12px;font-weight:900;margin-bottom:8px;">
            เลือกคนที่ต้องการให้แผนที่ติดตามอัตโนมัติ (เลือก “ปิด” เพื่อหยุดติดตาม)
          </div>
          <div class="kp-follow-form">
            <label class="kp-follow-label" for="mapFollowSelect">ติดตาม</label>
            <select id="mapFollowSelect" class="form-control kp-follow-select">
              <option value="">ปิดการติดตาม</option>
            </select>
            <div class="kp-follow-hint">ระบบจะเริ่มติดตามหลังจากกด “ตกลง”</div>
          </div>
          <div class="d-flex justify-content-end" style="gap:8px;margin-top:12px;">
            <button type="button" class="btn btn-light" data-dismiss="modal" id="btnFollowCancel">ยกเลิก</button>
            <button type="button" class="btn btn-success" id="btnFollowOk">ตกลง</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>


  <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
    <!-- รายชื่อลูกค้า: สไตล์ Neumorphism การ์ดนูนต่อคน (คำขอ 2026-10-04) — id เดิมทั้งหมดคงไว้ให้ JS เดิมทำงาน -->
    <!-- สลับ 2 ส่วน: รายชื่อลูกค้า / รายชื่อสาขา (คำขอ 2026-10-21) -->
    <div class="kp-seg2" role="tablist" aria-label="สลับรายชื่อ">
      <button type="button" class="kp-seg2__btn is-active" data-kp-contact-view="customer">ลูกค้า</button>
      <button type="button" class="kp-seg2__btn" data-kp-contact-view="branch">สาขา</button>
    </div>
    <div class="nc-wrap" id="ncCustomerPane">
      <div class="nc-top">
        <div class="nc-search">
          <i class="fa fa-search" aria-hidden="true"></i>
          <input type="text" class="form-control" id="contactSearch" placeholder="ค้นหาชื่อ หรือ รหัสลูกค้า..." autocomplete="off">
        </div>
        <div class="nc-tools">
          <button type="button" class="nc-tile" id="btnContactRefresh" title="รีเฟรช" aria-label="รีเฟรช">
            <i class="fa fa-refresh" aria-hidden="true"></i>
          </button>
          <?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1){ ?>
            <button type="button" class="nc-tile nc-tile--add" id="btnContactAdd" title="เพิ่มลูกค้า" aria-label="เพิ่มลูกค้า">
              <i class="fa fa-plus" aria-hidden="true"></i>
            </button>
          <?php } ?>
        </div>
      </div>
      <!-- เลือกหมู่บ้านที่ต้องการแสดงรายชื่อ (คำขอ 2026-10-04) -->
      <div class="nc-village">
        <i class="fa fa-map-marker" aria-hidden="true"></i>
        <select id="contactVillage" class="form-control" aria-label="เลือกหมู่บ้าน">
          <option value="">เลือกหมู่บ้าน</option>
          <?php
            $kpVsql="SELECT tb_adress.id_adress,tb_adress.name_adress AS name_ad FROM tb_adress ORDER BY tb_adress.name_adress";
            $kpVdata=$db->select($kpVsql);
            if(is_array($kpVdata)){
              foreach($kpVdata AS $kpVrow){
                echo "<option value='".htmlspecialchars($kpVrow['id_adress'], ENT_QUOTES, 'UTF-8')."'>".htmlspecialchars($kpVrow['name_ad'], ENT_QUOTES, 'UTF-8')."</option>";
              }
            }
          ?>
          <option value="none">ยังไม่ระบุหมู่บ้าน</option>
        </select>
        <button type="button" class="nc-vclear" id="contactVillageClear" title="ล้างตัวกรอง" aria-label="ล้างตัวกรองหมู่บ้าน" hidden><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
      <div class="nc-bar">
        <div class="nc-count"><small>ทั้งหมด</small><b id="contactCount">0</b></div>
        <div class="nc-chip nc-chip--sel">
          <span>ต่อหน้า</span>
          <select id="contactPerPage" class="form-control form-control-sm">
            <option value="10">10</option>
            <option value="50">50</option>
            <option value="all">ทั้งหมด</option>
          </select>
        </div>
        <div class="nc-pager">
          <button type="button" class="nc-tile nc-tile--sm" id="contactPrev" title="ก่อนหน้า"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
          <div class="nc-pager__info" id="contactPageInfo">หน้า 1/1</div>
          <button type="button" class="nc-tile nc-tile--sm" id="contactNext" title="ถัดไป"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
        </div>
      </div>
      <div class="nc-list" id="contactTableWrap">
        <div id="contactTbody"></div>
      </div>
    </div>
    <div class="nc-wrap" id="ncBranchPane" hidden>
      <div class="nc-top">
        <div class="nc-search">
          <i class="fa fa-search" aria-hidden="true"></i>
          <input type="text" class="form-control" id="branchSearch" placeholder="ค้นหาชื่อ หรือ รหัสสาขา..." autocomplete="off">
        </div>
        <div class="nc-tools">
          <button type="button" class="nc-tile" id="btnBranchRefresh" title="รีเฟรช" aria-label="รีเฟรช"><i class="fa fa-refresh" aria-hidden="true"></i></button>
          <?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1){ ?>
            <button type="button" class="nc-tile nc-tile--add" id="btnBranchAdd" title="เพิ่มสาขา" aria-label="เพิ่มสาขา"><i class="fa fa-plus" aria-hidden="true"></i></button>
          <?php } ?>
        </div>
      </div>
      <div class="nc-bar"><div class="nc-count"><small>ทั้งหมด</small><b id="branchCount">0</b></div></div>
      <div class="nc-list" id="branchListWrap"><div id="branchTbody"></div></div>
    </div>
  </div>

  <style>
    .kp-seg2{display:flex;gap:6px;margin:8px 12px 4px;padding:4px;border-radius:14px;background:rgba(15,42,28,.07)}
    .kp-seg2__btn{flex:1;border:0;border-radius:11px;padding:8px 10px;font-weight:700;background:transparent;color:inherit;opacity:.65}
    .kp-seg2__btn.is-active{background:#fff;opacity:1;box-shadow:0 2px 8px rgba(0,0,0,.12)}
    .kp-br-card{display:flex;align-items:center;justify-content:space-between;gap:8px;margin:8px 4px;padding:12px 14px;border-radius:16px;background:#fff;box-shadow:0 4px 14px rgba(0,0,0,.08)}
    .kp-br-card.is-off{opacity:.55}
    .kp-br-card small{display:block;color:#667}
    #ncBranchPane[hidden],#ncCustomerPane[hidden]{display:none!important}
  </style>
  <!-- Modal: เพิ่ม/แก้ไขสาขา (Admin เท่านั้น) -->
  <div class="modal fade" id="branchModal" tabindex="-1" role="dialog" aria-labelledby="branchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="branchModalLabel">เพิ่มรายชื่อสาขา</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="br_id" value="">
          <div class="form-group"><label class="font-weight-bold mb-1">รหัสสาขา</label><input type="text" class="form-control" id="br_code" placeholder="เช่น B02" maxlength="16"></div>
          <div class="form-group"><label class="font-weight-bold mb-1">ชื่อสาขา</label><input type="text" class="form-control" id="br_name" placeholder="ชื่อสาขา"></div>
          <div class="form-group"><label class="font-weight-bold mb-1">ที่อยู่</label><input type="text" class="form-control" id="br_address" placeholder="ที่อยู่"></div>
          <div class="form-group"><label class="font-weight-bold mb-1">เบอร์โทร</label><input type="tel" class="form-control" id="br_phone" placeholder="เบอร์โทร" inputmode="tel" maxlength="40"></div>
          <div class="form-group">
            <label class="font-weight-bold mb-1">รูปสาขา</label>
            <div class="d-flex align-items-center" style="gap:10px;">
              <img id="br_photo_preview" src="" alt="รูปสาขา" style="width:84px;height:64px;object-fit:cover;border-radius:10px;border:1px solid rgba(17,24,39,.15);display:none;background:#f3f4f6;">
              <input type="file" id="br_photo" accept="image/*" style="display:none;">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="btnBrPhoto"><i class="fa fa-camera" aria-hidden="true"></i> เลือก/ถ่ายรูป</button>
              <button type="button" class="btn btn-sm btn-outline-danger" id="btnBrPhotoRemove" style="display:none;"><i class="fa fa-trash" aria-hidden="true"></i></button>
            </div>
          </div>
          <div class="form-group mb-0">
            <label class="font-weight-bold mb-1">ที่ตั้งสาขา (แสดงเป็นหมุดบนแผนที่)</label>
            <div class="d-flex" style="gap:8px;">
              <input type="text" class="form-control" id="br_lat" placeholder="ละติจูด" inputmode="decimal">
              <input type="text" class="form-control" id="br_lng" placeholder="ลองจิจูด" inputmode="decimal">
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnBrUseMyLocation"><i class="fa fa-crosshairs" aria-hidden="true"></i> ใช้ตำแหน่งปัจจุบัน</button>
            <div class="custom-control custom-switch mt-3" id="br_active_wrap">
              <input type="checkbox" class="custom-control-input" id="br_active" checked>
              <label class="custom-control-label" for="br_active">เปิดใช้งานสาขา</label>
            </div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-outline-danger" id="btnBrDelete" style="display:none"><i class="fa fa-trash" aria-hidden="true"></i> ลบ</button>
          <span>
            <button type="button" class="btn btn-light" data-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-success" id="btnBrSave"><i class="fa fa-check" aria-hidden="true"></i> บันทึก</button>
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: เพิ่ม/แก้ไขรายชื่อลูกค้า (Admin เท่านั้น) -->
  <!-- เลือกพิกัดลูกค้าจากแผนที่ แบบเดียวกับการเลือกตำแหน่งในไลน์: หมุดอยู่กลางจอ ลากแผนที่เลือกจุด (คำขอ 2026-10-04) -->
  <div class="modal fade" id="cpPinMapModal" tabindex="-1" role="dialog" aria-labelledby="cpPinMapLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="cpPinMapLabel">เลือกตำแหน่งลูกค้า</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body p-0">
          <div class="kp-pinmap-wrap cp-pm-wrap">
            <div id="cpPinMapCanvas" class="kp-pinmap-canvas"></div>
            <div class="kp-pinmap-pin" aria-hidden="true"><i class="fa fa-map-marker" aria-hidden="true"></i></div>
            <button type="button" class="cp-pm-focus" id="cpPmFocus" title="ตำแหน่งปัจจุบัน" aria-label="ตำแหน่งปัจจุบัน"><i class="fa fa-crosshairs" aria-hidden="true"></i></button>
          </div>
          <div class="cp-pm-info">
            <div class="cp-pm-addr" id="cpPmAddr">ลากแผนที่เพื่อเลือกตำแหน่ง</div>
            <div class="cp-pm-coord" id="cpPmCoord"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-success" id="cpPmSave"><i class="fa fa-check" aria-hidden="true"></i> เลือกตำแหน่งนี้</button>
        </div>
      </div>
    </div>
  </div>

  <?php if(isset($_SESSION['id_status']) && (int)$_SESSION['id_status']===1){ ?>
  <!-- จัดการลูกค้า (แอดมิน) — ป๊อปอัพหลังกดปุ่มตั้งค่าบนการ์ดรายชื่อ (คำขอ 2026-10-04) -->
  <div class="modal fade" id="contactManageModal" tabindex="-1" role="dialog" aria-labelledby="contactManageLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="contactManageLabel">จัดการลูกค้า</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="ncm-who"><b id="ncmName">-</b><span id="ncmSub"></span></div>
          <button type="button" class="ncm-act ncm-act--edit btn-contact-edit">
            <span class="ncm-ico"><i class="fa fa-pencil" aria-hidden="true"></i></span>
            <span class="ncm-txt"><b>แก้ไขข้อมูล</b><small>ชื่อ เบอร์ ที่อยู่ พิกัด รูปภาพ</small></span>
          </button>
          <button type="button" class="ncm-act ncm-act--on btn-contact-uncancel" id="ncmUncancel">
            <span class="ncm-ico"><i class="fa fa-undo" aria-hidden="true"></i></span>
            <span class="ncm-txt"><b>เปิดใช้งานอีกครั้ง</b><small>กลับมาโผล่ในการค้นหาและแผนที่</small></span>
          </button>
          <button type="button" class="ncm-act ncm-act--del btn-contact-del">
            <span class="ncm-ico"><i class="fa fa-trash" aria-hidden="true"></i></span>
            <span class="ncm-txt"><b>ลบถาวร</b><small>กู้คืนไม่ได้ ประวัติการขายจะหายด้วย</small></span>
          </button>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>

  <!-- แก้ไขราคาขายลัง/ถัง เฉพาะการขายครั้งนี้ (json/sale_price_edit.js) -->
  <div class="modal fade" id="salePriceModal" tabindex="-1" role="dialog" aria-labelledby="salePriceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal kp-sp">
        <div class="modal-header">
          <h5 class="modal-title" id="salePriceLabel">แก้ไขราคาขาย</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="kp-sp__who"><b id="spName">-</b><span>ใช้กับการขายครั้งนี้เท่านั้น</span></div>
          <div class="kp-sp__grid">
            <label class="kp-sp__field"><span>ราคา ลัง</span>
              <div class="kp-sp__input"><input type="text" id="spBox" inputmode="decimal" autocomplete="off" placeholder="30"><i>บาท</i></div>
            </label>
            <label class="kp-sp__field"><span>ราคา ถัง</span>
              <div class="kp-sp__input"><input type="text" id="spBig" inputmode="decimal" autocomplete="off" placeholder="15"><i>บาท</i></div>
            </label>
          </div>
          <div id="spErr" class="kp-sp__err" hidden></div>
          <button type="button" id="spReset" class="kp-sp__reset"><i class="fa fa-undo" aria-hidden="true"></i> ใช้ราคามาตรฐาน (ลัง 30 / ถัง 15)</button>
          <div class="kp-sp__hint">ใช้คิดเฉพาะของที่ขายวันนี้ · ยอดค้างเก่ายังคิดราคามาตรฐาน</div>
        </div>
        <div class="modal-footer kp-sp__foot">
          <button type="button" class="btn btn-light" data-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-success" id="spApply">ตกลง</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="contactPersonModal" tabindex="-1" role="dialog" aria-labelledby="contactPersonLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content kp-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="contactPersonLabel">เพิ่มรายชื่อลูกค้า</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="ปิด">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="cp_id" value="">
          <input type="hidden" id="cp_photo_existing" value="">
          <input type="hidden" id="cp_remove_photo" value="0">
          <div class="form-group">
            <label class="font-weight-bold mb-1">ชื่อ</label>
            <input type="text" class="form-control" id="cp_name" placeholder="ชื่อ">
          </div>
          <div class="form-group">
            <label class="font-weight-bold mb-1">เบอร์โทร</label>
            <input type="tel" class="form-control" id="cp_tel" placeholder="เบอร์โทร เช่น 081-234-5678" inputmode="tel" maxlength="30">
          </div>
          <div class="form-group">
            <label class="font-weight-bold mb-1">หมู่บ้าน</label>
            <select class="form-control" id="cp_adress">
              <option value="">เลือก</option>
              <?php
                $sql="SELECT tb_adress.id_adress,tb_adress.name_adress AS name_ad FROM tb_adress";
                $data=$db->select($sql);
                if(is_array($data)){
                  foreach($data AS $row){
                    echo "<option value='".htmlspecialchars($row['id_adress'], ENT_QUOTES, 'UTF-8')."'>".htmlspecialchars($row['name_ad'], ENT_QUOTES, 'UTF-8')."</option>";
                  }
                }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label class="font-weight-bold mb-1">ที่อยู่</label>
            <input type="text" class="form-control" id="cp_address" placeholder="ที่อยู่">
          </div>
          <div class="form-group">
            <label class="font-weight-bold mb-1">พิกัด (สำหรับปักหมุด/ค้นหาระยะทาง)</label>
            <div class="d-flex" style="gap:8px;">
              <input type="text" class="form-control" id="cp_lat" placeholder="ละติจูด เช่น 18.29321" inputmode="decimal">
              <input type="text" class="form-control" id="cp_lng" placeholder="ลองจิจูด เช่น 99.50123" inputmode="decimal">
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnCpUseMyLocation">
              <i class="fa fa-crosshairs" aria-hidden="true"></i> ใช้ตำแหน่งปัจจุบัน
            </button>
            <button type="button" class="btn btn-sm btn-outline-success mt-2" id="btnCpPickOnMap">
              <i class="fa fa-map-marker" aria-hidden="true"></i> เลือกจากแผนที่
            </button>
          </div>
          <div class="form-group mb-0">
            <label class="font-weight-bold mb-1">รูปภาพ</label>
            <div class="d-flex align-items-center" style="gap:10px;">
              <img id="cp_photo_preview" src="" alt="รูปลูกค้า" style="width:64px;height:64px;object-fit:cover;border-radius:10px;border:1px solid rgba(17,24,39,0.15);display:none;background:#f3f4f6;">
              <!-- เลือกได้ว่าจะ "ถ่ายรูป" ใหม่ หรือ "อัพโหลด" รูปที่มีอยู่แล้ว (คำขอ 2026-09-16) -->
              <input type="file" id="cp_photo" accept="image/*" style="display:none;">
              <input type="file" id="cp_photo_camera" accept="image/*" capture="environment" style="display:none;">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCpTakePhoto">
                <i class="fa fa-camera" aria-hidden="true"></i> ถ่ายรูป
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCpUploadPhoto">
                <i class="fa fa-upload" aria-hidden="true"></i> อัพโหลด
              </button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="btnCpRemovePhoto" style="display:none;">
              <i class="fa fa-trash" aria-hidden="true"></i> ลบรูปนี้
            </button>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-light" data-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-success" id="btnCpSave">
            <i class="fa fa-check" aria-hidden="true"></i> บันทึก
          </button>
        </div>
      </div>
    </div>
  </div>
    


</div>

    </div><!-- /.container -->
  </div><!-- /.bottom-sheet-body -->
</div><!-- /#searchSheet -->




<script src="lib/jquery-3.5.1.min.js"></script>
<!-- ใช้ bundle เพื่อให้ modal/popover ทำงานครบ (รวม Popper) -->
<script src="lib/bootstrap-4.5.2-dist/js/bootstrap.bundle.min.js"></script>
<script src="json/kp_toast.js?v=1"></script>
<script src="lib/bootalert.min.js"></script>
    <script src="json/viewport.js?v=3"></script>
    <script src="json/pwa.js?v=1"></script>
    <script src="/lib/src/bootstrap-input-spinner.js"></script>
    <script src="/lib/select2.min.js"></script>
    <script src="json/sheet.js?v=3.5"></script>
    <script src="json/select.js?v=2.53"></script>
    <script src="lib/qrcode/qrcode.min.js?v=1"></script>
    <script src="json/kp_promptpay_qr.js?v=6"></script>
    <script src="json/kp_thai_date.js?v=2"></script>
    <script src="json/kp_receipt_share.js?v=3"></script>
    <!-- ย่อรูปในเครื่องก่อนอัปโหลด ต้องโหลดก่อนไฟล์ที่มีฟอร์มอัปรูป (คำขอ 2026-09-14) -->
    <script src="json/kp_photo_resize.js?v=1"></script>
    <script src="json/index2_ui.js?v=3.54.<?php echo (int)@filemtime(__DIR__ . "/json/index2_ui.js"); ?>"></script>
    <script src="json/sale_price_edit.js?v=1.0"></script>
    <script src="json/cash_submit_ui.js?v=43"></script>
    <script src="lib/html2canvas.min.js"></script>
    <script src="json/kp_bt_print.js?v=55"></script>
    <script src="json/index2_print_bridge.js?v=1.58"></script>
    <script src="json/qty_utils.js?v=1"></script>
    <!-- บันทึกการกดโทรออก ต้องโหลดก่อนไฟล์ที่มีปุ่มโทร (sale_sheet / contact_people) -->
    <script src="json/kp_call_log.js?v=1"></script>
    <script src="json/index2_sale_sheet.js?v=1.11"></script>
    <script src="json/report.js?v=1.3"></script>
    <script src="json/save.js?v=1.5"></script>
    <script src="json/contact_people.js?v=1.20"></script>
    <script src="json/contact_branches.js?v=1"></script>
    <script src="json/branch_search.js?v=1"></script>
    <script src="json/map.js?v=6.54.<?php echo (int)@filemtime(__DIR__ . "/json/map.js"); ?>"></script>
    <script src="json/transfer_confirm_ui.js?v=1.<?php echo (int)@filemtime(__DIR__ . "/json/transfer_confirm_ui.js"); ?>"></script>
    <script src="json/online_users_ui.js?v=8.<?php echo (int)@filemtime(__DIR__ . "/json/online_users_ui.js"); ?>"></script>
    <script async src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars(kp_google_maps_browser_key(), ENT_QUOTES, 'UTF-8'); ?>&callback=initMap&loading=async"></script>

    <script>
$(document).ready(function(){
  // (ลบปุ่ม refresh/home ข้างช่องค้นหาออกแล้วพร้อมช่องค้นหาทั้งก้อน — 2026-09-08)

});
</script>

<script>
$(document).ready(function(){
  // -----------------------------
  // Sheet home: หน้าค้นหาลูกค้า + ปุ่มขายเพิ่มเติม (ธีมเดียวกับ select_page)
  // แสดงเป็นค่าเริ่มต้นทุกครั้งที่เปิด sheet ใหม่ (ยังไม่ได้เลือกลูกค้า)
  // -----------------------------
  function kpShowSheetHome() {
    try { $("#sheetSearchHome").css("display", "flex"); } catch (e0) {}
    // ใช้ css("display","none") แทน .hide() เพราะ .show() ของ jQuery จะจำ
    // display เดิมไม่ได้ถูกต้องกับ #myTab (Bootstrap nav-tabs ใช้ display:flex)
    // ทำให้ไอคอนแท็บ (ประวัติ/ขายพิเศษ/QR ฯลฯ) เรียงตกลงมาเป็นแนวตั้งแทน
    try { $("#myTab").css("display", "none"); } catch (e1) {}
    try { $("#myTabContent").css("display", "none"); } catch (e2) {}
  }
  function kpShowSheetForm() {
    try { $("#sheetSearchHome").hide(); } catch (e0) {}
    // ซ่อนแถบไอคอนแท็บ (#myTab) ไว้เสมอ ไม่ให้เห็น — โชว์แค่เนื้อหา (#myTabContent)
    try { $("#myTab").css("display", "none"); } catch (e1) {}
    try { $("#myTabContent").css("display", "block"); } catch (e2) {}
  }

  // ค่าเริ่มต้นตอนโหลดหน้า: ยังไม่ได้เลือกลูกค้า -> โชว์หน้าค้นหา+ปุ่มบวก
  kpShowSheetHome();

  // ปุ่ม "+" กลางจอ -> ขายเพิ่มเติม (แท็บ "ขายพิเศษ" ที่มีอยู่แล้ว)
  $("body").on("click", "#sheetHomePlusBtn", function (e) {
    try { e.preventDefault(); } catch (e0) {}
    kpShowSheetForm();
    try {
      if (window.Index2Sheet && window.Index2Sheet.expand) window.Index2Sheet.expand();
    } catch (e2) {}
  });

  // เลือกรายชื่อลูกค้าแล้ว (จากช่องค้นหา หรือกดหมุดบนแผนที่) -> เปิดฟอร์มขาย (#home)
  $("body").on("select2:select", ".selectdata", function () {
    kpShowSheetForm();
    try { $("#myTab a[href='#home']").tab("show"); } catch (e0) {}
  });

  // เมนู (แฮมเบอร์เกอร์) -> ต้องโชว์เนื้อหาแท็บด้วย
  // ไม่งั้นจะสลับแท็บ+ขยายแผ่นจริง แต่ #myTabContent ยังถูกซ่อนจาก kpShowSheetHome()
  // ทำให้เห็นเป็น "หน้าค้นหา" เหมือนเดิม (อาการกดเมนูแล้วไม่ไปไหน)
  $("body").on("click", "#index2MenuModal [data-kp-tab]", function () {
    var target = String($(this).attr("data-kp-tab") || "");
    if (!target || target === "#home") return; // #home = กลับหน้าแผนที่/ค้นหา
    setTimeout(function () {
      kpShowSheetForm();
      try { $("#myTab a[href='" + target + "']").tab("show"); } catch (e0) {}
      try {
        if (window.Index2Sheet && window.Index2Sheet.expand) window.Index2Sheet.expand();
      } catch (e1) {}
    }, 0);
  });

  // เปิดให้ไฟล์อื่นเรียกใช้ได้ (เช่น จากเมนู/ปุ่มอื่น)
  window.Index2ShowSheetForm = kpShowSheetForm;
  window.Index2ShowSheetHome = kpShowSheetHome;

  // -----------------------------
  // เครื่องพิมพ์ Bluetooth: จับคู่ / ยกเลิกการจับคู่
  // ปุ่มนี้จะโชว์เฉพาะเครื่องที่รองรับ Web Bluetooth (Android / คอม)
  // iPhone/iPad ไม่รองรับ -> ซ่อนปุ่ม แล้วใช้แอป kpprint ตามเดิม
  // -----------------------------
  function kpSyncBtPrinterUI() {
    try {
      var BT = window.KPBluetoothPrint;
      var $btn = $("#btnBtPrinter");
      if (!BT || !$btn.length) return;
      if (!BT.isSupported()) { $btn.hide(); return; }
      $btn.show();
      var name = BT.savedPrinterName();
      if (!BT.isSecureOk()) {
        $("#btPrinterStatus").text("ต้องเปิดผ่าน https:// จึงจะใช้ได้");
      } else if (name) {
        $("#btPrinterStatus").text("จับคู่แล้ว: " + name + " (แตะเพื่อเปลี่ยน/ยกเลิก)");
      } else {
        $("#btPrinterStatus").text("ยังไม่ได้จับคู่ — แตะเพื่อเลือกเครื่องพิมพ์");
      }
    } catch (e) {}
  }

  $("body").on("click", "#btnBtPrinter", function (e) {
    try { e.preventDefault(); } catch (e0) {}
    var BT = window.KPBluetoothPrint;
    if (!BT) return;

    if (!BT.isSecureOk()) {
      try { if (window.toastr) toastr.warning("Bluetooth ใช้ได้เฉพาะเมื่อเปิดผ่าน https:// หรือ localhost"); } catch (e1) {}
      return;
    }

    // จับคู่ไว้แล้ว -> ถามว่าจะยกเลิกไหม
    if (BT.hasPairedPrinter()) {
      if (confirm("ยกเลิกการจับคู่ \"" + BT.savedPrinterName() + "\" ?\n(กดยกเลิกถ้าต้องการเลือกเครื่องใหม่แทน)")) {
        BT.forgetPrinter();
        kpSyncBtPrinterUI();
        try { if (window.toastr) toastr.info("ยกเลิกการจับคู่แล้ว"); } catch (e2) {}
        return;
      }
    }

    // ยังจับคู่ไว้อยู่ (แค่กด "ยกเลิก" ตอนถามว่าจะเลิกจับคู่ไหม) -> กรองรายการด้วยชื่อเดิม แตะยืนยันครั้งเดียวพอ
    var connectTask = BT.hasPairedPrinter() && BT.reconnectWithPicker
      ? BT.reconnectWithPicker()
      : BT.connect();
    connectTask.then(function (info) {
      kpSyncBtPrinterUI();
      try {
        var label = (BT.printerProtocolLabel) ? " — โหมด: " + BT.printerProtocolLabel(info && info.protocol) : "";
        if (window.toastr) toastr.success("จับคู่แล้ว: " + (info && info.name ? info.name : "เครื่องพิมพ์") + label);
      } catch (e3) {}
    }).catch(function (err) {
      var msg = "จับคู่ไม่สำเร็จ";
      var code = String((err && err.message) || "");
      if (code === "no-writable-characteristic") msg = "เครื่องนี้ไม่รองรับการสั่งพิมพ์ (ไม่พบช่องเขียนข้อมูล)";
      else if (code === "insecure") msg = "ต้องเปิดผ่าน https:// จึงจะใช้ Bluetooth ได้";
      else if (/cancel|User cancelled/i.test(code)) msg = "ยกเลิกการเลือกเครื่องพิมพ์";
      try { if (window.toastr) toastr.warning(msg); } catch (e4) {}
    });
  });

  kpSyncBtPrinterUI();
  $("#index2MenuModal").on("shown.bs.modal", kpSyncBtPrinterUI);

  // -----------------------------
  // (ลบปุ่ม refresh/home ข้างช่องค้นหาออกแล้ว จึงลบฟังก์ชัน sync
  //  ปุ่มเหล่านี้ (kpGetActiveTabHref/kpSyncSheetActionBtn) ออกด้วย — 2026-09-08)

  // ยุบ sheet กลับไปหน้าแผนที่ -> รีเซตกลับไปหน้าค้นหา+ปุ่มบวก สำหรับครั้งถัดไป
  window.addEventListener("kp:sheetchange", function (ev) {
    try {
      var expanded = !!(ev && ev.detail && ev.detail.expanded);
      if (!expanded) {
        kpShowSheetHome();
        try { $("#myTab a[href='#home']").tab("show"); } catch (e0) {}
      }
    } catch (e) {}
  });
});
</script>
<script src="/json/kp_time_hm.js?v=1"></script>
<script src="/json/kp_datepicker.js?v=1"></script>
</body>
</html>
