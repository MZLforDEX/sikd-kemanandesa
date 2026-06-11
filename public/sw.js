const CACHE_NAME = 'sikd-cache-v2';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/images/icon-192.png',
    '/images/icon-512.png',
];

// Install Event - cache initial assets
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate Event - clear old caches
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch Event - Stale-While-Revalidate caching strategy
self.addEventListener('fetch', function(event) {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests
    if (request.method !== 'GET') return;

    // Do NOT cache live polling, switch roles, or other dynamic application routes
    if (
        url.pathname.includes('/realtime-updates') ||
        url.pathname.includes('/simulasi/switch') ||
        url.pathname.includes('/logout') ||
        url.pathname.includes('/notifications') ||
        request.headers.get('accept')?.includes('application/json')
    ) {
        return; // Let it bypass service worker cache
    }

    // Stale-While-Revalidate for JS, CSS, CDN dependencies, and fonts
    if (
        url.hostname.includes('unpkg.com') ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com') ||
        url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.css') ||
        url.pathname.includes('/build/') || // Vite assets
        url.pathname.includes('/images/')
    ) {
        event.respondWith(
            caches.open(CACHE_NAME).then(function(cache) {
                return cache.match(request).then(function(cachedResponse) {
                    const fetchPromise = fetch(request).then(function(networkResponse) {
                        // Cache the newly fetched response
                        if (networkResponse.status === 200) {
                            cache.put(request, networkResponse.clone());
                        }
                        return networkResponse;
                    }).catch(() => {
                        // Silence fetch errors for background sync
                    });
                    
                    // Return cached response instantly if present, else wait for network
                    return cachedResponse || fetchPromise;
                });
            })
        );
        return;
    }

    // Network-First with Cache Fallback for general HTML page requests (ensuring offline visibility)
    if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then(function(networkResponse) {
                    // Update cache with the latest HTML page
                    if (networkResponse.status === 200) {
                        caches.open(CACHE_NAME).then(function(cache) {
                            cache.put(request, networkResponse.clone());
                        });
                    }
                    return networkResponse;
                })
                .catch(function() {
                    // Fallback to cache if network is offline/poor
                    return caches.match(request).then(function(cachedResponse) {
                        return cachedResponse || caches.match('/');
                    });
                })
        );
    }
});

self.addEventListener('push', function(event) {
    if (!event.data) return;

    try {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: data.icon || '/images/icon-192.png',
            badge: '/images/icon-192.png',
            vibrate: [200, 100, 200],
            data: {
                url: data.url || '/'
            }
        };

        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    } catch (e) {
        // Fallback for plain text push data
        const text = event.data.text();
        event.waitUntil(
            self.registration.showNotification('Pemberitahuan SIKD', {
                body: text,
                icon: '/images/icon-192.png',
                badge: '/images/icon-192.png',
                data: { url: '/' }
            })
        );
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    const url = event.notification.data.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
