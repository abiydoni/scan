const CACHE_NAME = "Auto-Scan-jimpitan-v1.5";
const urlsToCache = [
  "/",
  // "login.php",
  "manifest.json",
  "assets/audio/interface.wav",
  "assets/image/loading.gif",
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log("Opened cache");
      return cache.addAll(urlsToCache);
    })
  );
});

self.addEventListener("fetch", (event) => {
  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request);
    })
  );
});
