const CACHE_NAME = 'fieldcontrol-v1';

const STATIC_FILES = [
    '/',
    '/login',
    '/manifest.json'
];

self.addEventListener('install', event => {

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(STATIC_FILES))
    );

    self.skipWaiting();
});


self.addEventListener('activate', event => {

    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            );
        })
    );

    self.clients.claim();
});


self.addEventListener('fetch', event => {

    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(

        fetch(event.request)

            .then(response => {

                const responseClone = response.clone();

                caches.open(CACHE_NAME)
                    .then(cache => {
                        cache.put(
                            event.request,
                            responseClone
                        );
                    });

                return response;

            })

            .catch(() => {

                return caches.match(event.request);

            })

    );

});
