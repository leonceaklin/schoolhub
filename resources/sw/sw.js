importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.4.1/workbox-sw.js');
importScripts("https://cdn.onesignal.com/sdks/OneSignalSDKWorker.js");
importScripts('https://analytics.zebrapig.com/offline-service-worker.js');
matomoAnalytics.initialize();

workbox.setConfig({
  debug: false,
});

const {
  cacheableResponse: { CacheableResponsePlugin },
  expiration: { ExpirationPlugin },
  routing: { registerRoute },
  strategies: { CacheFirst, StaleWhileRevalidate },
} = workbox;


// cache name
workbox.core.setCacheNameDetails({
    prefix: 'schoolhub-cache',
    precache: 'precache',
    runtime: 'runtime',
  });

  workbox.routing.registerRoute(
      new RegExp('\.webmanifest$'),
      new StaleWhileRevalidate({
          cacheName: 'main-cache',
      })
  );

// runtime cache
workbox.routing.registerRoute(
    new RegExp('\.css$'),
    new StaleWhileRevalidate({
        cacheName: 'css-cache',
        plugins: [
            new ExpirationPlugin({
                maxAgeSeconds: 60 * 60 * 24 * 7, // cache for one week
            })
        ]
    })
);

workbox.routing.registerRoute(
    new RegExp('\.js$'),
    new StaleWhileRevalidate({
        cacheName: 'js-cache',
        plugins: [
            new ExpirationPlugin({
                maxAgeSeconds: 60 * 60 * 24 * 7, // cache for one week
            })
        ]
    })
);

workbox.routing.registerRoute(
    new RegExp('\.(png|svg|jpg|jpeg)$'),
    new CacheFirst({
        cacheName: 'image-cache',
        plugins: [
            new ExpirationPlugin({
                maxAgeSeconds: 60 * 60 * 24 * 7,
            })
        ]
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://content.zebrapig.com'),
    new StaleWhileRevalidate({
        cacheName: 'content-cache',
        plugins: [
            new ExpirationPlugin({
                maxAgeSeconds: 60 * 60 * 24 * 7,
            })
        ]
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://cdnjs.cloudflare.com'),
    new CacheFirst({
        cacheName: 'cdn-cache',
        cacheExpiration: {
          maxAgeSeconds: 60 * 60 * 24 * 7 * 4, // cache for four weeks
        }
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://unpkg.com'),
    new CacheFirst({
        cacheName: 'cdn-cache',
        cacheExpiration: {
          maxAgeSeconds: 60 * 60 * 24 * 7 * 4, // cache for four weeks
        }
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://cdn.jsdelivr.net'),
    new CacheFirst({
        cacheName: 'cdn-cache',
        cacheExpiration: {
          maxAgeSeconds: 60 * 60 * 24 * 7 * 4, // cache for four weeks
        }
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://fonts.googleapis.com'),
    new CacheFirst({
        cacheName: 'cdn-cache',
        cacheExpiration: {
          maxAgeSeconds: 60 * 60 * 24 * 7 * 4, // cache for four weeks
        }
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://fonts.gstatic.com'),
    new CacheFirst({
        cacheName: 'font-cache',
        cacheExpiration: {
          maxAgeSeconds: 60 * 60 * 24 * 7 * 4, // cache for four weeks
        }
    })
);

workbox.routing.registerRoute(
    new RegExp('/.*$'),
    new StaleWhileRevalidate({
        cacheName: 'main-cache',
    })
);
