<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<title>SchoolHub</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;700;900&family=Roboto+Mono&display=swap" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css" crossorigin="anonymous">
	<link href="{{ url('/css/app.css') }}" rel="stylesheet" type="text/css" />

	<link rel="apple-touch-icon" sizes="180x180" href="/icon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/icon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/icon/site.webmanifest">
	<link rel="mask-icon" href="/icon/safari-pinned-tab.svg" color="#2977ff">
	<link rel="shortcut icon" href="/icon/favicon.ico">
	<meta name="msapplication-TileColor" content="#2977ff">
	<meta name="msapplication-config" content="/icon/browserconfig.xml">
  <meta name="theme-color" media="(prefers-color-scheme: light)" content="#ffffff">
  <meta name="theme-color" media="(prefers-color-scheme: dark)"  content="#1e1e1e">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<meta name="author" content="Léonce Aklin">
	<meta name="description" content="Notenrechner, Terminplan, Absenzen und Bookstore.">


	<!-- Matomo -->
	<script type="text/javascript">
		var _paq = window._paq = window._paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//analytics.zebrapig.com/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '102']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();

	</script>
	<!-- End Matomo Code -->

</head>

<body>
	<div id="app">
		<index/>
  </div>
	<script>window.baseUrl = "{{ url("/") }}"</script>
    <script src="https://unpkg.com/axios/dist/axios.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-google-charts/dist/vue-google-charts.browser.js" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/@ericblade/quagga2/dist/quagga.min.js" crossorigin="anonymous"></script>
    <script src="{{ url("/js/app.js") }}"></script>

		<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
		<script>
		  window.OneSignal = window.OneSignal || [];
		  OneSignal.push(function() {
		    OneSignal.init({
		      appId: "61dd7782-d16b-4de4-8d21-c12f2823897c",
		      safari_web_id: "",
		      notifyButton: {
		        enable: true,
		      },
		    });
		  });
		</script>
  </body>
</html>
