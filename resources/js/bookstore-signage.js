import Vue from 'vue'
import vuetify from "./plugins/vuetify.js"

import bookstoreSignage from "./components/bookstore/Signage"

const app = new Vue({
    el: "#app",
    vuetify,
    components: {
      bookstoreSignage
    }
});


if("serviceWorker" in navigator){
  navigator.serviceWorker.register("sw.js").then(function(registration){
    console.log("Service Worker registriert");
  }).catch(function(error){
    console.log("Service Worker nicht registriert. Fehler: ",error);
  });
}


var darkModeQuery = window.matchMedia("(prefers-color-scheme: dark)");


app.$vuetify.theme.dark = darkModeQuery.matches

darkModeQuery.addEventListener( "change", (e) => {
    app.$vuetify.theme.dark = e.matches
})

var mouseHideTimeout = null
document.addEventListener('mousemove', () => {
  document.body.style.cursor = "auto !important"
  mouseHideTimeout = setTimeout(() => {
    hideMouse()
  }, 2000)
})

function hideMouse(){
  document.body.style.cursor = "none"
}

hideMouse()

window.app = app
