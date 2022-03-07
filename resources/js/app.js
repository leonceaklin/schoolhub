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
        school: '',
        apiUrl: "/api",
        version: "1.0.2",
        lastVersion: "",
        absencePeriods: [],
        events: [],
        user: {},
        schoolClass: {},
        credentialsToken: null,
        auth: null,

        subjects: [],

        grades: [],
        upcomingGrades: [],
        aimedAvg: 6,
        nextWeight: 1,

        birthdayToday: [],

        now: new Date(),

        fetchingData: false,
        loginError: false,

        fetchInterval: null,

        acceptConditions: false,

        installationBanner: false,
        installationPrompt: null,


        eventDetailDialog: false,
        focusedEvent: null,
      }
    },
    async mounted(){
      // Show App
      document.getElementById("app").style.display = "block"
      this.hydrate()

      if(this.version != this.lastVersion){
        this.logout()
        this.lastVersion = this.version
      }

      this.calculateBirthday()

      //Migrate old installations
      window.localStorage.removeItem("personalInformation")
      window.localStorage.removeItem("username")
      window.localStorage.removeItem("password")

      this.fetchData()
      this.checkInstallation()

      if(this.subjects.length == 0){
        this.fetchSchools()
      }

      setInterval(() => {
        console.log(this.$refs.eventsCalendar)
        this.setNow()
        this.$refs.eventsCalendar.updateTimes()
      }, 60 * 1000)

      this.setFetchInterval()
    },
    computed: {
      upcomingEvents(){
        var now = this.now
        var filtered = this.events.filter((e) => {
          return new Date(e.start.replaceAll("-", "/")) >= now
        })

        return filtered.sort((a,b) => {
          if(new Date(a.start.replaceAll("-", "/")) > new Date(b.start.replaceAll("-", "/"))){
            return 1
          }

          if(new Date(a.start.replaceAll("-", "/")) < new Date(b.start.replaceAll("-", "/"))){
            return -1
          }

          return 0
        })
      },
      bookstoreAvailable(){
        return this.subjects.length > 0 && this.school == "gymli"
      },

      gradesSortedByDate(){
        var grades = []
        for(var subject of this.subjects){
          if(subject.grades){
            for(var grade of subject.grades){
              if(grade.value && grade.date){
                var g = {
                  subject: subject,
                  ... grade
                }
                grades.push(g)
              }
            }
          }
        }

        grades = grades.sort((a, b) => {
          if(new Date(a.date) > new Date(b.date)){
            return -1
          }
          else if(new Date(a.date) < new Date(b.date)){
            return 1
          }
          return 0
        })

        return grades
      },
      eventsCalendarNowY(){
        if(!this.$refs.eventsCalendar){
          return "-10px"
        }
        return this.$refs.eventsCalendar.timeToY(this.$refs.eventsCalendar.times.now) + 'px'
      },
      loggedIn(){
        if(!this.credentialsToken){
          return false
        }
        return true
      }
    },
    methods: {
      setFetchInterval(){
        if(this.fetchingData){
          return false
        }
        if(this.subjects.length > 0){
       this.fetchInterval = setInterval(() => {
         this.fetchData()
       }, 120 * 1000)
       }
      },
      setNow(){
        this.now = new Date()
      },
      logout(){
        this.subjectsDialog = false
        this.username = ""
        this.password = ""
        this.user = {}
        this.schoolClass = {}
        this.credentialsToken = null
        this.subjects = []
        this.grades = []
        this.upcomingGrades = []
        this.absencePeriods = []
        this.acceptConditions = false
        this.events = []
        this.fetchingData = false

        window.app.auth = null
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

      async login(){
        this.username = this.username.split("@")[0]
        this.fetchingData = true
        var auth = btoa(this.username+":"+this.password);

        var response = await axios.get(`${this.apiUrl}/sal/${this.school}/login`,{headers: {"Authorization": "Basic "+auth}})
        if(response.data.data && response.data.data.token){
          this.credentialsToken = response.data.data.token
          this.subjectsDialog = false
          this.loginError = false
          this.username = ""
        }
        else{
          this.loginError = true
        }
        this.fetchingData = false
        this.password = ""
      },

      async fetchData(){
        if(this.fetchingData){
          return false
        }
        if(!this.school){
          return
        }

        if(!this.acceptConditions){
          this.logout()
          return
        }

        if(!this.credentialsToken){
          await this.login();
        }

        this.fetchingData = true
        var auth = this.credentialsToken

        var response = await axios.get(`${this.apiUrl}/sal/${this.school}/subjects`,{headers: {"Authorization": "Bearer "+auth}})

        if(response.data.data && response.data.data.subjects){
          this.subjects = response.data.data.subjects

          _paq.push(['trackGoal', 1]);

          this.setFetchInterval()

          //Absences
          var response = await axios.get(`${this.apiUrl}/sal/${this.school}/absence_information`,{headers: {"Authorization": "Bearer "+auth}})
          if(response.data.data && response.data.data.absence_periods){
            this.absencePeriods = response.data.data.absence_periods
          }

          //Personal Information
          var response = await axios.get(`${this.apiUrl}/sal/${this.school}/user`,{headers: {"Authorization": "Bearer "+auth}})
          if(response.data.data && response.data.data && this.loggedIn){
            this.user = response.data.data
          }

          //School Class
          var response = await axios.get(`${this.apiUrl}/sal/${this.school}/class`,{headers: {"Authorization": "Bearer "+auth}})
          if(response.data.data && response.data.data && this.loggedIn){
            this.schoolClass = response.data.data
          }

          //Events
          var response = await axios.get(`${this.apiUrl}/sal/${this.school}/events`,{headers: {"Authorization": "Bearer "+auth}})
          if(response.data.data && response.data.data.events && this.loggedIn){
            this.events = response.data.data.events
          }
        }
        else{
          this.logout()
        }
        this.fetchingData = false
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
      setWeightToUpcomingGradesSum(){
        var sum = 0
        for(var grade of this.upcomingGrades){
          sum += grade.weight
        }
        this.nextWeight = sum
      },
      hydrate(){
        var lastVersion = window.localStorage.getItem("version")
        if(lastVersion){
          this.lastVersion = lastVersion
        }

        var grades = window.localStorage.getItem("grades")
        if(grades){
          this.grades = JSON.parse(grades)
        }

        var upcomingGrades = window.localStorage.getItem("upcomingGrades")
        if(upcomingGrades){
          this.upcomingGrades = JSON.parse(upcomingGrades)
        }

        var aimedAvg = window.localStorage.getItem("aimedAvg")
        if(aimedAvg){
          this.aimedAvg = aimedAvg
        }

        var nextWeight = window.localStorage.getItem("nextWeight")
        if(nextWeight){
          this.nextWeight = nextWeight
        }

        var school = window.localStorage.getItem("school")
        if(school){
          this.school = school
        }

        var subjects = window.localStorage.getItem("subjects")
        if(subjects){
          this.subjects = JSON.parse(subjects)
        }

        var absencePeriods = window.localStorage.getItem("absencePeriods")
        if(absencePeriods){
          this.absencePeriods = JSON.parse(absencePeriods)
        }

        var events = window.localStorage.getItem("events")
        if(events){
          this.events = JSON.parse(events)
        }

        var user = window.localStorage.getItem("user")
        if(user){
          this.user = JSON.parse(user)
        }

        var auth = window.localStorage.getItem("auth")
        if(auth){
          this.auth = auth
        }

        var credentialsToken = window.localStorage.getItem("credentialsToken")
        if(credentialsToken){
          this.credentialsToken = credentialsToken
        }

        var schoolClass = window.localStorage.getItem("schoolClass")
        if(schoolClass){
          this.schoolClass = JSON.parse(schoolClass)
        }

        var acceptConditions = window.localStorage.getItem("acceptConditions")
        if(acceptConditions != "false"){
          this.acceptConditions = acceptConditions
        }
        else{
          this.acceptConditions = false
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
    watch: {
      bookstoreDialog(val){
        if(val){
          _paq.push(['trackGoal', 4]);
        }
      },
      lastVersion(val){
        window.localStorage.setItem("version", val)
      },
      grades(val){
        window.localStorage.setItem("grades", JSON.stringify(val))
        if(val.length == 0){
          this.nextWeight = 1
          window.localStorage.removeItem("grades")
        }
      },
      upcomingGrades(val){
        window.localStorage.setItem("upcomingGrades", JSON.stringify(val))
        if(val.length == 0){
          window.localStorage.removeItem("upcomingGrades")
        }
      },
      aimedAvg(val){
        window.localStorage.setItem("aimedAvg", val)
      },
      nextWeight(val){
        window.localStorage.setItem("nextWeight", val)
      },
      school(val){
        window.localStorage.setItem("school", val)
      },
      subjects(val){
        window.localStorage.setItem("subjects", JSON.stringify(val))
        if(val.length == 0){
          this.fetchSchools()
          window.localStorage.removeItem("subjects")
        }
      },
      absencePeriods(val){
        window.localStorage.setItem("absencePeriods", JSON.stringify(val))
        if(val.length == 0){
          window.localStorage.removeItem("absencePeriods")
        }
      },
      events(val){
        window.localStorage.setItem("events", JSON.stringify(val))
        if(val.length == 0){
          window.localStorage.removeItem("events")
        }
      },
      user(val){
        window.localStorage.setItem("user", JSON.stringify(val))
        if(JSON.stringify(val) == '{}'){
          window.localStorage.removeItem("user")
        }
      },
      schoolClass(val){
        window.localStorage.setItem("schoolClass", JSON.stringify(val))
        this.calculateBirthday()
        if(JSON.stringify(val) == '{}'){
          window.localStorage.removeItem("schoolClass")
        }
      },
      acceptConditions(val){
        window.localStorage.setItem("acceptConditions", val)
        if(!val){
          window.localStorage.removeItem("acceptConditions")
        }
      },
      credentialsToken(val){
        window.localStorage.setItem("credentialsToken", val)
        if(val == null){
          window.localStorage.removeItem("credentialsToken")
        }
      },

      auth(val){
        window.localStorage.setItem("auth", val)
        if(val == null){
          window.localStorage.removeItem("auth")
        }
      },
    }
});
