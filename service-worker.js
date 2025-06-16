const CACHE_NAME = "Auto-Scan-jimpitan-v1.5";
const urlsToCache = [
  "/",
  // "login.php",
  "manifest.json",
  "assets/audio/interface.wav",
  "assets/image/loading.gif",
];

// Install event
self.addEventListener("install", (event) => {
  console.log("[SW] Installing Service Worker...");
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log("[SW] Caching app shell");
      return cache.addAll(urlsToCache);
    })
  );
});

// Activate event
self.addEventListener("activate", (event) => {
  console.log("[SW] Activating Service Worker...");
  event.waitUntil(
    caches.keys().then((keyList) =>
      Promise.all(
        keyList.map((key) => {
          if (key !== CACHE_NAME) {
            console.log("[SW] Removing old cache:", key);
            return caches.delete(key);
          }
        })
      )
    )
  );
});

// Fetch event
self.addEventListener("fetch", (event) => {
  console.log("[SW] Fetching:", event.request.url);
  event.respondWith(
    caches
      .match(event.request)
      .then((response) => response || fetch(event.request))
  );
});
