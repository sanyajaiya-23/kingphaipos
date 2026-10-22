// Service Worker แบบเบา ๆ สำหรับ PWA (ช่วยให้ติดตั้ง/ทำงานเสถียรขึ้น)
const CACHE_NAME = "kpos-v1";

const PRECACHE_URLS = [
  "/manifest.json?v=4",
  "/lib/bootstrap-4.5.2-dist/css/bootstrap.min.css",
  "/lib/font-awesome-4.7.0/css/font-awesome.min.css",
  "/css/etc.css?v=177",
  "/lib/jquery-3.5.1.min.js",
  "/json/viewport.js?v=3",
  "/json/pwa.js?v=1",
  "/json/sheet.js?v=3.5",
  "/json/select.js?v=2.49",
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS)).catch(() => {})
  );
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.map((k) => (k === CACHE_NAME ? null : caches.delete(k))))
    )
  );
  self.clients.claim();
});

self.addEventListener("fetch", (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // เฉพาะ same-origin เท่านั้น
  if (url.origin !== self.location.origin) return;

  // ไม่แตะ request ที่ไม่ใช่ GET เลย (เช่น POST อัพโหลดรูป/บันทึกข้อมูล) — ปล่อยให้เบราว์เซอร์จัดการเอง
  // สาเหตุ (พบ 2026-09-14): มือถือบางรุ่น/บางเบราว์เซอร์ ตัว body ของ POST request (เช่นไฟล์รูปที่แนบ)
  // ส่งต่อผ่าน fetch(event.request) ใน service worker ไม่สำเร็จ (ตัว body หาย/พังระหว่างทาง)
  // ทำให้ request ไปไม่ถึงเซิร์ฟเวอร์เลยแบบเงียบๆ (ทดสอบแล้ว: อัพจากคอมสำเร็จ แต่อัพจากมือถือ
  // ไม่มีแม้แต่ request ไปถึง PHP) ทางแก้ที่ปลอดภัยสุดคือไม่ให้ service worker เข้าไปยุ่งกับ
  // request ที่ไม่ใช่ GET เลย (ของแบบนี้ไม่ต้องแคชอยู่แล้วด้วย)
  if (req.method !== "GET") return;

  // ไม่แคช API แบบ dynamic (กันข้อมูล marker ค้าง/เพี้ยน)
  // หมายเหตุ: ไฟล์ .js ใน /json/ ยังแคชได้ปกติ แต่ .php ไม่ควรแคช
  if (url.pathname.startsWith("/json/") && url.pathname.endsWith(".php")) {
    event.respondWith(fetch(req).catch(() => Response.error()));
    return;
  }
  // ไม่แคชหน้า PHP ที่ขึ้นกับ session/สิทธิ์ (กัน admin/driver เห็นหน้าค้างผิดสิทธิ์)
  if (url.pathname === "/index2.php" || url.pathname === "/select_page.php" || url.pathname === "/index.php") {
    event.respondWith(fetch(req).catch(() => Response.error()));
    return;
  }
  // ไม่แคช PHP ทั่วไป (เช่น logout.php)
  if (url.pathname.endsWith(".php")) {
    event.respondWith(fetch(req).catch(() => Response.error()));
    return;
  }

  event.respondWith(
    fetch(req)
      .then((res) => {
        // แคชเฉพาะ GET
        if (req.method === "GET") {
          const copy = res.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(req, copy)).catch(() => {});
        }
        return res;
      })
      .catch(() => caches.match(req).then((cached) => cached || Response.error()))
  );
});
