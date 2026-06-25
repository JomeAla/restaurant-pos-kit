const CACHE_NAME = 'pos-cache-v1';
const STATIC_ASSETS = [
    '/',
    '/build/assets/app-D7_KZq6U.js',
    '/build/assets/app-Rik_r6uX.css',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))))
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.url.includes('/api/')) return;
    event.respondWith(
        caches.match(event.request).then((cached) => cached || fetch(event.request).then((response) => {
            if (response.status === 200 && event.request.method === 'GET') {
                const clone = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
            }
            return response;
        }).catch(() => caches.match('/')))
    );
});
