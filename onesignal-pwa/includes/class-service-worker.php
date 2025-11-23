<?php
/**
 * Service Worker Management Class
 *
 * @package OneSignal_PWA
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OneSignal PWA Service Worker Class
 */
class OneSignal_PWA_Service_Worker {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'serve_service_worker'));
        add_action('wp_footer', array($this, 'register_service_worker'), 1);
    }

    /**
     * Add rewrite rules for service worker
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^OneSignalSDKWorker\.js$', 'index.php?onesignal_pwa_sw=1', 'top');
        add_rewrite_rule('^service-worker\.js$', 'index.php?onesignal_pwa_sw=1', 'top');
        add_rewrite_tag('%onesignal_pwa_sw%', '([^&]+)');
    }

    /**
     * Serve service worker file
     */
    public function serve_service_worker() {
        if (get_query_var('onesignal_pwa_sw')) {
            header('Content-Type: application/javascript; charset=utf-8');
            header('Service-Worker-Allowed: /');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: no-cache, no-store, must-revalidate');

            echo $this->generate_service_worker();
            exit;
        }
    }

    /**
     * Register service worker in frontend
     */
    public function register_service_worker() {
        if (!OneSignal_PWA_Settings::get('enable_service_worker', true)) {
            return;
        }

        $app_id = OneSignal_PWA_Settings::get('app_id');
        $safari_web_id = OneSignal_PWA_Settings::get('safari_web_id');
        ?>
        <script>
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js', {
                    scope: '/'
                }).then(function(registration) {
                    console.log('Service Worker registered:', registration.scope);

                    // Check for updates
                    registration.update();
                }).catch(function(error) {
                    console.error('Service Worker registration failed:', error);
                });
            });
        }

        // Initialize OneSignal
        <?php if ($app_id): ?>
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(function(OneSignal) {
            OneSignal.init({
                appId: "<?php echo esc_js($app_id); ?>",
                <?php if ($safari_web_id): ?>
                safari_web_id: "<?php echo esc_js($safari_web_id); ?>",
                <?php endif; ?>
                allowLocalhostAsSecureOrigin: <?php echo wp_is_local_environment() ? 'true' : 'false'; ?>,
                notifyButton: {
                    enable: <?php echo OneSignal_PWA_Settings::get('show_notify_button', false) ? 'true' : 'false'; ?>
                },
                welcomeNotification: {
                    disable: <?php echo OneSignal_PWA_Settings::get('welcome_notification', true) ? 'false' : 'true'; ?>
                }
            });

            // Track subscription
            OneSignal.on('subscriptionChange', function(isSubscribed) {
                console.log('Subscription changed:', isSubscribed);

                if (isSubscribed) {
                    OneSignal.getUserId(function(userId) {
                        // Send subscription to backend
                        fetch('<?php echo esc_url(rest_url('onesignal-pwa/v1/subscribers')); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                            },
                            body: JSON.stringify({
                                player_id: userId,
                                user_id: <?php echo get_current_user_id(); ?>
                            })
                        });
                    });
                }
            });

            // Track notification clicks
            OneSignal.on('notificationDisplay', function(event) {
                console.log('Notification displayed:', event);
            });
        });
        <?php endif; ?>
        </script>
        <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
        <?php
    }

    /**
     * Generate service worker code
     *
     * @return string
     */
    public function generate_service_worker() {
        $cache_version = OneSignal_PWA_Settings::get('cache_version', '1.0');
        $cache_strategy = OneSignal_PWA_Settings::get('cache_strategy', 'network_first');
        $offline_page = OneSignal_PWA_Settings::get('offline_page', '/offline/');

        $sw_code = <<<JS
// OneSignal PWA Service Worker
// Version: {$cache_version}

const CACHE_VERSION = '{$cache_version}';
const CACHE_NAME = 'onesignal-pwa-v' + CACHE_VERSION;
const OFFLINE_URL = '{$offline_page}';

// Import OneSignal service worker
importScripts('https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.sw.js');

// Files to cache on install
const STATIC_CACHE_URLS = [
    '/',
    OFFLINE_URL,
];

// Cache strategies
const CACHE_FIRST = [
    /\.(?:png|jpg|jpeg|svg|gif|webp|ico|woff|woff2|ttf|eot)$/
];

const NETWORK_FIRST = [
    /\/wp-admin\//,
    /\/wp-json\//,
    /\?/
];

const STALE_WHILE_REVALIDATE = [
    /\.(?:js|css)$/
];

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('Service Worker installing...');

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Caching static assets');
                return cache.addAll(STATIC_CACHE_URLS);
            })
            .then(() => self.skipWaiting())
            .catch(error => {
                console.error('Cache installation failed:', error);
            })
    );
});

// Activate event - clean old caches
self.addEventListener('activate', event => {
    console.log('Service Worker activating...');

    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames
                        .filter(cacheName => cacheName.startsWith('onesignal-pwa-') && cacheName !== CACHE_NAME)
                        .map(cacheName => {
                            console.log('Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        })
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch event - handle requests
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip chrome-extension and other special schemes
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // Determine caching strategy
    if (matchesPatterns(url, CACHE_FIRST)) {
        event.respondWith(cacheFirst(request));
    } else if (matchesPatterns(url, NETWORK_FIRST)) {
        event.respondWith(networkFirst(request));
    } else if (matchesPatterns(url, STALE_WHILE_REVALIDATE)) {
        event.respondWith(staleWhileRevalidate(request));
    } else {
        event.respondWith(networkFirst(request));
    }
});

// Helper: Check if URL matches patterns
function matchesPatterns(url, patterns) {
    return patterns.some(pattern => pattern.test(url.pathname) || pattern.test(url.href));
}

// Cache First Strategy
async function cacheFirst(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error('Fetch failed:', error);
        return new Response('Offline', { status: 503 });
    }
}

// Network First Strategy
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }

        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            const offlinePage = await caches.match(OFFLINE_URL);
            if (offlinePage) {
                return offlinePage;
            }
        }

        return new Response('Offline', { status: 503 });
    }
}

// Stale While Revalidate Strategy
async function staleWhileRevalidate(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);

    const fetchPromise = fetch(request).then(response => {
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    }).catch(() => cached);

    return cached || fetchPromise;
}

// Background Sync
self.addEventListener('sync', event => {
    if (event.tag === 'sync-analytics') {
        event.waitUntil(syncAnalytics());
    }
});

async function syncAnalytics() {
    // Implement analytics sync logic
    console.log('Syncing analytics...');
}

// Message handling
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

console.log('Service Worker loaded successfully');
JS;

        return apply_filters('onesignal_pwa_service_worker', $sw_code);
    }

    /**
     * Clear all caches
     *
     * @return bool
     */
    public function clear_caches() {
        // This would need to be handled client-side
        return true;
    }

    /**
     * Update service worker version
     *
     * @return bool
     */
    public function update_version() {
        $version = OneSignal_PWA_Settings::get('cache_version', '1.0');
        $parts = explode('.', $version);
        $parts[count($parts) - 1]++;
        $new_version = implode('.', $parts);

        return OneSignal_PWA_Settings::set('cache_version', $new_version);
    }
}
