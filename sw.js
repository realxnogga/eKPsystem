// The name of the cache
const CACHE_NAME = 'my-site-cache-v1';

// Files to cache
const urlsToCache = [
    // PHP files
    'user_dashboard.php',
    'user_add_complaint.php',
    'user_edit_complaint.php',
    'user_archives.php',
    'user_lupon.php',
    'user_complaints.php',
    'user_offline_add_complaint.php',
    'user_report.php',
    'user_signed_documents.php',
    'user_logs.php',
    'archive_complaint.php',
    'security_handler.php'

];

// Install event to precache resources
self.addEventListener('install', async (event) => {
    event.waitUntil(precache());
});
  
async function precache() {
    const cache = await caches.open(CACHE_NAME);
    for (const url of urlsToCache) { // Change from URLS_TO_CACHE to urlsToCache
        try {
            console.log(`Attempting to cache: ${url}`);
            await cache.add(url);
            console.log(`Successfully cached: ${url}`);
        } catch (error) {
            console.error(`Failed to cache: ${url}`, error);
        }
    }
    console.log('Precache complete.');
}
  
// Fetch event to serve cached resources
self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline_add_handler.php'); // Fallback for offline
            })
    );
});
  
// Activate event to clean up old caches
self.addEventListener('activate', (event) => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
