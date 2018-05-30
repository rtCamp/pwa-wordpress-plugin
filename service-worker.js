const pwa_vars = pwa_vars_json;
const admin_regex = new RegExp( pwa_vars.admin_url );
const site_regex = new RegExp( pwa_vars.site_url );
const cache_ver = pwa_vars.ver ? pwa_vars.ver : 0.1;
const CACHE_NAME = `pwaready-${cache_ver}`;
const urlsToCache = [];


self.addEventListener('install', event => {
	console.log('[SW] Install');
	// Perform install steps
	// Pre cache urls
	self.skipWaiting();
	return event.waitUntil(
		caches.open(CACHE_NAME).then(cache =>
			cache.addAll(urlsToCache).then(() => {
				console.log('Worker Install Complete');
			})
		)
	);
});

self.addEventListener('activate', function(event) {
	event.waitUntil(
		caches.keys().then(function(cacheNames) {
			return Promise.all(
				cacheNames.map(function(cacheName) {
					return caches.delete(cacheName);
				})
			)
		}).then(function() {
			return self.clients.claim();
		})
	);
});

// On fetch, try the cache but if there's a miss try loading the content
self.addEventListener('fetch', function (event) {

	if ( shouldCacheRequest( event.request ) ) {
		if ( isExternalAsset( event.request.url ) ) {
			event.request.mode = 'no-cors';
		}

		// @todo Update to workbox alpha version and use workbox class.
		event.respondWith(
			caches.open( dynamic_cache_name ).then(function(cache) {
				return cache.match(event.request).then(function(response) {
					var fetchPromise = fetch(event.request).then(function(networkResponse) {
						cache.put(event.request, networkResponse.clone());
						return networkResponse;
					});
					return response || fetchPromise;
				})
			})
		);
	}
});

function isExternalAsset( url ) {
	return ! site_regex.test( url ) && ! url.match(/^\/[^\/]/);
}

// having this function allows us to shortcut checking the cache,
// but we also have shouldCacheResponse which is able to look more deeply at what was returned.
// so it's possible that this should go away - I don't know how expensive cache checks are on most browsers.
function shouldCacheRequest( request ) {
	// if the request is for a wp-admin asset, or made from within wp-admin, ignore!
	if ( admin_regex.test( request.url ) || admin_regex.test( request.referrer ) || request.url === pwa_vars.sw_config_url ) {
		return false;
	}

	// @todo check for customizer request

	if ( request.method !== 'GET' ) {
		return false;
	}

	// get file extension using awful hackery, since we don't know response mime type before fetching
	var extension = request.url.split(/\#|\?/)[0].split('/').pop().split('.').pop();

	if ( extension.length > 0 && ! ['js', 'css', 'html', 'woff2', 'jpg', 'png'].includes( extension ) ) {
		return false;
	}

	return true;
}
