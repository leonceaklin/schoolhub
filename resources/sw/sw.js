importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.4.1/workbox-sw.js');
importScripts('https://analytics.zebrapig.com/offline-service-worker.js');
matomoAnalytics.initialize();


workbox.skipWaiting();
workbox.clientsClaim();

workbox.setConfig({
  debug: false,
});

// cache name
workbox.core.setCacheNameDetails({
    prefix: 'schoolhub-cache',
    precache: 'precache',
    runtime: 'runtime',
  });

  workbox.routing.registerRoute(
      new RegExp('/$'),
      workbox.strategies.staleWhileRevalidate({
          cacheName: 'main-cache',
      })
  );

  workbox.routing.registerRoute(
      new RegExp('\.webmanifest$'),
      workbox.strategies.staleWhileRevalidate({
          cacheName: 'main-cache',
      })
  );

// runtime cache
workbox.routing.registerRoute(
    new RegExp('\.css$'),
    workbox.strategies.staleWhileRevalidate({
        cacheName: 'css-cache',
        plugins: [
            new workbox.expiration.Plugin({
                maxAgeSeconds: 60 * 60 * 24 * 7, // cache for one week
            })
        ]
    })
);

workbox.routing.registerRoute(
    new RegExp('\.js$'),
    workbox.strategies.staleWhileRevalidate({
        cacheName: 'js-cache',
        plugins: [
            new workbox.expiration.Plugin({
                maxAgeSeconds: 60 * 60 * 24 * 7, // cache for one week
            })
        ]
    })
);

workbox.routing.registerRoute(
    new RegExp('\.(png|svg|jpg|jpeg)$'),
    workbox.strategies.cacheFirst({
        cacheName: 'image-cache',
        plugins: [
            new workbox.expiration.Plugin({
                maxAgeSeconds: 60 * 60 * 24 * 7,
            })
        ]
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://content.zebrapig.com'),
    workbox.strategies.staleWhileRevalidate({
        cacheName: 'content-cache',
        plugins: [
            new workbox.expiration.Plugin({
                maxAgeSeconds: 60 * 60 * 24 * 7,
            })
        ]
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://cdnjs.cloudflare.com'),
    workbox.strategies.cacheFirst({
        cacheName: 'cdn-cache',
        cacheExpiration: {
          maxAgeSeconds: 60 * 60 * 24 * 7 * 4, // cache for four weeks
        }
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://unpkg.com'),
    workbox.strategies.cacheFirst({
        cacheName: 'cdn-cache',
        cacheExpiration: {
          maxAgeSeconds: 60 * 60 * 24 * 7 * 4, // cache for four weeks
        }
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://cdn.jsdelivr.net'),
    workbox.strategies.cacheFirst({
        cacheName: 'cdn-cache',
        cacheExpiration: {
          maxAgeSeconds: 60 * 60 * 24 * 7 * 4, // cache for four weeks
        }
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://fonts.googleapis.com'),
    workbox.strategies.cacheFirst({
        cacheName: 'cdn-cache',
        cacheExpiration: {
          maxAgeSeconds: 60 * 60 * 24 * 7 * 4, // cache for four weeks
        }
    })
);

workbox.routing.registerRoute(
    new RegExp('^https://fonts.gstatic.com'),
    workbox.strategies.cacheFirst({
        cacheName: 'font-cache',
        cacheExpiration: {
          maxAgeSeconds: 60 * 60 * 24 * 7 * 4, // cache for four weeks
        }
    })
);

workbox.precaching.precacheAndRoute([]);
