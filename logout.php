<?php
// ออกจากระบบ + ลบตำแหน่ง lat/lng/update_at ของ user เพื่อให้ marker หายไปทันที
require($_SERVER['DOCUMENT_ROOT'] . "/inc/db/db_webdev.php");
require($_SERVER['DOCUMENT_ROOT'] . "/inc/auth.php");

if (session_id() === "") session_start();

// ลบตำแหน่งก่อน destroy session
$kpWasCustomer = isset($_SESSION["id_status"]) && (int)$_SESSION["id_status"] === 2;
$u = "";
try { $u = kp_get_current_user(); } catch (Exception $e) {}
try { kp_clear_location_for_current_user(); } catch (Exception $e) {}

try {
  session_unset();
  session_destroy();
} catch (Exception $e) {}

// แนบชื่อ user เพื่อช่วยตรวจสอบ (ไม่เปิดเผยข้อมูลละเอียด)
header("Location:" . ($kpWasCustomer ? "/" : "/index.php?res=logout"));
exit;
