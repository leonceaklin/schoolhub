/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window.Vue = require('vue').default;

import VueRouter from 'vue-router'
import router from './router'

import vuetify from './plugins/vuetify'
import i18n from './plugins/i18n'
import store from './plugins/store'

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('index', require('./Index.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

 Vue.use(VueRouter)


export const app = new Vue({
    el: '#app',
    i18n,
    router,
    vuetify,
    store,

    data(){
      return {
        version: this.$store.state.version,
        latestVersion: this.$store.state.latestVersion,
      }
    },
    async mounted(){
      this.installServiceWorker()
      this.checkVersion()
      this.startFetchIntervals()
      this.localStorageMigrations()
    },

    methods: {
      async startFetchIntervals(){
        await this.$store.dispatch('fetchSchoolSystemData');
        this.$store.dispatch('startSchoolSystemFetchInterval');
      },

      checkVersion(){
        //Version Check
        if(this.version != this.latestVersion){
          this.$store.dispatch('logout')
          this.$store.dispatch('setVersion', this.latestVersion)
        }
      },

      localStorageMigrations(){
        //Migrate old installations
        window.localStorage.removeItem("personalInformation")
        window.localStorage.removeItem("username")
        window.localStorage.removeItem("password")
      },

      installServiceWorker(){
        if("serviceWorker" in navigator){
          navigator.serviceWorker.register("sw.js").then(function(registration){
            console.log("Service Worker registriert");
          }).catch(function(error){
            console.log("Service Worker nicht registriert. Fehler: ",error);
          });
        }
      },

    }
});

var darkModeQuery = window.matchMedia("(prefers-color-scheme: dark)");


app.$vuetify.theme.dark = darkModeQuery.matches

darkModeQuery.addEventListener( "change", (e) => {
    app.$vuetify.theme.dark = e.matches
})

window.app = app
