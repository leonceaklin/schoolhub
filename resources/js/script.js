var themeColor = "#308efc"

const app = new Vue({
    el: "#app",
    vuetify: new Vuetify({
      theme: {
        themes: {
          light: {
            primary: themeColor
          },
          dark: {
            primary: themeColor
          }
        }
      }
    }),
    data(){
      return {
        newGradeValue: null,
        newGradeWeight: 1,
        grades: [],
        upcomingGrades: [],
        aimedAvg: 6,
        nextWeight: 1,
        subjects: [],
        username: null,
        password: null,
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

        birthdayToday: [],

        schools: [],

        now: new Date(),

        fetchingData: false,
        loginError: false,

        fetchInterval: null,


        subjectsDialog: false,
        absencesDialog: false,
        eventsDialog: false,
        bookstoreDialog: false,
        acceptConditions: false,

        installationBanner: false,
        installationPrompt: null,


        eventsCalendarMode: "day",
        eventsCalendarModes: [
          {text: "Tag", value: "day"},
          {text: "Woche", value: "week"},
          {text: "Monat", value: "month"},
        ],
        eventsCalendarDate: (new Date()).toString("Y-m-d"),

        eventsTab: 0,

        chartOptions: {
          chartArea:{left:25,top:10,bottom:10,right:10},
          vAxis: {minValue: 0, maxValue: 6, ticks: [1,2,3,4,5,6]},
          hAxis: {textPosition: 'none'},
          legend: {position: 'none'},
          pointSize: 5,
          series: {
            0: {
              color: "#ffffff"
            }
          },
          trendlines: {
            0: {
              type: 'polynomial',
              degree: 3,
              visibleInLegend: true,
            }
          },
          animation: {
            startup: true,
            duration: 500,
            easing: 'out',
          },
        },

        shareData: {
          title: "SchoolHub – Notenrechner und mehr",
          url: "https://schoolhub.ch?mtm_campaign=Share"
        },

        eventDetailDialog: false,
        focusedEvent: null,

        gradeHeaders: [
          {value: 'name', text: "Name"},
          {value: 'value', text: "Wert"},
          {value: 'weight', text: "Gewichtung"},
          {value: 'uid', text: "Entfernen"}
        ],

        subjectGradeHeaders: [
          {value: 'name', text: "Name"},
          {value: 'value', text: "Wert"},
          {value: 'weight', text: "Gewichtung"},
        ],

        upcomingGradeHeaders: [
          {value: 'name', text: "Name"},
          {value: 'weight', text: "Gewichtung"},
          {value: 'date', text: "Datum"}
        ],

        absenceHeaders: [
          {value: 'start', text: "Datum"},
          {value: 'reason', text: "Grund"},
          {value: 'points', text: "Punkte"}
        ],
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
      avg(){
        return this.sum/this.count
      },
      sum(){
        return this.getSum(this.grades)
      },
      count(){
        return this.getCount(this.grades)
      },
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
      nextEvent(){
        return this.upcomingEvents.filter((e) => {
          return (e.type != "appointment" && !e.deleted)
        })[0]
      },
      alteredEvents(){
        return this.upcomingEvents.filter((e) => e.altered)
      },
      calendarEvents(){
        var events = this.events
        var calendarEvents = []
        for(var event of events){
          var calendarEvent = {
            start: event.start,
            end: event.end
          }

          calendarEvent.color = "grey lighten-1"

          var eventName = ""
          var subject = this.subjects.find((s) => {return s.identifier == event.title})
          if(subject){
            eventName = subject.name
          }
          else{
            eventName = event.title
          }

          if(event.type == "test"){
            calendarEvent.color = "primary"
            eventName = event.grade.name
          }

          if(event.room && event.room.key != 0){
            eventName += " – "+event.room.label;
          }
          calendarEvent.name = eventName;


          if(event.altered){
            calendarEvent.color = "orange"
          }

          if(event.deleted){
            calendarEvent.color = "red"
          }

          calendarEvent.event = event

          var dateDiff = new Date(event.end.replaceAll("-", "/"))
           - new Date(event.start.replaceAll("-", "/"));

          if(dateDiff < 360000 * 24 && dateDiff > 0){
            calendarEvents.push(calendarEvent)
          }


        }
        return calendarEvents
      },
      tests(){
        return this.upcomingEvents.filter((e) => e.type == "test")
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
      mainGradeHeaders(){
        if(this.grades[0].name){
          return this.gradeHeaders
        }
        else{
          return this.gradeHeaders.filter((h) => h.value != "name" && h.value != "date")
        }
      },
      nextGrade(){
        if(this.grades.length == 0 && this.nextWeight && this.aimedAvg){
          return false
        }
        return (((this.aimedAvg*1.0-0.25)*(this.count + this.nextWeight*1.0)) - this.sum*1.0)/this.nextWeight*1.0
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
      getCount(grades){
        var count = 0
        for(var grade of grades){
          if(grade.value){
            count+=(1.0*grade.weight)
          }
        }
        return count
      },
      getSum(grades){
        var sum = 0
        for(var grade of grades){
          if(grade.value){
            sum+=(grade.value*grade.weight)
          }
        }
        return sum
      },
      getAvg(grades){
        return this.getSum(grades)/this.getCount(grades)
      },
      addGrade(){
        if(!this.newGradeValue){
          return
        }
        this.grades.push({
          value: this.newGradeValue,
          weight: this.newGradeWeight,
          uid: Math.floor(Math.random()*1000000)
        })

        this.newGradeValue = null
        this.newGradeWeight = 1
        this.$refs.valueInput.focus()
      },
      removeGrade(uid){
        this.grades = this.grades.filter((gr) => {
          return gr.uid != uid
        })
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
      async fetchSchools(){
        var response = await axios.get(`${this.apiUrl}/schools`)
        if(response.data.data && response.data.data.schools){
          this.schools = response.data.data.schools
        }
      },
      
      relativeTimeDays(value, locale) {
        const date = new Date(value);
        const deltaDays = (date.getTime() - Date.now()) / (1000 * 3600 * 24);
        const formatter = new Intl.RelativeTimeFormat(locale);
        var days = Math.round(deltaDays);
        if(days != 0){
          return formatter.format(days, 'days');
        }
        return "Heute";
      },
      onEventClick(e){
        this.focusedEvent = e.event.event
        this.eventDetailDialog = true
      },
      async shareApp(){
        try {
          await navigator.share(this.shareData)
        } catch(err) {
        }
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
      onChartReady(chart){

        let observer = new MutationObserver((e) => {
          var svg = chart.container.getElementsByTagName('svg')[0]
          if(svg){
            checkGradient = svg.getElementById("chart-gradient")
            if(checkGradient){
              return;
            }

            var gradient = document.getElementById("chart-gradient").cloneNode(true)
            svg.prepend(gradient)
            var path = svg.getElementsByTagName('path')[0]
            if(!path) return
            path.setAttribute('fill', 'url(#chart-gradient) #ffffff')
          }
        })

        observer.observe(chart.container, {childList: true, subtree: true})
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
      absencesDialog(val){
        if(val){
          _paq.push(['trackGoal', 3]);
        }
      },
      eventsDialog(val){
        if(val){
          _paq.push(['trackGoal', 2]);
        }
      },
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
