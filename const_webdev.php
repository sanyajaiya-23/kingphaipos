<?php
define("DB_HOST", "localhost:3306");
define("DBNAME", "kingphaipos");
// ฐานต้นแบบสำหรับคัดลอก "โครงสร้างตาราง" (ไม่เอาข้อมูล) ครั้งแรก — ถ้าไม่มี ให้วาง _setup/schema.sql แทน
define("KP_SCHEMA_SOURCE_DB", "kingphai");
// ล็อกอินใช้บัญชีพนักงานจากฐานของระบบขายน้ำเดิม ("" = ปิด ใช้ tb_admin ของ POS อย่างเดียว) — ดู inc/auth_source.php
define("KP_AUTH_SOURCE_DB", "kingphai");
define("DB_USER", "root");
define("DB_PWD", "1234");
error_reporting(E_ALL ^ E_NOTICE);
?>