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
      if(this.version != this.latestVersion){
        this.$store.dispatch('logout')
        this.$store.dispatch('setVersion', this.latestVersion)
      }
      await this.$store.dispatch('fetchSchoolSystemData');

      this.$store.dispatch('startSchoolSystemFetchInterval');

      //Migrate old installations
      window.localStorage.removeItem("personalInformation")
      window.localStorage.removeItem("username")
      window.localStorage.removeItem("password")
    },
    computed: {

    },
    methods: {
      setNow(){
        this.now = new Date()
      },

      calculateBirthday(){
        if(this.schoolClass == null){
          this.birthdayToday = [];
          return false;
        }

        var students = this.schoolClass.students
        var today = new Date();

        var birthdayToday = [];

        if(!students){
          this.birthdayToday = [];
          return false;
        }

        for(var student of students){
          var birth = new Date(student.birth.replaceAll("-", "/"))
          if(birth.getDate() == today.getDate() && birth.getMonth() == today.getMonth()){
            var age = today.getYear() - birth.getYear()
            birthdayToday.push({age: age, ...student})
          }
        }

        this.birthdayToday = birthdayToday
      },
      getGradesFromSubject(subject){
        this.grades = []
        this.upcomingGrades = []
        for(var grade of subject.grades){
          var date = new Date(grade.date)
          if(grade.value && grade.weight){
            this.grades.push({
              date: date.toLocaleDateString("de-DE"),
              name: grade.name,
              value: grade.value,
              weight: grade.weight,
              uid: Math.floor(Math.random()*1000000)
            })
          }
          else if(grade.weight && date > new Date()){
            this.upcomingGrades.push({
              date: date.toLocaleDateString("de-DE"),
              name: grade.name,
              value: grade.value,
              weight: grade.weight,
              uid: Math.floor(Math.random()*1000000)
            })
          }
        }
      },
      checkInstallation(){
        if(this.getDisplayMode() != "browser"){
          return;
        }

        window.addEventListener('beforeinstallprompt', (e) => {
          e.preventDefault();
          this.installationPrompt = e;
          this.installationBanner = true
        });

        if(navigator.userAgent.indexOf("iPhone") !== -1 || navigator.userAgent.indexOf("iPad") !== -1 || (navigator.userAgent.match(/Mac/) && navigator.maxTouchPoints && navigator.maxTouchPoints > 2)){
          setTimeout(() => {
            this.installationBanner = true
          }, 10000)
        }
      },
      installApp(){
        this.installationPrompt.prompt();
        this.installationBanner = false;
      },
      getDisplayMode(){
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
        if(document.referrer.startsWith('android-app://')) {
          return 'twa';
        }
        else if (navigator.standalone || isStandalone) {
          return 'standalone';
        }
        return 'browser';
      },
    },
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

window.app = app
