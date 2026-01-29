self.addEventListener('install', event => {
  self.skipWaiting();
});
self.addEventListener('activate', event => {
  event.waitUntil(clients.claim());
});
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.open('v1').then(cache =>
      cache.match(event.request).then(response =>
        response || fetch(event.request).then(res => {
          cache.put(event.request, res.clone());
          return res;
        })
      )
    )
  );
});
